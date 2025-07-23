<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('subpages')->get();

        
        return Inertia::render('Admin/Pages/Index', [
            'pages' => $pages,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Pages/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'sellable' => 'nullable|integer',
            'active' => 'nullable|integer',
            
        ]);


        if ($data['url'] !== null) {
            $data['url'] = '/' .  ltrim($data['url'] ?? '/', '/');
        }

        Page::create($data);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Stránka vytvořena!');
    }

    public function edit(Page $page)
    {
        return Inertia::render('Admin/Pages/Edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'sellable' => 'nullable|integer',
            'active' => 'nullable|integer',

        ]);


        if ($data['url'] !== null) {
            $data['url'] = '/' .  ltrim($data['url'] ?? '/', '/');
        }
       
        $page->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Stránka upravena!');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Stránka odstraněna!');
    }
}
