<?php

namespace App\Filament\Clusters\SystemSettings\Resources\FieldHistories;

use App\Filament\Clusters\SystemSettings\Resources\FieldHistories\Pages\ManageFieldHistories;
use App\Filament\Clusters\SystemSettings\SystemSettingsCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\FieldHistory;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FieldHistoryResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = FieldHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = '字段历史';

    protected static ?string $modelLabel = '字段历史';

    protected static ?string $pluralModelLabel = '字段历史';

    protected static ?string $title = '字段历史';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SystemSettingsCluster::class;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('created_at')
                ->label('时间')
                ->dateTime(),
            TextEntry::make('model_type')
                ->label('业务对象')
                ->formatStateUsing(fn (?string $state): string => class_basename((string) $state)),
            TextEntry::make('model_id')
                ->label('记录ID'),
            TextEntry::make('field')
                ->label('字段'),
            KeyValueEntry::make('old_value')
                ->label('修改前'),
            KeyValueEntry::make('new_value')
                ->label('修改后'),
            TextEntry::make('ip_address')
                ->label('IP')
                ->placeholder('-'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('时间')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('model_type')
                    ->label('对象')
                    ->formatStateUsing(fn (?string $state): string => class_basename((string) $state))
                    ->searchable(),
                TextColumn::make('model_id')
                    ->label('记录ID')
                    ->sortable(),
                TextColumn::make('field')
                    ->label('字段')
                    ->searchable(),
                TextColumn::make('old_value.value')
                    ->label('修改前')
                    ->limit(30),
                TextColumn::make('new_value.value')
                    ->label('修改后')
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('model_type')
                    ->label('对象')
                    ->options([
                        \App\Models\Lead::class => '线索',
                        \App\Models\Customer::class => '客户',
                        \App\Models\Contact::class => '联系人',
                        \App\Models\Opportunity::class => '商机',
                        \App\Models\Quote::class => '报价',
                        \App\Models\Order::class => '订单',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFieldHistories::route('/'),
        ];
    }
}
