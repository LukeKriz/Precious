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
        Schema::create('main_design', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->nullable(true);
            $table->string('secondary_color')->nullable(true);
            $table->string('tertiary_color')->nullable(true);
            $table->string('quaternary_color')->nullable(true);
            $table->string('quinary_color')->nullable(true);
            $table->string('button_type')->nullable(true);
            $table->string('button_color')->nullable(true);
            $table->string('button_text_color')->nullable(true);
            $table->string('button_hover_color')->nullable(true);
            $table->string('button_hover_text_color')->nullable(true);
            $table->string('link_type')->nullable(true);
            $table->string('link_color')->nullable(true);
            $table->string('link_text_color')->nullable(true);
            $table->string('link_hover_color')->nullable(true);
            $table->string('link_hover_text_color')->nullable(true);
            $table->string('logo_url')->nullable(true);
            $table->string('font_type')->nullable(true);
            $table->string('social_links')->nullable(true);
            $table->integer('top_left_radius')->nullable(true);
            $table->integer('top_right_radius')->nullable(true);
            $table->integer('bottom_left_radius')->nullable(true);
            $table->integer('bottom_right_radius')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_design');
    }
};
