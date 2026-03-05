<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('main_design_inputs', function (Blueprint $table) {
            // hover border
            if (!Schema::hasColumn('main_design_inputs', 'input_hover_border_color')) {
                $table->string('input_hover_border_color')->nullable()->after('input_border_color');
            }

            // focus border
            if (!Schema::hasColumn('main_design_inputs', 'input_focus_border_color')) {
                $table->string('input_focus_border_color')->nullable()->after('input_hover_border_color');
            }

            // focus ring (barva + šířka)
            if (!Schema::hasColumn('main_design_inputs', 'input_focus_ring_color')) {
                $table->string('input_focus_ring_color')->nullable()->after('input_focus_border_color');
            }

            if (!Schema::hasColumn('main_design_inputs', 'input_focus_ring_width')) {
                $table->unsignedTinyInteger('input_focus_ring_width')
                    ->nullable()
                    ->default(3)
                    ->after('input_focus_ring_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('main_design_inputs', function (Blueprint $table) {
            $cols = [
                'input_hover_border_color',
                'input_focus_border_color',
                'input_focus_ring_color',
                'input_focus_ring_width',
            ];

            foreach ($cols as $c) {
                if (Schema::hasColumn('main_design_inputs', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
