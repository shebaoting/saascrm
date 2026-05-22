<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KbArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Select::make('kb_category_id')
                    ->relationship('category', 'name'),
                Select::make('user_id')
                    ->relationship('author', 'name'),
                Select::make('status')
                    ->options([
                        'draft' => '草稿',
                        'published' => '已发布',
                        'archived' => '已归档',
                    ])
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at'),
            ]);
    }
}
