<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_design_header', function (Blueprint $table) {
            $table->id();

            // vazba na main_design (1:1)
            $table->unsignedBigInteger('main_design_id')->unique();

            $table->string('logo_url')->nullable();

            $table->string('background')->nullable();
            $table->string('submenu_background')->nullable();

            $table->string('color')->nullable();
            $table->string('color_hover')->nullable();

            $table->string('submenu_color')->nullable();
            $table->string('submenu_hover')->nullable();

            $table->timestamps();

            $table->foreign('main_design_id')
                ->references('id')
                ->on('main_design')
                ->onDelete('cascade');
        });

        // Přenos dat (logo_url) z main_design -> main_design_header
        if (Schema::hasColumn('main_design', 'logo_url')) {
            $rows = DB::table('main_design')->select('id', 'logo_url')->get();

            foreach ($rows as $r) {
                DB::table('main_design_header')->insert([
                    'main_design_id' => $r->id,
                    'logo_url' => $r->logo_url,
                    // ostatní sloupce zatím null (můžeš klidně doplnit defaulty)
                    'background' => null,
                    'submenu_background' => null,
                    'color' => null,
                    'color_hover' => null,
                    'submenu_color' => null,
                    'submenu_hover' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // odstranění sloupce z main_design
            Schema::table('main_design', function (Blueprint $table) {
                $table->dropColumn('logo_url');
            });
        }
    }

    public function down(): void
    {
        // vrátíme logo_url zpět do main_design
        Schema::table('main_design', function (Blueprint $table) {
            if (!Schema::hasColumn('main_design', 'logo_url')) {
                $table->string('logo_url')->nullable();
            }
        });

        // přeneseme logo_url zpět
        if (Schema::hasTable('main_design_header')) {
            $rows = DB::table('main_design_header')->select('main_design_id', 'logo_url')->get();

            foreach ($rows as $r) {
                DB::table('main_design')
                    ->where('id', $r->main_design_id)
                    ->update(['logo_url' => $r->logo_url]);
            }
        }

        Schema::dropIfExists('main_design_header');
    }
};
