<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use OndrejVrto\Visitors\Traits\VisitorsSettings;

return new class extends Migration {
    use VisitorsSettings;

    public function up(): void {

        // if ('mysql' !== config("database.connections.".$this->getConnection().".driver")) {
        //     throw new Exception('Error: The visit package tables were not migrated. Database driver must by "mysql" type.');
        // }

        Schema::create($this->defaultVisitorsNameTable('info'), function (Blueprint $table) {
            $table->tinyIncrements('info_id');

            $table->unsignedMediumInteger('count_rows');
            $table->timestamp('from');
            $table->timestamp('to');
            $table->unsignedMediumInteger("last_data_id");

            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create($this->defaultVisitorsNameTable('expires'), function (Blueprint $table) {
            $table->mediumIncrements('expires_id');

            $table->string("viewable_type", 60);
            $table->unsignedInteger("viewable_id");
            $table->unsignedTinyInteger('category');  // ENUM
            $table->ipAddress('ip_address')->nullable();
            $table->unique(
                ["viewable_type", "viewable_id", "ip_address", 'category'],
                'visitors_expires_viewable_ip_address_category_unique'
            );

            $table->timestamp('expires_at')->useCurrent();
        });

        Schema::create($this->defaultVisitorsNameTable('data'), function (Blueprint $table) {
            $table->integerIncrements('data_id');

            $table->string("viewable_type", 60);
            $table->unsignedInteger("viewable_id");
            $table->boolean('is_crawler');
            $table->unsignedTinyInteger('category');  // ENUM
            $table->index(
                ["viewable_type", "viewable_id", 'category', 'is_crawler'],
                'visitors_data_viewable_category_crawler_index'
            );

            $table->string('language', 15)->nullable();
            $table->unsignedTinyInteger('operating_system');  // ENUM

            $table->timestamp('visited_at')->useCurrent();
        });

        Schema::create($this->defaultVisitorsNameTable('traffic'), function (Blueprint $table) {
            $table->mediumIncrements('traffic_id');

            $table->string("viewable_type", 60)->nullable();
            $table->unsignedInteger("viewable_id")->nullable();
            $table->boolean('is_crawler')->nullable();
            $table->unsignedTinyInteger('category')->nullable();  // ENUM

            $table->unique(
                ["viewable_type", "viewable_id", 'category', 'is_crawler'],
                'visitors_traffic_viewable_category_crawler_unique'
            );

            $table->unsignedMediumInteger('visit_total');
            $table->unsignedSmallInteger('visit_last_1_day');
            $table->unsignedSmallInteger('visit_last_7_days');
            $table->unsignedMediumInteger('visit_last_30_days');
            $table->unsignedMediumInteger('visit_last_365_days');

            $table->unsignedSmallInteger('day_maximum');
            $table->json('daily_visits');

            $table->json('sumar_languages');
            $table->json('sumar_operating_systems');

            $table->mediumText('svg_graph')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists($this->defaultVisitorsNameTable('info'));
        Schema::dropIfExists($this->defaultVisitorsNameTable('data'));
        Schema::dropIfExists($this->defaultVisitorsNameTable('expires'));
        Schema::dropIfExists($this->defaultVisitorsNameTable('traffic'));
    }
};
