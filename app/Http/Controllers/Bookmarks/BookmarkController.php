<?php

namespace App\Http\Controllers\Bookmarks;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Bookmark;
use App\Models\Collection;
use App\Models\CompanyBookmark;
use App\Models\Setting;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class BookmarkController extends Controller
{
    public function interrogateUrl(Request $request)
    {
        $request->validate(['url' => 'required|url']);

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5, 'verify' => false]);
            $response = $client->get($request->url);
            $html = (string) $response->getBody();

            $title = '';
            if (preg_match('/<title>(.*?)<\/title>/is', $html, $matches)) {
                $title = trim($matches[1]);
            }

            $description = '';
            if (preg_match('/<meta name="description" content="(.*?)"/is', $html, $matches)) {
                $description = trim($matches[1]);
            } elseif (preg_match('/<meta property="og:description" content="(.*?)"/is', $html, $matches)) {
                $description = trim($matches[1]);
            }

            $favicon = '';
            $parsedUrl = parse_url($request->url);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

            if (preg_match('/<link rel="(?:shortcut )?icon" [^>]*?href="(.*?)"/is', $html, $matches)) {
                $favicon = $matches[1];
            } elseif (preg_match('/<link [^>]*?href="(.*?)" [^>]*?rel="(?:shortcut )?icon"/is', $html, $matches)) {
                $favicon = $matches[1];
            }

            if ($favicon && !filter_var($favicon, FILTER_VALIDATE_URL)) {
                $favicon = rtrim($baseUrl, '/') . '/' . ltrim($favicon, '/');
            }

            if (!$favicon) {
                $favicon = $baseUrl . '/favicon.ico';
            }

            $imageUrl = '';
            if (preg_match('/<meta property="og:image" content="(.*?)"/is', $html, $matches)) {
                $imageUrl = $matches[1];
            }

            return response()->json([
                'title' => html_entity_decode($title),
                'description' => html_entity_decode($description),
                'favicon' => $favicon,
                'image_url' => $imageUrl,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not fetch URL info'], 422);
        }
    }

    public function index(Request $request, $slug = null, $tag = null)
    {
        $user = Auth::user();

        // Fetch User Data - Include trashed so they are available in the store
        $bookmarks = $user->bookmarks()->withTrashed()->orderBy('order')->with(['tags', 'collection'])->get();
        $collections = $user->collections()->orderBy('order')->withCount('bookmarks')->get();
        $tags = $user->tags()->withCount('bookmarks')->get();

        // Fetch Company Settings
        $companyTagName = Setting::get('company_tag_name', 'Company');
        $companyTagColor = Setting::get('company_tag_color', 'blue');
        $companyTag = [
            'id' => 'company-tag',
            'name' => $companyTagName,
            'color' => $companyTagColor,
        ];

        $favoriteCompanyBookmarkIds = $user->favoriteCompanyBookmarks()->pluck('company_bookmarks.id')->toArray();

        $companyBookmarks = CompanyBookmark::all()->map(function ($item) use ($companyTag, $favoriteCompanyBookmarkIds) {
            return [
                'id' => 'company-' . $item->id,
                'title' => $item->title,
                'url' => $item->url,
                'description' => $item->description,
                'favicon' => $item->favicon,
                'image_url' => $item->image_url,
                'is_favorite' => in_array($item->id, $favoriteCompanyBookmarkIds),
                'collection_id' => 'company-resources',
                'is_company' => true,
                'tags' => [$companyTag],
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => null,
                'status' => 'active'
            ];
        });

        $companyCollection = [
            'id' => 'company-resources',
            'name' => Setting::get('company_collection_title', 'Company Resources'),
            'slug' => 'company-resources',
            'icon' => Setting::get('company_collection_icon', 'Building'),
            'color' => Setting::get('company_collection_color', 'blue'),
            'is_system' => true,
            'count' => $companyBookmarks->count(),
        ];

        // --- View Detection ---
        $initialView = $request->route('view'); // This comes from defaults() in web.php
        $initialCollection = null;
        $initialTag = null;

        if ($slug) {
            if ($slug === 'company-resources') {
                $initialCollection = 'company-resources';
            } else {
                $collection = $collections->firstWhere('slug', $slug);
                if ($collection) {
                    $initialCollection = (string) $collection->id;
                }
            }
        } elseif ($tag) {
            $tagModel = $tags->firstWhere('slug', $tag);
            if ($tagModel) {
                $initialTag = $tagModel->id;
            }
        } elseif (!$initialView) {
            // Default if we just hit /dashboard or similar
            $initialCollection = 'all';
        }

        return Inertia::render('Bookmarks/Index', [
            'initialBookmarks' => $bookmarks->concat($companyBookmarks)->values()->all(),
            'collections' => $collections->map(function ($c) {
                $c->count = $c->bookmarks_count;
                return $c;
            })->push($companyCollection),
            'tags' => $tags->map(function ($t) {
                $t->count = $t->bookmarks_count;
                return $t;
            })->push(array_merge($companyTag, ['count' => $companyBookmarks->count()])),
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
            'description' => 'nullable|string',
            'favicon' => 'nullable|string',
            'image_url' => 'nullable|string',
            'collection_id' => 'nullable|exists:collections,id',
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
        ]);

        $bookmark = Auth::user()->bookmarks()->create([
            'title' => $validated['title'],
            'url' => $validated['url'],
            'description' => $validated['description'] ?? null,
            'favicon' => $validated['favicon'] ?? null,
            'image_url' => $validated['image_url'] ?? null,
            'collection_id' => $validated['collection_id'] ?? null,
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
            'description' => 'nullable|string',
            'favicon' => 'nullable|string',
            'image_url' => 'nullable|string',
            'collection_id' => 'nullable|exists:collections,id',
            'is_favorite' => 'boolean',
            'tags' => 'array|nullable',
            'tags.*' => 'exists:tags,id',
        ]);

        $bookmark->update($request->only(['title', 'url', 'description', 'collection_id', 'is_favorite', 'favicon', 'image_url']));

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

    public function toggleCompanyFavorite(Request $request, $id)
    {
        $user = Auth::user();
        $id = str_replace('company-', '', $id);

        $exists = $user->favoriteCompanyBookmarks()->where('company_bookmark_id', $id)->exists();

        if ($exists) {
            $user->favoriteCompanyBookmarks()->detach($id);
        } else {
            $user->favoriteCompanyBookmarks()->attach($id);
        }

        return redirect()->back();
    }
}
