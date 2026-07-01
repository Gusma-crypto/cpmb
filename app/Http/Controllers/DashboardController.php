<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtResult;
use App\Modules\Registration\Models\Registration;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $user->syncLegacyRoleToSpatie();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('staff')) {
            return redirect()->route('staff.dashboard');
        }

        if ($user->hasRole('dosen')) {
            return redirect()->route('dosen.dashboard');
        }

        return Inertia::render('Dashboard', [
            'registration' => $user->registration,
            'cbtSummary' => $this->studentCbtSummary($user),
            'registrationWaves' => $this->activeRegistrationWaves(),
            'loginStats' => [
                'login_count' => $user->login_count ?? 0,
                'last_login_at' => $user->last_login_at,
                'previous_login_at' => $user->previous_login_at,
                'last_login_ip' => $user->last_login_ip,
            ],
        ]);
    }

    public function admin(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_students'      => User::where('role', 'student')->count(),
                'total_registrations' => Registration::count(),
                'pending'             => Registration::whereIn('status', ['submitted', 'under_review'])->count(),
                'exam_ready'          => Registration::where('status', 'exam_ready')->count(),
                'accepted'            => Registration::where('status', 'accepted')->count(),
                'cbt_attempts'         => CbtAttempt::count(),
                'cbt_passed'           => CbtResult::where('is_passed', true)->count(),
                'cbt_average_score'    => round((float) CbtResult::avg('final_score'), 2),
                'cbt_unpublished'      => CbtResult::whereNull('published_at')->count(),
            ],
            'recentRegistrations' => Registration::with('user')
                ->latest()
                ->take(5)
                ->get(['id', 'registration_number', 'status', 'created_at', 'user_id']),
        ]);
    }

    public function staff(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_students'      => User::where('role', 'student')->count(),
                'total_registrations' => Registration::count(),
                'pending'             => Registration::whereIn('status', ['submitted', 'under_review'])->count(),
                'exam_ready'          => Registration::where('status', 'exam_ready')->count(),
                'accepted'            => Registration::where('status', 'accepted')->count(),
            ],
            'recentRegistrations' => Registration::with('user')
                ->latest()
                ->take(5)
                ->get(['id', 'registration_number', 'status', 'created_at', 'user_id']),
        ]);
    }

    public function dosen(): Response
    {
        return Inertia::render('Dashboard', [
            'registration' => null,
            'cbtSummary' => null,
        ]);
    }

    private function studentCbtSummary(User $user): array
    {
        $publishedResults = CbtResult::query()
            ->where('user_id', $user->id)
            ->whereNotNull('published_at');

        return [
            'attempts' => CbtAttempt::where('user_id', $user->id)->count(),
            'published_results' => (clone $publishedResults)->count(),
            'best_score' => round((float) (clone $publishedResults)->max('final_score'), 2),
            'passed' => (clone $publishedResults)->where('is_passed', true)->count(),
        ];
    }

    private function activeRegistrationWaves()
    {
        return RegistrationWave::query()
            ->with('academicYear:id,label')
            ->whereHas('programs', function ($query): void {
                $query
                    ->where('programs.is_active', true)
                    ->where('gelombang_program.status', 'aktif')
                    ->where('gelombang_program.is_open', true)
                    ->where(function ($query): void {
                        $query->whereNull('gelombang_program.tanggal_mulai')
                            ->orWhere('gelombang_program.tanggal_mulai', '<=', now());
                    })
                    ->where(function ($query): void {
                        $query->whereNull('gelombang_program.tanggal_selesai')
                            ->orWhere('gelombang_program.tanggal_selesai', '>=', now());
                    });
            })
            ->where('is_active', true)
            ->where('open_at', '<=', now())
            ->where('close_at', '>=', now())
            ->orderBy('open_at')
            ->get();
    }
}
