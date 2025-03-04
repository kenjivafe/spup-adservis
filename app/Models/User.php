<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasPermissions;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPermissions;

    protected $fillable = [
        'name',
        'surname',
        'schoolid',
        'phone',
        'email',
        'password',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // public function sendEmailVerificationNotification()
    // {
    //     $this->notify(new VerifyEmailNotification());
    // }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            if ($this->hasRole(['Admin', 'Staff'])) {
                return true; // Allow Admin and Staff to access the Admin Panel
            } else {
                return false; // Redirect to the 'app' panel for non-admin and non-staff users
            }
        } elseif ($panel->getId() === 'app') {
            return true; // Allow access to the App Panel for specified roles
        }
        return false; // Redirect to the 'app' panel if no authenticated user
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url("$this->avatar_url") : null;
    }

    public function getAllPermissionsAttribute()
    {
        // Get permissions directly associated to the user
        $directPermissions = $this->permissions->pluck('name');

        // Get permissions via roles
        $rolePermissions = $this->roles->flatMap(function ($role) {
            return $role->permissions->pluck('name');
        });

        // Combine and remove duplicates
        return $directPermissions->merge($rolePermissions)->unique();
    }

    public function venue()
    {
        return $this->hasOne(Venue::class, 'facilitator');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'person_responsible'); // Replace 'user_id' with the actual foreign key in Booking model
    }

    public function unit()
    {
        return $this->hasOne(Unit::class, 'unit_head');
    }
}
