<?php

namespace App\Http\Controllers\Bookmarks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Collection;
use Illuminate\Support\Facades\Auth;

class CollectionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255', // Could validate against list of allowed icons
            'color' => 'nullable|string|max:255',
        ]);

        Auth::user()->collections()->create([
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? 'folder',
            'color' => $validated['color'] ?? 'slate',
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Collection $collection)
    {
         if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'background_image' => 'nullable|string|max:1000',
            'background_opacity' => 'nullable|integer|min:0|max:100',
        ]);

        $collection->update($validated);

        return redirect()->back();
    }

    public function destroy(Collection $collection)
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }

        // Logic to handle bookmarks in this collection?
        // Set their collection_id to null? Or delete them?
        // Default behavior: soft delete collection, bookmarks remain but orphaned?
        // Better: update bookmarks to collection_id = null
        $collection->bookmarks()->update(['collection_id' => null]);
        
        $collection->delete();

        return redirect()->back();
    }
}
