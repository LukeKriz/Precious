<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            // miniatura
            $table->string('thumbnail_url')->nullable()->after('banner_url');

            // texty banneru
            $table->string('heading_h1')->nullable()->after('thumbnail_url');
            $table->string('heading_h2')->nullable()->after('heading_h1');

            // tlačítko
            $table->boolean('button_enabled')->default(false)->after('heading_h2');
            $table->string('button_text')->nullable()->after('button_enabled');

            // připraveno do budoucna
            $table->string('button_url')->nullable()->after('button_text');
        });
    }

    public function down(): void
    {
        Schema::table('page_design_banners', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail_url',
                'heading_h1',
                'heading_h2',
                'button_enabled',
                'button_text',
                'button_url',
            ]);
        });
    }
};
