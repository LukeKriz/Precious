<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('main_design_footer', function (Blueprint $table) {
            $table->id();

            $table->string('footer_background')->nullable(); // hex nebo null
            $table->string('footer_color')->nullable();      // text color
            $table->unsignedTinyInteger('footer_columns')->nullable(); // 1..4 (doporučuju)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_design_footer');
    }
};
