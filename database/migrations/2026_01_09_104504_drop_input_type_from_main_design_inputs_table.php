<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('main_design_inputs', function (Blueprint $table) {
            // bezpečně – jen když existuje
            if (Schema::hasColumn('main_design_inputs', 'input_type')) {
                $table->dropColumn('input_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('main_design_inputs', function (Blueprint $table) {
            // vrátit zpět
            if (!Schema::hasColumn('main_design_inputs', 'input_type')) {
                $table->string('input_type')->nullable()->after('id');
            }
        });
    }
};
