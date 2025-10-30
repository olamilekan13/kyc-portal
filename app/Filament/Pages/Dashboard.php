<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'KYC Dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\TotalSubmissionsWidget::class,
            \App\Filament\Widgets\PendingSubmissionsWidget::class,
            \App\Filament\Widgets\ApprovedSubmissionsWidget::class,
        ];
    }

    public function getColumns(): array | int
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
