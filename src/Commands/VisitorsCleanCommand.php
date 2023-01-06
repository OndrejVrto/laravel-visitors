<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Commands;

use Illuminate\Console\Command;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsExpires;

class VisitorsCleanCommand extends Command {
    public $signature = 'visitors:clean';

    public $description = 'Prune old records in the visitors table.';

    public function handle(): int {
        $code = $this->call('model:prune', ['--model' => [VisitorsData::class, VisitorsExpires::class]]);

        if ($code > 0) {
            $this->error('Prune tables error!');
            return self::FAILURE;
        }

        $this->info('Prune tables done!');
        return self::SUCCESS;
    }
}
