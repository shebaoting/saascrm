<?php

namespace App\Filament\Clusters\SystemSettings\Resources\TenantInvitations\Schemas;

use App\Models\TenantInvitation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TenantInvitationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->label('邮箱')
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('token'),
                TextEntry::make('invite_url')
                    ->label('邀请链接')
                    ->state(fn (TenantInvitation $record): string => route('tenant-invitations.show', $record->token))
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('inviter.name'),
                TextEntry::make('accepted_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('expires_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
