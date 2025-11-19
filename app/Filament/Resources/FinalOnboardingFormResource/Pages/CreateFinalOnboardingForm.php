<?php

namespace App\Filament\Resources\FinalOnboardingFormResource\Pages;

use App\Filament\Resources\FinalOnboardingFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFinalOnboardingForm extends CreateRecord
{
    protected static string $resource = FinalOnboardingFormResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
