<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            // ✅ Border zap/vyp
            $table->boolean('button_border_enabled')
                ->nullable()
                ->default(false)
                ->after('button_hover_text_color');

            // ✅ Šířka borderu v px
            $table->unsignedTinyInteger('button_border_width')
                ->nullable()
                ->default(1)
                ->after('button_border_enabled');

            // ✅ Font weight (100..900 nebo "normal/bold" – ukládáme jako string)
            $table->string('button_font_weight')
                ->nullable()
                ->default('400')
                ->after('button_border_width');
        });
    }

    public function down(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->dropColumn([
                'button_border_enabled',
                'button_border_width',
                'button_font_weight',
            ]);
        });
    }
};
