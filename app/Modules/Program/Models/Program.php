<?php

namespace App\Modules\Program\Models;

use App\Modules\RegistrationWave\Models\RegistrationWave;
use App\Modules\CbtExam\Models\CbtExam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'code',
        'name',
        'level',
        'faculty',
        'accreditation',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function registrationWaves(): BelongsToMany
    {
        return $this->belongsToMany(RegistrationWave::class, 'gelombang_program')
            ->withPivot(['status', 'is_open', 'tanggal_mulai', 'tanggal_selesai'])
            ->withTimestamps();
    }

    public function cbtExams(): HasMany
    {
        return $this->hasMany(CbtExam::class);
    }
}
