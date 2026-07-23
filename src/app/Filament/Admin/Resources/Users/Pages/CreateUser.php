<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Role dipisahkan dari data users karena role_names bukan kolom
     * pada tabel users. Nilainya disimpan setelah user berhasil dibuat.
     *
     * @var array<int, string>
     */
    private array $selectedRoleNames = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->selectedRoleNames = array_values(
            array_filter(
                (array) ($data['role_names'] ?? []),
                fn (mixed $role): bool => is_string($role)
                    && trim($role) !== '',
            )
        );

        unset($data['role_names']);

        $this->validateSelectedRoles();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles($this->selectedRoleNames);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    private function validateSelectedRoles(): void
    {
        if ($this->selectedRoleNames === []) {
            throw ValidationException::withMessages([
                'data.role_names' => 'Pilih minimal satu role.',
            ]);
        }

        $availableRoles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $this->selectedRoleNames)
            ->pluck('name')
            ->all();

        if (count($availableRoles) !== count($this->selectedRoleNames)) {
            throw ValidationException::withMessages([
                'data.role_names' => 'Terdapat role yang tidak valid.',
            ]);
        }

        if (
            in_array('super_admin', $this->selectedRoleNames, true)
            && ! auth()->user()?->hasRole('super_admin')
        ) {
            throw ValidationException::withMessages([
                'data.role_names' => 'Hanya Super Admin yang dapat memberikan role Super Admin.',
            ]);
        }
    }
}
