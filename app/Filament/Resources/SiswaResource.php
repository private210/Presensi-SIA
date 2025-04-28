<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Kelas;
use App\Models\Siswa;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use App\Filament\Imports\SiswaImporter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SiswaResource\Pages;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;


class SiswaResource extends Resource
{
    protected static ?string $model = Siswa::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Siswa';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nis')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('NIS')
                    ->numeric()  // Menambahkan validasi hanya angka
                    ->minLength(6) // Opsional, jika NISN harus memiliki panjang tertentu
                    ->placeholder('Masukkan NISN'),
                Forms\Components\TextInput::make('nama_siswa')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Siswa'),
                Forms\Components\Select::make('kelas_id')
                    ->label('Kelas')
                    ->options(Kelas::all()->pluck('nama_kelas', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('wali_murid_id')
                    ->label('Wali Murid')
                    ->options(
                        User::whereHas('roles', function ($query) {
                            $query->where('name', 'Wali Murid');
                        })->pluck('name', 'id')
                    )
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('wali_murid_id', $state);
                    })
                    ->required(),
                Forms\Components\Select::make('jenis_kelamin')
                    ->required()
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                Forms\Components\Textarea::make('alamat')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('no_telp')
                    ->tel()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nis')
                    ->searchable()
                    ->sortable()
                    ->label('NIS'),
                Tables\Columns\TextColumn::make('nama_siswa')
                    ->searchable()
                    ->sortable()
                    ->label('Nama Siswa'),
                Tables\Columns\TextColumn::make('kelas.nama_kelas')
                    ->sortable()
                    ->searchable()
                    ->label('Kelas'),
                Tables\Columns\TextColumn::make('WaliMurid.name')
                    ->label('Wali Murid')
                    ->searchable(),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'L' => 'primary',
                        'P' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('no_telp')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kelas_id')
                    ->label('Kelas')
                    ->options(Kelas::all()->pluck('nama_kelas', 'id')),
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make(),
            ]);
        // ->headerActions([
        //     ImportAction::make()
        //         ->label('Import Siswa')
        //         ->importer(SiswaImporter::class)
        // ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiswas::route('/'),
            // 'create' => Pages\CreateSiswa::route('/create'),
            // 'edit' => Pages\EditSiswa::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Filter berdasarkan peran pengguna
        if ($user->role === 'wali_kelas') {
            // Wali kelas hanya bisa melihat siswa di kelasnya
            return parent::getEloquentQuery()
                ->whereHas('kelas', function (Builder $query) use ($user) {
                    $query->where('wali_kelas_id', $user->id);
                });
        } elseif ($user->role === 'wali_murid') {
            // Wali murid hanya bisa melihat anaknya
            return parent::getEloquentQuery()
                ->where('wali_murid_id', $user->id);
        }

        // Admin dan kepala sekolah bisa melihat semua siswa
        return parent::getEloquentQuery();
    }
}
