<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subpage;
use App\Models\Page;
use Illuminate\Http\Request;

class SubpageController extends Controller
{

    public function index()
    {
        return Subpage::all();
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string|unique:subpages,url',
            'sellable' => 'nullable|boolean',
            'page_id' => 'required|exists:pages,id',
            'active' => 'nullable|boolean',
        ]);

        $subpage = Subpage::create($validated);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Stránka vytvořena!');
    }

    public function update(Request $request, Subpage $subpage)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|string',
            'sellable' => 'nullable|boolean',
            'active' => 'nullable|boolean',
        ]);

        $data['url'] = '/' . ltrim($data['url'] ?? '/', '/');
        $subpage->update($data);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Podstránka upravena!');
    }

    public function destroy(Subpage $subpage)
    {
        $subpage->delete();

        return redirect()->route('admin.pages.index')
            ->with('success', 'Podstránka odstraněna!');
    }
}
