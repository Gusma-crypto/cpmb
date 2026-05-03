<?php

namespace App\Modules\QuestionBank\Services;

use App\Models\User;
use App\Modules\QuestionBank\Models\CbtQuestion;
use App\Modules\QuestionBank\Models\CbtQuestionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CbtQuestionService
{
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return CbtQuestion::query()
            ->with(['category', 'creator'])
            ->withCount('options')
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('question_text', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, string $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function formData(): array
    {
        return [
            'categories' => CbtQuestionCategory::query()->orderBy('name')->get(['id', 'name']),
            'types' => CbtQuestion::TYPES,
            'difficulties' => CbtQuestion::DIFFICULTIES,
        ];
    }

    public function create(User $user, array $data): CbtQuestion
    {
        return DB::transaction(function () use ($user, $data): CbtQuestion {
            $question = CbtQuestion::create([
                'category_id' => $data['category_id'],
                'created_by' => $user->id,
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'explanation' => $data['explanation'] ?? null,
                'difficulty' => $data['difficulty'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            $this->syncOptions($question, $data['options']);

            return $question->load(['category', 'options']);
        });
    }

    public function update(CbtQuestion $question, array $data): CbtQuestion
    {
        return DB::transaction(function () use ($question, $data): CbtQuestion {
            $question->update([
                'category_id' => $data['category_id'],
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'explanation' => $data['explanation'] ?? null,
                'difficulty' => $data['difficulty'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            $question->options()->delete();
            $this->syncOptions($question, $data['options']);

            return $question->refresh()->load(['category', 'options']);
        });
    }

    public function delete(CbtQuestion $question): void
    {
        $question->delete();
    }

    private function syncOptions(CbtQuestion $question, array $options): void
    {
        foreach (array_values($options) as $index => $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'order_index' => $index + 1,
            ]);
        }
    }
}
