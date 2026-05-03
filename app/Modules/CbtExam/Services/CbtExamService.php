<?php

namespace App\Modules\CbtExam\Services;

use App\Models\User;
use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\CbtExam\Models\CbtExam;
use App\Modules\Program\Models\Program;
use App\Modules\QuestionBank\Models\CbtQuestion;
use App\Modules\QuestionBank\Models\CbtQuestionCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CbtExamService
{
    public function list(array $filters): LengthAwarePaginator
    {
        return CbtExam::query()
            ->with(['academicYear', 'program', 'creator'])
            ->withCount('questions')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['program_id'] ?? null, fn ($query, string $programId) => $query->where('program_id', $programId))
            ->latest()
            ->paginate(10)
            ->withQueryString();
    }

    public function questionBank(array $filters): LengthAwarePaginator
    {
        return CbtQuestion::query()
            ->with(['category'])
            ->withCount('options')
            ->where('is_active', true)
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where('question_text', 'like', "%{$search}%");
            })
            ->when($filters['category_id'] ?? null, fn ($query, string $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['difficulty'] ?? null, fn ($query, string $difficulty) => $query->where('difficulty', $difficulty))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();
    }

    public function formData(): array
    {
        return [
            'academicYears' => AcademicYear::query()->orderByDesc('id')->get(['id', 'label']),
            'programs' => Program::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'statuses' => CbtExam::STATUSES,
        ];
    }

    public function questionFormData(): array
    {
        return [
            'categories' => CbtQuestionCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'types' => CbtQuestion::TYPES,
            'difficulties' => CbtQuestion::DIFFICULTIES,
        ];
    }

    public function publishedOptions()
    {
        return CbtExam::query()
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'duration_minutes', 'pass_score']);
    }

    public function create(User $user, array $data): CbtExam
    {
        return CbtExam::create([
            ...$this->payload($data),
            'created_by' => $user->id,
        ]);
    }

    public function update(CbtExam $exam, array $data): CbtExam
    {
        $this->assertDraft($exam, 'Paket ujian hanya dapat diedit saat status draft.');

        $exam->update($this->payload($data));

        return $exam->refresh();
    }

    public function delete(CbtExam $exam): void
    {
        $this->assertDraft($exam, 'Paket ujian hanya dapat dihapus saat status draft.');

        $exam->delete();
    }

    public function syncQuestions(CbtExam $exam, array $questions): CbtExam
    {
        $this->assertDraft($exam, 'Soal paket ujian hanya dapat diubah saat status draft.');

        return DB::transaction(function () use ($exam, $questions): CbtExam {
            $syncData = collect($questions)
                ->mapWithKeys(fn (array $question): array => [
                    $question['cbt_question_id'] => [
                        'points' => $question['points'],
                        'order_index' => $question['order_index'],
                    ],
                ])
                ->all();

            $exam->questions()->sync($syncData);
            $exam->update(['total_questions' => count($syncData)]);

            return $exam->refresh()->load('questions');
        });
    }

    public function publish(CbtExam $exam): CbtExam
    {
        if ($exam->status === 'closed') {
            throw ValidationException::withMessages(['status' => 'Paket ujian closed tidak dapat dipublish ulang.']);
        }

        $this->assertPublishable($exam);

        $exam->update(['status' => 'published']);

        return $exam->refresh();
    }

    public function close(CbtExam $exam): CbtExam
    {
        if ($exam->status === 'closed') {
            return $exam;
        }

        $exam->update(['status' => 'closed']);

        return $exam->refresh();
    }

    private function payload(array $data): array
    {
        return Arr::only($data, [
            'title',
            'description',
            'academic_year_id',
            'program_id',
            'duration_minutes',
            'pass_score',
            'total_questions',
            'randomize_questions',
            'randomize_options',
            'max_attempts',
            'start_at',
            'end_at',
            'status',
        ]);
    }

    private function assertDraft(CbtExam $exam, string $message): void
    {
        if ($exam->status !== 'draft') {
            throw ValidationException::withMessages(['status' => $message]);
        }
    }

    private function assertPublishable(CbtExam $exam): void
    {
        $exam->loadMissing('questions');

        $errors = [];

        if (! $exam->title) {
            $errors['title'] = 'Judul paket ujian wajib diisi.';
        }

        if ($exam->duration_minutes < 1) {
            $errors['duration_minutes'] = 'Durasi ujian wajib lebih dari 0 menit.';
        }

        if ($exam->pass_score < 0 || $exam->pass_score > 100) {
            $errors['pass_score'] = 'Passing grade harus antara 0 sampai 100.';
        }

        if ($exam->start_at && $exam->end_at && $exam->end_at->lessThanOrEqualTo($exam->start_at)) {
            $errors['end_at'] = 'Waktu selesai harus lebih besar dari waktu mulai.';
        }

        $activeQuestionCount = $exam->questions()->where('cbt_questions.is_active', true)->count();

        if ($activeQuestionCount < 1) {
            $errors['questions'] = 'Paket ujian wajib memiliki minimal 1 soal aktif sebelum publish.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
