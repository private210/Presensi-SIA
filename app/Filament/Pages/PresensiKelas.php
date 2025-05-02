<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\HariLibur;
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
    public $isTodayHoliday = false;
    public $holidayInfo = null;

    public function mount(): void
    {
        $user = Auth::user();

        // Set kelas_id otomatis untuk wali kelas
        if ($user->roles->contains('Wali Kelas')) {
            $kelas = Kelas::where('wali_kelas_id', $user->id)->first();
            if ($kelas) {
                $this->kelas_id = $kelas->id;
            }
        }

        $this->tanggal = Carbon::now()->format('Y-m-d');
        $this->checkHoliday();
        $this->updateSiswaList();
    }

    // Periksa apakah hari ini adalah hari libur
    public function checkHoliday(): void
    {
        if ($this->tanggal) {
            $hariLibur = HariLibur::whereDate('tanggal', $this->tanggal)->first();
            if ($hariLibur) {
                $this->isTodayHoliday = true;
                $this->holidayInfo = $hariLibur->keterangan;
            } else {
                $this->isTodayHoliday = false;
                $this->holidayInfo = null;
            }
        }
    }

    public function updateSiswaList(): void
    {
        if (!$this->kelas_id || !$this->tanggal) {
            $this->siswaList = [];
            return;
        }

        $tanggal = Carbon::parse($this->tanggal);
        $this->checkHoliday();

        // Ambil data siswa di kelas yang dipilih
        $siswas = Siswa::where('kelas_id', $this->kelas_id)
            ->orderBy('nama_siswa')
            ->get();

        $this->siswaList = [];

        foreach ($siswas as $siswa) {
            // Cek apakah sudah ada data presensi untuk siswa ini pada tanggal tersebut
            $presensi = Presensi::where('siswa_id', $siswa->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            $this->siswaList[] = [
                'siswa_id' => $siswa->id,
                'nis' => $siswa->nis,
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

        // Cek apakah ada status yang belum diisi
        $hasMissingStatus = false;
        foreach ($this->siswaList as $siswa) {
            if (!isset($siswa['status']) || empty($siswa['status'])) {
                $hasMissingStatus = true;
                break;
            }
        }

        if ($hasMissingStatus && !$this->isTodayHoliday) {
            Notification::make()
                ->title('Peringatan')
                ->body('Ada siswa yang belum diisi status kehadirannya')
                ->warning()
                ->send();
            return;
        }

        foreach ($this->siswaList as $siswa) {
            // Jika hari libur, set semua siswa status 'izin' dengan keterangan hari libur
            if ($this->isTodayHoliday) {
                Presensi::updateOrCreate(
                    [
                        'siswa_id' => $siswa['siswa_id'],
                        'tanggal' => $tanggal,
                    ],
                    [
                        'status' => 'izin',
                        'keterangan' => 'Hari Libur: ' . $this->holidayInfo,
                        'diinput_oleh' => $user->id,
                    ]
                );
                continue;
            }

            // Proses normal jika bukan hari libur
            if (isset($siswa['status']) && !empty($siswa['status'])) {
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
        }

        Notification::make()
            ->title('Berhasil')
            ->body('Data presensi berhasil disimpan')
            ->success()
            ->send();

        $this->updateSiswaList();
    }

    public function setAllStatus($status): void
    {
        foreach ($this->siswaList as $index => $siswa) {
            $this->siswaList[$index]['status'] = $status;
        }
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->roles->contains('Wali Kelas') || $user->roles->contains('admin');
    }
}
