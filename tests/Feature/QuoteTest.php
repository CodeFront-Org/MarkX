<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketer_can_create_quote(): void
    {
        /** @var User&Authenticatable */
        $processor = User::factory()->createOne(['role' => 'rfq_processor']);
        
        Storage::fake('public');
        $file = UploadedFile::fake()->create('rfq_document.pdf', 100);

        $response = $this->actingAs($processor)->post('/quotes', [
            'title' => 'Test Quote',
            'description' => 'Test Description',
            'valid_until' => now()->addDays(30)->format('Y-m-d'),
            'total_rfq_items' => 1,
            'items' => [
                [
                    'item' => 'Product A',
                    'quantity' => 10,
                    'price' => 100,
                    'approved' => 0
                ]
            ],
            'files' => [$file],
            'descriptions' => ['Test file description']
        ]);

        $response->assertRedirect(route('quotes.index'));
        $this->assertDatabaseHas('quotes', [
            'title' => 'Test Quote',
            'user_id' => $processor->id
        ]);
    }

    public function test_marketer_can_only_see_their_quotes(): void
    {
        /** @var User&Authenticatable */
        $processor1 = User::factory()->createOne(['role' => 'rfq_processor']);
        /** @var User&Authenticatable */
        $processor2 = User::factory()->createOne(['role' => 'rfq_processor']);
        
        $quote1 = Quote::factory()->create(['user_id' => $processor1->id]);
        $quote2 = Quote::factory()->create(['user_id' => $processor2->id]);

        $response = $this->actingAs($processor1)->get('/quotes');
        
        $response->assertSee($quote1->title);
        $response->assertDontSee($quote2->title);
    }

    public function test_manager_can_see_all_quotes(): void
    {
        /** @var User&Authenticatable */
        $approver = User::factory()->createOne(['role' => 'rfq_approver']);
        /** @var User&Authenticatable */
        $processor = User::factory()->createOne(['role' => 'rfq_processor']);
        
        $quote = Quote::factory()->create(['user_id' => $processor->id]);

        $response = $this->actingAs($approver)->get('/quotes');
        
        $response->assertSee($quote->title);
    }

    public function test_manager_can_access_reports(): void
    {
        /** @var User&Authenticatable */
        $approver = User::factory()->createOne(['role' => 'rfq_approver']);
        /** @var User&Authenticatable */
        $processor = User::factory()->createOne(['role' => 'rfq_processor']);
        
        Quote::factory()->successful()->create(['user_id' => $processor->id]);

        $response = $this->actingAs($approver)->get('/reports');
        
        $response->assertOk();
        $response->assertViewHas('rfqProcessorStats');
    }

    public function test_marketer_cannot_access_reports(): void
    {
        /** @var User&Authenticatable */
        $processor = User::factory()->createOne(['role' => 'rfq_processor']);

        $response = $this->actingAs($processor)->get('/reports');
        
        $response->assertForbidden();
    }

    public function test_can_filter_product_reports_by_status_and_days(): void
    {
        /** @var User&Authenticatable */
        $approver = User::factory()->createOne(['role' => 'rfq_approver']);

        // Create a quote that was submitted to customer 25 days ago
        $quote1 = Quote::factory()->create([
            'title' => 'Quote 25 Days Old',
            'status' => 'pending_customer',
            'submitted_to_customer_at' => now()->subDays(25),
        ]);
        QuoteItem::create([
            'quote_id' => $quote1->id,
            'item' => 'Product X',
            'quantity' => 5,
            'price' => 100,
            'approved' => 1
        ]);

        // Create a quote that was submitted to customer 5 days ago
        $quote2 = Quote::factory()->create([
            'title' => 'Quote 5 Days Old',
            'status' => 'pending_customer',
            'submitted_to_customer_at' => now()->subDays(5),
        ]);
        QuoteItem::create([
            'quote_id' => $quote2->id,
            'item' => 'Product Y',
            'quantity' => 2,
            'price' => 50,
            'approved' => 1
        ]);

        // Filter product reports for Awaiting Customer Response (pending_customer) between 20 and 30 days
        $response = $this->actingAs($approver)->get('/product-reports?' . http_build_query([
            'quote_status' => 'pending_customer',
            'days_min' => 20,
            'days_max' => 30
        ]));

        $response->assertSee($quote1->title);
        $response->assertDontSee($quote2->title);
    }
}
