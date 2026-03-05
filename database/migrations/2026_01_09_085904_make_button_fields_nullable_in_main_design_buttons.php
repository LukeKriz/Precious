<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design_buttons', function (Blueprint $table) {
            $table->boolean('button_border_enabled')->nullable()->change();
            $table->unsignedTinyInteger('button_border_width')->nullable()->change();
            $table->string('button_font_weight', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('main_design_buttons', function (Blueprint $table) {
            $table->boolean('button_border_enabled')->nullable(false)->change();
            $table->unsignedTinyInteger('button_border_width')->nullable(false)->change();
            $table->string('button_font_weight', 255)->nullable(false)->change();
        });
    }
};
