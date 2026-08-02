<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ClaimResource;
use App\Filament\Resources\DocumentResource;
use App\Filament\Resources\LeaveResource;
use App\Models\Claim;
use App\Models\Document;
use App\Models\Leave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingApprovalsOverview extends BaseWidget
{
    protected ?string $heading = 'Pending Approvals';

    protected static ?int $sort = 8;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // "Pending" leaves include those still awaiting the final admin sign-off
        // after a supervisor has approved them.
        $pendingLeaves = Leave::whereIn('status', ['Pending', 'Supervisor Approved'])->count();
        $pendingClaims = Claim::where('status', 'Pending')->count();
        $pendingReports = Document::where('status', 'pending')->count();

        return [
            Stat::make('Leave Requests', $pendingLeaves)
                ->description('Tap to review pending leaves')
                ->descriptionIcon('heroicon-m-arrow-top-right-on-square')
                ->color($pendingLeaves > 0 ? 'warning' : 'success')
                ->url(LeaveResource::getUrl('index')),

            Stat::make('Expense Claims', $pendingClaims)
                ->description('Tap to review pending claims')
                ->descriptionIcon('heroicon-m-arrow-top-right-on-square')
                ->color($pendingClaims > 0 ? 'warning' : 'success')
                ->url(ClaimResource::getUrl('index')),

            Stat::make('Documents / Reports', $pendingReports)
                ->description('Tap to review pending documents')
                ->descriptionIcon('heroicon-m-arrow-top-right-on-square')
                ->color($pendingReports > 0 ? 'warning' : 'success')
                ->url(DocumentResource::getUrl('index')),
        ];
    }
}
