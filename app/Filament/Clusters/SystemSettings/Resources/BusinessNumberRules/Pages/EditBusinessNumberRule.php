<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages;

use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\BusinessNumberRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditBusinessNumberRule extends EditRecord
{
    protected static string $resource = BusinessNumberRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
