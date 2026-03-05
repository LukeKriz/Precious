<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\MainDesign;
use App\Models\Page;;

use App\Models\Subpage;
use App\Models\PageDesign;
use App\Models\PageDesignBanner;
use App\Models\PageContent;
use App\Models\SiteSetting;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Globální data
            'pages' => fn() => Page::where('active', 1)->get(),
            'subpages' => fn() => Subpage::where('active', 1)->get(),
            'mainDesign' => fn() => MainDesign::first(),

            // page-specific data
            'pageDesign' => fn() => $this->resolvePageContext($request)['design'],
            'banner'     => fn() => $this->resolvePageContext($request)['banner'],

            //content editor
            'pageContent' => fn() => $this->resolvePageContent($request),
            'isPreview' => fn() => $request->boolean('preview'),
            'siteSettings' => fn() => SiteSetting::first(),

        ]);
    }


    private function resolvePageContent(Request $request): array
    {
        $path = $request->path();
        $url = ($path === '' || $path === '/') ? '/' : '/' . ltrim($path, '/');

        $page = \App\Models\Page::where('url', $url)->where('active', 1)->first();
        $subpage = null;

        if (!$page) {
            $subpage = \App\Models\Subpage::where('url', $url)->where('active', 1)->first();
            if ($subpage) {
                $page = \App\Models\Page::where('id', $subpage->page_id)->where('active', 1)->first();
            }
        }

        if (!$page && !$subpage) return [];

        $row = \App\Models\PageContent::query()
            ->when($subpage, fn($q) => $q->where('subpage_id', $subpage->id))
            ->when(!$subpage && $page, fn($q) => $q->where('page_id', $page->id))
            ->first();

        return $row?->content ?? [];
    }
    private function resolvePageContext(Request $request): array
    {
        $path = $request->path();
        $url = ($path === '' || $path === '/') ? '/' : '/' . ltrim($path, '/');

        // najdi page / subpage
        $page = Page::where('url', $url)->where('active', 1)->first();
        $subpage = null;

        if (!$page) {
            $subpage = Subpage::where('url', $url)->where('active', 1)->first();
            if ($subpage) {
                $page = Page::where('id', $subpage->page_id)
                    ->where('active', 1)
                    ->first();
            }
        }

        if (!$page && !$subpage) {
            return [
                'design' => null,
                'banner' => null,
            ];
        }

        // najdi PageDesign (preferuj subpage)
        $design = null;

        if ($subpage) {
            $design = PageDesign::with(['banner', 'banners'])
                ->where('subpage_id', $subpage->id)
                ->first();
        }

        if (!$design && $page) {
            $design = PageDesign::with(['banner', 'banners'])
                ->where('page_id', $page->id)
                ->first();
        }

        if (!$design) {
            return [
                'design' => null,
                'banner' => null,
            ];
        }

        return [
            'design' => $design,
            'banner' => $this->resolveBannerFromDesign($design),
        ];
    }

    private function resolveBannerFromDesign(PageDesign $design): ?array
    {
        // slider bannery (více obrázků)
        $sliderUrls = PageDesignBanner::where('page_design_id', $design->id)
            ->orderBy('sort_order')
            ->pluck('banner_url')
            ->filter()
            ->values()
            ->all();

        // statický banner (1 obrázek)
        $staticUrl = null;
        if ($design->banner_id) {
            $staticUrl = PageDesignBanner::where('id', $design->banner_id)
                ->value('banner_url');
        }

        // rozhodnutí
        $urls = $sliderUrls;

        if (count($urls) === 0 && $staticUrl) {
            $urls = [$staticUrl];
        }

        if (count($urls) === 0) return null;

        return [
            'type'   => count($urls) > 1 ? 'slider' : 'static',
            'urls'   => $urls,
            'height' => (int) ($design->banner_height ?? 260),
        ];
    }
}
