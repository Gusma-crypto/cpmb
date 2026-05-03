<?php

namespace App\Modules\Program\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Program\Models\Program;
use App\Modules\Program\Requests\StoreProgramRequest;
use App\Modules\Program\Requests\UpdateProgramRequest;
use App\Modules\Program\Services\ProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function __construct(private readonly ProgramService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Programs/Index', [
            'programs' => $this->service->paginate($request->string('search')->toString()),
            'filters' => ['search' => $request->string('search')->toString()],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Programs/Create');
    }

    public function store(StoreProgramRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil ditambahkan.');
    }

    public function edit(Program $program): Response
    {
        return Inertia::render('Admin/Programs/Edit', [
            'program' => $program,
        ]);
    }

    public function update(UpdateProgramRequest $request, Program $program): RedirectResponse
    {
        $this->service->update($program, $request->validated());

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil diperbarui.');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->service->delete($program);

        return redirect()->route('admin.programs.index')->with('success', 'Program berhasil dihapus.');
    }

}
