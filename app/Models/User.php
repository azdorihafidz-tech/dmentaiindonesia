<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
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
            'is_admin' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->role ??= $user->is_admin ? 'owner' : 'editor';
        });

        static::updating(function (User $user): void {
            $wasOwner = (bool) $user->getOriginal('is_admin') && $user->getOriginal('role') === 'owner';
            $remainsOwner = $user->is_admin && $user->role === 'owner';

            if ($wasOwner && ! $remainsOwner && static::ownerCount() <= 1) {
                throw ValidationException::withMessages(['role' => 'Setidaknya satu akun pemilik harus tetap aktif.']);
            }
        });

        static::deleting(function (User $user): void {
            $isPersistedOwner = $user->exists
                && static::query()->whereKey($user->getKey())->where('is_admin', true)->where('role', 'owner')->exists();

            if ($isPersistedOwner && static::ownerCount() <= 1) {
                throw ValidationException::withMessages(['user' => 'Akun pemilik terakhir tidak dapat dihapus.']);
            }
        });
    }

    public function isOwner(): bool
    {
        return $this->is_admin && $this->role === 'owner';
    }

    public function isLastOwner(): bool
    {
        return $this->isOwner() && static::ownerCount() <= 1;
    }

    private static function ownerCount(): int
    {
        return static::query()->where('is_admin', true)->where('role', 'owner')->count();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->is_admin && in_array($this->role, ['owner', 'editor'], true);
    }
}
