<?php

namespace App\Filament\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Collection;

class SiswaImporter extends Importer
{
    protected static ?string $model = Siswa::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nis')
                ->label('NIS')
                ->rules(['required', 'numeric', 'min:6', 'unique:siswas,nis']),

            ImportColumn::make('nama_siswa')
                ->label('Nama Siswa')
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('kelas')
                ->label('Kelas')
                ->rules(['required', 'string'])
                ->requiredMapping()
                ->fillRecordUsing(function (array $data, Siswa $record): void {
                    $kelas = Kelas::where('nama_kelas', $data['kelas'])->first();

                    if ($kelas) {
                        $record->kelas_id = $kelas->id;
                    }
                }),

            ImportColumn::make('wali_murid')
                ->label('Wali Murid')
                ->rules(['required', 'string'])
                ->requiredMapping()
                ->fillRecordUsing(function (array $data, Siswa $record): void {
                    $waliMurid = User::whereHas('roles', function ($query) {
                        $query->where('name', 'Wali Murid');
                    })->where('name', $data['wali_murid'])->first();

                    if ($waliMurid) {
                        $record->wali_murid_id = $waliMurid->id;
                    }
                }),

            ImportColumn::make('jenis_kelamin')
                ->label('Jenis Kelamin')
                ->rules(['required', 'in:L,P'])
                ->validationMessages([
                    'in' => 'Jenis kelamin harus L (Laki-laki) atau P (Perempuan)',
                ]),

            ImportColumn::make('alamat')
                ->label('Alamat')
                ->rules(['required', 'string']),

            ImportColumn::make('no_telp')
                ->label('No. Telepon')
                ->rules(['nullable', 'string', 'max:255']),
        ];
    }

    public function resolveRecord(): ?Siswa
    {
        // Jika NIS sudah ada, update data siswa tersebut
        return Siswa::firstOrNew([
            'nis' => $this->data['nis'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = $import->successful_rows;

        return "Berhasil mengimpor {$count} data siswa.";
    }
}
