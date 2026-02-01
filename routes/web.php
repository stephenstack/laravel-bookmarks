<?php

use App\Http\Controllers\Bookmarks\BookmarkController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return redirect('/bookmarks/all');
    })->name('dashboard');
    Route::get('bookmarks/all', [BookmarkController::class, 'index'])->defaults('view', 'all')->name('bookmarks.all');
    Route::get('bookmarks/favorites', [BookmarkController::class, 'index'])->defaults('view', 'favorites')->name('bookmarks.favorites');
    Route::get('bookmarks/archive', [BookmarkController::class, 'index'])->defaults('view', 'archive')->name('bookmarks.archive');
    Route::get('bookmarks/trash', [BookmarkController::class, 'index'])->defaults('view', 'trash')->name('bookmarks.trash');
    Route::get('bookmarks/collection/{slug}', [BookmarkController::class, 'index'])->name('bookmarks.collection');
    Route::get('bookmarks/tags/{tag}', [BookmarkController::class, 'index'])->name('bookmarks.tag');

    Route::post('bookmarks/interrogate-url', [BookmarkController::class, 'interrogateUrl'])->name('bookmarks.interrogate-url');
    Route::post('bookmarks/reorder', [BookmarkController::class, 'reorder'])->name('bookmarks.reorder');
    Route::post('bookmarks/preferences', [BookmarkController::class, 'updatePreferences'])->name('bookmarks.preferences');
    Route::post('bookmarks/upload-image', [App\Http\Controllers\Bookmarks\ImageUploadController::class, 'upload'])->name('bookmarks.upload-image');
    Route::delete('bookmarks/delete-image', [App\Http\Controllers\Bookmarks\ImageUploadController::class, 'delete'])->name('bookmarks.delete-image');

    Route::post('bookmarks/{bookmark}/archive', [BookmarkController::class, 'archive'])->name('bookmarks.archive.action');
    Route::post('bookmarks/{bookmark}/unarchive', [BookmarkController::class, 'unarchive'])->name('bookmarks.unarchive.action');
    Route::post('bookmarks/{bookmark}/trash', [BookmarkController::class, 'trash'])->name('bookmarks.trash.action');
    Route::post('bookmarks/{bookmark}/restore', [BookmarkController::class, 'restore'])->name('bookmarks.restore.action');

    Route::post('bookmarks/company/{id}/favorite', [BookmarkController::class, 'toggleCompanyFavorite'])->name('bookmarks.company.favorite');

    Route::resource('bookmarks', BookmarkController::class)->only(['store', 'update', 'destroy']);
    Route::resource('collections', App\Http\Controllers\Bookmarks\CollectionController::class)->only(['store', 'update', 'destroy']);
    Route::resource('tags', App\Http\Controllers\Bookmarks\TagController::class)->only(['store', 'update', 'destroy']);
});

Route::middleware(['auth', 'verified', App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
    Route::get('admin/settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'edit'])->name('admin.settings');
    Route::post('admin/settings', [App\Http\Controllers\Admin\SiteSettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('admin/company-bookmarks', [App\Http\Controllers\Admin\SiteSettingsController::class, 'updateCompanyBookmarks'])->name('admin.company-bookmarks.update');
    Route::post('admin/interrogate-url', [App\Http\Controllers\Admin\SiteSettingsController::class, 'interrogateUrl'])->name('admin.interrogate-url');
    Route::post('admin/test-email', [App\Http\Controllers\Admin\SiteSettingsController::class, 'testEmail'])->name('admin.test-email');
});

require __DIR__ . '/settings.php';
