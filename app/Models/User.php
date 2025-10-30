<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * Represents a system user with role-based access
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $role
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * User role constants
     */
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_KYC_OFFICER = 'kyc_officer';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    /**
     * Get all KYC forms created by this user.
     *
     * @return HasMany
     */
    public function createdForms(): HasMany
    {
        return $this->hasMany(KycForm::class, 'created_by');
    }

    /**
     * Get all KYC submissions reviewed by this user.
     *
     * @return HasMany
     */
    public function reviewedSubmissions(): HasMany
    {
        return $this->hasMany(KycSubmission::class, 'reviewed_by');
    }

    /**
     * Check if the user is a Super Admin.
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if the user is a KYC Officer.
     *
     * @return bool
     */
    public function isKycOfficer(): bool
    {
        return $this->role === self::ROLE_KYC_OFFICER;
    }

    /**
     * Get all available user roles.
     *
     * @return array
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_KYC_OFFICER => 'KYC Officer',
        ];
    }

    /**
     * Determine if the user can access the Filament admin panel.
     *
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access for super admins and KYC officers
        return $this->isSuperAdmin() || $this->isKycOfficer();
    }
}
