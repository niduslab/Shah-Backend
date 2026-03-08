<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Services\PageTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::with('sections')->orderBy('sort_order')->get();
        return response()->json($pages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:pages,slug',
            'type' => 'required|in:landing,brand,flash_deal,gallery,custom',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page = Page::create($validated);
        return response()->json($page, 201);
    }

    public function show($id)
    {
        $page = Page::with('activeSections')->findOrFail($id);
        return response()->json($page);
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'slug' => 'string|unique:pages,slug,' . $id,
            'type' => 'in:landing,brand,flash_deal,gallery,custom',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $page->update($validated);
        return response()->json($page);
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();
        return response()->json(['message' => 'Page deleted successfully']);
    }

    public function getSections($pageId)
    {
        $page = Page::findOrFail($pageId);
        $sections = PageSection::where('page_id', $pageId)
            ->orderBy('sort_order')
            ->get();
        
        return response()->json($sections);
    }

    public function addSection(Request $request, $pageId)
    {
        // Validate page exists
        $page = Page::findOrFail($pageId);

        $validated = $request->validate([
            'section_type' => 'required|string',
            'title' => 'nullable|string',
            'content' => 'required|array',
            'settings' => 'nullable|array',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        // Set defaults
        $validated['page_id'] = $pageId;
        $validated['is_active'] = $validated['is_active'] ?? true;
        
        // Auto-calculate sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $maxOrder = PageSection::where('page_id', $pageId)->max('sort_order') ?? 0;
            $validated['sort_order'] = $maxOrder + 1;
        }

        $section = PageSection::create($validated);
        
        return response()->json($section, 201);
    }

    public function getSection($pageId, $sectionId)
    {
        $section = PageSection::where('page_id', $pageId)
            ->findOrFail($sectionId);
        
        return response()->json($section);
    }

    public function updateSection(Request $request, $pageId, $sectionId)
    {
        $section = PageSection::where('page_id', $pageId)->findOrFail($sectionId);

        $validated = $request->validate([
            'section_type' => 'string',
            'title' => 'nullable|string',
            'content' => 'array',
            'settings' => 'nullable|array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $section->update($validated);
        return response()->json($section);
    }

    public function deleteSection($pageId, $sectionId)
    {
        $section = PageSection::where('page_id', $pageId)->findOrFail($sectionId);
        $section->delete();
        return response()->json(['message' => 'Section deleted successfully']);
    }

    public function reorderSections(Request $request, $pageId)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:page_sections,id',
            'sections.*.sort_order' => 'required|integer',
        ]);

        foreach ($validated['sections'] as $section) {
            PageSection::where('id', $section['id'])
                ->where('page_id', $pageId)
                ->update(['sort_order' => $section['sort_order']]);
        }

        return response()->json(['message' => 'Sections reordered successfully']);
    }

    /**
     * Get all available section templates
     */
    public function getTemplates()
    {
        $templates = PageTemplateService::getSectionTemplates();
        return response()->json($templates);
    }

    /**
     * Get templates by category
     */
    public function getTemplatesByCategory($category)
    {
        $templates = PageTemplateService::getTemplatesByCategory($category);
        return response()->json($templates);
    }

    /**
     * Get templates by page type
     */
    public function getTemplatesByPageType($pageType)
    {
        $templates = PageTemplateService::getTemplatesByPageType($pageType);
        return response()->json($templates);
    }

    /**
     * Get specific template schema
     */
    public function getTemplateSchema($templateType)
    {
        $template = PageTemplateService::getTemplateSchema($templateType);
        
        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }
        
        return response()->json($template);
    }

    /**
     * Duplicate a page with all its sections
     */
    public function duplicate($id)
    {
        $originalPage = Page::with('sections')->findOrFail($id);
        
        // Create new page
        $newPage = $originalPage->replicate();
        $newPage->title = $originalPage->title . ' (Copy)';
        $newPage->slug = $originalPage->slug . '-copy-' . time();
        $newPage->is_active = false; // Set as inactive by default
        $newPage->save();
        
        // Duplicate all sections
        foreach ($originalPage->sections as $section) {
            $newSection = $section->replicate();
            $newSection->page_id = $newPage->id;
            $newSection->save();
        }
        
        return response()->json($newPage->load('sections'), 201);
    }
}
