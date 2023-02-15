<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OndrejVrto\Visitors\Contracts\Visitable;
use OndrejVrto\Visitors\Models\VisitorsData;
use OndrejVrto\Visitors\Models\VisitorsInfo;
use OndrejVrto\Visitors\Data\VisitorsInfoData;
use OndrejVrto\Visitors\Builder\TrafficListQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSummaryQueryBuilder;
use OndrejVrto\Visitors\Builder\TrafficSingleModelQueryBuilder;

final class Traffic {
    /** @param Visitable|class-string|Visitable[]|array<class-string>|null $models */
    public function list(Visitable|string|array|null $models = null): TrafficListQueryBuilder {
        return (new TrafficListQueryBuilder())
            ->addModels($models);
    }

    public function single(Visitable&Model $model): TrafficSingleModelQueryBuilder {
        return (new TrafficSingleModelQueryBuilder())
            ->forModel($model);
    }

    public function summary(): TrafficSummaryQueryBuilder {
        return (new TrafficSummaryQueryBuilder());
    }

    public function info(): ?VisitorsInfoData {
        $info = VisitorsInfo::query()
            ->latest()
            ->first();

        if ( ! $info instanceof Model) {
            return null;
        }

        $lastData = VisitorsData::query()
            ->orderByDesc('visited_at')
            ->first();

        if ( ! $lastData instanceof Model) {
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
