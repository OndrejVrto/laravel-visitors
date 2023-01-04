<?php

namespace OndrejVrto\Visitors\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use OndrejVrto\Visitors\Enums\VisitorCategory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

abstract class BaseVisitors extends Model {
    use ModelSettings;

    public $timestamps = false;

    public $guarded = [];

    protected ?string $configTableName = null;

    protected $casts = [
        'id'            => 'integer',
        "viewable_type" => 'string',
        "viewable_id"   => 'integer',
    ];

    public function viewable(): MorphTo {
        return $this->morphTo('viewable');
    }

    // public function scopeWhereVisitorCategory(Builder $query, ?VisitorCategory $category = null): Builder {
    //     return $query
    //         ->when(
    //             $category === null,
    //             fn ($q) => $q->whereNull('category'),
    //             fn ($q) => $q->where('category', $category),
    //         );
    // }
}
