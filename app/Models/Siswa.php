<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nama_siswa',
        'kelas_id',
        'wali_murid_id',
        'jenis_kelamin',
        'alamat',
        'no_telp'
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->nis)) {
                $model->nis = '625840'; // Tentukan nilai default untuk nis jika tidak ada
            }
        });
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliMurid()
    {
        return $this->belongsTo(User::class, 'wali_murid_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function suratIzin()
    {
        return $this->hasMany(SuratIzin::class);
    }
}
