<?php

namespace App\Filament\Resources\KycFormResource\Pages;

use App\Filament\Resources\KycFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKycForm extends CreateRecord
{
    protected static string $resource = KycFormResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'KYC Form created successfully';
    }
}
