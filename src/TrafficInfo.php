<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsInfo;
use OndrejVrto\Visitors\Data\VisitorsInfoData;

class TrafficInfo {
    public static function get(): ?VisitorsInfoData {
        $info = VisitorsInfo::query()
            ->latest()
            ->first();

        $lastData = VisitorsData::query()
            ->orderByDesc('visited_at')
            ->first();

        if (null === $info || null === $lastData ) {
            return null;
        }

        /** @var integer */
        $count_rows = $info->getAttribute('count_rows');
        /** @var Carbon */
        $from = $info->getAttribute('from');
        /** @var Carbon */
        $to = $info->getAttribute('to');
        /** @var integer */
        $last_data_id = $info->getAttribute('last_data_id');
        /** @var Carbon */
        $created_at = $info->getAttribute('created_at');
        /** @var integer */
        $recent_visit_count = $lastData->getAttribute('data_id') - $info->getAttribute('last_data_id');
        /** @var Carbon */
        $recent_visit_date = $lastData->getAttribute('visited_at');

        //TODO: rename properties in DTO
        return new VisitorsInfoData(
            to:                 $to,
            from:               $from,
            created_at:         $created_at,
            count_rows:         $count_rows,
            last_data_id:       $last_data_id,
            recent_visit_date:  $recent_visit_date,
            recent_visit_count: $recent_visit_count,
        );
    }
}
