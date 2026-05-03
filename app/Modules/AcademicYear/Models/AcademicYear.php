<?php

namespace App\Modules\AcademicYear\Models;

use App\Modules\Registration\Models\Registration;
use App\Modules\RegistrationWave\Models\RegistrationWave;
use App\Modules\CbtExam\Models\CbtExam;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
        'label',
        'is_active',
        'registration_open_at',
        'registration_close_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'registration_open_at' => 'datetime',
            'registration_close_at' => 'datetime',
        ];
    }

    public function registrationWaves(): HasMany
    {
        return $this->hasMany(RegistrationWave::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function cbtExams(): HasMany
    {
        return $this->hasMany(CbtExam::class);
    }
}
