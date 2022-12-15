<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Commands;

use Illuminate\Console\Command;

class VisitorsCleanCommand extends Command {
    public $signature = 'visitors:clean';

    public $description = '[OV - Laravel visitors] Prune models.';

    public function handle(): int {
        $this->error('TODO: Implement this command');
        $this->newLine();

        return self::SUCCESS;
    }
}
