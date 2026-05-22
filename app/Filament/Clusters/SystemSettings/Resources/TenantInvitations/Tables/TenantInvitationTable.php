<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Tables;

use App\Models\TenantInvitation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TenantInvitationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->label('邮箱')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('token')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('invite_url')
                    ->label('邀请链接')
                    ->state(fn (TenantInvitation $record): string => route('tenant-invitations.show', $record->token))
                    ->copyable()
                    ->limit(36),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('inviter.name')
                    ->searchable(),
                TextColumn::make('accepted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => '待接受',
                        'accepted' => '已接受',
                        'expired' => '已过期',
                        'cancelled' => '已取消',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
