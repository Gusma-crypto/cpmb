<?php

namespace App\Modules\Registration\Models;

use App\Models\User;
use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\Biodata\Models\StudentBiodata;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtResult;
use App\Modules\Document\Models\Document;
use App\Modules\Program\Models\Program;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Registration extends Model
{
    protected $fillable = [
        'user_id',
        'academic_year_id',
        'registration_wave_id',
        'registration_number',
        'status',
        'wave',
        'program_id',
        'revision_notes',
        'submitted_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function registrationWave(): BelongsTo
    {
        return $this->belongsTo(RegistrationWave::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function biodata(): HasOne
    {
        return $this->hasOne(StudentBiodata::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function cbtResults(): HasMany
    {
        return $this->hasMany(CbtResult::class);
    }
}
