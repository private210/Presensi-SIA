<?php

namespace App\Filament\Imports;

use App\Models\User;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Filament\Actions\Imports\ImportColumn\ImportColumnRule;
use Illuminate\Validation\Rule;

class UserImporter extends Importer
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('email')
                ->rules([
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email'), // Email wajib unik
                ]),

            ImportColumn::make('password')
                ->prepareStateUsing(fn($state) => Hash::make($state))
                ->rules(['required', 'string', 'min:8']), // Minimal 8 karakter

            ImportColumn::make('roles')
                ->prepareStateUsing(function ($state, $record) {
                    if ($state && Role::where('name', trim($state))->exists()) {
                        $record->save(); // Save user dulu
                        $record->assignRole(trim($state));
                    }
                    return null; // Tidak simpan 'roles' ke kolom user
                })
                ->rules([
                    'required',
                    Rule::exists('roles', 'name'), // Role harus sudah ada
                ]),
        ];
    }

    public function resolveRecord(): ?User
    {
        // return User::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new User();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your user import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
