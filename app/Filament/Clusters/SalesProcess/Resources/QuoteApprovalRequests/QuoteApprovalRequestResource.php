<?php

namespace App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests;

use App\Filament\Clusters\SalesProcess\Resources\QuoteApprovalRequests\Pages\ManageQuoteApprovalRequests;
use App\Filament\Clusters\SalesProcess\SalesProcessCluster;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\QuoteApprovalRequest;
use App\Services\Crm\ActivityService;
use App\Services\Crm\AuditLogService;
use App\Support\Filament\CrmUi;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteApprovalRequestResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = QuoteApprovalRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '报价审批';

    protected static ?string $modelLabel = '报价审批';

    protected static ?string $pluralModelLabel = '报价审批';

    protected static ?string $title = '报价审批';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = SalesProcessCluster::class;

    protected static ?string $recordTitleAttribute = 'status';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
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

    public static function infolist(Schema $schema): Schema
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

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('quote.title')
                    ->searchable(),
                TextColumn::make('requester.name')
                    ->searchable(),
                TextColumn::make('approver.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('reason')
                    ->searchable(),
                TextColumn::make('approval_comment')
                    ->searchable(),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('rejected_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('通过')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (QuoteApprovalRequest $record): bool => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (QuoteApprovalRequest $record): void {
                        $record->forceFill([
                            'approver_id' => $record->approver_id ?: auth()->id(),
                            'status' => 'approved',
                            'approved_at' => now(),
                        ])->save();

                        $record->quote()->update(['status' => 'approved']);

                        $quote = $record->quote()->with(['customer', 'contact', 'opportunity'])->first();

                        if ($quote) {
                            app(ActivityService::class)->recordSystemEvent(
                                $quote->tenant_id,
                                '报价审批通过：'.$quote->quote_number,
                                $record->approval_comment,
                                [
                                    'customer' => $quote->customer,
                                    'contact' => $quote->contact,
                                    'opportunity' => $quote->opportunity,
                                ],
                            );

                            app(AuditLogService::class)->record('quote_approved', $quote, [
                                'status' => 'pending_approval',
                            ], [
                                'status' => 'approved',
                                'approval_id' => $record->id,
                                'approver_id' => $record->approver_id,
                            ]);
                        }

                        Notification::make()->success()->title('报价审批已通过')->send();
                    }),
                Action::make('reject')
                    ->label('拒绝')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (QuoteApprovalRequest $record): bool => $record->status === 'pending')
                    ->form([
                        TextInput::make('approval_comment')
                            ->label('审批意见')
                            ->maxLength(1000),
                    ])
                    ->action(function (QuoteApprovalRequest $record, array $data): void {
                        $record->forceFill([
                            'approver_id' => $record->approver_id ?: auth()->id(),
                            'status' => 'rejected',
                            'approval_comment' => $data['approval_comment'] ?? null,
                            'rejected_at' => now(),
                        ])->save();

                        $record->quote()->update(['status' => 'rejected']);

                        $quote = $record->quote()->with(['customer', 'contact', 'opportunity'])->first();

                        if ($quote) {
                            app(ActivityService::class)->recordSystemEvent(
                                $quote->tenant_id,
                                '报价审批拒绝：'.$quote->quote_number,
                                $record->approval_comment,
                                [
                                    'customer' => $quote->customer,
                                    'contact' => $quote->contact,
                                    'opportunity' => $quote->opportunity,
                                ],
                            );

                            app(AuditLogService::class)->record('quote_rejected', $quote, [
                                'status' => 'pending_approval',
                            ], [
                                'status' => 'rejected',
                                'approval_id' => $record->id,
                                'approver_id' => $record->approver_id,
                            ]);
                        }

                        Notification::make()->success()->title('报价审批已拒绝')->send();
                    }),
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQuoteApprovalRequests::route('/'),
        ];
    }
}
