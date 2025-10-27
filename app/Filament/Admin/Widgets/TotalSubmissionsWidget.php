<?php

namespace App\Filament\Admin\Widgets;

use App\Models\KycSubmission;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalSubmissionsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = KycSubmission::count();

        return [
            Stat::make('Total KYC Submissions', $total)
                ->description('All time submissions')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([7, 4, 6, 8, 10, 12, 15]),
        ];
    }
}
