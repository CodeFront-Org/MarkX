<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\PdfService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupCommand extends Command
{
    protected $signature = 'app:cleanup';
    protected $description = 'Clean up soft-deleted records and temporary files';

    public function handle(PdfService $pdfService)
    {
        $this->info('Starting cleanup...');

        // Clean up temporary PDF files
        $pdfService->cleanup();
        $this->info('Cleaned up temporary PDF files.');

        $this->info('Cleanup completed successfully.');
        
        return Command::SUCCESS;
    }
}