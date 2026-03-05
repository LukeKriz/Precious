<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->foreignId('banner_id')
                ->nullable()
                ->after('banner_count')  // uprav si pozici
                ->constrained('page_design_banners')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('banner_id');
        });
    }
};
