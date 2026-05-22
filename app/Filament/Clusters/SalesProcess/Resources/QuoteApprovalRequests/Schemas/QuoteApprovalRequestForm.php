<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class QuoteApprovalRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('quote_id')
                    ->relationship('quote', 'title')
                    ->required(),
                Select::make('approver_id')
                    ->relationship('approver', 'name'),
                TextInput::make('reason'),
                TextInput::make('approval_comment'),
            ]);
    }
}
