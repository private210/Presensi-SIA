<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\Siswa;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use pxlrbt\FilamentExcel\Actions\Pages\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class RekapPresensiKepalaSekolah extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static string $view = 'filament.pages.rekap-presensi-kepala-sekolah';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $title = 'Rekap Presensi Seluruh Kelas';

    public $tanggalMulai = null;
    public $tanggalSelesai = null;

    public $rekapData = [];
    public $rekapPerKelas = [];
    public $kelasList = [];

    public function mount(): void
    {
        // Set default tanggal bulan ini
        $this->tanggalMulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggalSelesai = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Load data awal
        $this->kelasList = Kelas::orderBy('nama_kelas')->get();
        $this->generateReport();
    }

    public function generateReport(): void
    {
        if (!$this->tanggalMulai || !$this->tanggalSelesai) {
            Notification::make()
                ->title('Tanggal harus diisi')
                ->danger()
                ->send();
            return;
        }

        $tanggalMulai = Carbon::parse($this->tanggalMulai);
        $tanggalSelesai = Carbon::parse($this->tanggalSelesai);

        // Reset data rekap
        $this->rekapData = [];
        $this->rekapPerKelas = [];

        // Ambil semua kelas
        $kelas = $this->kelasList;

        // Rekap presensi untuk setiap kelas
        foreach ($kelas as $kelasItem) {
            $rekapKelas = [
                'id' => $kelasItem->id,
                'nama_kelas' => $kelasItem->nama_kelas,
                'wali_kelas' => $kelasItem->WaliKelas ? $kelasItem->WaliKelas->name : '-',
                'jumlah_siswa' => 0,
                'total' => [
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpa' => 0,
                    'total_hari' => $tanggalMulai->diffInDays($tanggalSelesai) + 1,
                ],
                'persentase' => [
                    'hadir' => 0,
                    'sakit' => 0,
                    'izin' => 0,
                    'alpa' => 0,
                ]
            ];

            // Ambil siswa di kelas ini
            $siswas = Siswa::where('kelas_id', $kelasItem->id)->get();
            $rekapKelas['jumlah_siswa'] = $siswas->count();

            $totalKehadiran = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalAlpa = 0;

            // Ambil presensi untuk setiap siswa
            foreach ($siswas as $siswa) {
                // Hitung presensi per status
                $presensiCounts = Presensi::where('siswa_id', $siswa->id)
                    ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])
                    ->select('status', DB::raw('count(*) as total'))
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->toArray();

                $totalKehadiran += $presensiCounts['hadir'] ?? 0;
                $totalSakit += $presensiCounts['sakit'] ?? 0;
                $totalIzin += $presensiCounts['izin'] ?? 0;
                $totalAlpa += $presensiCounts['alpa'] ?? 0;
            }

            $rekapKelas['total']['hadir'] = $totalKehadiran;
            $rekapKelas['total']['sakit'] = $totalSakit;
            $rekapKelas['total']['izin'] = $totalIzin;
            $rekapKelas['total']['alpa'] = $totalAlpa;

            // Hitung total hari presensi yang seharusnya ada
            $totalHariPresensi = $rekapKelas['total']['total_hari'] * $rekapKelas['jumlah_siswa'];

            // Hitung persentase jika ada siswa
            if ($rekapKelas['jumlah_siswa'] > 0 && $totalHariPresensi > 0) {
                $rekapKelas['persentase']['hadir'] = round(($totalKehadiran / $totalHariPresensi) * 100, 2);
                $rekapKelas['persentase']['sakit'] = round(($totalSakit / $totalHariPresensi) * 100, 2);
                $rekapKelas['persentase']['izin'] = round(($totalIzin / $totalHariPresensi) * 100, 2);
                $rekapKelas['persentase']['alpa'] = round(($totalAlpa / $totalHariPresensi) * 100, 2);
            }

            $this->rekapPerKelas[] = $rekapKelas;
        }

        // Hitung total statistik untuk seluruh sekolah
        $totalSiswa = Siswa::count();
        $totalHadir = 0;
        $totalSakit = 0;
        $totalIzin = 0;
        $totalAlpa = 0;
        $totalHariPresensi = 0;

        // Jumlahkan semua data presensi di semua kelas
        foreach ($this->rekapPerKelas as $rekapKelas) {
            $totalHadir += $rekapKelas['total']['hadir'];
            $totalSakit += $rekapKelas['total']['sakit'];
            $totalIzin += $rekapKelas['total']['izin'];
            $totalAlpa += $rekapKelas['total']['alpa'];
            $totalHariPresensi += $rekapKelas['total']['total_hari'] * $rekapKelas['jumlah_siswa'];
        }

        // Simpan statistik global
        $this->rekapData = [
            'total_siswa' => $totalSiswa,
            'total_kelas' => count($this->rekapPerKelas),
            'periode' => [
                'mulai' => $tanggalMulai->format('d M Y'),
                'selesai' => $tanggalSelesai->format('d M Y'),
                'total_hari' => $tanggalMulai->diffInDays($tanggalSelesai) + 1,
            ],
            'total' => [
                'hadir' => $totalHadir,
                'sakit' => $totalSakit,
                'izin' => $totalIzin,
                'alpa' => $totalAlpa,
            ],
            'persentase' => [
                'hadir' => $totalHariPresensi > 0 ? round(($totalHadir / $totalHariPresensi) * 100, 2) : 0,
                'sakit' => $totalHariPresensi > 0 ? round(($totalSakit / $totalHariPresensi) * 100, 2) : 0,
                'izin' => $totalHariPresensi > 0 ? round(($totalIzin / $totalHariPresensi) * 100, 2) : 0,
                'alpa' => $totalHariPresensi > 0 ? round(($totalAlpa / $totalHariPresensi) * 100, 2) : 0,
            ]
        ];

        Notification::make()
            ->title('Data rekap presensi berhasil dimuat')
            ->success()
            ->send();
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
                        ->withFilename('rekap-presensi-seluruh-kelas-' . now()->format('Y-m-d'))
                        ->withColumns([
                            Column::make('no')->heading('No'),
                            Column::make('kelas')->heading('Kelas'),
                            Column::make('wali_kelas')->heading('Wali Kelas'),
                            Column::make('jumlah_siswa')->heading('Jumlah Siswa'),
                            Column::make('hadir')->heading('Hadir'),
                            Column::make('sakit')->heading('Sakit'),
                            Column::make('izin')->heading('Izin'),
                            Column::make('alpa')->heading('Alpa'),
                            Column::make('persen_hadir')->heading('% Hadir'),
                            Column::make('persen_sakit')->heading('% Sakit'),
                            Column::make('persen_izin')->heading('% Izin'),
                            Column::make('persen_alpa')->heading('% Alpa'),
                        ])
                        ->withColumns(
                            fn() => collect($this->rekapPerKelas)
                                ->map(function ($kelas, $index) {
                                    return [
                                        'no' => $index + 1,
                                        'kelas' => $kelas['nama_kelas'],
                                        'wali_kelas' => $kelas['wali_kelas'],
                                        'jumlah_siswa' => $kelas['jumlah_siswa'],
                                        'hadir' => $kelas['total']['hadir'],
                                        'sakit' => $kelas['total']['sakit'],
                                        'izin' => $kelas['total']['izin'],
                                        'alpa' => $kelas['total']['alpa'],
                                        'persen_hadir' => $kelas['persentase']['hadir'] . '%',
                                        'persen_sakit' => $kelas['persentase']['sakit'] . '%',
                                        'persen_izin' => $kelas['persentase']['izin'] . '%',
                                        'persen_alpa' => $kelas['persentase']['alpa'] . '%',
                                    ];
                                })
                                ->toArray()
                        )
                ])
        ];
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->roles->contains(['admin', 'Kepala Sekolah']);
    }
}
