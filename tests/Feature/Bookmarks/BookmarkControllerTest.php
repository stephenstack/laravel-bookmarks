<?php

namespace Tests\Feature\Bookmarks;

use App\Models\Bookmark;
use App\Models\Collection;
use App\Models\CompanyBookmark;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BookmarkControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup a configured system for most tests
        \App\Models\Setting::set('company_collection_title', 'Test Company');
        CompanyBookmark::create(['title' => 'Test', 'url' => 'https://test.com']);
    }

    public function test_user_can_view_all_bookmarks()
    {
        $user = User::factory()->create();
        Bookmark::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get('/bookmarks/all')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookmarks/Index')
                ->has('initialBookmarks')
            );
    }

    public function test_user_can_view_favorites()
    {
        $user = User::factory()->create();
        Bookmark::factory()->create(['user_id' => $user->id, 'is_favorite' => false]);
        Bookmark::factory()->create(['user_id' => $user->id, 'is_favorite' => true]);

        $this->actingAs($user)
            ->get('/bookmarks/favorites')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookmarks/Index')
                ->where('initialView', 'favorites')
            );
    }

    public function test_user_can_view_archive()
    {
        $user = User::factory()->create();
        Bookmark::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Bookmark::factory()->create(['user_id' => $user->id, 'status' => 'archived']);

        $this->actingAs($user)
            ->get('/bookmarks/archive')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookmarks/Index')
                ->where('initialView', 'archive')
            );
    }

    public function test_user_can_view_trash()
    {
        $user = User::factory()->create();
        Bookmark::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Bookmark::factory()->create(['user_id' => $user->id])->delete(); // Soft deleted

        $this->actingAs($user)
            ->get('/bookmarks/trash')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Bookmarks/Index')
                ->where('initialView', 'trash')
                ->has('initialBookmarks')
            );
    }

    public function test_can_create_bookmark()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post('/bookmarks', [
                'title' => 'New Bookmark',
                'url' => 'https://google.com',
                'collection_id' => $collection->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookmarks', [
            'title' => 'New Bookmark',
            'user_id' => $user->id
        ]);
    }

    public function test_can_update_bookmark()
    {
        $user = User::factory()->create();
        $bookmark = Bookmark::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->put("/bookmarks/{$bookmark->id}", [
                'title' => 'Updated Title',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookmarks', [
            'id' => $bookmark->id,
            'title' => 'Updated Title'
        ]);
    }

    public function test_can_archive_bookmark()
    {
        $user = User::factory()->create();
        $bookmark = Bookmark::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $response = $this->actingAs($user)
            ->post("/bookmarks/{$bookmark->id}/archive");

        $response->assertRedirect();
        $this->assertEquals('archived', $bookmark->refresh()->status);
    }

    public function test_can_trash_and_restore_bookmark()
    {
        $user = User::factory()->create();
        $bookmark = Bookmark::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $this->actingAs($user)->post("/bookmarks/{$bookmark->id}/trash");
        $this->assertEquals('trashed', $bookmark->refresh()->status);

        $this->actingAs($user)->post("/bookmarks/{$bookmark->id}/restore");
        $this->assertEquals('active', $bookmark->refresh()->status);
    }

    public function test_can_toggle_company_favorite()
    {
        $user = User::factory()->create();
        $companyBookmark = CompanyBookmark::first();

        $this->actingAs($user)->post("/bookmarks/company/{$companyBookmark->id}/favorite");
        $this->assertCount(1, $user->refresh()->favoriteCompanyBookmarks);

        $this->actingAs($user)->post("/bookmarks/company/{$companyBookmark->id}/favorite");
        $this->assertCount(0, $user->refresh()->favoriteCompanyBookmarks);
    }

    public function test_interrogate_url_returns_data()
    {
        $user = User::factory()->create();
        
        // Mock Guzzle if possible, but for vibe coding we might just assert 200 or 422 
        // since we didn't setup full mocking environment.
        // Let's try a real URL that should exist (google.com)
        $response = $this->actingAs($user)
            ->postJson('/bookmarks/interrogate-url', ['url' => 'https://www.google.com']);
        
        $response->assertStatus(200)
            ->assertJsonStructure(['title', 'favicon', 'description']);
    }
}
