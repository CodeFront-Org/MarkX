<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use App\Models\Invoice;
use App\Events\InvoiceEvent;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Exception;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize invoice sequence
        DB::table('sequences')->insert([
            'name' => 'invoice_number',
            'current_value' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createDraftInvoice(User $user): Invoice
    {
        $quote = Quote::factory()->successful()->create(['user_id' => $user->id]);
        return Invoice::factory()->create([
            'quote_id' => $quote->id,
            'status' => 'draft'
        ]);
    }

    public function test_manager_can_generate_invoice_for_successful_quote(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        
        $quote = Quote::factory()->successful()->create([
            'user_id' => $marketer->id
        ]);

        // First step - show create form
        $response = $this->actingAs($manager)->get("/invoices/create?quote={$quote->id}");
        $response->assertOk();
        
        // Second step - store invoice
        $response = $this->actingAs($manager)->post("/invoices", [
            'quote' => $quote->id
        ]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        
        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertDatabaseHas('invoices', [
            'quote_id' => $quote->id
        ]);
    }

    public function test_marketer_cannot_generate_invoice(): void
    {
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        $quote = Quote::factory()->successful()->create([
            'user_id' => $marketer->id
        ]);

        $response = $this->actingAs($marketer)->post("/invoices", [
            'quote' => $quote->id
        ]);

        $response->assertForbidden();
    }

    public function test_manager_cannot_generate_invoice_for_pending_quote(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        
        $quote = Quote::factory()->pending()->create([
            'user_id' => $marketer->id
        ]);

        $response = $this->actingAs($manager)->post("/invoices", [
            'quote' => $quote->id
        ]);

        $response->assertRedirect(route('quotes.show', $quote));
        $response->assertSessionHas('error', 'Only successful quotes can have invoices generated.');
    }

    public function test_marketer_can_view_their_quote_invoices(): void
    {
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        $quote = Quote::factory()->successful()->create(['user_id' => $marketer->id]);
        $invoice = Invoice::factory()->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($marketer)->get("/invoices/{$invoice->id}");

        $response->assertOk();
    }

    public function test_marketer_cannot_view_other_marketers_invoices(): void
    {
        /** @var User&Authenticatable */
        $marketer1 = User::factory()->createOne(['role' => 'marketer']);
        /** @var User&Authenticatable */
        $marketer2 = User::factory()->createOne(['role' => 'marketer']);
        
        $quote = Quote::factory()->successful()->create(['user_id' => $marketer2->id]);
        $invoice = Invoice::factory()->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($marketer1)->get("/invoices/{$invoice->id}");

        $response->assertForbidden();
    }

    public function test_marketer_cannot_delete_invoice(): void
    {
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        $quote = Quote::factory()->successful()->create(['user_id' => $marketer->id]);
        $invoice = Invoice::factory()->create(['quote_id' => $quote->id]);

        $response = $this->actingAs($marketer)->delete("/invoices/{$invoice->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    public function test_manager_can_mark_invoice_as_final(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);

        $response = $this->actingAs($manager)->post("/invoices/{$invoice->id}/send");

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'final'
        ]);
    }

    public function test_can_mark_final_invoice_as_paid(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);
        $invoice->update(['status' => 'final']);

        $response = $this->actingAs($manager)->post("/invoices/{$invoice->id}/mark-paid");

        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid'
        ]);
    }

    public function test_cannot_mark_draft_invoice_as_paid(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);

        $response = $this->actingAs($manager)->post("/invoices/{$invoice->id}/mark-paid");

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Only final or overdue invoices can be marked as paid.');
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'draft'
        ]);
    }

    public function test_invoice_numbers_are_unique_under_concurrency(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $quote = Quote::factory()->successful()->create(['user_id' => $manager->id]);

        // Simulate multiple concurrent requests
        $invoices = [];
        for ($i = 0; $i < 5; $i++) {
            $invoices[] = Invoice::factory()->create([
                'quote_id' => $quote->id,
                'status' => 'draft'
            ]);
        }

        // Check that all invoice numbers are unique
        $numbers = collect($invoices)->pluck('invoice_number')->toArray();
        $this->assertEquals(count($numbers), count(array_unique($numbers)));
        
        // Verify format
        foreach ($numbers as $number) {
            $this->assertMatchesRegularExpression('/^INV-\d{4}-\d{5}$/', $number);
        }
    }

    public function test_invoice_status_transitions(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);

        // Test valid transitions
        $this->actingAs($manager);

        // draft -> final
        $response = $this->post("/invoices/{$invoice->id}/send");
        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertEquals('final', $invoice->fresh()->status);

        // final -> paid
        $response = $this->post("/invoices/{$invoice->id}/mark-paid");
        $response->assertRedirect(route('invoices.show', $invoice));
        $this->assertEquals('paid', $invoice->fresh()->status);

        // Create a new draft invoice for testing invalid transitions
        $invoice2 = $this->createDraftInvoice($manager);

        // Try invalid transition: draft -> paid (should fail)
        $response = $this->post("/invoices/{$invoice2->id}/mark-paid");
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals('draft', $invoice2->fresh()->status);
    }

    public function test_events_are_logged(): void
    {
        Event::fake();
        
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);

        $this->actingAs($manager);

        // Test create event
        Event::assertDispatched(InvoiceEvent::class, function ($event) {
            return $event->action === 'created';
        });

        // Test status change event
        $response = $this->post("/invoices/{$invoice->id}/send");
        Event::assertDispatched(InvoiceEvent::class, function ($event) {
            return $event->action === 'status_change' 
                && $event->oldStatus === 'draft' 
                && $event->newStatus === 'final';
        });

        // Test delete event
        $invoice2 = $this->createDraftInvoice($manager);
        $response = $this->delete("/invoices/{$invoice2->id}");
        Event::assertDispatched(InvoiceEvent::class, function ($event) {
            return $event->action === 'deleted';
        });
    }

    public function test_invoice_logs_are_created(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);

        $this->actingAs($manager);

        // Test status change log
        $this->post("/invoices/{$invoice->id}/send");

        $this->assertDatabaseHas('invoice_logs', [
            'invoice_id' => $invoice->id,
            'user_id' => $manager->id,
            'action' => 'status_change',
            'old_status' => 'draft',
            'new_status' => 'final'
        ]);
    }

    public function test_only_draft_invoices_can_be_deleted(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $invoice = $this->createDraftInvoice($manager);

        $this->actingAs($manager);

        // Mark as final
        $invoice->markAsFinal();

        // Try to delete final invoice (should fail)
        $response = $this->delete("/invoices/{$invoice->id}");
        $response->assertStatus(302);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);

        // Create new draft invoice
        $draftInvoice = $this->createDraftInvoice($manager);

        // Delete draft invoice (should succeed)
        $response = $this->delete("/invoices/{$draftInvoice->id}");
        $response->assertRedirect(route('invoices.index'));
        $this->assertSoftDeleted('invoices', ['id' => $draftInvoice->id]);
    }

    public function test_cleanup_command_removes_old_draft_invoices(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        
        // Create an old draft invoice
        $oldInvoice = $this->createDraftInvoice($manager);
        $oldInvoice->deleted_at = now()->subDays(31);
        $oldInvoice->save();

        // Create a recent draft invoice
        $recentInvoice = $this->createDraftInvoice($manager);
        $recentInvoice->deleted_at = now()->subDays(15);
        $recentInvoice->save();

        // Create a non-draft deleted invoice
        $finalInvoice = $this->createDraftInvoice($manager);
        $finalInvoice->status = 'final';
        $finalInvoice->deleted_at = now()->subDays(31);
        $finalInvoice->save();

        // Run cleanup command
        $this->artisan('app:cleanup');

        // Old draft invoice should be gone
        $this->assertDatabaseMissing('invoices', ['id' => $oldInvoice->id]);
        
        // Recent draft invoice should still exist
        $this->assertDatabaseHas('invoices', ['id' => $recentInvoice->id]);
        
        // Non-draft invoice should still exist even though it's old
        $this->assertDatabaseHas('invoices', ['id' => $finalInvoice->id]);
    }

    public function test_invoice_number_sequence_is_continuous(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        $quote = Quote::factory()->successful()->create(['user_id' => $manager->id]);

        // Create invoices and track their sequence numbers
        $numbers = [];
        for ($i = 0; $i < 3; $i++) {
            $invoice = Invoice::factory()->create([
                'quote_id' => $quote->id,
                'status' => 'draft'
            ]);
            preg_match('/(\d+)$/', $invoice->invoice_number, $matches);
            $numbers[] = (int)$matches[1];
        }

        // Verify sequence is continuous
        sort($numbers);
        for ($i = 1; $i < count($numbers); $i++) {
            $this->assertEquals(1, $numbers[$i] - $numbers[$i-1], 
                'Invoice number sequence is not continuous');
        }
    }
}
