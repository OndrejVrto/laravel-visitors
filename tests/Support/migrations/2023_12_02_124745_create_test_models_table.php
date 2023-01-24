<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void {
        Schema::create('test_models', function (Blueprint $table): void {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('test_models');
    }
};
