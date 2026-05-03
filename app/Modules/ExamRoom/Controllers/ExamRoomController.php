<?php

namespace App\Modules\ExamRoom\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ExamRoom\Models\ExamRoom;
use App\Modules\ExamRoom\Services\ExamRoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ExamRoomController extends Controller
{
    public function __construct(private readonly ExamRoomService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/ExamRooms/Index', [
            'rooms' => $this->service->paginate($request->string('search')->toString()),
            'filters' => ['search' => $request->string('search')->toString()],
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Admin/ExamRooms/Create', ['routePrefix' => $this->routePrefix($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $this->service->create($request->user(), $data);

        return redirect()->route($this->routePrefix($request) . '.exam-rooms.index')->with('success', 'Ruang ujian berhasil ditambahkan.');
    }

    public function show(Request $request, ExamRoom $examRoom): Response
    {
        return Inertia::render('Admin/ExamRooms/Show', [
            'room' => $examRoom->load(['assignments.schedule', 'assignments.supervisor'])->loadCount('assignments'),
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function edit(Request $request, ExamRoom $examRoom): Response
    {
        return Inertia::render('Admin/ExamRooms/Edit', [
            'room' => $examRoom,
            'routePrefix' => $this->routePrefix($request),
        ]);
    }

    public function update(Request $request, ExamRoom $examRoom): RedirectResponse
    {
        $data = $request->validate($this->rules($examRoom));
        $this->service->update($request->user(), $examRoom, $data);

        return redirect()->route($this->routePrefix($request) . '.exam-rooms.index')->with('success', 'Ruang ujian berhasil diperbarui.');
    }

    public function destroy(Request $request, ExamRoom $examRoom): RedirectResponse
    {
        $this->service->delete($examRoom);

        return redirect()->route($this->routePrefix($request) . '.exam-rooms.index')->with('success', 'Ruang ujian berhasil dihapus.');
    }

    private function rules(?ExamRoom $room = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('exam_rooms', 'code')->ignore($room)],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    private function routePrefix(Request $request): string
    {
        return str_starts_with((string) $request->route()?->getName(), 'staff.') ? 'staff' : 'admin';
    }
}
