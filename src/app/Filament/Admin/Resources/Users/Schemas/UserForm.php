<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('avatar_url')
                    ->label('Avatar')
                    ->image()
                    ->imageEditor()
                    ->imagePreviewHeight('250')
                    ->panelAspectRatio('6:5')
                    ->panelLayout('integrated')
                    ->columnSpan('2'),

                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->minLength(2)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Email')
                            ->required()
                            ->prefixIcon(Heroicon::Envelope)
                            ->email()
                            ->unique(
                                table: 'users',
                                column: 'email',
                                ignoreRecord: true,
                            )
                            ->columnSpanFull(),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->confirmed()
                            ->revealable()
                            ->prefixIcon(Heroicon::FingerPrint)
                            ->dehydrateStateUsing(
                                fn (string $state): string => Hash::make($state)
                            )
                            ->dehydrated(
                                fn (?string $state): bool => filled($state)
                            )
                            ->required(
                                fn (string $context): bool => $context === 'create'
                            )
                            ->columnSpan(1),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->required(
                                fn (string $context): bool => $context === 'create'
                            )
                            ->password()
                            ->revealable()
                            ->prefixIcon(Heroicon::FingerPrint)
                            ->dehydrated(false)
                            ->columnSpan(1),

                        /*
                         * Jangan gunakan relationship('roles', 'name') di sini.
                         * Role dimuat langsung dari tabel Spatie, kemudian
                         * CreateUser/EditUser menyimpannya melalui syncRoles().
                         */
                        Select::make('role_names')
                            ->label('Roles')
                            ->prefixIcon(Heroicon::ShieldCheck)
                            ->options(function (): array {
                                return Role::query()
                                    ->where('guard_name', 'web')
                                    ->when(
                                        ! auth()->user()?->hasRole('super_admin'),
                                        fn (Builder $query): Builder => $query
                                            ->where('name', '!=', 'super_admin'),
                                    )
                                    ->orderByRaw("
                                        CASE name
                                            WHEN 'super_admin' THEN 1
                                            WHEN 'manajemen' THEN 2
                                            WHEN 'kasir' THEN 3
                                            ELSE 4
                                        END
                                    ")
                                    ->pluck('name', 'name')
                                    ->mapWithKeys(
                                        fn (string $name): array => [
                                            $name => match ($name) {
                                                'super_admin' => 'Super Admin',
                                                'manajemen' => 'Manajemen',
                                                'kasir' => 'Kasir',
                                                default => ucwords(
                                                    str_replace(
                                                        ['_', '-'],
                                                        ' ',
                                                        $name,
                                                    )
                                                ),
                                            },
                                        ]
                                    )
                                    ->all();
                            })
                            ->multiple()
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih satu atau beberapa role')
                            ->helperText(
                                'Kasir mengelola customer dan poin. Manajemen juga dapat mengatur retention. Super Admin memiliki seluruh akses.'
                            )
                            ->noSearchResultsMessage('Role tidak ditemukan')
                            ->loadingMessage('Memuat daftar role...')
                            ->columnSpanFull(),
                    ])
                    ->columnSpan('4'),
            ])
            ->columns(6);
    }
}
