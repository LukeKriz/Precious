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
        Schema::create('page_designs', function (Blueprint $table) {
            $table->id();
            $table->integer('layout_id');
            $table->string('primary_color');
            $table->string('secondary_color');
            $table->string('tertiary_color');
            $table->string('quaternary_color');
            $table->string('quinary_color');
            $table->string('button_type');
            $table->string('button_color');
            $table->string('button_text_color');
            $table->string('button_hover_color');
            $table->string('button_hover_text_color');
            $table->string('link_type');
            $table->string('link_color');
            $table->string('link_text_color');
            $table->string('link_hover_color');
            $table->string('link_hover_text_color');
            $table->string('logo_url');
            $table->string('font_type');
            $table->string('social_links');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_designs');
    }
};
