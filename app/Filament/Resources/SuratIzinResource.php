<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuratIzinResource\Pages;
use App\Models\Siswa;
use App\Models\SuratIzin;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SuratIzinResource extends Resource
{
    protected static ?string $model = SuratIzin::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Presensi';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form
            ->schema([
                Forms\Components\Select::make('siswa_id')
                    ->label('Siswa')
                    ->options(function () use ($user) {
                        if ($user->role === 'wali_murid') {
                            return Siswa::where('wali_murid_id', $user->id)->pluck('nama_siswa', 'id');
                        } elseif ($user->role === 'wali_kelas') {
                            return Siswa::whereHas('kelas', function ($query) use ($user) {
                                $query->where('wali_kelas_id', $user->id);
                            })->pluck('nama_siswa', 'id');
                        }
                        return Siswa::all()->pluck('nama_siswa', 'id');
                    })
                    ->required()
                    ->searchable(),
                Forms\Components\DatePicker::make('tanggal_mulai')
                    ->required()
                    ->default(Carbon::now())
                    ->maxDate(now()),
                Forms\Components\DatePicker::make('tanggal_selesai')
                    ->required()
                    ->default(Carbon::now())
                    ->minDate(fn(Forms\Get $get) => $get('tanggal_mulai'))
                    ->maxDate(fn(Forms\Get $get) => Carbon::parse($get('tanggal_mulai'))->addDays(14)),
                Forms\Components\Select::make('jenis_izin')
                    ->required()
                    ->options([
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                    ]),
                Forms\Components\Textarea::make('keterangan')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('file_pendukung')
                    ->directory('surat-izin')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('diajukan_oleh')
                    ->default(fn() => Auth::id()),
                Forms\Components\Hidden::make('status')
                    ->default('menunggu'),
            ]);
    }

    public static function table(Table $table): Table
    {
        $user = Auth::user();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('siswa.nama_siswa')
                    ->searchable()
                    ->sortable()
                    ->label('Siswa'),
                Tables\Columns\TextColumn::make('siswa.kelas.nama_kelas')
                    ->sortable()
                    ->searchable()
                    ->label('Kelas'),
                Tables\Columns\TextColumn::make('tanggal_mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_izin')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sakit' => 'info',
                        'izin' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'menunggu' => 'warning',
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('pengaju.name')
                    ->label('Diajukan Oleh'),
                Tables\Columns\TextColumn::make('verifikator.name')
                    ->label('Diverifikasi Oleh')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('tanggal_verifikasi')
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_izin')
                    ->options([
                        'sakit' => 'Sakit',
                        'izin' => 'Izin',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari'),
                        Forms\Components\DatePicker::make('tanggal_sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_mulai', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_selesai', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('verifikasi')
                    ->action(function (SuratIzin $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                            'diverifikasi_oleh' => Auth::id(),
                            'tanggal_verifikasi' => now(),
                        ]);
                    })
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'disetujui' => 'Disetujui',
                                'ditolak' => 'Ditolak',
                            ])
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->visible(
                        fn(SuratIzin $record) =>
                        $user->role === 'wali_kelas' &&
                            $record->status === 'menunggu' &&
                            $user->id === $record->siswa->kelas->wali_kelas_id
                    ),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(
                        fn(SuratIzin $record) =>
                        $user->role === 'wali_murid' &&
                            $record->status === 'menunggu' &&
                            $user->id === $record->diajukan_oleh
                    ),
                Tables\Actions\DeleteAction::make()
                    ->visible(
                        fn(SuratIzin $record) =>
                        $user->role === 'wali_murid' &&
                            $record->status === 'menunggu' &&
                            $user->id === $record->diajukan_oleh
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => $user->role === 'admin'),
                ]),
            ]);
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
            'index' => Pages\ListSuratIzins::route('/'),
            // 'create' => Pages\CreateSuratIzin::route('/create'),
            // 'edit' => Pages\EditSuratIzin::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Filter berdasarkan peran pengguna
        if ($user->role === 'wali_kelas') {
            // Wali kelas hanya bisa melihat surat izin siswa di kelasnya
            return parent::getEloquentQuery()
                ->whereHas('siswa.kelas', function (Builder $query) use ($user) {
                    $query->where('wali_kelas_id', $user->id);
                });
        } elseif ($user->role === 'wali_murid') {
            // Wali murid hanya bisa melihat surat izin anaknya
            return parent::getEloquentQuery()
                ->whereHas('siswa', function (Builder $query) use ($user) {
                    $query->where('wali_murid_id', $user->id);
                });
        }

        // Admin dan kepala sekolah bisa melihat semua surat izin
        return parent::getEloquentQuery();
    }
}
