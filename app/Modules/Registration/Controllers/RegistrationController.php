<?php

namespace App\Modules\Registration\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamCard\Models\ExamCard;
use App\Modules\Registration\Models\Registration;
use App\Modules\Registration\Requests\RevisionRegistrationRequest;
use App\Modules\Registration\Requests\StoreRegistrationRequest;
use App\Modules\Registration\Services\RegistrationExportService;
use App\Modules\Registration\Services\RegistrationService;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationService $service,
        private readonly RegistrationExportService $exportService,
    )
    {
    }

    public function index(Request $request): Response
    {
        if ($this->service->canManageAll($request->user())) {
            abort_unless($this->service->canViewRegistrationList($request->user()), 403);

            $filters = $this->service->filtersFrom($request->query());
            $routePrefix = $this->routePrefix($request);

            return Inertia::render('Admin/Registrations/Index', [
                'registrations' => $this->service->paginateFor($request->user(), $filters),
                'filters' => $filters,
                'statusOptions' => RegistrationService::REGISTRATION_STATUSES,
                'canExport' => $this->service->canExport($request->user()),
                'routePrefix' => $routePrefix,
            ]);
        }

        $existingRegistration = $request->user()->registration()->with(['biodata', 'documents'])->first();

        return Inertia::render('Modules/Registration/Index', [
            'registrations' => $this->service->paginateFor($request->user()),
            'canManageAll'  => $this->service->canManageAll($request->user()),
            'hasRegistration' => $existingRegistration !== null,
            'existingRegistration' => $existingRegistration,
            'requiredDocs' => RegistrationService::REQUIRED_DOCUMENTS,
        ]);
    }

    public function exportPdf(Request $request): SymfonyResponse
    {
        abort_unless($this->service->canExport($request->user()), 403);

        return $this->exportService->pdf(
            $this->service->exportFor($request->user(), $request->query())
        );
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        abort_unless($this->service->canExport($request->user()), 403);

        return $this->exportService->excel(
            $this->service->exportFor($request->user(), $request->query())
        );
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $existingRegistration = $request->user()->registration()->first();

        if (! $this->service->canManageAll($request->user()) && $existingRegistration) {
            return redirect()
                ->route('registrations.show', $existingRegistration)
                ->with('info', 'Anda sudah memiliki pendaftaran. Silakan lengkapi biodata dan dokumen.');
        }

        return Inertia::render('Modules/Registration/Create', [
            'academicYears' => DB::table('academic_years')->where('is_active', true)->orderByDesc('id')->get(),
            'programs'      => [],
            'registrationWaves' => $this->availableRegistrationWaves(),
        ]);
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $registration = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('registrations.show', $registration)
            ->with('success', 'Pendaftaran berhasil dibuat.');
    }

    public function show(Request $request, Registration $registration): Response
    {
        $this->service->authorize($request->user(), $registration);

        $submitCaptcha = null;

        if (! $this->service->canManageAll($request->user()) && in_array($registration->status, ['draft', 'revision_required'], true)) {
            $submitCaptcha = strtoupper(Str::random(5));
            $request->session()->put("registration_submit_captcha_{$registration->id}", $submitCaptcha);
        }

        return Inertia::render('Modules/Registration/Show', [
            'registration'    => $registration->load([
                'user',
                'biodata',
                'documents',
                'cbtResults.cbtExam',
            ]),
            'canManageAll'    => $this->service->canManageAll($request->user()),
            'requiredDocs'    => RegistrationService::REQUIRED_DOCUMENTS,
            'submitCaptcha'   => $submitCaptcha,
            'hasExamCard'     => ExamCard::query()->where('registration_id', $registration->id)->exists(),
        ]);
    }

    public function submit(Request $request, Registration $registration): RedirectResponse
    {
        if (! $this->service->canManageAll($request->user())) {
            $request->validate([
                'review_biodata' => ['accepted'],
                'review_ijazah' => ['accepted'],
                'review_ktp' => ['accepted'],
                'review_photo' => ['accepted'],
                'declaration' => ['accepted'],
                'captcha' => [
                    'required',
                    function (string $attribute, mixed $value, \Closure $fail) use ($request, $registration): void {
                        $expected = $request->session()->get("registration_submit_captcha_{$registration->id}");

                        if (! $expected || strtoupper((string) $value) !== $expected) {
                            $fail('Kode captcha tidak sesuai.');
                        }
                    },
                ],
            ], [
                'review_biodata.accepted' => 'Checklist biodata wajib dicentang.',
                'review_ijazah.accepted' => 'Checklist ijazah wajib dicentang.',
                'review_ktp.accepted' => 'Checklist KTP wajib dicentang.',
                'review_photo.accepted' => 'Checklist pasphoto wajib dicentang.',
                'declaration.accepted' => 'Pernyataan kebenaran data wajib dicentang.',
                'captcha.required' => 'Kode captcha wajib diisi.',
            ]);
        }

        $this->service->submit($request->user(), $registration);

        $request->session()->forget("registration_submit_captcha_{$registration->id}");

        return redirect()
            ->route('registrations.show', $registration)
            ->with('success', 'Pendaftaran berhasil disubmit.');
    }

    public function verify(Request $request, Registration $registration): RedirectResponse
    {
        $this->service->verify($request->user(), $registration);

        return redirect()
            ->route('registrations.show', $registration)
            ->with('success', 'Pendaftaran berhasil diverifikasi.');
    }

    public function startReview(Request $request, Registration $registration): RedirectResponse
    {
        $this->service->startReview($request->user(), $registration);

        return redirect()
            ->route('registrations.show', $registration)
            ->with('success', 'Pendaftaran masuk proses review.');
    }

    public function requestRevision(RevisionRegistrationRequest $request, Registration $registration): RedirectResponse
    {
        $this->service->requestRevision($request->user(), $registration, $request->validated('revision_notes'));

        return redirect()
            ->route('registrations.show', $registration)
            ->with('warning', 'Pendaftaran dikembalikan untuk revisi.');
    }

    public function reject(Request $request, Registration $registration): RedirectResponse
    {
        $this->service->reject($request->user(), $registration);

        return redirect()
            ->route('registrations.show', $registration)
            ->with('warning', 'Pendaftaran ditolak.');
    }

    private function routePrefix(Request $request): string
    {
        $routeName = $request->route()?->getName() ?? 'registrations.index';

        if (Str::startsWith($routeName, 'admin.')) {
            return 'admin';
        }

        if (Str::startsWith($routeName, 'staff.')) {
            return 'staff';
        }

        return '';
    }

    private function availableRegistrationWaves()
    {
        return RegistrationWave::query()
            ->with('academicYear:id,label')
            ->with(['programs' => function ($query): void {
                $query
                    ->select('programs.*')
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
                    })
                    ->orderBy('programs.name');
            }])
            ->where('is_active', true)
            ->where('open_at', '<=', now())
            ->where('close_at', '>=', now())
            ->orderBy('open_at')
            ->get()
            ->map(function (RegistrationWave $wave): RegistrationWave {
                $wave->setRelation('programs', $wave->programs->values());

                return $wave;
            })
            ->filter(fn (RegistrationWave $wave): bool => $wave->programs->isNotEmpty())
            ->values();
    }
}
