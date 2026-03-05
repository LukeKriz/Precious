<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            $table->string('site_name')->nullable();
            $table->string('base_url')->nullable();

            $table->string('default_title')->nullable();
            $table->text('default_description')->nullable();

            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable(); // path/url

            // icons
            $table->string('favicon_ico')->nullable();    // /storage/icons/favicon.ico
            $table->string('favicon_apple')->nullable();  // /storage/icons/apple-touch-icon.png
            $table->string('favicon_pwa')->nullable();  

            // robots global
            $table->boolean('site_noindex')->default(false);

            // analytics
            $table->string('ga4_id')->nullable(); // G-XXXX
            $table->string('gtm_id')->nullable(); // GTM-XXXX

            // custom head
            $table->longText('meta_head_custom')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
