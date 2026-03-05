<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->string('page_background')->nullable()->after('indentation_id');
        });
    }

    public function down(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->dropColumn('page_background');
        });
    }
};
