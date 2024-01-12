<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FolioService;

class ApplyDailyCharges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'charges:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply daily accommodation charges to active reservations';

    /**
     * The folio service instance.
     *
     * @var FolioService
     */
    protected $folioService;

    /**
     * Create a new command instance.
     *
     * @param FolioService $folioService
     * @return void
     */
    public function __construct(FolioService $folioService)
    {
        parent::__construct();
        $this->folioService = $folioService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting to apply daily charges...');

        try {
            $this->folioService->applyDailyCharges();
            $this->info('Daily charges applied successfully.');
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            return 1; // Returning a non-zero value indicates error
        }

        return 0; // Success
    }
}
