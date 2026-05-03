<?php

namespace App\Modules\CbtResult\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\CbtAttempt\Models\CbtResult;
use App\Modules\CbtResult\Services\CbtResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CbtResultController extends Controller
{
    public function __construct(private readonly CbtResultService $service)
    {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Cbt/Results/Index', [
            'results' => $this->service->list($request->only(['search', 'published'])),
            'filters' => $request->only(['search', 'published']),
        ]);
    }

    public function publish(CbtResult $result): RedirectResponse
    {
        $this->service->publish($result);

        return back()->with('success', 'Hasil CBT berhasil dipublish.');
    }

    public function unpublish(CbtResult $result): RedirectResponse
    {
        $this->service->unpublish($result);

        return back()->with('success', 'Publish hasil CBT dibatalkan.');
    }

    public function publishMany(Request $request): RedirectResponse
    {
        $data = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:cbt_results,id']]);
        $count = $this->service->publishMany($data['ids']);

        return back()->with('success', "{$count} hasil CBT berhasil dipublish.");
    }
}
