<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads;

use App\Filament\Clusters\LeadCenter\LeadCenterCluster;
use App\Filament\Clusters\LeadCenter\Resources\Leads\Pages\ManageLeads;
use App\Models\Lead;
use App\Services\Crm\LeadAssignmentService;
use App\Services\Crm\LeadConversionService;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '全部线索';

    protected static ?string $modelLabel = '全部线索';

    protected static ?string $pluralModelLabel = '全部线索';

    protected static ?string $title = '全部线索';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = LeadCenterCluster::class;

    protected static ?string $recordTitleAttribute = 'company_name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name'),
                TextInput::make('contact_name'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('email')
                    ->label('邮箱')
                    ->email(),
                TextInput::make('wechat_id'),
                TextInput::make('country_code'),
                TextInput::make('area_id'),
                TextInput::make('address'),
                TextInput::make('source'),
                TextInput::make('tags'),
                Select::make('status')
                    ->options(CrmUi::options('lead.status'))
                    ->required()
                    ->default('new'),
                Select::make('qualification_status')
                    ->options(CrmUi::options('lead.qualification_status')),
                TextInput::make('score')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('owner_user_id')
                    ->relationship('owner', 'name'),
                DateTimePicker::make('pool_entered_at'),
                DateTimePicker::make('last_activity_at'),
                DateTimePicker::make('next_activity_at'),
                Select::make('converted_customer_id')
                    ->relationship('convertedCustomer', 'name'),
                DateTimePicker::make('converted_at'),
                Select::make('converted_by')
                    ->relationship('convertedBy', 'name'),
                TextInput::make('lost_reason'),
                TextInput::make('custom_fields'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Lead $record): bool => $record->trashed()),
                TextEntry::make('company_name')
                    ->placeholder('-'),
                TextEntry::make('contact_name')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('邮箱')
                    ->placeholder('-'),
                TextEntry::make('wechat_id')
                    ->placeholder('-'),
                TextEntry::make('country_code')
                    ->placeholder('-'),
                TextEntry::make('area_id')
                    ->placeholder('-'),
                TextEntry::make('address')
                    ->placeholder('-'),
                TextEntry::make('source')
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('qualification_status')
                    ->placeholder('-'),
                TextEntry::make('score')
                    ->numeric(),
                TextEntry::make('owner.name')
                    ->placeholder('-'),
                TextEntry::make('pool_entered_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('last_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('next_activity_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('convertedCustomer.name')
                    ->label('已转客户')
                    ->placeholder('-'),
                TextEntry::make('converted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('convertedBy.name')
                    ->placeholder('-'),
                TextEntry::make('lost_reason')
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company_name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('company_name')
                    ->searchable(),
                TextColumn::make('contact_name')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable(),
                TextColumn::make('wechat_id')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->searchable(),
                TextColumn::make('area_id')
                    ->searchable(),
                TextColumn::make('address')
                    ->searchable(),
                TextColumn::make('source')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('qualification_status')
                    ->searchable(),
                TextColumn::make('score')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('owner.name')
                    ->searchable(),
                TextColumn::make('pool_entered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('last_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('next_activity_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('convertedCustomer.name')
                    ->searchable(),
                TextColumn::make('converted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('convertedBy.name')
                    ->searchable(),
                TextColumn::make('lost_reason')
                    ->searchable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('claim')
                    ->label('领取')
                    ->icon('heroicon-o-hand-raised')
                    ->visible(fn (Lead $record): bool => blank($record->owner_user_id) || in_array($record->status, ['unassigned', 'pooled'], true))
                    ->action(function (Lead $record): void {
                        app(LeadAssignmentService::class)->claim($record, auth()->user());

                        Notification::make()->success()->title('线索已领取')->send();
                    }),
                Action::make('convert')
                    ->label('转客户')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->visible(fn (Lead $record): bool => $record->status !== 'converted')
                    ->form([
                        Toggle::make('create_opportunity')
                            ->label('同时创建商机')
                            ->default(true),
                        TextInput::make('opportunity_name')
                            ->label('商机名称')
                            ->maxLength(255),
                    ])
                    ->action(function (Lead $record, array $data): void {
                        app(LeadConversionService::class)->convert(
                            $record,
                            (bool) ($data['create_opportunity'] ?? false),
                            $data['opportunity_name'] ?? null,
                        );

                        Notification::make()->success()->title('线索已转为客户')->send();
                    }),
                Action::make('release')
                    ->label('释放到公海')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (Lead $record): void {
                        app(LeadAssignmentService::class)->release($record, '手动释放');

                        Notification::make()->success()->title('线索已进入公海')->send();
                    }),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLeads::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
