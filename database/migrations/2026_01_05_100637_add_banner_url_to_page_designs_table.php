<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->string('banner_url')->nullable()->after('banner_height');
        });
    }

    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->dropColumn('banner_url');
        });
    }
};
