<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use OndrejVrto\Visitors\Services\StatisticsGenerator;

class VisitorsFreshCommand extends Command {
    public $signature = 'visitors:fresh';

    public $description = 'Generate fresh traffic and statistics data.';

    public function handle(): int {
        $this->info('Start generate visitors traffic and statistics...');
        $this->newLine();

        try {
            $countRows = (new StatisticsGenerator())->run();
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
            $this->error("Dispatch error");
            return self::FAILURE;
        }

        $this->info("Dispatch queue for $countRows visit data rows done!");
        return self::SUCCESS;
    }
}
