<?php

namespace App\Filament\Clusters\SystemSettings\Resources\Departments\Pages;

use App\Filament\Clusters\SystemSettings\Resources\Departments\DepartmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;
}
