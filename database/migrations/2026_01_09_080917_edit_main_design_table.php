<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | REMOVE OLD
            |--------------------------------------------------------------------------
            */
            $table->dropColumn([
                'social_links',
                'link_type',
                'link_color',
                'link_text_color',
                'link_hover_color',
                'link_hover_text_color',
            ]);

            /*
            |--------------------------------------------------------------------------
            | SOCIAL
            |--------------------------------------------------------------------------
            */
            $table->string('social_instagram')->nullable();
            $table->string('social_fb')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_x')->nullable();
            $table->string('social_whatsapp')->nullable();

            /*
            |--------------------------------------------------------------------------
            | FONTS
            |--------------------------------------------------------------------------
            */
            $table->string('font_type_2')->nullable();
            $table->string('font_type_3')->nullable();

            /*
            |--------------------------------------------------------------------------
            | INPUT
            |--------------------------------------------------------------------------
            */
            $table->string('input_type')->nullable();
            $table->string('input_background_color')->nullable();
            $table->string('input_text_color')->nullable();

            $table->tinyInteger('input_border_enabled')->default(0);
            $table->tinyInteger('input_border_width')->default(1);
            $table->string('input_border_color')->nullable();

            $table->integer('input_radius')->default(0);
            $table->string('input_font_weight')->default('400');

            /*
            |--------------------------------------------------------------------------
            | PADDING
            |--------------------------------------------------------------------------
            */
            $table->integer('padding_top')->default(0);
            $table->integer('padding_right')->default(0);
            $table->integer('padding_bottom')->default(0);
            $table->integer('padding_left')->default(0);

            /*
            |--------------------------------------------------------------------------
            | MARGIN
            |--------------------------------------------------------------------------
            */
            $table->integer('margin_top')->default(0);
            $table->integer('margin_right')->default(0);
            $table->integer('margin_bottom')->default(0);
            $table->integer('margin_left')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('main_design', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | DROP NEW
            |--------------------------------------------------------------------------
            */
            $table->dropColumn([
                // social
                'social_instagram',
                'social_fb',
                'social_linkedin',
                'social_tiktok',
                'social_x',
                'social_whatsapp',

                // fonts
                'font__type_3',
                'font_type_2',

                // input
                'input_font_weight',
                'input_radius',
                'input_border_color',
                'input_border_width',
                'input_border_enabled',
                'input_text_color',
                'input_background_color',
                'input_type',

                // padding
                'padding_top',
                'padding_right',
                'padding_bottom',
                'padding_left',

                // margin
                'margin_top',
                'margin_right',
                'margin_bottom',
                'margin_left',
            ]);

            /*
            |--------------------------------------------------------------------------
            | RESTORE OLD
            |--------------------------------------------------------------------------
            */
            $table->string('social_links')->nullable();

            $table->string('link_type')->nullable();
            $table->string('link_color')->nullable();
            $table->string('link_text_color')->nullable();
            $table->string('link_hover_color')->nullable();
            $table->string('link_hover_text_color')->nullable();
        });
    }
};
