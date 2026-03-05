<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Intervention v3 (stejně jako používáš jinde)
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first() ?: SiteSetting::create([]);
        return inertia('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function save(Request $request)
    {
        $settings = SiteSetting::first() ?: SiteSetting::create([]);

        $data = $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'string', 'max:255'],

            'default_title' => ['nullable', 'string', 'max:255'],
            'default_description' => ['nullable', 'string'],

            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],

            // ✅ nové 3 sloupce
            'favicon_ico' => ['nullable', 'string'],
            'favicon_apple' => ['nullable', 'string'],
            'favicon_pwa' => ['nullable', 'string'],

            'site_noindex' => ['nullable', 'boolean'],

            'ga4_id' => ['nullable', 'string', 'max:64'],
            'gtm_id' => ['nullable', 'string', 'max:64'],

            'meta_head_custom' => ['nullable', 'string'],
        ]);

        $settings->fill($data);
        $settings->site_noindex = (bool) ($data['site_noindex'] ?? false);
        $settings->save();

        return back()->with('success', 'Nastavení uloženo.');
    }

    /**
     * ✅ 1 upload -> vygeneruje 3 ikony a uloží je na fixní cesty:
     *  - /storage/icons/favicon.ico
     *  - /storage/icons/apple-touch-icon.png
     *  - /storage/icons/pwa-512.png
     *
     * Vrací: { favicon_ico, favicon_apple, favicon_pwa }
     */

    public function uploadIcons(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10120'], // ~10MB
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension() ?: '');

        $allowed = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
        if (!in_array($ext, $allowed, true)) {
            return response()->json(['message' => 'Nepodporovaný typ souboru.'], 422);
        }

        $dir = 'img/settings/icons/';
        Storage::disk('public')->makeDirectory($dir);

        // pokud je SVG -> uložíme ho jako zdroj, raster ikony si můžeš generovat později,
        // nebo rovnou nechat jen svg (ale ty chceš 3 výstupy, takže svg bys musel rasterizovat přes imagick)
        if ($ext === 'svg') {
            // uložíme zdroj SVG
            $srcName = Str::uuid()->toString() . '.svg';
            $srcPath = $dir . $srcName;
            Storage::disk('public')->put($srcPath, file_get_contents($file->getRealPath()));

            // pro teď vrátíme jen uložený svg jako všechny tři (nebo null a generovat až jobem)
            // Pokud chceš fakt generovat PNG z SVG, musíš použít Imagick nebo externí renderer.
            return response()->json([
                'favicon_ico'   => Storage::url($srcPath),
                'favicon_apple' => Storage::url($srcPath),
                'favicon_pwa'   => Storage::url($srcPath),
            ]);
        }

        $manager = new ImageManager(new Driver());
        $src = $file->getRealPath();

        // --- APPLE 180x180 PNG ---
        $apple = $manager->read($src)->cover(180, 180);
        $appleName = Str::uuid()->toString() . '-apple.png';
        $applePath = $dir . $appleName;
        Storage::disk('public')->put($applePath, (string) $apple->toPng());

        // --- PWA 512x512 PNG ---
        $pwa = $manager->read($src)->cover(512, 512);
        $pwaName = Str::uuid()->toString() . '-pwa.png';
        $pwaPath = $dir . $pwaName;
        Storage::disk('public')->put($pwaPath, (string) $pwa->toPng());

        // --- FAVICON ICO (fallback: PNG 64x64, pokud nechceš řešit ico encoding) ---
        // Intervention v3 neumí "toIco" nativně ve všech driverech.
        // Jednodušší: generuj 64x64 PNG a v headu ho použij jako favicon.
        $ico = $manager->read($src)->cover(64, 64);
        $icoName = Str::uuid()->toString() . '-favicon.png';
        $icoPath = $dir . $icoName;
        Storage::disk('public')->put($icoPath, (string) $ico->toPng());

        return response()->json([
            'favicon_ico'   => Storage::url($icoPath),
            'favicon_apple' => Storage::url($applePath),
            'favicon_pwa'   => Storage::url($pwaPath),
        ]);
    }

    /**
     * Upload libovolného assetu (OG image apod.)
     * Vrací { path: "/storage/..." }
     */
    public function uploadAsset(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10120'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension() ?: '');

        $allowed = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'ico'];
        if (!in_array($ext, $allowed, true)) {
            return response()->json(['message' => 'Nepodporovaný typ souboru.'], 422);
        }

        $name = Str::uuid()->toString() . '.' . $ext;
        $path = 'img/settings/' . $name;

        $stream = fopen($file->getRealPath(), 'r');
        Storage::disk('public')->put($path, $stream);
        if (is_resource($stream)) fclose($stream);

        return response()->json([
            'path' => Storage::url($path),
        ]);
    }

    public function deleteAsset(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
        ]);

        $url = (string) $request->input('path');

        $prefix = '/storage/';
        $rel = str_starts_with($url, $prefix) ? substr($url, strlen($prefix)) : $url;

        Storage::disk('public')->delete($rel);

        return response()->json(['ok' => true]);
    }
}
