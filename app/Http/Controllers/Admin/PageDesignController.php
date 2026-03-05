<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageDesign;
use App\Models\PageDesignBanner;
use Illuminate\Support\Facades\Storage;

class PageDesignController extends Controller
{
    // =========================================================
    // ✅ TARGET HELPERS
    // =========================================================
    private function target(Request $request): string
    {
        $t = strtolower((string)$request->query('target', 'page'));
        return in_array($t, ['page', 'subpage'], true) ? $t : 'page';
    }

    private function getDesignByTarget(Request $request, int $id): ?PageDesign
    {
        $t = $this->target($request);
        if ($t === 'subpage') {
            return PageDesign::where('subpage_id', $id)->with(['banner', 'banners'])->first();
        }
        return PageDesign::where('page_id', $id)->with(['banner', 'banners'])->first();
    }

    private function getOrCreateDesignByTarget(Request $request, int $id): PageDesign
    {
        if ($id <= 0) {
            abort(422, 'Chybí page/subpage id');
        }

        $t = $this->target($request);

        if ($t === 'subpage') {
            return PageDesign::firstOrCreate(
                ['subpage_id' => $id],
                [
                    'subpage_id' => $id,
                    'page_id' => null,
                    'layout_id' => 1,
                ]
            );
        }

        return PageDesign::firstOrCreate(
            ['page_id' => $id],
            [
                'page_id' => $id,
                'subpage_id' => null,
                'layout_id' => 1,
            ]
        );
    }


    // =========================================================
    // BASE
    // =========================================================
    public function index()
    {
        return [
            'designs' => PageDesign::with(['banner', 'banners'])->get(),
        ];
    }

