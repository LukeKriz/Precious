<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('main_design_inputs', function (Blueprint $table) {
            // ✅ přidáme toggle (default OFF nebo ON – doporučuju OFF)
            $table->boolean('input_focus_ring_enabled')
                ->default(false)
                ->after('input_border_color');

            // ✅ pokud už to používáš, nech; pokud neexistuje, přidej podle reality:
            // $table->string('input_focus_ring_color')->nullable()->after('input_focus_ring_enabled');
            // $table->integer('input_focus_ring_width')->default(0)->after('input_focus_ring_color');

            // ✅ smažeme focus_border_color (už nebude potřeba)
            if (Schema::hasColumn('main_design_inputs', 'input_focus_border_color')) {
                $table->dropColumn('input_focus_border_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('main_design_inputs', function (Blueprint $table) {
            if (Schema::hasColumn('main_design_inputs', 'input_focus_ring_enabled')) {
                $table->dropColumn('input_focus_ring_enabled');
            }

            // vrátíme zpět (pokud rollback)
            $table->string('input_focus_border_color')->nullable()->after('input_border_color');
        });
    }
};
