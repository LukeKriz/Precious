<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->unsignedBigInteger('footer_id')->nullable()->after('socials_id');

            $table->foreign('footer_id')
                ->references('id')
                ->on('main_design_footer')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('main_design', function (Blueprint $table) {
            $table->dropForeign(['footer_id']);
            $table->dropColumn('footer_id');
        });
    }
};
