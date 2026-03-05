<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->string('button_border_color')
                ->nullable()
                ->after('button_border_width');

            $table->string('button_border_hover_color')
                ->nullable()
                ->after('button_border_color');
        });
    }

    public function down(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->dropColumn([
                'button_border_color',
                'button_border_hover_color',
            ]);
        });
    }
};