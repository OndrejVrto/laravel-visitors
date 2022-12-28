<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Observers;

use OndrejVrto\Visitors\Contracts\Visitable;

class VisitableObserver {
    /**
     * Handle the deleted event for the visitable model.
     */
    public function deleted(Visitable $visitable): void {
        if ($this->removeDataOnDelete($visitable)) {
            $visitable->visitData()->delete();
            $visitable->visitExpires()->delete();
            $visitable->visitDailyGraphs()->delete();
        }
    }

    /**
     * Determine if should remove views on model delete (defaults to true).
     */
    private function removeDataOnDelete(Visitable $visitable): bool {
        return $visitable->removeDataOnDelete ?? true;
    }
}
