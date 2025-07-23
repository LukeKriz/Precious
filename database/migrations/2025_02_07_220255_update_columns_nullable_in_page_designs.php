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
            $table->string('primary_color')->nullable()->change();
            $table->string('secondary_color')->nullable()->change();
            $table->string('tertiary_color')->nullable()->change();
            $table->string('quaternary_color')->nullable()->change();
            $table->string('quinary_color')->nullable()->change();
            $table->string('button_type')->nullable()->change();
            $table->string('button_color')->nullable()->change();
            $table->string('button_text_color')->nullable()->change();
            $table->string('button_hover_color')->nullable()->change();
            $table->string('button_hover_text_color')->nullable()->change();
            $table->string('link_type')->nullable()->change();
            $table->string('link_color')->nullable()->change();
            $table->string('link_text_color')->nullable()->change();
            $table->string('link_hover_color')->nullable()->change();
            $table->string('link_hover_text_color')->nullable()->change();
            $table->string('logo_url')->nullable()->change();
            $table->string('font_type')->nullable()->change();
            $table->string('social_links')->nullable()->change();
            $table->integer('top_left_radius')->nullable()->change();
            $table->integer('top_right_radius')->nullable()->change();
            $table->integer('bottom_left_radius')->nullable()->change();
            $table->integer('bottom_right_radius')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            $table->string('primary_color')->nullable(false)->change();
            $table->string('secondary_color')->nullable(false)->change();
            $table->string('tertiary_color')->nullable(false)->change();
            $table->string('quaternary_color')->nullable(false)->change();
            $table->string('quinary_color')->nullable(false)->change();
            $table->string('button_type')->nullable(false)->change();
            $table->string('button_color')->nullable(false)->change();
            $table->string('button_text_color')->nullable(false)->change();
            $table->string('button_hover_color')->nullable(false)->change();
            $table->string('button_hover_text_color')->nullable(false)->change();
            $table->string('link_type')->nullable(false)->change();
            $table->string('link_color')->nullable(false)->change();
            $table->string('link_text_color')->nullable(false)->change();
            $table->string('link_hover_color')->nullable(false)->change();
            $table->string('link_hover_text_color')->nullable(false)->change();
            $table->string('logo_url')->nullable(false)->change();
            $table->string('font_type')->nullable(false)->change();
            $table->string('social_links')->nullable(false)->change();
            $table->integer('top_left_radius')->nullable(false)->change();
            $table->integer('top_right_radius')->nullable(false)->change();
            $table->integer('bottom_left_radius')->nullable(false)->change();
            $table->integer('bottom_right_radius')->nullable(false)->change();
        });
    }
};
