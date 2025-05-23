<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class RekapPresensi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string $view = 'filament.pages.rekap-presensi';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $title = 'Rekap Presensi';

    public $kelas_id = null;
    public $siswa_id = null;
    public $bulan = null;
    public $tahun = null;

    public $rekapData = [];
    public $tanggalList = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Otomatisasi filter berdasarkan peran pengguna
        if ($user->roles->contains('Wali Kelas')) {
            // Cari kelas yang diampu oleh wali kelas
            $kelas = Kelas::where('wali_kelas_id', $user->id)->first();
            if ($kelas) {
                $this->kelas_id = $kelas->id;
            }
        } elseif ($user->roles->contains('Wali Murid')) {
            // Ambil siswa yang menjadi anak dari wali murid
            $siswa = Siswa::where('wali_murid_id', $user->id)->first();
            if ($siswa) {
                $this->kelas_id = $siswa->kelas_id;
                $this->siswa_id = $siswa->id;
            }
        }

        $this->bulan = Carbon::now()->month;
        $this->tahun = Carbon::now()->year;

        // Generate laporan otomatis jika filter sudah tersedia
        if ($this->kelas_id) {
            $this->generateReport();
        }
    }

    public function bulanOptions()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    public function tahunOptions()
    {
        $tahunSekarang = Carbon::now()->year;
        $options = [];

        for ($i = $tahunSekarang - 2; $i <= $tahunSekarang + 2; $i++) {
            $options[$i] = $i;
        }

        return $options;
    }

    public function kelasOptions()
    {
        $user = Auth::user();

        if ($user->roles->contains('Wali Kelas')) {
            return Kelas::where('wali_kelas_id', $user->id)->pluck('nama_kelas', 'id')->toArray();
        } elseif ($user->roles->contains('Wali Murid')) {
            return Kelas::whereHas('siswa', function ($query) use ($user) {
                $query->where('wali_murid_id', $user->id);
            })->pluck('nama_kelas', 'id')->toArray();
        }

        return Kelas::pluck('nama_kelas', 'id')->toArray();
    }

    public function siswaOptions()
    {
        $user = Auth::user();

        if ($user->roles->contains('Wali Murid')) {
            return Siswa::where('wali_murid_id', $user->id)->pluck('nama_siswa', 'id')->toArray();
        }

        if ($this->kelas_id) {
            return Siswa::where('kelas_id', $this->kelas_id)->pluck('nama_siswa', 'id')->toArray();
        }

        return [];
    }

    public function generateReport()
    {
        if (!$this->kelas_id) {
            Notification::make()
                ->title('Pilih kelas terlebih dahulu')
                ->danger()
                ->send();
            return;
        }

        // Tentukan tanggal awal dan akhir bulan
        $tanggalAwal = Carbon::createFromDate($this->tahun, $this->bulan, 1)->startOfMonth();
        $tanggalAkhir = Carbon::createFromDate($this->tahun, $this->bulan, 1)->endOfMonth();

        // Buat daftar tanggal untuk header tabel
        $period = CarbonPeriod::create($tanggalAwal, $tanggalAkhir);
        $this->tanggalList = [];

        foreach ($period as $date) {
            $this->tanggalList[] = $date->format('Y-m-d');
        }

        // Query siswa berdasarkan filter
        $siswaQuery = Siswa::where('kelas_id', $this->kelas_id);

        if ($this->siswa_id) {
            $siswaQuery->where('id', $this->siswa_id);
        }

        $siswas = $siswaQuery->orderBy('nama_siswa')->get();

        // Reset data rekap
        $this->rekapData = [];

        // Ambil data presensi untuk setiap siswa
        foreach ($siswas as $siswa) {
            $siswaData = [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nama' => $siswa->nama_siswa,
                'presensi' => [],
                'total' => [
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpa' => 0,
                ]
            ];

            // Ambil data presensi untuk siswa ini
            $presensiList = Presensi::where('siswa_id', $siswa->id)
                ->whereBetween('tanggal', [$tanggalAwal->format('Y-m-d'), $tanggalAkhir->format('Y-m-d')])
                ->get();

            // Olah data presensi
            foreach ($presensiList as $presensi) {
                $tanggal = $presensi->tanggal;
                $status = $presensi->status;

                $siswaData['presensi'][$tanggal] = $status;

                // Hitung total berdasarkan status
                if (isset($siswaData['total'][$status])) {
                    $siswaData['total'][$status]++;
                }
            }

            $this->rekapData[] = $siswaData;
        }

        // Notifikasi sukses jika ada data
        if (count($this->rekapData) > 0) {
            Notification::make()
                ->title('Data berhasil dimuat')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Tidak ada data untuk filter yang dipilih')
                ->warning()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ExportAction::make('export')
                ->label('Ekspor Excel')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->exports([
                    ExcelExport::make()
                        ->withFilename('rekap-presensi-' . now()->format('Y-m-d'))
                        ->withColumns([
                            Column::make('no')->heading('No'),
                            Column::make('nis')->heading('NIS'),
                            Column::make('nama')->heading('Nama Siswa'),
                            ...collect($this->tanggalList)->map(
                                fn($tanggal) =>
                                Column::make('tanggal_' . str_replace('-', '_', $tanggal))
                                    ->heading(Carbon::parse($tanggal)->format('d'))
                            )->toArray(),
                            Column::make('hadir')->heading('Hadir'),
                            Column::make('sakit')->heading('Sakit'),
                            Column::make('izin')->heading('Izin'),
                            Column::make('alpa')->heading('Alpa'),
                        ])
                        ->withColumns(
                            fn() => collect($this->rekapData)
                                ->map(function ($siswa, $index) {
                                    $row = [
                                        'no' => $index + 1,
                                        'nis' => $siswa['nis'],
                                        'nama' => $siswa['nama'],
                                        'hadir' => $siswa['total']['hadir'] ?? 0,
                                        'sakit' => $siswa['total']['sakit'] ?? 0,
                                        'izin' => $siswa['total']['izin'] ?? 0,
                                        'alpa' => $siswa['total']['alpa'] ?? 0,
                                    ];

                                    // Tambahkan data per-tanggal
                                    foreach ($this->tanggalList as $tanggal) {
                                        $columnName = 'tanggal_' . str_replace('-', '_', $tanggal);
                                        $row[$columnName] = $siswa['presensi'][$tanggal] ?? '-';
                                    }

                                    return $row;
                                })
                                ->toArray()
                        )
                ])
                ->visible(count($this->rekapData) > 0)
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->roles->contains(['admin', 'Wali Kelas', 'Wali Murid', 'Kepala Sekolah']);
    }
}
