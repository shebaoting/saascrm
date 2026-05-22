<?php

namespace App\Filament\Clusters\SystemSettings\Resources\CustomFieldLayouts\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CustomFieldLayoutForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('model_type')
                    ->options(CrmUi::options('target_type'))
                    ->required(),
                Select::make('role_id')
                    ->relationship('role', 'name'),
                Textarea::make('layout')
                    ->rows(12)
                    ->json()
                    ->formatStateUsing(fn (mixed $state): string => json_encode($state ?: [
                        'groups' => [
                            ['name' => '基础信息', 'fields' => []],
                            ['name' => '扩展字段', 'fields' => []],
                        ],
                        'hidden_fields' => [],
                        'readonly_fields' => [],
                        'list_columns' => [],
                        'detail_fields' => [],
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                    ->dehydrateStateUsing(fn (?string $state): array => json_decode($state ?: '{}', true) ?: [])
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
