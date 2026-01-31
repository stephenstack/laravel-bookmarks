<?php

namespace App\Http\Controllers\Bookmarks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Bookmark;
use App\Models\Collection;
use App\Models\CompanyBookmark;
use App\Models\Tag;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index(Request $request, $slug = null, $tag = null)
    {
        $user = Auth::user();
        
        // Fetch User Data
        $bookmarks = $user->bookmarks()->orderBy('order')->with(['tags', 'collection'])->get();
        $collections = $user->collections()->orderBy('order')->withCount('bookmarks')->get();
        $tags = $user->tags()->withCount('bookmarks')->get();
        
        // Fetch Company Data (to be merged seamlessly or separated)
        $companyBookmarks = CompanyBookmark::all()->map(function ($item) {
             return [
                'id' => 'company-' . $item->id, // Unique ID to distinguish
                'title' => $item->title,
                'url' => $item->url,
                'description' => $item->description,
                'favicon' => $item->favicon,
                'is_favorite' => false,
                'collection_id' => 'company-resources', // Hardcoded ID for the collection
                'is_company' => true,
                'tags' => [],
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
             ];
        });

        // Company Collection (virtual)
        $companyCollection = [
            'id' => 'company-resources',
            'name' => Setting::get('company_collection_title', 'Company Resources'),
            'slug' => 'company-resources',
            'icon' => Setting::get('company_collection_icon', 'Building'),
            'color' => Setting::get('company_collection_color', 'blue'),
            'is_system' => true,
            'count' => $companyBookmarks->count(),
        ];
        
        // Determine initial view based on route
        $initialView = null;
        $initialCollection = null;
        $initialTag = null;
        
        if ($slug) {
            // Find collection by slug
            $collection = $collections->firstWhere('slug', $slug);
            if ($collection) {
                $initialCollection = (string) $collection->id;
            } elseif ($slug === 'company-resources') {
                $initialCollection = 'company-resources';
            }
        } elseif ($tag) {
            $tagModel = $tags->firstWhere('slug', $tag);
            if ($tagModel) {
                $initialTag = $tagModel->id;
            }
        } elseif ($view = $request->route()->defaults['view'] ?? null) {
            if ($view === 'all') {
                $initialCollection = 'all';
            } else {
                $initialView = $view;
            }
        }
        
        // Merge Bookmarks for display
        // Note: In a real app, you might want to keep them separate in the store
        // but for now we pass them as a merged list or separate prop
        
        return Inertia::render('Bookmarks/Index', [
            'initialBookmarks' => $bookmarks->concat($companyBookmarks),
            'collections' => $collections->map(function($c) {
                $c->count = $c->bookmarks_count;
                return $c;
            })->push($companyCollection),
            'tags' => $tags->map(function($t) {
                $t->count = $t->bookmarks_count;
                return $t;
            }),
            'initialView' => $initialView,
            'initialCollection' => $initialCollection,
            'initialTag' => $initialTag,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url',
            'collection_id' => 'nullable|exists:collections,id',
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
        ]);

        $bookmark = Auth::user()->bookmarks()->create([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'collection_id' => $validated['collection_id'] ?? null,
            'description' => $request->input('description'),
        ]);

        if (!empty($validated['tags'])) {
            $bookmark->tags()->attach($validated['tags']);
        }

        return redirect()->back();
    }

    public function update(Request $request, Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'url' => 'sometimes|url',
            'collection_id' => 'nullable|exists:collections,id',
            'is_favorite' => 'boolean',
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
        ]);

        $bookmark->update($request->only(['title', 'url', 'description', 'collection_id', 'is_favorite']));

        if ($request->has('tags')) {
            $bookmark->tags()->sync($validated['tags']);
        }

        return redirect()->back();
    }

    public function destroy(Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->delete();
        return redirect()->back();
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:bookmarks,id',
            'updates.*.order' => 'required|integer',
        ]);

        foreach ($validated['updates'] as $update) {
            Bookmark::where('id', $update['id'])
                ->where('user_id', Auth::id())
                ->update(['order' => $update['order']]);
        }

        return redirect()->back();
    }

    public function archive(Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->update(['status' => 'archived']);

        return redirect()->back();
    }

    public function unarchive(Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->update(['status' => 'active']);

        return redirect()->back();
    }

    public function trash(Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->update(['status' => 'trashed']);

        return redirect()->back();
    }

    public function restore(Bookmark $bookmark)
    {
        if ($bookmark->user_id !== Auth::id()) {
            abort(403);
        }

        $bookmark->update(['status' => 'active']);

        return redirect()->back();
    }

    public function updatePreferences(Request $request)
    {
        $user = Auth::user();
        $preferences = $user->preferences ?? [];
        
        $user->update([
            'preferences' => array_merge($preferences, $request->all())
        ]);

        return redirect()->back();
    }
}
