<?php

namespace App\Modules\Document\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Document\Models\Document;
use App\Modules\Document\Requests\RejectDocumentRequest;
use App\Modules\Document\Requests\StoreDocumentRequest;
use App\Modules\Document\Services\DocumentService;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Document/Index', [
            'documents' => $this->service->paginateFor($request->user()),
            'types' => DocumentService::TYPES,
            'currentRegistration' => $this->service->currentRegistrationFor($request->user()),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Modules/Document/Create', [
            'registrations' => $this->service->availableRegistrationsFor($request->user()),
            'types' => DocumentService::TYPES,
            'canManageAll' => $this->service->canManageAll($request->user()),
            'registrationOpen' => $this->service->canManageAll($request->user()) || $this->registrationWaveIsOpen(),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $document = $this->service->store($request->user(), $request->validated());

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Dokumen berhasil diunggah.');
    }

    public function show(Request $request, Document $document): Response
    {
        $this->service->authorize($request->user(), $document);

        return Inertia::render('Modules/Document/Show', [
            'document' => $document->load('registration.user'),
            'types' => DocumentService::TYPES,
        ]);
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->service->authorize($request->user(), $document);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->original_name);
    }

    public function view(Request $request, Document $document): BinaryFileResponse
    {
        $this->service->authorize($request->user(), $document);

        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return response()->file(Storage::disk('local')->path($document->file_path), [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($document->original_name) . '"',
        ]);
    }

    public function destroy(Request $request, Document $document): RedirectResponse
    {
        $this->service->delete($request->user(), $document);

        return redirect()
            ->route('documents.index')
            ->with('status', 'Dokumen berhasil dihapus.');
    }

    public function approve(Request $request, Document $document): RedirectResponse
    {
        $this->service->approve($request->user(), $document);

        return back()->with('success', 'Dokumen berhasil disetujui.');
    }

    public function reject(RejectDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->service->reject($request->user(), $document, $request->validated('notes'));

        return back()->with('success', 'Dokumen berhasil ditolak.');
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
