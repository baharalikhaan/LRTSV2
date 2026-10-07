<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Security: authentication gates, role authorization and PDF-serving protection.
 *
 * Runs against the dedicated test database (phpunit.xml -> rtsnew_test) and
 * rolls back every test via DatabaseTransactions, so no real data is touched.
 */
class SecurityAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function activeUser(string $type): ?User
    {
        return User::where('type', $type)->where('is_active', 1)->first();
    }

    /** Guest access to protected pages must redirect to the login screen. */
    public function test_guests_are_redirected_to_login_from_protected_pages()
    {
        $protected = ['/home', '/programs', '/users', '/file-explorer', '/admin/system-settings', '/admin/send-email'];

        foreach ($protected as $url) {
            $res = $this->get($url);
            $this->assertContains(
                $res->getStatusCode(),
                [301, 302],
                "Guest should be redirected from {$url} (got {$res->getStatusCode()})"
            );
        }
    }

    /** Admin-only areas must not be reachable by LPI and Reviewer roles. */
    public function test_admin_only_pages_reject_non_admin_roles()
    {
        $adminOnly = ['/file-explorer', '/admin/send-email', '/admin/system-settings'];

        foreach (['LPI', 'Reviewer'] as $type) {
            $user = $this->activeUser($type);
            if (!$user) {
                continue;
            }
            foreach ($adminOnly as $url) {
                $res = $this->actingAs($user)->get($url);
                // Denied access is expressed as 403 (abort) or 302 (redirect home).
                $this->assertContains(
                    $res->getStatusCode(),
                    [302, 403],
                    "{$type} must be denied {$url} (got {$res->getStatusCode()})"
                );
                $this->assertNotSame(200, $res->getStatusCode(), "{$type} must not reach {$url}");
            }
        }
    }

    /** Admin can reach the admin-only pages. */
    public function test_admin_can_reach_admin_only_pages()
    {
        $admin = $this->activeUser('Admin');
        if (!$admin) {
            $this->markTestSkipped('No active admin in the test database.');
        }

        foreach (['/file-explorer', '/admin/system-settings'] as $url) {
            $res = $this->actingAs($admin)->get($url);
            $this->assertSame(200, $res->getStatusCode(), "Admin should receive 200 for {$url}");
        }
    }

    /** Proposal/report PDF serving must require authentication. */
    public function test_pdf_serving_requires_authentication()
    {
        $res = $this->get('/serveFile2?type=proposal&id=1');
        $this->assertContains($res->getStatusCode(), [301, 302], 'serveFile2 must redirect guests to login');
    }

    /** Inactive accounts must not be able to log in with a password. */
    public function test_inactive_users_cannot_authenticate()
    {
        $inactive = User::where('is_active', 0)->first();
        if (!$inactive) {
            $this->markTestSkipped('No inactive user in the test database.');
        }

        $this->assertFalse(
            auth()->attempt(['email' => $inactive->email, 'password' => 'password']),
            'Inactive user must not authenticate'
        );
    }
}
