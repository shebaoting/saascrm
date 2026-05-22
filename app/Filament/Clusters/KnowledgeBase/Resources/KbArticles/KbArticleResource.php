<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbArticles;

use App\Filament\Clusters\KnowledgeBase\KnowledgeBaseCluster;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages\CreateKbArticle;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages\EditKbArticle;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages\ListKbArticles;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Pages\ViewKbArticle;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Schemas\KbArticleForm;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Schemas\KbArticleInfolist;
use App\Filament\Clusters\KnowledgeBase\Resources\KbArticles\Tables\KbArticleTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\KbArticle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KbArticleResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = KbArticle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '知识库文章';

    protected static ?string $modelLabel = '知识库文章';

    protected static ?string $pluralModelLabel = '知识库文章';

    protected static ?string $title = '知识库文章';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = KnowledgeBaseCluster::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return KbArticleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KbArticleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KbArticleTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKbArticles::route('/'),
            'create' => CreateKbArticle::route('/create'),
            'view' => ViewKbArticle::route('/{record}'),
            'edit' => EditKbArticle::route('/{record}/edit'),
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
