<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KycSubmission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingSubmissionsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $pending = KycSubmission::where('status', KycSubmission::STATUS_PENDING)->count();

        return [
            Stat::make('Pending Submissions', $pending)
                ->description('Awaiting review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([3, 2, 4, 5, 3, 4, 6]),
        ];
    }
}
