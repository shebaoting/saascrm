<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('跟进内容')
                    ->schema([
                        Select::make('lead_id')
                            ->label('线索')
                            ->relationship('lead', 'company_name')
                            ->preload(),
                        Select::make('customer_id')
                            ->label('客户')
                            ->relationship('customer', 'name')
                            ->preload(),
                        Select::make('contact_id')
                            ->label('联系人')
                            ->relationship('contact', 'name')
                            ->preload(),
                        Select::make('opportunity_id')
                            ->label('商机')
                            ->relationship('opportunity', 'name')
                            ->preload(),
                        Select::make('type')
                            ->label('跟进类型')
                            ->options(CrmUi::options('activity.type'))
                            ->required()
                            ->default('note'),
                        Select::make('direction')
                            ->label('方向')
                            ->options(CrmUi::options('activity.direction')),
                        DateTimePicker::make('occurred_at')
                            ->label('跟进时间')
                            ->default(now())
                            ->required(),
                        DateTimePicker::make('next_follow_at')
                            ->label('下次跟进时间'),
                        Select::make('owner_user_id')
                            ->label('记录人')
                            ->relationship('owner', 'name')
                            ->default(fn (): ?int => auth()->id())
                            ->required(),
                        Textarea::make('content')
                            ->label('跟进内容')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(['md' => 2, 'xl' => 4])
                    ->compact()
                    ->columnSpanFull(),
            ]);
    }
}
