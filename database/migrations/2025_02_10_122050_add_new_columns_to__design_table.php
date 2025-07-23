<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->string('logo_position')->nullable()->after('logo_url');
            $table->string('menu_position')->nullable()->after('logo_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->dropColumn('logo_position');
            $table->dropColumn('menu_position');
            
        });
    }
};
