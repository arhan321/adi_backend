<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @var array<int, string>
     */
    private array $selectedRoleNames = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_names'] = $this->record
            ->getRoleNames()
            ->values()
            ->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
    {
        $this->record->syncRoles($this->selectedRoleNames);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('View Details')
                ->icon(Heroicon::Eye),

            DeleteAction::make()
                ->label('Delete')
                ->icon(Heroicon::Trash),
        ];
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
