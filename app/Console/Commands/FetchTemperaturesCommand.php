<?php

namespace App\Console\Commands;

use App\Jobs\FetchTemperaturesJob;
use Illuminate\Console\Command;

class FetchTemperaturesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-temperatures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calls open meto API and updates temperatures for all delivery items.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        FetchTemperaturesJob::dispatch();

        $this->info('Fetch temperatures job has been dispatched.');

        return self::SUCCESS;
    }
}
