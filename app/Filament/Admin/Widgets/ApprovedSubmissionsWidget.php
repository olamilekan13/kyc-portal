<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KycSubmission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ApprovedSubmissionsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $approved = KycSubmission::where('status', KycSubmission::STATUS_APPROVED)->count();

        return [
            Stat::make('Approved Submissions', $approved)
                ->description('Successfully verified')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([2, 3, 4, 5, 6, 8, 9]),
        ];
    }
}
