<?php

namespace App\Modules\Biodata\Models;

use App\Modules\Registration\Models\Registration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentBiodata extends Model
{
    protected $table = 'student_biodata';

    protected $fillable = [
        'registration_id',
        'nik',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'address',
        'province',
        'city',
        'school_name',
        'school_graduation_year',
        'parent_name',
        'parent_phone',
        'parent_job',
        'photo',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'school_graduation_year' => 'integer',
        ];
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
