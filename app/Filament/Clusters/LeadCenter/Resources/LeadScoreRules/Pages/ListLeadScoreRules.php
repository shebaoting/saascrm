<?php

namespace App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\Pages;

use App\Filament\Clusters\LeadCenter\Resources\LeadScoreRules\LeadScoreRuleResource;
use App\Services\Crm\LeadScoringService;
use App\Support\CrmAccess;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListLeadScoreRules extends ListRecords
{
    protected static string $resource = LeadScoreRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('recalculate')
                ->label('批量重算分数')
                ->icon('heroicon-o-arrow-path')
                ->requiresConfirmation()
                ->action(function (): void {
                    $count = app(LeadScoringService::class)->refreshMany(CrmAccess::tenantId());

                    Notification::make()
                        ->success()
                        ->title('线索分数已重算')
                        ->body("已更新 {$count} 条线索。")
                        ->send();
                }),
        ];
    }
}
