<?php

namespace Tests\Feature;

use App\Models\ApprovalChainStep;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    private function processor(): User
    {
        return User::factory()->createOne(['role' => 'rfq_processor']);
    }

    private function approver(string $name): User
    {
        return User::factory()->createOne(['role' => 'rfq_approver', 'name' => $name]);
    }

    private function pendingQuote(User $owner): Quote
    {
        return Quote::factory()->create([
            'user_id' => $owner->id,
            'status' => 'pending_manager',
        ]);
    }

    /**
     * Build the global ordered chain from the given approvers, in order.
     */
    private function buildChain(User ...$approvers): void
    {
        foreach ($approvers as $i => $approver) {
            ApprovalChainStep::create([
                'user_id' => $approver->id,
                'position' => $i + 1,
            ]);
        }
    }

    // ---------------------------------------------------------------------
    // Single-step (no chain configured) — legacy behaviour
    // ---------------------------------------------------------------------

    public function test_with_no_chain_any_approver_finalizes_in_one_step(): void
    {
        /** @var User&Authenticatable */
        $approver = $this->approver('Alice');
        $quote = $this->pendingQuote($this->processor());

        $response = $this->actingAs($approver)->post(route('quotes.approve', $quote));

        $response->assertRedirect(route('quotes.show', $quote));
        $this->assertSame('pending_customer', $quote->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // Sequential multi-approver chain
    // ---------------------------------------------------------------------

    public function test_chain_advances_one_approver_at_a_time_and_finalizes_on_last(): void
    {
        /** @var User&Authenticatable */
        $alice = $this->approver('Alice');
        /** @var User&Authenticatable */
        $bob = $this->approver('Bob');
        /** @var User&Authenticatable */
        $carol = $this->approver('Carol');
        $this->buildChain($alice, $bob, $carol);

        $quote = $this->pendingQuote($this->processor());

        // Step 1: Alice approves -> still pending, awaiting Bob.
        $this->actingAs($alice)->post(route('quotes.approve', $quote))->assertRedirect();
        $this->assertSame('pending_manager', $quote->fresh()->status);
        $this->assertTrue($quote->fresh()->nextApprover()->is($bob));

        // Step 2: Bob approves -> still pending, awaiting Carol.
        $this->actingAs($bob)->post(route('quotes.approve', $quote))->assertRedirect();
        $this->assertSame('pending_manager', $quote->fresh()->status);
        $this->assertTrue($quote->fresh()->nextApprover()->is($carol));

        // Step 3: Carol (last) approves -> finalized.
        $this->actingAs($carol)->post(route('quotes.approve', $quote))->assertRedirect();
        $fresh = $quote->fresh();
        $this->assertSame('pending_customer', $fresh->status);
        $this->assertSame($carol->id, $fresh->approved_by);
        $this->assertNotNull($fresh->approved_at);
        $this->assertNotNull($fresh->submitted_to_customer_at);

        // One approval record per approver.
        $this->assertDatabaseCount('quote_approvals', 3);
    }

    public function test_out_of_turn_approver_is_forbidden(): void
    {
        /** @var User&Authenticatable */
        $alice = $this->approver('Alice');
        /** @var User&Authenticatable */
        $bob = $this->approver('Bob');
        $this->buildChain($alice, $bob);

        $quote = $this->pendingQuote($this->processor());

        // It is Alice's turn, so Bob may not approve yet.
        $this->actingAs($bob)->post(route('quotes.approve', $quote))->assertForbidden();
        $this->assertSame('pending_manager', $quote->fresh()->status);
        $this->assertDatabaseCount('quote_approvals', 0);
    }

    public function test_same_approver_cannot_approve_twice(): void
    {
        /** @var User&Authenticatable */
        $alice = $this->approver('Alice');
        /** @var User&Authenticatable */
        $bob = $this->approver('Bob');
        $this->buildChain($alice, $bob);

        $quote = $this->pendingQuote($this->processor());

        $this->actingAs($alice)->post(route('quotes.approve', $quote))->assertRedirect();
        // Now it is Bob's turn; Alice acting again is out of turn.
        $this->actingAs($alice)->post(route('quotes.approve', $quote))->assertForbidden();
        $this->assertDatabaseCount('quote_approvals', 1);
    }

    // ---------------------------------------------------------------------
    // Rejection kills the chain
    // ---------------------------------------------------------------------

    public function test_rejection_by_current_approver_kills_the_chain(): void
    {
        /** @var User&Authenticatable */
        $alice = $this->approver('Alice');
        /** @var User&Authenticatable */
        $bob = $this->approver('Bob');
        $this->buildChain($alice, $bob);

        $quote = $this->pendingQuote($this->processor());

        $this->actingAs($alice)->post(route('quotes.reject', $quote), [
            'rejection_reason' => 'credit_limit',
        ])->assertRedirect();

        $this->assertSame('rejected', $quote->fresh()->status);
        $this->assertDatabaseHas('quote_approvals', [
            'quote_id' => $quote->id,
            'user_id' => $alice->id,
            'action' => 'rejected',
        ]);
    }

    public function test_out_of_turn_approver_cannot_reject(): void
    {
        /** @var User&Authenticatable */
        $alice = $this->approver('Alice');
        /** @var User&Authenticatable */
        $bob = $this->approver('Bob');
        $this->buildChain($alice, $bob);

        $quote = $this->pendingQuote($this->processor());

        $this->actingAs($bob)->post(route('quotes.reject', $quote), [
            'rejection_reason' => 'credit_limit',
        ])->assertForbidden();

        $this->assertSame('pending_manager', $quote->fresh()->status);
    }

    // ---------------------------------------------------------------------
    // Super admin override
    // ---------------------------------------------------------------------

    public function test_superadmin_override_finalizes_immediately(): void
    {
        /** @var User&Authenticatable */
        $super = User::factory()->createOne(['role' => 'superadmin']);
        $alice = $this->approver('Alice');
        $bob = $this->approver('Bob');
        $this->buildChain($alice, $bob);

        $quote = $this->pendingQuote($this->processor());

        $this->actingAs($super)->post(route('quotes.approve', $quote))->assertRedirect();

        $this->assertSame('pending_customer', $quote->fresh()->status);
        $this->assertDatabaseHas('quote_approvals', [
            'quote_id' => $quote->id,
            'user_id' => $super->id,
            'action' => 'approved',
            'is_override' => true,
        ]);
    }

    // ---------------------------------------------------------------------
    // Return for editing restarts the chain
    // ---------------------------------------------------------------------

    public function test_return_for_editing_clears_approvals_and_restarts_chain(): void
    {
        /** @var User&Authenticatable */
        $lpoAdmin = User::factory()->createOne(['role' => 'lpo_admin']);
        $alice = $this->approver('Alice');
        $bob = $this->approver('Bob');
        $this->buildChain($alice, $bob);

        // A quote that has already collected an approval and reached finance.
        $quote = Quote::factory()->create([
            'user_id' => $this->processor()->id,
            'status' => 'pending_finance',
        ]);
        $quote->approvals()->create(['user_id' => $alice->id, 'action' => 'approved']);

        $this->actingAs($lpoAdmin)->post(route('quotes.return-for-editing', $quote), [
            'return_reason' => 'Please revise the pricing.',
        ])->assertRedirect();

        $fresh = $quote->fresh();
        $this->assertSame('pending_manager', $fresh->status);
        $this->assertDatabaseCount('quote_approvals', 0);
        // Chain starts over from the first approver.
        $this->assertTrue($fresh->nextApprover()->is($alice));
    }

    // ---------------------------------------------------------------------
    // Chain settings management (super admin only)
    // ---------------------------------------------------------------------

    public function test_superadmin_can_manage_the_chain(): void
    {
        /** @var User&Authenticatable */
        $super = User::factory()->createOne(['role' => 'superadmin']);
        $alice = $this->approver('Alice');
        $bob = $this->approver('Bob');

        // Add two approvers (each appended to the end).
        $this->actingAs($super)->post(route('settings.approval-chain.store'), ['user_id' => $alice->id])->assertRedirect();
        $this->actingAs($super)->post(route('settings.approval-chain.store'), ['user_id' => $bob->id])->assertRedirect();

        $this->assertDatabaseHas('approval_chain_steps', ['user_id' => $alice->id, 'position' => 1]);
        $this->assertDatabaseHas('approval_chain_steps', ['user_id' => $bob->id, 'position' => 2]);

        // Move Bob up to the front.
        $bobStep = ApprovalChainStep::where('user_id', $bob->id)->first();
        $this->actingAs($super)->post(route('settings.approval-chain.move-up', $bobStep))->assertRedirect();

        $this->assertSame(1, ApprovalChainStep::where('user_id', $bob->id)->value('position'));
        $this->assertSame(2, ApprovalChainStep::where('user_id', $alice->id)->value('position'));

        // Remove Bob; the gap closes so Alice becomes position 1.
        $this->actingAs($super)->delete(route('settings.approval-chain.destroy', $bobStep))->assertRedirect();

        $this->assertDatabaseMissing('approval_chain_steps', ['user_id' => $bob->id]);
        $this->assertSame(1, ApprovalChainStep::where('user_id', $alice->id)->value('position'));
    }

    public function test_non_superadmin_cannot_manage_the_chain(): void
    {
        /** @var User&Authenticatable */
        $approver = $this->approver('Alice');

        $this->actingAs($approver)->get(route('settings.approval-chain.index'))->assertForbidden();
        $this->actingAs($approver)->post(route('settings.approval-chain.store'), ['user_id' => $approver->id])->assertForbidden();
        $this->assertDatabaseCount('approval_chain_steps', 0);
    }

    public function test_chain_only_accepts_rfq_approvers_and_no_duplicates(): void
    {
        /** @var User&Authenticatable */
        $super = User::factory()->createOne(['role' => 'superadmin']);
        $processor = $this->processor();
        $alice = $this->approver('Alice');

        // A processor is not a valid approver.
        $this->actingAs($super)
            ->post(route('settings.approval-chain.store'), ['user_id' => $processor->id])
            ->assertSessionHasErrors('user_id');

        // Adding the same approver twice is rejected.
        ApprovalChainStep::create(['user_id' => $alice->id, 'position' => 1]);
        $this->actingAs($super)
            ->post(route('settings.approval-chain.store'), ['user_id' => $alice->id])
            ->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('approval_chain_steps', 1);
    }

    // ---------------------------------------------------------------------
    // Super admin has full system access
    // ---------------------------------------------------------------------

    public function test_superadmin_has_full_system_access(): void
    {
        /** @var User&Authenticatable */
        $super = User::factory()->createOne(['role' => 'superadmin']);

        // Areas normally gated to approver/lpo_admin are reachable.
        $this->actingAs($super)->get(route('user-management'))->assertOk();
        $this->actingAs($super)->get(route('reports.index'))->assertOk();
        $this->actingAs($super)->get(route('settings.approval-chain.index'))->assertOk();
    }
}
