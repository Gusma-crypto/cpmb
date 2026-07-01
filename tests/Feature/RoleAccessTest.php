<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_student_cannot_access_admin_dashboard()
    {
        // 1. Buat user dengan role student
        $user = User::factory()->create([
            'role' => 'student',
        ]);
        $user->syncLegacyRoleToSpatie();

        // 2. Bertindak sebagai user ini, coba akses halaman admin
        $response = $this->actingAs($user)->get('/admin/dashboard');

        // 3. Pastikan akses ditolak (403)
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);
        $user->syncLegacyRoleToSpatie();

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }
}
