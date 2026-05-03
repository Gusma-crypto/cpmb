<?php

namespace App\Modules\ExamRoom\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamRoom extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'location', 'capacity', 'status', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['capacity' => 'integer'];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ExamRoomAssignment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
