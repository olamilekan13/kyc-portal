<?php

namespace App\Filament\Resources\KycSubmissionResource\Pages;

use App\Filament\Resources\KycSubmissionResource;
use App\Models\KycSubmission;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListKycSubmissions extends ListRecords
{
    protected static string $resource = KycSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action - submissions come from public portal
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Submissions'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', KycSubmission::STATUS_PENDING))
                ->badge(fn (): int => KycSubmission::where('status', KycSubmission::STATUS_PENDING)->count())
                ->badgeColor('gray'),

            'under_review' => Tab::make('Under Review')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', KycSubmission::STATUS_UNDER_REVIEW))
                ->badge(fn (): int => KycSubmission::where('status', KycSubmission::STATUS_UNDER_REVIEW)->count())
                ->badgeColor('info'),

            'verified' => Tab::make('Verified')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', KycSubmission::STATUS_VERIFIED))
                ->badge(fn (): int => KycSubmission::where('status', KycSubmission::STATUS_VERIFIED)->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', KycSubmission::STATUS_APPROVED))
                ->badge(fn (): int => KycSubmission::where('status', KycSubmission::STATUS_APPROVED)->count())
                ->badgeColor('success'),

            'declined' => Tab::make('Declined')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', KycSubmission::STATUS_DECLINED))
                ->badge(fn (): int => KycSubmission::where('status', KycSubmission::STATUS_DECLINED)->count())
                ->badgeColor('danger'),
        ];
    }
}
