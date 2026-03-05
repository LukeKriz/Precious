<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\MainDesign;
use App\Models\MainDesignColor;
use App\Models\MainDesignButton;
use App\Models\MainDesignFont;
use App\Models\MainDesignRadius;
use App\Models\MainDesignInput;
use App\Models\MainDesignIndentation;
use App\Models\MainDesignSocial;
use App\Models\MainDesignFooter;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MainDesignController extends Controller
{
    // GET /admin/main-design
    public function index()
    {
        return response()->json(MainDesign::first());
    }

    // POST /admin/main-design
    public function store(Request $request)
    {
        $data = $request->validate([
            // colors
            'primary_color'     => 'nullable|string|max:255',
            'secondary_color'   => 'nullable|string|max:255',
            'tertiary_color'    => 'nullable|string|max:255',
            'quaternary_color'  => 'nullable|string|max:255',
            'quinary_color'     => 'nullable|string|max:255',

            // fonts
            'font_type'   => 'nullable|string|max:255',
            'font_type_2' => 'nullable|string|max:255',
            'font_type_3' => 'nullable|string|max:255',

            // header ✅ (do main_design_header)
            'logo_url'            => 'nullable|string|max:255',
            'logo_width'          => 'nullable|integer|min:0|max:2000',
            'logo_height'         => 'nullable|integer|min:0|max:2000',
            'header_background'   => 'nullable|string|max:255',
            'header_color'        => 'nullable|string|max:255',
            'header_color_hover'  => 'nullable|string|max:255',
            'submenu_background'  => 'nullable|string|max:255',
            'submenu_color'       => 'nullable|string|max:255',
            'submenu_hover'       => 'nullable|string|max:255',

            // ✅ footer (do main_design_footer přes footer_id)
            'footer_background' => 'nullable|string|max:255',
            'footer_color'      => 'nullable|string|max:255',
            'footer_columns'    => 'nullable|integer|min:1|max:6',
            'footer_content'    => 'nullable|array',

            // buttons
            'button_type'               => 'nullable|string|max:255',
            'button_color'              => 'nullable|string|max:255',
            'button_text_color'         => 'nullable|string|max:255',
            'button_hover_color'        => 'nullable|string|max:255',
            'button_hover_text_color'   => 'nullable|string|max:255',
            'button_border_enabled'     => 'nullable|boolean',
            'button_border_width'       => 'nullable|integer|min:0|max:20',
            'button_font_weight'        => 'nullable|string|max:255',
            'button_border_color'       => 'nullable|string|max:255',
            'button_border_hover_color' => 'nullable|string|max:255',

            // radius
            'top_left_radius'     => 'nullable|integer|min:0|max:9999',
            'top_right_radius'    => 'nullable|integer|min:0|max:9999',
            'bottom_left_radius'  => 'nullable|integer|min:0|max:9999',
            'bottom_right_radius' => 'nullable|integer|min:0|max:9999',

            // socials
            'social_instagram' => 'nullable|string|max:255',
            'social_fb'        => 'nullable|string|max:255',
            'social_linkedin'  => 'nullable|string|max:255',
            'social_tiktok'    => 'nullable|string|max:255',
            'social_x'         => 'nullable|string|max:255',
            'social_whatsapp'  => 'nullable|string|max:255',

            // inputs
            'input_background_color'   => 'nullable|string|max:255',
            'input_text_color'         => 'nullable|string|max:255',
            'input_border_enabled'     => 'nullable|boolean',
            'input_border_width'       => 'nullable|integer|min:0|max:20',
            'input_border_color'       => 'nullable|string|max:255',
            'input_radius'             => 'nullable|integer|min:0|max:9999',
            'input_font_weight'        => 'nullable|string|max:255',

            'input_hover_border_color' => 'nullable|string|max:255',
            'input_focus_ring_enabled' => 'nullable|boolean',
            'input_focus_ring_color'   => 'nullable|string|max:255',
            'input_focus_ring_width'   => 'nullable|integer|min:0|max:30',

            // indentation
            'padding_top'    => 'nullable|integer|min:0|max:9999',
            'padding_right'  => 'nullable|integer|min:0|max:9999',
            'padding_bottom' => 'nullable|integer|min:0|max:9999',
            'padding_left'   => 'nullable|integer|min:0|max:9999',

            'margin_top'     => 'nullable|integer|min:0|max:9999',
            'margin_right'   => 'nullable|integer|min:0|max:9999',
            'margin_bottom'  => 'nullable|integer|min:0|max:9999',
            'margin_left'    => 'nullable|integer|min:0|max:9999',
        ]);

        // vždy existuje jen 1 design
        $design = MainDesign::first();
        if (!$design) {
            $design = MainDesign::create([]);
        }

        // ✅ HEADER upsert (do main_design_header)
        $headerPayload = array_intersect_key($data, array_flip([
            'logo_url',
            'logo_width',
            'logo_height',
            'header_background',
            'header_color',
            'header_color_hover',
            'submenu_background',
            'submenu_color',
            'submenu_hover',
        ]));

        if (!empty($headerPayload)) {
            $design->header()->updateOrCreate(
                ['main_design_id' => $design->id],
                $headerPayload
            );
        }

        // Mapy FK -> modely
        $fkMap = [
            'colors_id'      => MainDesignColor::class,
            'buttons_id'     => MainDesignButton::class,
            'fonts_id'       => MainDesignFont::class,
            'radius_id'      => MainDesignRadius::class,
            'inputs_id'      => MainDesignInput::class,
            'indentation_id' => MainDesignIndentation::class,
            'socials_id'     => MainDesignSocial::class,
            'footer_id'      => MainDesignFooter::class, // ✅ NEW
        ];

        // Rozdělení payloadu do bloků
        $colorsPayload = array_intersect_key($data, array_flip([
            'primary_color',
            'secondary_color',
            'tertiary_color',
            'quaternary_color',
            'quinary_color'
        ]));

        $buttonsPayload = array_intersect_key($data, array_flip([
            'button_type',
            'button_color',
            'button_text_color',
            'button_hover_color',
            'button_hover_text_color',
            'button_border_enabled',
            'button_border_width',
            'button_border_color',
            'button_border_hover_color',
            'button_font_weight'
        ]));

        $fontsPayload = array_intersect_key($data, array_flip([
            'font_type',
            'font_type_2',
            'font_type_3'
        ]));

        $radiusPayload = array_intersect_key($data, array_flip([
            'top_left_radius',
            'top_right_radius',
            'bottom_left_radius',
            'bottom_right_radius'
        ]));

        $inputsPayload = array_intersect_key($data, array_flip([
            'input_background_color',
            'input_text_color',
            'input_border_enabled',
            'input_border_width',
            'input_border_color',
            'input_radius',
            'input_font_weight',
            'input_hover_border_color',
            'input_focus_ring_enabled',
            'input_focus_ring_color',
            'input_focus_ring_width'
        ]));

        $indentPayload = array_intersect_key($data, array_flip([
            'padding_top',
            'padding_right',
            'padding_bottom',
            'padding_left',
            'margin_top',
            'margin_right',
            'margin_bottom',
            'margin_left'
        ]));

        $socialsPayload = array_intersect_key($data, array_flip([
            'social_instagram',
            'social_fb',
            'social_linkedin',
            'social_tiktok',
            'social_x',
            'social_whatsapp'
        ]));

        // ✅ footer payload (do main_design_footer přes footer_id)
        $footerPayload = array_intersect_key($data, array_flip([
            'footer_background',
            'footer_color',
            'footer_columns',
            'footer_content',
        ]));

        if (!empty($footerPayload)) {
            // pokud už je footer_id, updatuj existující
            if ($design->footer_id) {
                $footer = MainDesignFooter::find($design->footer_id);
                if ($footer) {
                    $footer->fill($footerPayload);
                    $footer->save();
                } else {
                    // footer_id je rozbité -> vytvoř nový
                    $footer = MainDesignFooter::create($footerPayload);
                    $design->footer_id = $footer->id;
                }
            } else {
                // vytvoř nový footer a naváž do main_design.footer_id
                $footer = MainDesignFooter::create($footerPayload);
                $design->footer_id = $footer->id;
            }
        }

        // helper: vytvoř/aktualizuj child row a zapiš FK do main_design
        $upsertChild = function (string $fkColumn, array $payload) use (&$design, $fkMap) {
            if (empty($payload)) return;

            $modelClass = $fkMap[$fkColumn] ?? null;
            if (!$modelClass) return;

            $childId = $design->{$fkColumn};

            if ($childId) {
                $child = $modelClass::find($childId);
                if ($child) {
                    $child->fill($payload);
                    $child->save();
                    return;
                }
            }

            $child = $modelClass::create($payload);
            $design->{$fkColumn} = $child->id;
        };

        $upsertChild('colors_id', $colorsPayload);
        $upsertChild('buttons_id', $buttonsPayload);
        $upsertChild('fonts_id', $fontsPayload);
        $upsertChild('radius_id', $radiusPayload);
        $upsertChild('inputs_id', $inputsPayload);
        $upsertChild('indentation_id', $indentPayload);
        $upsertChild('socials_id', $socialsPayload);
        $upsertChild('footer_id', $footerPayload); // ✅ NEW

        $design->save();

        // ✅ vrať čerstvý design včetně relací + appends
        $fresh = MainDesign::query()->whereKey($design->id)->first();

        if ($request->wantsJson()) {
            return response()->json($fresh);
        }

        return back();
    }

    // POST /admin/main-design/logo
 

   public function uploadLogo(Request $request)
{
    $request->validate([
        'logo' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:4096',

        // rozměry můžeš poslat z adminu (px)
        'logo_width'  => 'nullable|integer|min:1|max:2000',
        'logo_height' => 'nullable|integer|min:1|max:2000',
    ]);

    $design = MainDesign::first() ?? MainDesign::create([]);

    // zajistit header řádek
    $header = $design->header()->first();
    if (!$header) {
        $header = $design->header()->create(['main_design_id' => $design->id]);
    }

    // smazat staré logo
    if ($header->logo_url && Str::startsWith($header->logo_url, '/storage/')) {
        $oldPath = Str::after($header->logo_url, '/storage/');
        if (Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }
    }

    $file = $request->file('logo');
    $ext  = strtolower($file->getClientOriginalExtension());

    // cílové rozměry z requestu (můžou být null)
    $targetW = $request->input('logo_width');
    $targetH = $request->input('logo_height');

    // když uživatel nepošle nic, nastav rozumný default (klidně změň)
    // tím zajistíš, že se vždy komprimuje na "logo velikost"
    $targetW = $targetW ?: 260;
    $targetH = $targetH ?: 80;

    // =========================================================
    // SVG: uložit bez úprav (nepřepočítáváme)
    // =========================================================
    if ($ext === 'svg') {
        $filename = 'logo_' . time() . '.svg';
        $path = $file->storeAs('design', $filename, 'public');

        $header->logo_url = '/storage/' . $path;
        $header->logo_width = $targetW;
        $header->logo_height = $targetH;
        $header->save();

        return response()->json([
            'logo_url' => $header->logo_url,
            'logo_width' => $header->logo_width,
            'logo_height' => $header->logo_height,
        ]);
    }

    // =========================================================
    // Raster (png/jpg/jpeg/webp): resize + komprese do WEBP
    // =========================================================
    $manager = new ImageManager(new Driver());
    $image = $manager->read($file->getRealPath());

    // zmenší do boxu targetW x targetH (bez ořezu), zachová poměr stran
    // scaleDown nedovolí zvětšování (upscale), jen zmenšuje
    $image->scaleDown(width: $targetW, height: $targetH);

    // výsledný reálný rozměr po zachování poměru stran
    $finalW = $image->width();
    $finalH = $image->height();

    // ulož jako webp (kvalita 80 = super kompromis)
    $filename = 'logo_' . time() . '.webp';
    $path = 'design/' . $filename;

    $encoded = (string) $image->toWebp(80);
    Storage::disk('public')->put($path, $encoded);

    // uložit do DB
    $header->logo_url = '/storage/' . $path;
    $header->logo_width = $finalW;
    $header->logo_height = $finalH;
    $header->save();

    return response()->json([
        'logo_url' => $header->logo_url,
        'logo_width' => $header->logo_width,
        'logo_height' => $header->logo_height,
    ]);
}
    // DELETE /admin/main-design/logo

    public function deleteLogo()
    {
        $design = MainDesign::first();
        if (!$design) {
            return response()->json(['ok' => true]);
        }

        $header = $design->header()->first();
        if (!$header) {
            return response()->json(['ok' => true]);
        }

        if ($header->logo_url && Str::startsWith($header->logo_url, '/storage/')) {
            $path = Str::after($header->logo_url, '/storage/');
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $header->logo_url = null;
        $header->save();

        return response()->json(['ok' => true]);
    }


    public function uploadFooterImage(Request $request)
    {
        $request->validate([
            'image' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        $file = $request->file('image');

        $filename = 'footer_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('design/footer', $filename, 'public');

        // ✅ stejně jako logo
        $url = '/storage/' . $path;

        return response()->json([
            'url'  => $url,
            'path' => $path,
        ]);
    }


    public function themeCss()
    {
        $design = MainDesign::find(1);

        $font = $design?->font_type ?: 'Inter';

        $allowed = [
            'Inter',
            'Roboto',
            'Roboto Condensed',
            'Open Sans',
            'Lato',
            'Source Sans 3',
            'Montserrat',
            'Poppins',
            'Nunito',
            'DM Sans',
            'Raleway',
        ];

        if (!in_array($font, $allowed, true)) {
            $font = 'Inter';
        }

        $familyQuery = str_replace(' ', '+', $font);

        $googleCss = "https://fonts.googleapis.com/css2?family={$familyQuery}:wght@100;200;300;400;500;600;700;800;900&display=swap";
        $fallback = "system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif";

        $css = <<<CSS
@import url('{$googleCss}');

:root{
  --font-family: '{$font}', {$fallback};
}

html, body, .font-sans {
  font-family: var(--font-family) !important;
}
CSS;

        return response($css, 200, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
