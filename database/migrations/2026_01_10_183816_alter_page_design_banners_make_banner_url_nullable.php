<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            // ✅ allow empty slider items
            $table->string('banner_url', 255)->nullable()->change();

            // (volitelné, ale doporučené) – ať máš konzistentní defaulty pro nový “prázdný banner”
            // Pokud už tyhle sloupce máš a mají defaulty, klidně tuhle část smaž.
            if (Schema::hasColumn('page_design_banners', 'banner_type')) {
                $table->string('banner_type', 255)->default('static')->change();
            }
            if (Schema::hasColumn('page_design_banners', 'banner_count')) {
                $table->integer('banner_count')->default(1)->change();
            }
            if (Schema::hasColumn('page_design_banners', 'sort_order')) {
                $table->integer('sort_order')->default(1)->change();
            }
            if (Schema::hasColumn('page_design_banners', 'button_enabled')) {
                $table->tinyInteger('button_enabled')->default(0)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            // revert – banner_url zpět NOT NULL
            $table->string('banner_url', 255)->nullable(false)->change();

            // defaulty nechávám bez revertu (často nechceš “rozbíjet” existující data)
        });
    }
};
