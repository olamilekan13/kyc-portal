<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSystemSetting extends EditRecord
{
    protected static string $resource = SystemSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure the value field contains the actual database value
        // This prevents issues with multiple fields binding to the same column
        if (isset($this->record)) {
            $data['value'] = $this->record->getOriginal('value');
        }

        return $data;
    }

    protected function afterFill(): void
    {
        // Force the value field to contain the database value after all fields are filled
        if (isset($this->record)) {
            $this->data['value'] = $this->record->getOriginal('value');
        }
    }
}
