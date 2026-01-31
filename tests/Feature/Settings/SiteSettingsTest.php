<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Models\Setting;
use App\Models\CompanyBookmark;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('company_collection_title', 'Test Company');
        CompanyBookmark::create(['title' => 'Test', 'url' => 'https://test.com']);
    }

    public function test_admin_can_view_settings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/admin/settings')
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/SiteSettings')
            );
    }

    public function test_non_admin_cannot_view_settings()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get('/admin/settings')
            ->assertStatus(403);
    }

    public function test_admin_can_update_general_settings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)
            ->post('/admin/settings', [
                'site_title' => 'Updated Title',
            ]);

        $response->assertRedirect();
        $this->assertEquals('Updated Title', Setting::get('site_title'));
    }

    public function test_admin_can_update_company_bookmarks()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Update Title via general settings
        $this->actingAs($admin)
            ->post('/admin/settings', [
                'company_collection_title' => 'New Resources',
            ]);

        // Update Bookmarks via specific endpoint
        $response = $this->actingAs($admin)
            ->post('/admin/company-bookmarks', [
                'bookmarks' => [
                    ['title' => 'New Link', 'url' => 'https://new.com']
                ]
            ]);

        $response->assertRedirect();
        $this->assertEquals('New Resources', Setting::get('company_collection_title'));
        $this->assertDatabaseHas('company_bookmarks', ['title' => 'New Link']);
    }

    public function test_test_email_endpoint()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Mock mail
        \Illuminate\Support\Facades\Mail::fake();

        $response = $this->actingAs($admin)
            ->postJson('/admin/test-email', [
                'email' => 'test@example.com'
            ]);

        $response->assertStatus(200);
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\TestMail::class);
    }
}
