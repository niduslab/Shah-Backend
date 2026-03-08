<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show($slug)
    {
        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->with('activeSections')
            ->firstOrFail();

        return response()->json($page);
    }

    public function getByType($type)
    {
        $pages = Page::where('type', $type)
            ->where('is_active', true)
            ->with('activeSections')
            ->orderBy('sort_order')
            ->get();

        return response()->json($pages);
    }
}
