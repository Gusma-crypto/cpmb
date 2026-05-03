<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Modules\Registration\Models\Registration;
use App\Modules\CbtAttempt\Models\CbtAttempt;
use App\Modules\CbtAttempt\Models\CbtResult;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active', 
        'login_count',
        'last_login_at',
        'previous_login_at',
        'last_login_ip',
        'profile_photo_path',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'login_count' => 'integer',
            'last_login_at' => 'datetime',
            'previous_login_at' => 'datetime',
        ];
    }

    public function registration(): HasOne
    {
        return $this->hasOne(Registration::class);
    }

    public function cbtAttempts(): HasMany
    {
        return $this->hasMany(CbtAttempt::class);
    }

    public function cbtResults(): HasMany
    {
        return $this->hasMany(CbtResult::class);
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        $version = $this->updated_at?->timestamp;

        return '/storage/'.ltrim($this->profile_photo_path, '/').($version ? "?v={$version}" : '');
    }

    public function syncLegacyRoleToSpatie(): void
    {
        if ($this->getRoleNames()->isNotEmpty() || ! $this->role) {
            return;
        }

        if (! in_array($this->role, ['admin', 'student', 'staff', 'dosen', 'superadmin'], true)) {
            return;
        }

        Role::findOrCreate($this->role);

        $this->assignRole($this->role);
        $this->load('roles');
    }
}
