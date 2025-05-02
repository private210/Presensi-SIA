<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    // Getter untuk role tertentu
    public function getIsAdminAttribute()
    {
        return $this->hasRole('admin');
    }

    public function getIsWaliKelasAttribute()
    {
        return $this->hasRole('Wali Kelas');
    }

    public function getIsWaliMuridAttribute()
    {
        return $this->hasRole('Wali Murid');
    }

    public function getIsKepalaSekolahAttribute()
    {
        return $this->hasRole('Kepala Sekolah');
    }

    // Getter untuk role aktif
    public function getRoleAttribute()
    {
        if ($this->hasRole('admin')) return 'admin';
        if ($this->hasRole('Wali Kelas')) return 'wali_kelas';
        if ($this->hasRole('Wali Murid')) return 'wali_murid';
        if ($this->hasRole('Kepala Sekolah')) return 'kepala_sekolah';
        return null;
    }

    public function kelasWali()
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    public function siswaWaliMurid()
    {
        return $this->hasMany(Siswa::class, 'wali_murid_id');
    }
}
