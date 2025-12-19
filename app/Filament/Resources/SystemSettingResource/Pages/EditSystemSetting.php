<?php

namespace App\Filament\Resources\SystemSettingResource\Pages;

use App\Filament\Resources\SystemSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSystemSetting extends EditRecord
{
    protected static string $resource = SystemSettingResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        // After mounting, fix the form data to ensure proper display
        $recordModel = $this->getRecord();

        if ($recordModel->type === 'richtext') {
            // For richtext, use richtext_value field
            $value = $recordModel->getOriginal('value');
            $this->data['richtext_value'] = (is_null($value) || trim($value) === '') ? '<p></p>' : $value;
            $this->data['value'] = null;
        } elseif ($recordModel->type === 'image') {
            // For image type, FileUpload component expects the value as-is (it will handle it)
            // Don't override what Filament already set
        } elseif ($recordModel->type === 'boolean') {
            // For boolean type, convert string "true"/"false" to actual boolean
            $value = $recordModel->getOriginal('value');
            $this->data['value'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } else {
            // For other types (text, number, textarea, json), ensure value is the raw database value
            $this->data['value'] = $recordModel->getOriginal('value');
        }
    }

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
            // CRITICAL: Must use getOriginal to bypass accessor for proper handling
            $value = $this->record->getOriginal('value');
            $type = $this->record->getOriginal('type');

            // For richtext type, use the richtext_value field (separate from value column)
            if ($type === 'richtext') {
                // TipTap requires valid HTML, not null or empty string
                $data['richtext_value'] = (is_null($value) || trim($value) === '') ? '<p></p>' : $value;
                // Clear the 'value' field to prevent it from being populated for richtext
                $data['value'] = null;
            }
            // For other types, ensure value is properly set as a string
            else {
                // Force override whatever was in $data['value'] with the actual database value
                $data['value'] = $value;
            }
        }

        return $data;
    }

    // Removed afterFill() to avoid interfering with Filament's state initialization
    // The mutateFormDataBeforeFill() method should be sufficient

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // For richtext type, copy richtext_value back to value column
        if (isset($this->record) && $this->record->type === 'richtext') {
            if (isset($data['richtext_value'])) {
                $data['value'] = $data['richtext_value'];
                unset($data['richtext_value']); // Remove the temporary field
            } else {
                // If richtext_value is not set, ensure value is not null
                // This can happen if the field wasn't touched
                if (!isset($data['value'])) {
                    $data['value'] = $this->record->getOriginal('value') ?? '<p></p>';
                }
            }
        }

        // For image type, FileUpload automatically handles the path correctly
        // No need to manipulate it - Filament stores the full path with directory

        return $data;
    }
}
