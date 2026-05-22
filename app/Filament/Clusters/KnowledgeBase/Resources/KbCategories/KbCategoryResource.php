<?php

namespace App\Filament\Clusters\KnowledgeBase\Resources\KbCategories;

use App\Filament\Clusters\KnowledgeBase\KnowledgeBaseCluster;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages\CreateKbCategory;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages\EditKbCategory;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages\ListKbCategories;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Pages\ViewKbCategory;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Schemas\KbCategoryForm;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Schemas\KbCategoryInfolist;
use App\Filament\Clusters\KnowledgeBase\Resources\KbCategories\Tables\KbCategoryTable;
use App\Filament\Concerns\UsesCrmAccess;
use App\Models\KbCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class KbCategoryResource extends Resource
{
    use UsesCrmAccess;

    protected static ?string $model = KbCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = '知识库分类';

    protected static ?string $modelLabel = '知识库分类';

    protected static ?string $pluralModelLabel = '知识库分类';

    protected static ?string $title = '知识库分类';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $cluster = KnowledgeBaseCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KbCategoryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KbCategoryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KbCategoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKbCategories::route('/'),
            'create' => CreateKbCategory::route('/create'),
            'view' => ViewKbCategory::route('/{record}'),
            'edit' => EditKbCategory::route('/{record}/edit'),
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
