<?php

namespace App\Modules\Biodata\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Biodata\Models\StudentBiodata;
use App\Modules\Biodata\Requests\StudentBiodataRequest;
use App\Modules\Biodata\Services\StudentBiodataService;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentBiodataController extends Controller
{
    public function __construct(private readonly StudentBiodataService $service)
    {
    }

    public function index(Request $request): Response
    {
        $canManageAll = $this->service->canManageAll($request->user());
        $firstBiodata = $canManageAll
            ? null
            : $this->service->firstFor($request->user())?->load('registration.user');

        return Inertia::render('Modules/Biodata/Index', [
            'biodata' => $this->service->paginateFor($request->user()),
            'studentBiodata' => $firstBiodata,
            'registrations' => $this->service->availableRegistrationsFor($request->user()),
            'canManageAll' => $canManageAll,
            'hasBiodata' => $this->service->hasBiodata($request->user()),
            'registrationOpen' => $canManageAll || $this->registrationWaveIsOpen(),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        if (! $this->service->canManageAll($request->user())) {
            $existingBiodata = $this->service->firstFor($request->user());

            if ($existingBiodata) {
                return redirect()
                    ->route('biodata.show', $existingBiodata)
                    ->with('status', 'Anda sudah pernah mengisi biodata. Silakan periksa atau edit biodata yang sudah ada.');
            }
        }

        return Inertia::render('Modules/Biodata/Create', [
            'studentBiodata' => new StudentBiodata(),
            'registrations' => $this->service->availableRegistrationsFor($request->user()),
            'canManageAll' => $this->service->canManageAll($request->user()),
            'registrationOpen' => $this->service->canManageAll($request->user()) || $this->registrationWaveIsOpen(),
        ]);
    }

    public function store(StudentBiodataRequest $request): RedirectResponse
    {
        $studentBiodata = $this->service->create($request->user(), $request->validated());

        return redirect()
            ->route('biodata.show', $studentBiodata)
            ->with('status', 'Biodata mahasiswa berhasil dibuat.');
    }

    public function show(Request $request, StudentBiodata $student_biodata): Response
    {
        $this->service->authorize($request->user(), $student_biodata);

        return Inertia::render('Modules/Biodata/Show', [
            'studentBiodata' => $student_biodata->load('registration.user'),
        ]);
    }

    public function edit(Request $request, StudentBiodata $student_biodata): Response
    {
        $this->service->authorize($request->user(), $student_biodata);

        return Inertia::render('Modules/Biodata/Edit', [
            'studentBiodata' => $student_biodata->load('registration.user'),
            'registrations' => $this->service->availableRegistrationsFor($request->user())->push($student_biodata->registration),
            'canManageAll' => $this->service->canManageAll($request->user()),
        ]);
    }

    public function update(StudentBiodataRequest $request, StudentBiodata $student_biodata): RedirectResponse
    {
        $studentBiodata = $this->service->update($request->user(), $student_biodata, $request->validated());

        return redirect()
            ->route('biodata.show', $studentBiodata)
            ->with('status', 'Biodata mahasiswa berhasil diperbarui.');
    }

    public function destroy(Request $request, StudentBiodata $student_biodata): RedirectResponse
    {
        $this->service->delete($request->user(), $student_biodata);

        return redirect()
            ->route('biodata.index')
            ->with('status', 'Biodata mahasiswa berhasil dihapus.');
    }

    private function registrationWaveIsOpen(): bool
    {
        return RegistrationWave::query()
            ->where('is_active', true)
            ->where('open_at', '<=', now())
            ->where('close_at', '>=', now())
            ->exists();
    }
}
