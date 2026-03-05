<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // projdi všechny page_designs
        $designs = DB::table('page_designs')->get();

        foreach ($designs as $d) {
            // 1) přenes banner_type/banner_count do všech banner rows té stránky (pokud existují)
            DB::table('page_design_banners')
                ->where('page_design_id', $d->id)
                ->update([
                    'banner_type' => $d->banner_type ?? 'static',
                    'banner_count' => $d->banner_count ?? 1,
                    'updated_at' => now(),
                ]);

            // 2) pokud je to STATIC a má banner_url v page_designs, vytvoř/nahraď řádek v page_design_banners se sort_order=1
            $type = $d->banner_type ?? 'static';

            if ($type === 'static' && !empty($d->banner_url)) {
                // najdi existující row sort_order=1, nebo vytvoř
                $row = DB::table('page_design_banners')
                    ->where('page_design_id', $d->id)
                    ->orderBy('sort_order')
                    ->first();

                if ($row) {
                    DB::table('page_design_banners')
                        ->where('id', $row->id)
                        ->update([
                            'banner_url' => $d->banner_url,
                            'banner_type' => 'static',
                            'banner_count' => 1,
                            'updated_at' => now(),
                        ]);

                    $bannerId = $row->id;
                } else {
                    $bannerId = DB::table('page_design_banners')->insertGetId([
                        'page_design_id' => $d->id,
                        'banner_url' => $d->banner_url,
                        'sort_order' => 1,
                        'banner_type' => 'static',
                        'banner_count' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // nastav banner_id v page_designs
                DB::table('page_designs')->where('id', $d->id)->update([
                    'banner_id' => $bannerId,
                ]);
            }

            // 3) pokud je to SLIDER: nastav banner_id na první banner row podle sort_order (pokud existuje)
            if ($type === 'slider') {
                $first = DB::table('page_design_banners')
                    ->where('page_design_id', $d->id)
                    ->orderBy('sort_order')
                    ->first();

                if ($first) {
                    DB::table('page_designs')->where('id', $d->id)->update([
                        'banner_id' => $first->id,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // reverz je složitější – nepřepisujeme zpět (většinou se nevrací)
    }
};
