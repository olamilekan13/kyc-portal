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
            $value = $this->record->getOriginal('value');

            // For image type, FileUpload expects an array, but we store as string
            // Don't convert here - let the field handle it naturally
            if ($this->record->type === 'image' && is_string($value)) {
                $data['value'] = $value;
            } else {
                $data['value'] = $value;
            }
        }

        return $data;
    }

    protected function afterFill(): void
    {
        // Force the value field to contain the database value after all fields are filled
        if (isset($this->record)) {
            $value = $this->record->getOriginal('value');

            // For image type, don't override if it's already set correctly
            if ($this->record->type !== 'image') {
                $this->data['value'] = $value;
            }
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // For image type, FileUpload returns the path as string or array
        // Ensure we store it as a string in the database
        if (isset($this->record) && $this->record->type === 'image') {
            if (isset($data['value'])) {
                // If it's an array (from FileUpload), get the first item
                if (is_array($data['value'])) {
                    $data['value'] = $data['value'][0] ?? '';
                }
            }
        }

        return $data;
    }
}
