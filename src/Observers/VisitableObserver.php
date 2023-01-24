<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Observers;

use OndrejVrto\Visitors\Contracts\Visitable;

class VisitableObserver {
    /**
     * Handle the deleted event for the visitable model.
     */
    public function deleted(Visitable $visitable): void {
        if ($visitable->getDefaultRemoveDataOnDelete()) {
            $visitable->visitData()->delete();
            $visitable->visitExpires()->delete();
            $visitable->visitTraffic()->delete();
        }
    }
}
