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
            $table->integer('page_id')->nullable()->after('layout_id');
            $table->integer('subpage_id')->nullable()->after('page_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->dropColumn('page_id');
            $table->dropColumn('subpage_id');
        });
    }
};
