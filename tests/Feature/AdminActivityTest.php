<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * User activity log: sign-ins (with IP + duration) and actions, plus the
 * admin-only viewer.
 */
class AdminActivityTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    public function test_admin_can_view_activity_page()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $res = $this->actingAs($admin)->get('/admin/activity');

        $res->assertStatus(200);
        $res->assertSee('User Activity');
    }

    public function test_non_admins_cannot_view_activity_page()
    {
        foreach (['LPI', 'Reviewer'] as $type) {
            $user = $this->user($type);
            if (!$user) {
                continue;
            }
            $this->actingAs($user)->get('/admin/activity')->assertStatus(403);
        }
    }

    public function test_guests_are_redirected_from_activity_page()
    {
        $this->get('/admin/activity')->assertRedirect('/login');
    }

    public function test_login_records_an_activity_entry()
    {
        $user = $this->user('Admin') ?? User::where('is_active', 1)->first();

        event(new Login('web', $user, false));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'event'   => 'login',
        ]);
    }

    public function test_logout_records_session_duration()
    {
        $user = $this->user('Admin') ?? User::where('is_active', 1)->first();

        // Simulate a session that started 5 minutes ago.
        session(['activity_login_at' => now()->subMinutes(5)->timestamp]);

        event(new Logout('web', $user));

        $row = ActivityLog::where('event', 'logout')->where('user_id', $user->id)->first();

        $this->assertNotNull($row);
        $this->assertGreaterThanOrEqual(299, (int) $row->duration_seconds);
        $this->assertNotNull($row->duration_for_humans);
    }

    public function test_authenticated_request_records_view_activity()
    {
        $admin = $this->user('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        $this->actingAs($admin)->get('/home');

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'event'   => 'view',
        ]);
    }
}
