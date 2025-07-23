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
            $table->dropColumn('primary_color');
            $table->dropColumn('secondary_color');
            $table->dropColumn('tertiary_color');
            $table->dropColumn('quaternary_color');
            $table->dropColumn('quinary_color');
            $table->dropColumn('button_type');
            $table->dropColumn('button_color');
            $table->dropColumn('button_text_color');
            $table->dropColumn('button_hover_color');
            $table->dropColumn('button_hover_text_color');
            $table->dropColumn('link_type');
            $table->dropColumn('link_color');
            $table->dropColumn('link_text_color');
            $table->dropColumn('link_hover_color');
            $table->dropColumn('link_hover_text_color');
            $table->dropColumn('logo_url');
            $table->dropColumn('font_type');
            $table->dropColumn('social_links');
            $table->dropColumn('top_left_radius');
            $table->dropColumn('top_right_radius');
            $table->dropColumn('bottom_left_radius');
            $table->dropColumn('bottom_right_radius');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_designs', function (Blueprint $table) {
            //
        });
    }
};
