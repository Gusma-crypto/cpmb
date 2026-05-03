<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $student = Role::firstOrCreate(['name' => 'student']);
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $dosen = Role::firstOrCreate(['name' => 'dosen']);

        $permissions = collect([
            'registration.view',
            'registration.export',
            'exam-schedule.view',
            'exam-schedule.create',
            'exam-schedule.update',
            'exam-schedule.delete',
            'exam-schedule.assign',
            'exam-schedule.student-view',
            'exam-schedule.supervisor-view',
            'exam-room.view',
            'exam-room.create',
            'exam-room.update',
            'exam-room.delete',
            'exam-room.assign',
            'exam-room.capacity-view',
            'exam-room.student-view',
            'exam-room.supervisor-view',
            'exam-card.view',
            'exam-card.generate',
            'exam-card.print',
            'exam-card.reprint',
            'exam-card.student-view',
            'question-bank.view',
            'question-bank.create',
            'question-bank.update',
            'question-bank.delete',
            'cbt-exam.view',
            'cbt-exam.create',
            'cbt-exam.update',
            'cbt-exam.delete',
            'cbt-exam.publish',
            'cbt-exam.close',
        ])->map(fn (string $name) => Permission::firstOrCreate(['name' => $name]));

        $admin->syncPermissions($permissions);
        $superadmin->syncPermissions($permissions);
        $staff->givePermissionTo([
            'registration.view',
            'exam-schedule.view',
            'exam-schedule.create',
            'exam-schedule.update',
            'exam-schedule.assign',
            'exam-room.view',
            'exam-room.create',
            'exam-room.update',
            'exam-room.assign',
            'exam-room.capacity-view',
            'exam-card.view',
            'exam-card.generate',
            'exam-card.print',
        ]);
        $student->givePermissionTo([
            'exam-schedule.student-view',
            'exam-room.student-view',
            'exam-card.student-view',
            'exam-card.print',
        ]);
        $dosen->givePermissionTo([
            'exam-schedule.supervisor-view',
            'exam-room.supervisor-view',
        ]);
    }
}
