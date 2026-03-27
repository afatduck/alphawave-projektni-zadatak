<?php

namespace App\Console\Commands;

use App\Jobs\FetchAllTemperaturesJob;
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
        FetchAllTemperaturesJob::dispatch();

        $this->info('Fetch all temperatures job has been dispatched.');

        return self::SUCCESS;
    }
}
