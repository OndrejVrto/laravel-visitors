<?php

// @codeCoverageIgnoreStart

declare(strict_types=1);

namespace OndrejVrto\Visitors\Utilities;

require __DIR__.'/../../vendor/autoload.php';

use OndrejVrto\Visitors\Visit;
use Elfsundae\Laravel\FacadePhpdocGenerator;
use OndrejVrto\Visitors\Facades\Visit as VisitFacade;

FacadePhpdocGenerator::make(Visit::class)
    ->see(Visit::class)
    ->updateFacade(VisitFacade::class);

// @codeCoverageIgnoreEnd
