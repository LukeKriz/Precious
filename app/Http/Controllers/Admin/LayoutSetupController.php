<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LayoutSetup;
use App\Models\PageDesign;
use App\Models\Page;
use App\Models\Subpage;

class LayoutSetupController extends Controller
{
    public function index()
    {
        $layouts = LayoutSetup::all();
        $pageDesign = PageDesign::all();
        $pages = Page::where('active', 1)->get();
        $subpages = Subpage::all();

        return Inertia::render('Admin/LayoutSetup/Index', [
            'layouts' => $layouts,
            'pageDesign' => $pageDesign,
            'pages' => $pages,
            'subpages' => $subpages,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/LayoutSetup/Create');
    }

    

    public function edit(LayoutSetup $page)
    {
        return Inertia::render('Admin/LayoutSetup/Edit', compact('page'));
    }


   

 
}
