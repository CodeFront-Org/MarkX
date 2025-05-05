<?php

namespace Tests\Feature;

use App\Models\Invoice;
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

    private function createInvoice(): Invoice
    {
        $user = User::factory()->create(['role' => 'manager']);
        $quote = Quote::factory()->successful()->create(['user_id' => $user->id]);
        return Invoice::factory()->create(['quote_id' => $quote->id]);
    }

    private function mockPdfWithOutput(string $output): void
    {
        $pdfMock = Mockery::mock('Barryvdh\DomPDF\PDF');
        $pdfMock->shouldReceive('stream')->andReturn('pdf content');
        $pdfMock->shouldReceive('output')->andReturn($output);
        
        Pdf::shouldReceive('loadView')
            ->with('pdf.invoice', Mockery::any())
            ->andReturn($pdfMock);
    }

    public function test_pdf_generation_retries_on_failure(): void
    {
        $invoice = $this->createInvoice();

        // First two calls will fail, third will succeed
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.invoice', ['invoice' => $invoice])
            ->andThrow(new Exception('PDF generation failed'))
            ->ordered();
        
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.invoice', ['invoice' => $invoice])
            ->andThrow(new Exception('PDF generation failed again'))
            ->ordered();
        
        // Mock successful PDF generation
        $pdfMock = Mockery::mock('Barryvdh\DomPDF\PDF');
        $pdfMock->shouldReceive('stream')->andReturn('pdf content');
        $pdfMock->shouldReceive('output')->andReturn('test content');
        
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.invoice', ['invoice' => $invoice])
            ->andReturn($pdfMock)
            ->ordered();

        $result = $this->pdfService->streamInvoicePdf($invoice);
        
        $this->assertNotNull($result);
    }

    public function test_pdf_generation_fails_after_max_retries(): void
    {
        $invoice = $this->createInvoice();

        Pdf::shouldReceive('loadView')
            ->times(3)
            ->with('pdf.invoice', ['invoice' => $invoice])
            ->andThrow(new Exception('PDF generation failed'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('PDF generation failed after 3 attempts');
        
        $this->pdfService->streamInvoicePdf($invoice);
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

    public function test_pdf_validation_checks_file_content(): void
    {
        $invoice = $this->createInvoice();

        // Mock PDF with empty output
        $pdfMock = Mockery::mock('Barryvdh\DomPDF\PDF');
        $pdfMock->shouldReceive('output')->andReturn('');
        
        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdf.invoice', ['invoice' => $invoice])
            ->andReturn($pdfMock);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Generated PDF file is invalid or empty');

        $this->pdfService->generateInvoicePdf($invoice);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}