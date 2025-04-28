<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class PresensiKelas extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.pages.presensi-kelas';

    protected static ?string $navigationGroup = 'Presensi';

    protected static ?string $title = 'Presensi Kelas';

    public ?array $data = [];

    public $kelas_id = null;
    public $tanggal = null;
    public $siswaList = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->role === 'wali_kelas') {
            // Cari kelas yang diampu oleh wali kelas
            $kelas = Kelas::where('wali_kelas_id', $user->id)->first();
            if ($kelas) {
                $this->kelas_id = $kelas->id;
            }
        }

        $this->tanggal = Carbon::now()->format('Y-m-d');
        $this->updateSiswaList();
    }

    public function updateSiswaList(): void
    {
        if (!$this->kelas_id || !$this->tanggal) {
            $this->siswaList = [];
            return;
        }

        $tanggal = Carbon::parse($this->tanggal);

        $siswas = Siswa::where('kelas_id', $this->kelas_id)
            ->orderBy('nama_siswa')
            ->get();

        $this->siswaList = [];

        foreach ($siswas as $siswa) {
            $presensi = Presensi::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            $this->siswaList[] = [
                'siswa_id' => $siswa->id,
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama_siswa,
                'status' => $presensi ? $presensi->status : null,
                'keterangan' => $presensi ? $presensi->keterangan : null,
            ];
        }
    }

    public function simpanPresensi(): void
    {
        $user = Auth::user();

        if (!$this->kelas_id || !$this->tanggal) {
            Notification::make()
                ->title('Error')
                ->body('Kelas dan tanggal harus diisi')
                ->danger()
                ->send();
            return;
        }

        $tanggal = Carbon::parse($this->tanggal);

        foreach ($this->siswaList as $siswa) {
            if (!isset($siswa['status']) || !$siswa['status']) {
                continue;
            }

            Presensi::updateOrCreate(
                [
                    'siswa_id' => $siswa['siswa_id'],
                    'tanggal' => $tanggal,
                ],
                [
                    'status' => $siswa['status'],
                    'keterangan' => $siswa['keterangan'] ?? null,
                    'diinput_oleh' => $user->id,
                ]
            );
        }

        Notification::make()
            ->title('Berhasil')
            ->body('Data presensi berhasil disimpan')
            ->success()
            ->send();

        $this->updateSiswaList();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->role === 'wali_kelas' || $user->role === 'admin';
    }
}
