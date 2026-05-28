<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'nim_nip',
        'email',
        'password',
        'role',
        'avatar',
        'kelas_id',
        'is_active',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Akses panel Filament hanya untuk admin aktif.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasRole('admin');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(PraktikumClass::class, 'kelas_id');
    }

    public function kelasDiikuti(): BelongsToMany
    {
        return $this->belongsToMany(
            PraktikumClass::class,
            'class_students',
            'student_id',
            'class_id'
        )->withTimestamps();
    }

    public function kelasDiasisteni(): HasMany
    {
        return $this->hasMany(PraktikumClass::class, 'assistant_id');
    }

    public function materialsCreated(): HasMany
    {
        return $this->hasMany(Material::class, 'created_by');
    }

    public function assignmentsCreated(): HasMany
    {
        return $this->hasMany(Assignment::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'student_id');
    }

    public function attendancesOpened(): HasMany
    {
        return $this->hasMany(Attendance::class, 'opened_by');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'student_id');
    }

    public function announcementsCreated(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    public function chatHistories(): HasMany
    {
        return $this->hasMany(ChatHistory::class);
    }

    public function lmsNotifications(): HasMany
    {
        return $this->hasMany(LmsNotification::class, 'user_id')->latest();
    }

    public function unreadLmsNotifications(): HasMany
    {
        return $this->lmsNotifications()->whereNull('read_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMahasiswa($query)
    {
        return $query->role('mahasiswa');
    }

    public function scopeAsisten($query)
    {
        return $query->role('asisten');
    }

    public function scopeAdmin($query)
    {
        return $query->role('admin');
    }
}