    // ✅ show now works for pages and subpages via ?target=
    public function show(Request $request, $id)
    {
        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) {
            return response()->json(['message' => 'PageDesign nenalezen'], 404);
        }
        return response()->json($pageDesign, 200);
    }

    public function store(Request $request)
    {
        $t = $this->target($request);

        $data = $request->validate([
            'layout_id' => 'required|integer',

            // posílej vždy jen jednu z nich podle targetu
            'page_id' => 'nullable|integer',
            'subpage_id' => 'nullable|integer',

            'header_height' => 'nullable|integer',
            'banner_height' => 'nullable|integer',
            'footer_height' => 'nullable|integer',

            'logo_position' => 'nullable|string',
            'menu_position' => 'nullable|string',

            'category_width' => 'nullable|integer',
            'category_height' => 'nullable|integer',

            'banner_id' => 'nullable|integer',
        ]);

        // ✅ validace dle targetu (Inertia-friendly)
        if ($t === 'subpage') {
            $request->validate([
                'subpage_id' => 'required|integer',
            ]);
            $data['page_id'] = null;

            PageDesign::updateOrCreate(
                ['subpage_id' => (int)$data['subpage_id']],
                $data
            );
        } else {
            $request->validate([
                'page_id' => 'required|integer',
            ]);
            $data['subpage_id'] = null;

            PageDesign::updateOrCreate(
                ['page_id' => (int)$data['page_id']],
                $data
            );
        }

        return redirect()->route('admin.layout.index')
            ->with('success', 'Rozložení stránky uloženo');
    }


    // =========================================================
    // ✅ BANNER SETTINGS
    // =========================================================
    public function saveBannerSettings(Request $request, $id)
    {
        $data = $request->validate([
            'banner_type'  => 'required|in:static,slider',
            'banner_count' => 'required|integer|min:1|max:10',
        ]);

        $pageDesign = $this->getOrCreateDesignByTarget($request, (int)$id);

        $existing = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->orderBy('sort_order')
            ->get();

        $bannerType = $data['banner_type'];
        $bannerCount = $bannerType === 'static' ? 1 : (int)$data['banner_count'];

        // nic neexistuje => OK (settings uložíš po prvním itemu)
        if ($existing->count() === 0) {
            return response()->json([
                'banner_type' => $bannerType,
                'banner_count' => $bannerCount,
                'message' => 'Nastavení bude uloženo po prvním vytvoření banneru.'
            ], 200);
        }

        if ($bannerType === 'slider' && $existing->count() > $bannerCount) {
            return response()->json([
                'message' => 'Nelze nastavit menší počet než je aktuálně nahraný počet bannerů.'
            ], 422);
        }

        if ($bannerType === 'static') {
            $keep = $existing->first();

            foreach ($existing->slice(1) as $row) {
                $this->deleteBannerFiles($row);
                $row->delete();
            }

            $keep->update([
                'banner_type' => 'static',
                'banner_count' => 1,
                'sort_order' => 1,
            ]);

            $this->normalizeBannerOrder($pageDesign->id);
            $this->syncBannerIdToFirst($pageDesign->id);

            return response()->json([
                'banner_type' => 'static',
                'banner_count' => 1,
            ]);
        }

        PageDesignBanner::where('page_design_id', $pageDesign->id)->update([
            'banner_type' => 'slider',
            'banner_count' => $bannerCount,
        ]);

        $this->normalizeBannerOrder($pageDesign->id);
        $this->syncBannerIdToFirst($pageDesign->id);

        return response()->json([
            'banner_type' => 'slider',
            'banner_count' => $bannerCount,
        ]);
    }

    // =========================================================
    // ✅ STATIC UPLOAD (1 banner)
    // =========================================================
    public function uploadBanner(Request $request, $id)
    {
        $request->validate([
            'banner' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        $pageDesign = $this->getOrCreateDesignByTarget($request, (int)$id);

        $existing = PageDesignBanner::where('page_design_id', $pageDesign->id)->get();
        foreach ($existing as $row) {
            $this->deleteBannerFiles($row);
            $row->delete();
        }

        $path = $request->file('banner')->store('banners', 'public');
        $url = Storage::url($path);

        $main = PageDesignBanner::create([
            'page_design_id' => $pageDesign->id,
            'banner_url' => $url,
            'sort_order' => 1,
            'banner_type' => 'static',
            'banner_count' => 1,

            'bg_type' => 'image',
            'bg_color' => null,
            'overlay_color' => null,
            'overlay_opacity' => 0,

            'thumbnail_url' => null,
            'thumbnail_size' => 64,

            'heading_h1' => null,
            'heading_h2' => null,
            'h1_color' => null,
            'h2_color' => null,
            'h1_size' => 42,
            'h2_size' => 20,

            'button_enabled' => false,
            'button_text' => null,
            'button_url' => null,
        ]);

        $pageDesign->banner_id = $main->id;
        $pageDesign->save();

        return response()->json([
            'banner_id' => $main->id,
            'banner_url' => $url,
            'banner_type' => 'static',
            'banner_count' => 1,
        ]);
    }

    public function deleteBanner(Request $request, $id)
    {
        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $existing = PageDesignBanner::where('page_design_id', $pageDesign->id)->get();
        foreach ($existing as $row) {
            $this->deleteBannerFiles($row);
            $row->delete();
        }

        $pageDesign->banner_id = null;
        $pageDesign->save();

        return response()->json(['message' => 'Banner smazán']);
    }

    // =========================================================
    // ✅ SLIDER EMPTY
    // =========================================================
    public function createEmptyBannerItem(Request $request, $id)
    {
        $request->validate([
            'banner_count' => 'nullable|integer|min:1|max:10',
        ]);

        $pageDesign = $this->getOrCreateDesignByTarget($request, (int)$id);

        $nextOrder = (int)(PageDesignBanner::where('page_design_id', $pageDesign->id)->max('sort_order') ?? 0) + 1;

        $item = PageDesignBanner::create([
            'page_design_id' => $pageDesign->id,
            'banner_url' => null,
            'sort_order' => $nextOrder,
            'banner_type' => 'slider',

            'bg_type' => 'color',
            'bg_color' => '#f3f4f6',
            'overlay_color' => null,
            'overlay_opacity' => 0,

            'thumbnail_url' => null,
            'thumbnail_size' => 64,

            'heading_h1' => null,
            'heading_h2' => null,
            'h1_color' => null,
            'h2_color' => null,
            'h1_size' => 42,
            'h2_size' => 20,

            'button_enabled' => false,
            'button_text' => null,
            'button_url' => null,
        ]);

        PageDesignBanner::where('page_design_id', $pageDesign->id)->update([
            'banner_type' => 'slider',
        ]);

        $this->normalizeBannerOrder($pageDesign->id);
        $this->syncBannerIdToFirst($pageDesign->id);

        return response()->json(['item' => $item]);
    }

    // =========================================================
    // ✅ SLIDER UPLOAD ITEM
    // =========================================================
    public function uploadBannerItem(Request $request, $id)
    {
        $request->validate([
            'banner' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
            'banner_count' => 'nullable|integer|min:1|max:10',
        ]);

        $pageDesign = $this->getOrCreateDesignByTarget($request, (int)$id);

        $path = $request->file('banner')->store('banners', 'public');
        $url = Storage::url($path);

        $nextOrder = (int)(PageDesignBanner::where('page_design_id', $pageDesign->id)->max('sort_order') ?? 0) + 1;

        $item = PageDesignBanner::create([
            'page_design_id' => $pageDesign->id,
            'banner_url' => $url,
            'sort_order' => $nextOrder,
            'banner_type' => 'slider',

            'bg_type' => 'image',
            'bg_color' => null,
            'overlay_color' => null,
            'overlay_opacity' => 0,

            'thumbnail_url' => null,
            'thumbnail_size' => 64,

            'heading_h1' => null,
            'heading_h2' => null,
            'h1_color' => null,
            'h2_color' => null,
            'h1_size' => 42,
            'h2_size' => 20,

            'button_enabled' => false,
            'button_text' => null,
            'button_url' => null,
        ]);

        PageDesignBanner::where('page_design_id', $pageDesign->id)->update([
            'banner_type' => 'slider',
        ]);

        $this->normalizeBannerOrder($pageDesign->id);
        $this->syncBannerIdToFirst($pageDesign->id);

        return response()->json(['item' => $item]);
    }

    // =========================================================
    // ✅ BUILDER
    // =========================================================
    public function saveBannerBuilder(Request $request, $id, $banner_id)
    {
        $data = $request->validate([
            'bg_type' => 'required|in:image,color',
            'bg_color' => 'nullable|string|max:32',
            'overlay_color' => 'nullable|string|max:32',
            'overlay_opacity' => 'nullable|integer|min:0|max:100',
            'banner_url' => 'nullable|string|max:2048',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        $bgType = $data['bg_type'];
        $update = [
            'bg_type' => $bgType,
            'bg_color' => $bgType === 'color' ? ($data['bg_color'] ?? null) : $item->bg_color,
            'overlay_color' => $data['overlay_color'] ?? null,
            'overlay_opacity' => isset($data['overlay_opacity']) ? (int)$data['overlay_opacity'] : 0,
        ];

        if (array_key_exists('banner_url', $data)) {
            $update['banner_url'] = $data['banner_url'];
        }

        $item->update($update);

        return response()->json([
            'message' => 'Builder uložen',
            'item' => $item->fresh(),
        ]);
    }

    public function saveBannerTextStyle(Request $request, $id, $banner_id)
    {
        $data = $request->validate([
            'h1_color' => 'nullable|string|max:32',
            'h2_color' => 'nullable|string|max:32',
            'h1_size' => 'nullable|integer|min:8|max:200',
            'h2_size' => 'nullable|integer|min:8|max:200',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        $item->update([
            'h1_color' => $data['h1_color'] ?? null,
            'h2_color' => $data['h2_color'] ?? null,
            'h1_size' => isset($data['h1_size']) ? (int)$data['h1_size'] : $item->h1_size,
            'h2_size' => isset($data['h2_size']) ? (int)$data['h2_size'] : $item->h2_size,
        ]);

        return response()->json([
            'message' => 'Text style uložen',
            'item' => $item,
        ]);
    }

    public function saveThumbnailSize(Request $request, $id, $banner_id)
    {
        $data = $request->validate([
            'thumbnail_size' => 'required|integer|min:16',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        $item->update([
            'thumbnail_size' => (int)$data['thumbnail_size'],
        ]);

        return response()->json([
            'message' => 'Velikost miniatury uložena',
            'item' => $item,
        ]);
    }

    public function deleteBannerItem(Request $request, $id, $banner_id)
    {
        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        $this->deleteBannerFiles($item);
        $item->delete();

        $this->normalizeBannerOrder($pageDesign->id);
        $this->syncBannerIdToFirst($pageDesign->id);

        return response()->json(['message' => 'Banner item smazán']);
    }

    public function reorderBannerItems(Request $request, $id)
    {
        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        foreach ($data['order'] as $index => $bannerId) {
            PageDesignBanner::where('page_design_id', $pageDesign->id)
                ->where('id', $bannerId)
                ->update(['sort_order' => $index + 1]);
        }

        $this->normalizeBannerOrder($pageDesign->id);
        $this->syncBannerIdToFirst($pageDesign->id);

        return response()->json(['message' => 'Seřazení uloženo']);
    }

    public function uploadBannerThumbnail(Request $request, $id, $banner_id)
    {
        $request->validate([
            'thumbnail' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        if ($item->thumbnail_url) {
            $this->deleteFileByUrl($item->thumbnail_url);
        }

        $path = $request->file('thumbnail')->store('banners/thumbnails', 'public');
        $url = Storage::url($path);

        $item->update([
            'thumbnail_url' => $url,
        ]);

        return response()->json([
            'message' => 'Miniatura uložena',
            'item' => $item,
        ]);
    }

    public function saveBannerText(Request $request, $id, $banner_id)
    {
        $data = $request->validate([
            'heading_h1' => 'nullable|string|max:255',
            'heading_h2' => 'nullable|string|max:255',

            'button_enabled' => 'nullable|boolean',
            'button_text' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:2048',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        $buttonEnabled = (bool)($data['button_enabled'] ?? false);

        $item->update([
            'heading_h1' => $data['heading_h1'] ?? null,
            'heading_h2' => $data['heading_h2'] ?? null,
            'button_enabled' => $buttonEnabled,
            'button_text' => $buttonEnabled ? ($data['button_text'] ?? null) : null,
            'button_url' => $buttonEnabled ? ($data['button_url'] ?? null) : null,
        ]);

        return response()->json([
            'message' => 'Text banneru uložen',
            'item' => $item,
        ]);
    }

    public function uploadBannerItemImage(Request $request, $id, $banner_id)
    {
        $request->validate([
            'banner' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
        ]);

        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        if (!empty($item->banner_url)) {
            $this->deleteFileByUrl($item->banner_url);
        }

        $path = $request->file('banner')->store('banners', 'public');
        $url  = Storage::url($path);

        $item->update([
            'banner_url' => $url,
            'bg_type' => 'image',
        ]);

        $this->syncBannerIdToFirst($pageDesign->id);

        return response()->json([
            'message' => 'Obrázek uložen',
            'item' => $item,
        ]);
    }

    public function deleteBannerThumbnail(Request $request, $id, $banner_id)
    {
        $pageDesign = $this->getDesignByTarget($request, (int)$id);
        if (!$pageDesign) return response()->json(['message' => 'PageDesign nenalezen'], 404);

        $item = PageDesignBanner::where('page_design_id', $pageDesign->id)
            ->where('id', $banner_id)
            ->first();

        if (!$item) return response()->json(['message' => 'Banner item nenalezen'], 404);

        if ($item->thumbnail_url) {
            try {
                $url = $item->thumbnail_url;
                $path = ltrim(str_replace('/storage/', '', parse_url($url, PHP_URL_PATH) ?? ''), '/');
                if ($path) Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $item->thumbnail_url = null;
        $item->save();

        return response()->json(['item' => $item]);
    }

    public function destroy(Request $request, $id)
    {
        $t = $this->target($request);

        if ($t === 'subpage') {
            PageDesign::where('subpage_id', $id)->delete();
        } else {
            PageDesign::where('page_id', $id)->delete();
        }
    }


    // =========================================================
    // HELPERS
    // =========================================================
    private function deleteBannerFiles(PageDesignBanner $row): void
    {
        if (!empty($row->banner_url)) {
            $this->deleteFileByUrl($row->banner_url);
        }
        if (!empty($row->thumbnail_url)) {
            $this->deleteFileByUrl($row->thumbnail_url);
        }
    }

    private function deleteFileByUrl(string $url): void
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $relativePath = ltrim(str_replace('/storage/', '', $path), '/');

        if ($relativePath !== '') {
            Storage::disk('public')->delete($relativePath);
        }
    }

    private function normalizeBannerOrder(int $pageDesignId): void
    {
        $items = PageDesignBanner::where('page_design_id', $pageDesignId)
            ->orderBy('sort_order')
            ->get();

        foreach ($items as $i => $item) {
            $item->update(['sort_order' => $i + 1]);
        }
    }

    private function syncBannerIdToFirst(int $pageDesignId): void
    {
        $first = PageDesignBanner::where('page_design_id', $pageDesignId)
            ->orderBy('sort_order')
            ->first();

        $pageDesign = PageDesign::where('id', $pageDesignId)->first();
        if (!$pageDesign) return;

        $pageDesign->banner_id = $first ? $first->id : null;
        $pageDesign->save();
    }
}
