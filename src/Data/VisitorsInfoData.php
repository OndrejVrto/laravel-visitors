<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Data;

use Carbon\Carbon;

class VisitorsInfoData {
    public function __construct(
        public readonly int    $count_rows,
        public readonly Carbon $from,
        public readonly Carbon $to,
        public readonly int    $last_data_id,
        public readonly Carbon $created_at,
        public readonly int    $recent_visit_count,
        public readonly Carbon $recent_visit_date,
    ) {
    }
}
