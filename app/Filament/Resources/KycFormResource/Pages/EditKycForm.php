<?php

namespace App\Filament\Resources\KycFormResource\Pages;

use App\Filament\Resources\KycFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKycForm extends EditRecord
{
    protected static string $resource = KycFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'KYC Form updated successfully';
    }
}
