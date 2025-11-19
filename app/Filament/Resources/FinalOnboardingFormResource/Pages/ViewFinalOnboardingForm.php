<?php

namespace App\Filament\Resources\FinalOnboardingFormResource\Pages;

use App\Filament\Resources\FinalOnboardingFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewFinalOnboardingForm extends ViewRecord
{
    protected static string $resource = FinalOnboardingFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
