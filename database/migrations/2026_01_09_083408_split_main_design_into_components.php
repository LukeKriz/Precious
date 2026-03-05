<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | COMPONENT TABLES
        |--------------------------------------------------------------------------
        */

        Schema::create('main_design_colors', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('tertiary_color')->nullable();
            $table->string('quaternary_color')->nullable();
            $table->string('quinary_color')->nullable();
            $table->timestamps();
        });

        Schema::create('main_design_buttons', function (Blueprint $table) {
            $table->id();
            $table->string('button_type')->nullable();
            $table->string('button_color')->nullable();
            $table->string('button_text_color')->nullable();
            $table->string('button_hover_color')->nullable();
            $table->string('button_hover_text_color')->nullable();
            $table->tinyInteger('button_border_enabled')->default(0);
            $table->tinyInteger('button_border_width')->default(1);
            $table->string('button_border_color')->nullable();
            $table->string('button_border_hover_color')->nullable();
            $table->string('button_font_weight')->default('400');
            $table->timestamps();
        });

        Schema::create('main_design_fonts', function (Blueprint $table) {
            $table->id();
            $table->string('font_type')->nullable();
            $table->string('font_type_2')->nullable();
            $table->string('font_type_3')->nullable();
            $table->timestamps();
        });

        Schema::create('main_design_radius', function (Blueprint $table) {
            $table->id();
            $table->integer('top_left_radius')->nullable();
            $table->integer('top_right_radius')->nullable();
            $table->integer('bottom_left_radius')->nullable();
            $table->integer('bottom_right_radius')->nullable();
            $table->timestamps();
        });

        Schema::create('main_design_socials', function (Blueprint $table) {
            $table->id();
            $table->string('social_instagram')->nullable();
            $table->string('social_fb')->nullable();
            $table->string('social_linkedin')->nullable();
            $table->string('social_tiktok')->nullable();
            $table->string('social_x')->nullable();
            $table->string('social_whatsapp')->nullable();
            $table->timestamps();
        });

        Schema::create('main_design_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('input_type')->nullable();
            $table->string('input_background_color')->nullable();
            $table->string('input_text_color')->nullable();
            $table->tinyInteger('input_border_enabled')->default(0);
            $table->tinyInteger('input_border_width')->default(1);
            $table->string('input_border_color')->nullable();
            $table->integer('input_radius')->default(0);
            $table->string('input_font_weight')->default('400');
            $table->timestamps();
        });

        Schema::create('main_design_indentation', function (Blueprint $table) {
            $table->id();
            $table->integer('padding_top')->default(0);
            $table->integer('padding_right')->default(0);
            $table->integer('padding_bottom')->default(0);
            $table->integer('padding_left')->default(0);
            $table->integer('margin_top')->default(0);
            $table->integer('margin_right')->default(0);
            $table->integer('margin_bottom')->default(0);
            $table->integer('margin_left')->default(0);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | ADD FK COLUMNS TO main_design
        |--------------------------------------------------------------------------
        */

        Schema::table('main_design', function (Blueprint $table) {
            $table->unsignedBigInteger('colors_id')->nullable()->after('id');
            $table->unsignedBigInteger('buttons_id')->nullable();
            $table->unsignedBigInteger('fonts_id')->nullable();
            $table->unsignedBigInteger('radius_id')->nullable();
            $table->unsignedBigInteger('socials_id')->nullable();
            $table->unsignedBigInteger('inputs_id')->nullable();
            $table->unsignedBigInteger('indentation_id')->nullable();
        });

        Schema::table('main_design', function (Blueprint $table) {
            $table->foreign('colors_id')->references('id')->on('main_design_colors')->nullOnDelete();
            $table->foreign('buttons_id')->references('id')->on('main_design_buttons')->nullOnDelete();
            $table->foreign('fonts_id')->references('id')->on('main_design_fonts')->nullOnDelete();
            $table->foreign('radius_id')->references('id')->on('main_design_radius')->nullOnDelete();
            $table->foreign('socials_id')->references('id')->on('main_design_socials')->nullOnDelete();
            $table->foreign('inputs_id')->references('id')->on('main_design_inputs')->nullOnDelete();
            $table->foreign('indentation_id')->references('id')->on('main_design_indentation')->nullOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | BACKFILL EXISTING DATA
        |--------------------------------------------------------------------------
        */

        $designs = DB::table('main_design')->get();

        foreach ($designs as $d) {
            $now = now();

            $colorsId = DB::table('main_design_colors')->insertGetId([
                'primary_color' => $d->primary_color,
                'secondary_color' => $d->secondary_color,
                'tertiary_color' => $d->tertiary_color,
                'quaternary_color' => $d->quaternary_color,
                'quinary_color' => $d->quinary_color,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $buttonsId = DB::table('main_design_buttons')->insertGetId([
                'button_type' => $d->button_type,
                'button_color' => $d->button_color,
                'button_text_color' => $d->button_text_color,
                'button_hover_color' => $d->button_hover_color,
                'button_hover_text_color' => $d->button_hover_text_color,
                'button_border_enabled' => $d->button_border_enabled,
                'button_border_width' => $d->button_border_width,
                'button_border_color' => $d->button_border_color,
                'button_border_hover_color' => $d->button_border_hover_color,
                'button_font_weight' => $d->button_font_weight,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $fontsId = DB::table('main_design_fonts')->insertGetId([
                'font_type' => $d->font_type,
                'font_type_2' => $d->font_type_2,
                'font_type_3' => $d->font_type_3,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $radiusId = DB::table('main_design_radius')->insertGetId([
                'top_left_radius' => $d->top_left_radius,
                'top_right_radius' => $d->top_right_radius,
                'bottom_left_radius' => $d->bottom_left_radius,
                'bottom_right_radius' => $d->bottom_right_radius,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $socialsId = DB::table('main_design_socials')->insertGetId([
                'social_instagram' => $d->social_instagram,
                'social_fb' => $d->social_fb,
                'social_linkedin' => $d->social_linkedin,
                'social_tiktok' => $d->social_tiktok,
                'social_x' => $d->social_x,
                'social_whatsapp' => $d->social_whatsapp,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $inputsId = DB::table('main_design_inputs')->insertGetId([
                'input_type' => $d->input_type,
                'input_background_color' => $d->input_background_color,
                'input_text_color' => $d->input_text_color,
                'input_border_enabled' => $d->input_border_enabled,
                'input_border_width' => $d->input_border_width,
                'input_border_color' => $d->input_border_color,
                'input_radius' => $d->input_radius,
                'input_font_weight' => $d->input_font_weight,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $indentId = DB::table('main_design_indentation')->insertGetId([
                'padding_top' => $d->padding_top,
                'padding_right' => $d->padding_right,
                'padding_bottom' => $d->padding_bottom,
                'padding_left' => $d->padding_left,
                'margin_top' => $d->margin_top,
                'margin_right' => $d->margin_right,
                'margin_bottom' => $d->margin_bottom,
                'margin_left' => $d->margin_left,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('main_design')->where('id', $d->id)->update([
                'colors_id' => $colorsId,
                'buttons_id' => $buttonsId,
                'fonts_id' => $fontsId,
                'radius_id' => $radiusId,
                'socials_id' => $socialsId,
                'inputs_id' => $inputsId,
                'indentation_id' => $indentId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | DROP OLD COLUMNS (logo_url stays!)
        |--------------------------------------------------------------------------
        */

        Schema::table('main_design', function (Blueprint $table) {
            $table->dropColumn([
                'primary_color','secondary_color','tertiary_color','quaternary_color','quinary_color',
                'button_type','button_color','button_text_color','button_hover_color','button_hover_text_color',
                'button_border_enabled','button_border_width','button_border_color','button_border_hover_color','button_font_weight',
                'font_type','font_type_2','font_type_3',
                'top_left_radius','top_right_radius','bottom_left_radius','bottom_right_radius',
                'social_instagram','social_fb','social_linkedin','social_tiktok','social_x','social_whatsapp',
                'input_type','input_background_color','input_text_color','input_border_enabled','input_border_width','input_border_color','input_radius','input_font_weight',
                'padding_top','padding_right','padding_bottom','padding_left',
                'margin_top','margin_right','margin_bottom','margin_left',
            ]);
        });
    }

    public function down(): void
    {
        // rollback nedoporučuji – je to strukturální změna
    }
};
