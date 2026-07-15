<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use App\Services\PdfService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Exception;
use Mockery;

class PdfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PdfService $pdfService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->pdfService = new PdfService();
    }

    private function createQuote(): Quote
    {
        $user = User::factory()->create(['role' => 'rfq_approver']);
        return Quote::factory()->create(['user_id' => $user->id]);
    }

    public function test_generate_quote_pdf(): void
    {
        $quote = $this->createQuote();
        
        $pdfMock = Mockery::mock('Barryvdh\DomPDF\PDF');
        $pdfMock->shouldReceive('setOptions')->andReturnSelf();
        
        Pdf::shouldReceive('loadView')
            ->once()
            ->andReturn($pdfMock);

        $result = $this->pdfService->generateQuotePdf($quote);
        $this->assertNotNull($result);
    }

    public function test_cleanup_removes_old_temporary_files(): void
    {
        // Create some temp files
        Storage::put('temp/old_file.pdf', 'test content');
        Storage::put('temp/new_file.pdf', 'test content');

        // Set old file to be older than 1 hour
        $oldTime = now()->subHours(2)->timestamp;
        Storage::setVisibility('temp/old_file.pdf', 'public');
        touch(Storage::path('temp/old_file.pdf'), $oldTime);

        // Set new file to be recent
        $newTime = now()->subMinutes(30)->timestamp;
        Storage::setVisibility('temp/new_file.pdf', 'public');
        touch(Storage::path('temp/new_file.pdf'), $newTime);

        // Run cleanup
        $this->pdfService->cleanup();

        // Check results
        $this->assertFalse(Storage::exists('temp/old_file.pdf'));
        $this->assertTrue(Storage::exists('temp/new_file.pdf'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}