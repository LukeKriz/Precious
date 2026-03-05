<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('main_design_footer', function (Blueprint $table) {
            // JSON obsah patičky (komponenty po sloupcích)
            $table->json('footer_content')->nullable()->after('footer_columns');
        });
    }

    public function down(): void
    {
        Schema::table('main_design_footer', function (Blueprint $table) {
            $table->dropColumn('footer_content');
        });
    }
};
