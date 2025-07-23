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
            $table->integer('header_height')->default(0)->after('layout_id');
            $table->integer('banner_height')->default(0)->after('header_height');
            $table->integer('category_height')->default(0)->after('banner_height');
            $table->integer('category_weight')->default(0)->after('category_height');
            $table->integer('footer_height')->default(0)->after('category_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->dropColumn('header_height');
            $table->dropColumn('banner_height');
            $table->dropColumn('category_height');
            $table->dropColumn('category_weight');
            $table->dropColumn('footer_height');
        });
    }
};
