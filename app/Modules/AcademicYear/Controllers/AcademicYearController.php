<?php

namespace App\Modules\AcademicYear\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\AcademicYear\Requests\StoreAcademicYearRequest;
use App\Modules\AcademicYear\Requests\UpdateAcademicYearRequest;
use App\Modules\AcademicYear\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AcademicYearController extends Controller
{
    public function __construct(private readonly AcademicYearService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/AcademicYears/Index', [
            'academicYears' => $this->service->paginate($request->string('search')->toString()),
            'filters' => ['search' => $request->string('search')->toString()],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AcademicYears/Create');
    }

    public function store(StoreAcademicYearRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function edit(AcademicYear $academicYear): Response
    {
        return Inertia::render('Admin/AcademicYears/Edit', [
            'academicYear' => $academicYear,
        ]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $this->service->update($academicYear, $request->validated());

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if (! $this->service->delete($academicYear)) {
            return redirect()
                ->route('admin.academic-years.index')
                ->with('error', 'Tahun akademik tidak dapat dihapus karena sudah digunakan pada data pendaftaran. Silakan nonaktifkan statusnya.');
        }

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function deactivate(AcademicYear $academicYear): RedirectResponse
    {
        $this->service->deactivate($academicYear);

        return redirect()->route('admin.academic-years.index')->with('success', 'Tahun akademik berhasil dinonaktifkan.');
    }
}
