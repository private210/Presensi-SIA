<?php

namespace App\Filament\Imports;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class SiswaImporter extends Importer
{
    protected static ?string $model = Siswa::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nis')
                ->rules(['max:255', 'min:6', 'max:6']),
            ImportColumn::make('nama_siswa')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('kelas_id')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('wali_murid_id')
                ->requiredMapping()
                ->relationship('roles.name', 'Wali Murid')
                ->rules(['required']),
            ImportColumn::make('jenis_kelamin')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('alamat')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('no_telp')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): ?Siswa
    {
        // return Siswa::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Siswa();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your siswa import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public function beforeValidate():Void {
        $kelas_id = Kelas :: query () ->where('nama_kelas', $this->data['kelas'])->first()?->id;
        $this->data['kelas_id'] = $kelas_id;
        $wali_murid_id = User :: query () ->where('name', $this->data['wali_murid'])->first()?->id;
        $this->data['wali_murid_id'] = $wali_murid_id;
        // $this->data['kelas_id'] = Kelas::find($this->data['kelas_id'])->id;
        // $this->data['wali_murid_id'] = User::find($this->data['wali_murid_id'])->id;
    }
}
