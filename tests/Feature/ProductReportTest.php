<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_reports_can_be_filtered_by_days_in_status(): void
    {
        $user = User::factory()->create(['role' => 'rfq_processor']);

        $quoteOld = Quote::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Pending Quote',
            'status' => 'pending_customer',
            'submitted_to_customer_at' => now()->subDays(25),
        ]);
        $itemOld = QuoteItem::create([
            'quote_id' => $quoteOld->id,
            'item' => 'Widget Old',
            'quantity' => 1,
            'price' => 100,
            'approved' => 0,
        ]);

        $quoteNew = Quote::factory()->create([
            'user_id' => $user->id,
            'title' => 'New Pending Quote',
            'status' => 'pending_customer',
            'submitted_to_customer_at' => now()->subDays(5),
        ]);
        $itemNew = QuoteItem::create([
            'quote_id' => $quoteNew->id,
            'item' => 'Widget New',
            'quantity' => 1,
            'price' => 100,
            'approved' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('product-reports.index', [
            'quote_status' => 'pending_customer',
            'days_min' => 20,
            'days_max' => 30,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Widget Old');
        $response->assertDontSee('Widget New');
    }

    public function test_product_reports_export_filters_by_days_in_status(): void
    {
        $user = User::factory()->create(['role' => 'rfq_processor']);

        $quoteOld = Quote::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Pending Quote Export',
            'status' => 'pending_customer',
            'submitted_to_customer_at' => now()->subDays(25),
        ]);
        QuoteItem::create([
            'quote_id' => $quoteOld->id,
            'item' => 'Widget Old Export',
            'quantity' => 2,
            'price' => 50,
            'approved' => 0,
        ]);

        $quoteNew = Quote::factory()->create([
            'user_id' => $user->id,
            'title' => 'New Pending Quote Export',
            'status' => 'pending_customer',
            'submitted_to_customer_at' => now()->subDays(5),
        ]);
        QuoteItem::create([
            'quote_id' => $quoteNew->id,
            'item' => 'Widget New Export',
            'quantity' => 1,
            'price' => 50,
            'approved' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('product-reports.export', [
            'quote_status' => 'pending_customer',
            'days_min' => 20,
            'days_max' => 30,
            'format' => 'csv',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('Widget Old Export', $content);
        $this->assertStringNotContainsString('Widget New Export', $content);
    }
}
