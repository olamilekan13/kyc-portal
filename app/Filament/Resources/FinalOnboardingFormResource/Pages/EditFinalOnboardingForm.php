<?php

namespace App\Filament\Resources\FinalOnboardingFormResource\Pages;

use App\Filament\Resources\FinalOnboardingFormResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinalOnboardingForm extends EditRecord
{
    protected static string $resource = FinalOnboardingFormResource::class;

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
}
