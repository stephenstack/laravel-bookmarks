<?php

namespace App\Http\Controllers\Bookmarks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        Auth::user()->tags()->create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'color' => $validated['color'] ?? null,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'nullable|string|max:255',
        ]);

        $updateData = $validated;
        if (isset($validated['name'])) {
            $updateData['slug'] = Str::slug($validated['name']);
        }

        $tag->update($updateData);

        return redirect()->back();
    }

    public function destroy(Tag $tag)
    {
        if ($tag->user_id !== Auth::id()) {
            abort(403);
        }

        $tag->delete(); // Detaches from bookmarks automatically via foreign key constraints usually, or need to handle pivot
        // If pivot table doesn't cascade delete, we might need to detach first.
        // Migration has `constrained()->onDelete('cascade')`?
        // Let's check migration.
        
        return redirect()->back();
    }
}
