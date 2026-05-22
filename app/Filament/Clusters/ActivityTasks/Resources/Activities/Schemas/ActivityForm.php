<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('lead_id')
                    ->relationship('lead', 'company_name'),
                Select::make('customer_id')
                    ->relationship('customer', 'name'),
                Select::make('contact_id')
                    ->relationship('contact', 'name'),
                Select::make('opportunity_id')
                    ->relationship('opportunity', 'name'),
                Select::make('type')
                    ->options(CrmUi::options('activity.type'))
                    ->required()
                    ->default('note'),
                Select::make('direction')
                    ->options(CrmUi::options('activity.direction')),
                TextInput::make('subject'),
                Textarea::make('content')
                    ->columnSpanFull(),
                TextInput::make('outcome'),
                DateTimePicker::make('occurred_at')
                    ->required(),
                DateTimePicker::make('next_follow_at'),
                Select::make('owner_user_id')
                    ->relationship('owner', 'name')
                    ->default(fn (): ?int => auth()->id())
                    ->required(),
            ]);
    }
}
