<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         // reset cache spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->call(RolePermissionSeeder::class);

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'staff']);
        Role::firstOrCreate(['name' => 'dosen']);
        
        DB::table('academic_years')->updateOrInsert(
            ['label' => '2026/2027'],
            [
                'is_active' => true,
                'registration_open_at' => now()->subMonth(),
                'registration_close_at' => now()->addMonths(3),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('programs')->updateOrInsert(
            ['code' => 'TI'],
            [
                'name' => 'Teknik Informatika',
                'level' => 'S1',
                'faculty' => 'Program Sarjana',
                'description' => 'Program studi Teknik Informatika',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('programs')->updateOrInsert(
            ['code' => 'SI'],
            [
                'name' => 'Sistem Informasi',
                'level' => 'S1',
                'faculty' => 'Program Sarjana',
                'description' => 'Program studi Sistem Informasi',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('exam_time_slots')->updateOrInsert(
            ['name' => 'Sesi 1'],
            [
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'description' => 'Sesi ujian pagi',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('exam_time_slots')->updateOrInsert(
            ['name' => 'Sesi 2'],
            [
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'description' => 'Sesi ujian siang ',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

         DB::table('exam_time_slots')->updateOrInsert(
            ['name' => 'Sesi 3'],
            [
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'description' => 'Sesi ujian siang',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('exam_time_slots')->updateOrInsert(
            ['name' => 'Sesi 3'],
            [
                'start_time' => '15:00:00',
                'end_time' => '17:00:00',
                'description' => 'Sesi ujian sore',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $student = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'student',
        ]);
        $student->assignRole('student');

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);
        $admin->assignRole('admin');

        $staff = User::factory()->create([
            'name' => 'Staff',
            'email' => 'staff@example.com',
            'role' => 'staff',
        ]);
        $staff->assignRole('staff');
    }
}
