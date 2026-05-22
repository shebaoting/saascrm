<?php

namespace App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\Pages;

use App\Filament\Clusters\SystemSettings\Resources\BusinessNumberRules\BusinessNumberRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBusinessNumberRule extends CreateRecord
{
    protected static string $resource = BusinessNumberRuleResource::class;
}
