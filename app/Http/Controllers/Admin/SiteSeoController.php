<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Subpage;
use Illuminate\Http\Request;

class SiteSeoController extends Controller
{
    public function index()
    {
        return inertia('Admin/Settings/Seo', [
            'pages' => Page::select('id', 'title', 'url', 'meta_title', 'meta_description')
                ->orderBy('id')
                ->get(),

            'subpages' => Subpage::select('id', 'page_id', 'title', 'url', 'meta_title', 'meta_description')
                ->orderBy('page_id')
                ->orderBy('id')
                ->get(),
        ]);
    }
    public function save(Request $request)
    {
        // ✅ payload: pages: { [id]: {meta_title, meta_description}}, subpages: { [id]: {...}}
        $data = $request->validate([
            'pages' => ['nullable', 'array'],
            'pages.*.meta_title' => ['nullable', 'string', 'max:255'],
            'pages.*.meta_description' => ['nullable', 'string'],

            'subpages' => ['nullable', 'array'],
            'subpages.*.meta_title' => ['nullable', 'string', 'max:255'],
            'subpages.*.meta_description' => ['nullable', 'string'],
        ]);

        // Pages
        foreach (($data['pages'] ?? []) as $id => $row) {
            if (!is_numeric($id)) continue;

            Page::where('id', (int)$id)->update([
                'meta_title' => $row['meta_title'] ?? null,
                'meta_description' => $row['meta_description'] ?? null,
            ]);
        }

        // Subpages
        foreach (($data['subpages'] ?? []) as $id => $row) {
            if (!is_numeric($id)) continue;

            Subpage::where('id', (int)$id)->update([
                'meta_title' => $row['meta_title'] ?? null,
                'meta_description' => $row['meta_description'] ?? null,
            ]);
        }

        return back()->with('success', 'SEO uloženo.');
    }
}
