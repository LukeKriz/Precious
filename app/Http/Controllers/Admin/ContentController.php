<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Page;
use App\Models\Subpage;
use App\Models\PageContent;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class ContentController extends Controller
{
    public function index(Request $request)
    {
        $url = $this->normalizeUrl($request->get('url', '/'));

        [$page, $subpage] = $this->resolveByUrl($url);

        $content = [];

        if ($page || $subpage) {
            if ($subpage) {
                $row = PageContent::whereNull('page_id')
                    ->where('subpage_id', $subpage->id)
                    ->first();
            } else {
                $row = PageContent::where('page_id', $page?->id)
                    ->whereNull('subpage_id')
                    ->first();
            }


            $content = is_array($row?->content) ? $row->content : [];
        }

        return Inertia::render('Admin/Content/Index', [
            'selectedUrl' => $url,
            'pageContent' => $content,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'string'],
            'content_json' => ['required', 'string'], // ✅ vždy string
        ]);

        $url = $this->normalizeUrl($data['url']);
        [$page, $subpage] = $this->resolveByUrl($url);

        if (!$page && !$subpage) {
            return back()->with('error', 'Stránka nebyla nalezena.');
        }

        $decoded = json_decode($data['content_json'], true);
        $content = is_array($decoded) ? $decoded : [];

        $isSub = (bool) $subpage;

        $row = PageContent::updateOrCreate(
            [
                'page_id'    => $isSub ? null : $page?->id,
                'subpage_id' => $isSub ? $subpage->id : null,
            ],
            [
                'schema_version' => 1,
            ]
        );

        $row->forceFill([
            'content' => $content,
        ])->save();

        return back()->with('success', 'Uloženo.');
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10120', 'mimes:jpg,jpeg,png,webp,svg'],
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        // ===== SVG: uložit bez Intervention =====
        if ($ext === 'svg' || $mime === 'image/svg+xml') {

            // (DŮLEŽITÉ) sanitizace - viz níže
            $svg = file_get_contents($file->getRealPath());
            $svg = $this->sanitizeSvg($svg);

            $name = \Illuminate\Support\Str::uuid()->toString() . '.svg';
            $path = 'img/content/' . $name;

            \Illuminate\Support\Facades\Storage::disk('public')->put($path, $svg);

            return response()->json([
                'path' => \Illuminate\Support\Facades\Storage::url($path),
            ]);
        }

        // ===== RASTER: tvoje původní logika -> webp =====
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $img = $manager->read($file->getRealPath());

        $max = 1000;
        $img->scaleDown($max, $max);

        $quality = 80;

        $name = \Illuminate\Support\Str::uuid()->toString() . '.webp';
        $path = 'img/content/' . $name;

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, (string) $img->toWebp($quality));

        return response()->json([
            'path' => \Illuminate\Support\Facades\Storage::url($path),
        ]);
    }




    // ✅ smazání souboru (posíláš "/storage/...")
    public function deleteFile(Request $request)
    {
        $data = $request->validate([
            'path' => ['required', 'string'],
        ]);

        $path = $data['path'];

        // povolíme jen /storage/... aby se nedalo mazat cokoliv
        if (!str_starts_with($path, '/storage/')) {
            return response()->json(['ok' => false], 400);
        }

        // /storage/xxx -> public/xxx
        $relative = 'public/' . ltrim(substr($path, strlen('/storage/')), '/');

        Storage::delete($relative);

        return response()->json(['ok' => true]);
    }


    private function sanitizeSvg(string $svg): string
    {
        // remove script tags
        $svg = preg_replace('~<script[^>]*>.*?</script>~is', '', $svg) ?? $svg;

        // remove on* event handlers (onload, onclick,...)
        $svg = preg_replace('~\son\w+\s*=\s*"[^"]*"~i', '', $svg) ?? $svg;
        $svg = preg_replace("~\son\w+\s*=\s*'[^']*'~i", '', $svg) ?? $svg;

        // remove javascript: urls
        $svg = preg_replace('~javascript:\s*~i', '', $svg) ?? $svg;

        return $svg;
    }

    private function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '' || $url === '/') return '/';
        if ($url[0] !== '/') $url = '/' . $url;
        if (strlen($url) > 1) $url = rtrim($url, '/');
        return $url;
    }

    private function resolveByUrl(string $url): array
    {
        $page = Page::where('url', $url)->where('active', 1)->first();
        $subpage = null;

        if (!$page) {
            $subpage = Subpage::where('url', $url)->where('active', 1)->first();
            if ($subpage) {
                $page = Page::where('id', $subpage->page_id)->where('active', 1)->first();
            }
        }

        return [$page, $subpage];
    }
}
