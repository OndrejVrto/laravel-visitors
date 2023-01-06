<?php

declare(strict_types=1);

namespace OndrejVrto\Visitors\Traits;

trait ModelSettings {
    public function getConnectionName(): ?string {
        $nameConnection = config('visitors.eloquent_connection');
        return is_string($nameConnection)
            ? $nameConnection
            : parent::getConnectionName();
    }

    public function getTable(): string {
        $nameTable = config("visitors.table_names.$this->configTableName");
        return is_string($nameTable)
            ? $nameTable
            : parent::getTable();
    }
}
