<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            // smaž nejdřív banner_url/banner_type/banner_count, banner_id nech
            if (Schema::hasColumn('page_designs', 'banner_url')) $table->dropColumn('banner_url');
            if (Schema::hasColumn('page_designs', 'banner_type')) $table->dropColumn('banner_type');
            if (Schema::hasColumn('page_designs', 'banner_count')) $table->dropColumn('banner_count');
        });
    }

    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->string('banner_url')->nullable();
            $table->string('banner_type')->default('static');
            $table->unsignedInteger('banner_count')->default(1);
        });
    }
};
