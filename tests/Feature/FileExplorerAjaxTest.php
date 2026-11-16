<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * File Explorer project list (server-side DataTable).
 *
 * DataTables sends length = -1 when the page-length selector is set to
 * "All". That produced an OFFSET without a LIMIT, which MySQL/MariaDB
 * rejects — the table then failed with an Ajax error.
 */
class FileExplorerAjaxTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): ?User
    {
        return User::where('type', 'like', '%Admin%')->first();
    }

    public function test_the_ajax_list_returns_data()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No admin in the test database.');
        }

        $res = $this->actingAs($admin)->get('/file-explorer/ajax-list?draw=1&start=0&length=10');

        $res->assertStatus(200);
        $res->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
    }

    public function test_the_ajax_list_handles_the_all_page_length_option()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No admin in the test database.');
        }

        // length = -1 means "show all records" for DataTables.
        $res = $this->actingAs($admin)->get('/file-explorer/ajax-list?draw=1&start=0&length=-1');

        $res->assertStatus(200);
        $res->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data']);
        $this->assertIsArray($res->json('data'));
    }

    public function test_the_ajax_list_handles_a_zero_length()
    {
        $admin = $this->admin();
        if (!$admin) {
            $this->markTestSkipped('No admin in the test database.');
        }

        $res = $this->actingAs($admin)->get('/file-explorer/ajax-list?draw=1&start=0&length=0');

        $res->assertStatus(200);
        $this->assertIsArray($res->json('data'));
    }
}
