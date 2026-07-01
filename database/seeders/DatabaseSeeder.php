<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        $academicYearId = DB::table('academic_years')
            ->where('label', '2026/2027')
            ->value('id');

        DB::table('registration_waves')->updateOrInsert(
            [
                'academic_year_id' => $academicYearId,
                'wave_number' => 1,
            ],
            [
                'label' => 'Gelombang 1',
                'open_at' => '2026-06-15 00:00:00',
                'close_at' => '2026-07-17 23:59:59',
                'is_active' => true,
                'description' => 'Gelombang pendaftaran pertama tahun akademik 2026/2027.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $registrationWaveId = DB::table('registration_waves')
            ->where('academic_year_id', $academicYearId)
            ->where('wave_number', 1)
            ->value('id');

        DB::table('programs')
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($program) use ($registrationWaveId): void {
                DB::table('gelombang_program')->updateOrInsert(
                    [
                        'registration_wave_id' => $registrationWaveId,
                        'program_id' => $program->id,
                    ],
                    [
                        'status' => 'aktif',
                        'is_open' => true,
                        'tanggal_mulai' => '2026-06-15 00:00:00',
                        'tanggal_selesai' => '2026-07-17 23:59:59',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            });

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

        $student = User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '081234567890',
                'role' => 'student',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $student->assignRole('student');

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone' => '081234567891',
                'role' => 'admin',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff',
                'phone' => '081234567892',
                'role' => 'staff',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]
        );
        $staff->assignRole('staff');
    }
}
