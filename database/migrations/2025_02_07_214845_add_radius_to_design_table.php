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
            $table->integer('top_left_radius');
            $table->integer('top_right_radius');
            $table->integer('bottom_left_radius');
            $table->integer('bottom_right_radius');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->dropColumn('top_left_radius');
            $table->dropColumn('top_right_radius');
            $table->dropColumn('bottom_left_radius');
            $table->dropColumn('bottom_right_radius');
        });
    }
};
