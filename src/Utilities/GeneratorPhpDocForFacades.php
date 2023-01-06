<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Utilities;

require __DIR__.'/../../vendor/autoload.php';

use OndrejVrto\Visitors\Visit;
use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Statistics;
use Elfsundae\Laravel\FacadePhpdocGenerator;
use OndrejVrto\Visitors\Facades\Visit as VisitFacade;
use OndrejVrto\Visitors\Facades\Traffic as TrafficFacade;
use OndrejVrto\Visitors\Facades\Statistics as StatisticsFacade;

FacadePhpdocGenerator::make(Visit::class)
    ->see(Visit::class)
    ->updateFacade(VisitFacade::class);

FacadePhpdocGenerator::make(Traffic::class)
    ->see(Traffic::class)
    ->updateFacade(TrafficFacade::class);

FacadePhpdocGenerator::make(Statistics::class)
    ->see(Statistics::class)
    ->updateFacade(StatisticsFacade::class);
