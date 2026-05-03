<?php

namespace App\Modules\RegistrationWave\Models;

use App\Modules\AcademicYear\Models\AcademicYear;
use App\Modules\Program\Models\Program;
use App\Modules\Registration\Models\Registration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationWave extends Model
{
    protected $fillable = [
        'academic_year_id',
        'wave_number',
        'label',
        'open_at',
        'close_at',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'wave_number' => 'integer',
            'open_at' => 'datetime',
            'close_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'gelombang_program')
            ->withPivot(['status', 'is_open', 'tanggal_mulai', 'tanggal_selesai'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
