<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('page_design_banners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_design_id')->constrained('page_designs')->cascadeOnDelete();
            $table->string('banner_url');
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->index(['page_design_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_design_banners');
    }
};
