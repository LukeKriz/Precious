<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageDesign;

class PageDesignController extends Controller
{

    public function index()
    {
        $pageDesigns = PageDesign::all();

        return [
            'designs' => $pageDesigns
        ];
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'layout_id' => 'required|integer',
            'page_id' => 'nullable|integer',
            'header_height' => 'nullable|integer',
            'banner_height' => 'nullable|integer',
            'footer_height' => 'nullable|integer',
            'logo_position' => 'nullable|string',
            'menu_position' => 'nullable|string',
        ]);


        PageDesign::updateOrCreate(
            ['page_id' => $data['page_id']],
            $data
        );

        return redirect()->route('admin.layout.index')
            ->with('success', 'Rozložení a design byly vytvořeny!');
    }

    public function show($page_id)
    {
        $pageDesign = PageDesign::where('page_id', $page_id)->first();

        if (!$pageDesign) {
            return response()->json(['message' => 'Žádná data pro tuto stránku nenalezena.'], 404);
        }

        return response()->json($pageDesign);
    }


    public function destroy($page_id)
    {
        $pageDesign = PageDesign::where('page_id', $page_id)->first();

        if ($pageDesign) {
            $pageDesign->delete();
            return redirect()->route('admin.layout.index')
                ->with('success', 'Stránka odstraněna!');
        }

        return;
    }
}
