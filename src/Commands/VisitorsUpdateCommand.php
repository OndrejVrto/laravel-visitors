<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Commands;

use Illuminate\Console\Command;

class VisitorsUpdateCommand extends Command {
    public $signature = 'visitors:update';

    public $description = '[OV - Laravel visitors] Update statistics for visitors.';

    public function handle(): int {
        $this->error('TODO: Implement this command.');
        $this->newLine();

        return self::SUCCESS;
    }
}
