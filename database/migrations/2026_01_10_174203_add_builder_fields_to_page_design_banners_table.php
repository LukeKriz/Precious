<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            $table->string('bg_type', 20)->default('image'); // image|color
            $table->string('bg_color')->nullable();
            $table->string('overlay_color')->nullable();
            $table->unsignedInteger('overlay_opacity')->nullable(); // 0-100

            $table->string('h1_color')->nullable();
            $table->unsignedInteger('h1_size')->nullable();

            $table->string('h2_color')->nullable();
            $table->unsignedInteger('h2_size')->nullable();

            $table->unsignedInteger('thumbnail_size')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            $table->dropColumn([
                'bg_type',
                'bg_color',
                'overlay_color',
                'overlay_opacity',
                'h1_color',
                'h1_size',
                'h2_color',
                'h2_size',
                'thumbnail_size',
            ]);
        });
    }
};
