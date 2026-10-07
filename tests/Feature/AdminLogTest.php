<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Admin-only system log viewer (issue logger) and its access control.
 */
class AdminLogTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    public function test_admin_can_view_the_log_viewer()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $res = $this->actingAs($admin)->get('/admin/logs');

        $res->assertStatus(200);
        $res->assertSee('System Logs');
    }

    public function test_non_admins_cannot_view_the_log_viewer()
    {
        foreach (['LPI', 'Reviewer'] as $type) {
            $user = $this->user($type);
            if (!$user) {
                continue;
            }
            $this->actingAs($user)->get('/admin/logs')->assertStatus(403);
        }
    }

    public function test_non_admins_cannot_clear_logs()
    {
        // Access control must reject before any file is touched.
        foreach (['LPI', 'Reviewer'] as $type) {
            $user = $this->user($type);
            if (!$user) {
                continue;
            }
            $this->actingAs($user)->post('/admin/logs/clear')->assertStatus(403);
        }
    }

    public function test_guests_cannot_view_the_log_viewer()
    {
        $this->get('/admin/logs')->assertRedirect('/login');
    }
}
