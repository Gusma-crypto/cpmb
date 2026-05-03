<?php

namespace App\Modules\CbtResult\Services;

use App\Models\User;
use App\Modules\CbtAttempt\Models\CbtResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CbtResultService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return CbtResult::query()
            ->with(['user', 'registration.program', 'cbtExam', 'attempt.schedule'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('registration', fn ($query) => $query->where('registration_number', 'like', "%{$search}%"))
                        ->orWhereHas('cbtExam', fn ($query) => $query->where('title', 'like', "%{$search}%"));
                });
            })
            ->when(($filters['published'] ?? '') !== '', function ($query) use ($filters): void {
                $filters['published'] === 'yes'
                    ? $query->whereNotNull('published_at')
                    : $query->whereNull('published_at');
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function studentResults(User $user)
    {
        return CbtResult::query()
            ->with(['cbtExam', 'attempt.schedule'])
            ->where('user_id', $user->id)
            ->whereNotNull('published_at')
            ->latest()
            ->get();
    }

    public function publish(CbtResult $result): CbtResult
    {
        $result->update(['published_at' => $result->published_at ?: now()]);

        return $result->refresh();
    }

    public function unpublish(CbtResult $result): CbtResult
    {
        $result->update(['published_at' => null]);

        return $result->refresh();
    }

    public function publishMany(array $ids): int
    {
        return CbtResult::query()
            ->whereIn('id', $ids)
            ->whereNull('published_at')
            ->update(['published_at' => now()]);
    }
}
