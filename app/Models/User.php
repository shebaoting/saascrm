<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'phone', 'password', 'avatar', 'gender', 'position', 'telephone', 'locale', 'status', 'is_platform_admin', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasName, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'status' => 'boolean',
            'is_platform_admin' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['member_name', 'is_owner', 'is_admin', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id')
            ->wherePivot('model_type', self::class)
            ->withPivot(['tenant_id', 'model_type']);
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_user')
            ->withPivot(['tenant_id', 'is_leader', 'main_department'])
            ->withTimestamps();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->status) {
            return false;
        }

        return $panel->getId() !== 'platform' || $this->is_platform_admin;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->tenants()
            ->whereKey($tenant->getKey())
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->tenants()
            ->wherePivot('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
