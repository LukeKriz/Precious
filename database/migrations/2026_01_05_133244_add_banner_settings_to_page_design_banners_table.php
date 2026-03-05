<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            $table->string('banner_type')->default('static')->after('banner_url'); // static|slider
            $table->unsignedInteger('banner_count')->default(1)->after('banner_type');
        });
    }

    public function down(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            $table->dropColumn(['banner_type', 'banner_count']);
        });
    }
};
