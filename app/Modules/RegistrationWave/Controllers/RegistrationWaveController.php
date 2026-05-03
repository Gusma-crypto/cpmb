<?php

namespace App\Modules\RegistrationWave\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\Program\Models\Program;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use App\Modules\RegistrationWave\Requests\StoreRegistrationWaveRequest;
use App\Modules\RegistrationWave\Requests\UpdateRegistrationWaveRequest;
use App\Modules\RegistrationWave\Services\RegistrationWaveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationWaveController extends Controller
{
    public function __construct(private readonly RegistrationWaveService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/RegistrationWaves/Index', [
            'registrationWaves' => $this->service->paginate($request->string('search')->toString()),
            'filters' => ['search' => $request->string('search')->toString()],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/RegistrationWaves/Create', [
            'academicYears' => AcademicYear::query()->orderByDesc('is_active')->orderByDesc('id')->get(),
            'programs' => Program::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreRegistrationWaveRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.registration-waves.index')->with('success', 'Gelombang pendaftaran berhasil ditambahkan.');
    }

    public function edit(RegistrationWave $registrationWave): Response
    {
        return Inertia::render('Admin/RegistrationWaves/Edit', [
            'registrationWave' => $registrationWave->load('programs'),
            'academicYears' => AcademicYear::query()->orderByDesc('is_active')->orderByDesc('id')->get(),
            'programs' => Program::query()->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRegistrationWaveRequest $request, RegistrationWave $registrationWave): RedirectResponse
    {
        $this->service->update($registrationWave, $request->validated());

        return redirect()->route('admin.registration-waves.index')->with('success', 'Gelombang pendaftaran berhasil diperbarui.');
    }

    public function destroy(RegistrationWave $registrationWave): RedirectResponse
    {
        $this->service->delete($registrationWave);

        return redirect()->route('admin.registration-waves.index')->with('success', 'Gelombang pendaftaran berhasil dihapus.');
    }
}
