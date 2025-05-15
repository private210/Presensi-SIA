<?php

namespace App\Filament\Resources\SiswaResource\Pages;

use Filament\Actions;
use Filament\Actions\ImportAction;
use App\Filament\Imports\SiswaImporter;
use App\Filament\Resources\SiswaResource;
use Filament\Resources\Pages\ListRecords;

class ListSiswas extends ListRecords
{
    protected static string $resource = SiswaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            // ImportAction::make()
            //     ->label('Import Siswa')
            //     ->importer(SiswaImporter::class),
            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->url(route('download.siswa.template'))
                ->icon('heroicon-o-arrow-down-tray')
        ];
    }
}
