<?php

namespace App\Modules\QuestionBank\Services;

use App\Modules\QuestionBank\Models\CbtQuestionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class CbtQuestionCategoryService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return CbtQuestionCategory::query()
            ->withCount('questions')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function create(array $data): CbtQuestionCategory
    {
        return CbtQuestionCategory::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);
    }

    public function update(CbtQuestionCategory $category, array $data): CbtQuestionCategory
    {
        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return $category->refresh();
    }

    public function delete(CbtQuestionCategory $category): void
    {
        if ($category->questions()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak dapat dihapus karena sudah dipakai oleh soal.',
            ]);
        }

        $category->delete();
    }
}
