<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Observers;

use OndrejVrto\Visitors\Contracts\Visitable;

class VisitableObserver {
    /**
     * Handle the deleted event for the visitable model.
     */
    public function deleted(Visitable $visitable): void {
        if ($this->removeViewsOnDelete($visitable)) {
            $visitable->visitStatistics()->delete();
        }
    }

    /**
     * Determine if should remove views on model delete (defaults to true).
     */
    private function removeViewsOnDelete(Visitable $visitable): bool {
        return $visitable->removeViewsOnDelete ?? true;
    }
}
