<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use OndrejVrto\Visitors\Traffic;
use OndrejVrto\Visitors\Visitor;
use OndrejVrto\Visitors\Statistics;
use Elfsundae\Laravel\FacadePhpdocGenerator;
use OndrejVrto\Visitors\Facades\Traffic as TrafficFacade;
use OndrejVrto\Visitors\Facades\Visitor as VisitorFacade;
use OndrejVrto\Visitors\Facades\Statistics as StatisticsFacade;

FacadePhpdocGenerator::make(Visitor::class)
    ->see(Visitor::class)
    ->updateFacade(VisitorFacade::class);

FacadePhpdocGenerator::make(Traffic::class)
    ->see(Traffic::class)
    ->updateFacade(TrafficFacade::class);

FacadePhpdocGenerator::make(Statistics::class)
    ->see(Statistics::class)
    ->updateFacade(StatisticsFacade::class);
