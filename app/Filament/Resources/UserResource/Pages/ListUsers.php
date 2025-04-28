<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Models\User;
use Filament\Actions;
use Maatwebsite\Excel\Excel;
use Filament\Actions\ImportAction;
use App\Filament\Imports\UserImporter;
use Illuminate\Support\Facades\Storage;
use App\Filament\Resources\UserResource;
use Filament\Resources\Concerns\HasTabs;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ImportAction::make()
                ->label('Import Pengguna')
                ->importer(UserImporter::class)
                ->icon('heroicon-o-arrow-up-tray')
                ->hidden(fn() => Storage::disk('local')->exists('import/user.csv')),
            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('download.user.template')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Pengguna'),
            'admin' => Tab::make()
                ->modifyqueryUsing(fn(Builder $query) => $query->whereHas('roles', function ($query) {
                    $query->where('name', 'super_admin');
                }))
                ->badge(User::whereHas('roles', fn($q) => $q->where('name', 'super_admin'))->count()),
            'kepala_sekolah' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('roles', function ($query) {
                    $query->where('name', 'Kepala Sekolah');
                }))
                ->badge(User::whereHas('roles', fn($q) => $q->where('name', 'Kepala Sekolah'))->count()),
            'wali_kelas' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('roles', function ($query) {
                    $query->where('name', 'Wali Kelas');
                }))
                ->badge(User::whereHas('roles', fn($q) => $q->where('name', 'Wali Kelas'))->count()),
            'wali_murid' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('roles', function ($query) {
                    $query->where('name', 'Wali Murid');
                }))
                ->badge(User::whereHas('roles', fn($q) => $q->where('name', 'Wali Murid'))->count()),
        ];
    }
}
