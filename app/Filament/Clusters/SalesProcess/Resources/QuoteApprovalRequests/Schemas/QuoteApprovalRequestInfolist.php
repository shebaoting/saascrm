<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class QuoteApprovalRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('quote.title')
                    ->label('报价单'),
                TextEntry::make('requester.name'),
                TextEntry::make('approver.name')
                    ->label('审批人')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('reason')
                    ->placeholder('-'),
                TextEntry::make('approval_comment')
                    ->placeholder('-'),
                TextEntry::make('requested_at')
                    ->dateTime(),
                TextEntry::make('approved_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejected_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
