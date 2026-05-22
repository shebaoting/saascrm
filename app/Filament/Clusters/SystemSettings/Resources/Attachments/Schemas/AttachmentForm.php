<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Attachments\Schemas;

use App\Support\Filament\CrmUi;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AttachmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('path')
                    ->disk('local')
                    ->directory(fn (): string => 'tenants/'.Filament::getTenant()->getKey().'/attachments')
                    ->downloadable()
                    ->openable()
                    ->required(),
                TextInput::make('disk')
                    ->required()
                    ->default('local'),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('model_type')
                    ->required(),
                TextInput::make('model_id')
                    ->required()
                    ->numeric(),
                Select::make('category')
                    ->options(CrmUi::options('attachment.category')),
                TextInput::make('name'),
                TextInput::make('mime_type'),
                TextInput::make('extension'),
                TextInput::make('size')
                    ->numeric(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
