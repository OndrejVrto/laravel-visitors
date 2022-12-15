<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Commands;

use Illuminate\Console\Command;

class VisitorsFreshCommand extends Command {
    public $signature = 'visitors:fresh';

    public $description = '[OV - Laravel visitors] Fresh statistics data.';

    public function handle(): int {
        $this->error('TODO: Implement this command');
        $this->newLine();

        return self::SUCCESS;
    }
}
