<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design_header', function (Blueprint $table) {
            $table->renameColumn('background', 'header_background');
            $table->renameColumn('color', 'header_color');
            $table->renameColumn('color_hover', 'header_color_hover');
        });
    }

    public function down(): void
    {
        Schema::table('main_design_header', function (Blueprint $table) {
            $table->renameColumn('header_background', 'background');
            $table->renameColumn('header_color', 'color');
            $table->renameColumn('header_color_hover', 'color_hover');
        });
    }
};
