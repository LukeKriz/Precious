<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Subpage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontPageController extends Controller
{
    public function show(Request $request, string $any = '')
    {
        $url = '/' . ltrim($any, '/');

        $page = Page::where('url', $url)->where('active', 1)->first();
        $subpage = null;

        if (!$page) {
            $subpage = Subpage::where('url', $url)->where('active', 1)->first();

            if ($subpage) {
                $page = Page::where('id', $subpage->page_id)->where('active', 1)->first();
            }
        }

        abort_if(!$page && !$subpage, 404);

        return Inertia::render('Page', [
            'currentUrl' => $url,
            'page' => $page,
            'subpage' => $subpage,
        ]);
    }
}
