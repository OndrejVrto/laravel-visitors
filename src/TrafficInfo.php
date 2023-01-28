<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsInfo;
use OndrejVrto\Visitors\Data\VisitorsInfoData;

class TrafficInfo {
    public static function get(): VisitorsInfoData {
        $info = VisitorsInfo::query()
            ->latest()
            ->first();

        $lastData = VisitorsData::query()
            ->orderByDesc('visited_at')
            ->first();

        //TODO: rename properties in DTO
        return new VisitorsInfoData(
            count_rows:         $info->getAttribute('count_rows'),
            from:               $info->getAttribute('from'),
            to:                 $info->getAttribute('to'),
            last_data_id:       $info->getAttribute('last_data_id'),
            created_at:         $info->getAttribute('created_at'),
            recent_visit_count: $lastData->getAttribute('data_id') - $info->getAttribute('last_data_id'),
            recent_visit_date:  $lastData->getAttribute('visited_at'),
        );
    }
}
