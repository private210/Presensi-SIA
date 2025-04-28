<?php

namespace App\Http\Controllers;

use App\Models\Siswa;

abstract class Controller
{
    public function saveSiswaData($request)
    {
        $siswa = new Siswa();
        $siswa->nis = $request->input('nis', '625840'); // Gunakan nilai default jika nis tidak disediakan
        $siswa->nama_siswa = $request->input('nama_siswa');
        // Lanjutkan pengisian data lainnya
        $siswa->save();
    }
}
