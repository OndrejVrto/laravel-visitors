<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use OndrejVrto\Visitors\Traits\VisitorsSettings;

class VisitorsInfo extends VisitorsBase {
    use MassPrunable;
    use VisitorsSettings;

    protected $primaryKey = 'info_id';

    protected $casts = [
        'count_rows'   => 'integer',
        'from'         => 'datetime',
        'to'           => 'datetime',
        'last_data_id' => 'integer',
        'updated_at'   => 'datetime',
    ];

    protected function tableConfigKey(): string {
        return 'info';
    }

    public function prunable(): Builder {
        return static::query()
            ->whereDate('updated_at', '<=', now()->subDays($this->numberDaysStatistics()));
    }
}
