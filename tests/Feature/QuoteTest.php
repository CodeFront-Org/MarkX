<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketer_can_create_quote(): void
    {
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        
        $response = $this->actingAs($marketer)->post('/quotes', [
            'title' => 'Test Quote',
            'description' => 'Test Description',
            'amount' => 1000.00,
            'status' => 'pending',
            'valid_until' => now()->addDays(30)->format('Y-m-d')
        ]);

        $response->assertRedirect(route('quotes.index'));
        $this->assertDatabaseHas('quotes', [
            'title' => 'Test Quote',
            'user_id' => $marketer->id
        ]);
    }

    public function test_marketer_can_only_see_their_quotes(): void
    {
        /** @var User&Authenticatable */
        $marketer1 = User::factory()->createOne(['role' => 'marketer']);
        /** @var User&Authenticatable */
        $marketer2 = User::factory()->createOne(['role' => 'marketer']);
        
        $quote1 = Quote::factory()->create(['user_id' => $marketer1->id]);
        $quote2 = Quote::factory()->create(['user_id' => $marketer2->id]);

        $response = $this->actingAs($marketer1)->get('/quotes');
        
        $response->assertSee($quote1->title);
        $response->assertDontSee($quote2->title);
    }

    public function test_manager_can_see_all_quotes(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        
        $quote = Quote::factory()->create(['user_id' => $marketer->id]);

        $response = $this->actingAs($manager)->get('/quotes');
        
        $response->assertSee($quote->title);
    }

    public function test_manager_can_access_reports(): void
    {
        /** @var User&Authenticatable */
        $manager = User::factory()->createOne(['role' => 'manager']);
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);
        
        Quote::factory()->successful()->create(['user_id' => $marketer->id]);

        $response = $this->actingAs($manager)->get('/reports');
        
        $response->assertOk();
        $response->assertViewHas('revenueByMarketer');
    }

    public function test_marketer_cannot_access_reports(): void
    {
        /** @var User&Authenticatable */
        $marketer = User::factory()->createOne(['role' => 'marketer']);

        $response = $this->actingAs($marketer)->get('/reports');
        
        $response->assertForbidden();
    }
}
