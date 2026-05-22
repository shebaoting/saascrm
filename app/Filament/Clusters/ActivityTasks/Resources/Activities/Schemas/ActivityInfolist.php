<?php

namespace App\Filament\Clusters\ActivityTasks\Resources\Activities\Schemas;

use App\Models\Activity;
use App\Support\Filament\CrmUi;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('跟进详情')
                    ->schema([
                        TextEntry::make('content')
                            ->label('跟进内容')
                            ->state(fn (Activity $record): string => CrmUi::followUpContent($record))
                            ->columnSpanFull(),
                        TextEntry::make('type')
                            ->label('跟进类型'),
                        TextEntry::make('direction')
                            ->label('方向')
                            ->placeholder('-'),
                        TextEntry::make('occurred_at')
                            ->label('跟进时间')
                            ->dateTime(),
                        TextEntry::make('next_follow_at')
                            ->label('下次跟进时间')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('owner.name')
                            ->label('记录人'),
                    ])
                    ->columns(['md' => 2, 'xl' => 4])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('关联对象')
                    ->schema([
                        TextEntry::make('lead.company_name')
                            ->label('线索')
                            ->placeholder('-'),
                        TextEntry::make('customer.name')
                            ->label('客户')
                            ->placeholder('-'),
                        TextEntry::make('contact.name')
                            ->label('联系人')
                            ->placeholder('-'),
                        TextEntry::make('opportunity.name')
                            ->label('商机')
                            ->placeholder('-'),
                    ])
                    ->columns(['md' => 2, 'xl' => 4])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('系统信息')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('创建时间')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('更新时间')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('deleted_at')
                            ->label('删除时间')
                            ->dateTime()
                            ->visible(fn (Activity $record): bool => $record->trashed()),
                    ])
                    ->columns(['md' => 3])
                    ->compact()
                    ->columnSpanFull(),
            ]);
    }
}
