<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/';

    protected static ?string $title = 'KYC Dashboard';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Admin\Widgets\TotalSubmissionsWidget::class,
            \App\Filament\Admin\Widgets\PendingSubmissionsWidget::class,
            \App\Filament\Admin\Widgets\ApprovedSubmissionsWidget::class,
            \App\Filament\Admin\Widgets\RecentSubmissionsWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 3,
        ];
    }
}
