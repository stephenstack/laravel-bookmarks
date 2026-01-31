<?php

use App\Models\User;
use App\Models\Bookmark;
use App\Models\Collection;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('bookmarks index page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Bookmarks/Index')
            ->has('initialBookmarks')
            ->has('collections')
            ->has('tags')
        );
});


test('bookmarks index loads user data', function () {
    $user = User::factory()->create();
    Collection::factory()->create(['user_id' => $user->id]);
    Bookmark::factory()->create(['user_id' => $user->id]);
    
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});
