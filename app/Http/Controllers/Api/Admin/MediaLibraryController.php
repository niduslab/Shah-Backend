<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaLibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaLibrary::query();

        if ($request->has('file_type')) {
            $query->where('file_type', $request->file_type);
        }

        $media = $query->latest()->paginate(20);
        return response()->json($media);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,webm|max:51200',
            'title' => 'nullable|string',
            'alt_text' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = MediaLibrary::create([
            'title' => $validated['title'] ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'alt_text' => $validated['alt_text'] ?? null,
        ]);

        return response()->json($media, 201);
    }

    public function update(Request $request, $id)
    {
        $media = MediaLibrary::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string',
            'alt_text' => 'nullable|string',
            'metadata' => 'nullable|array',
        ]);

        $media->update($validated);
        return response()->json($media);
    }

    public function destroy($id)
    {
        $media = MediaLibrary::findOrFail($id);
        Storage::disk('public')->delete($media->file_path);
        $media->delete();

        return response()->json(['message' => 'Media deleted successfully']);
    }
}
