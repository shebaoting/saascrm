<?php

namespace App\Filament\Clusters\SalesProcess\Resources\Opportunities\Schemas;

use App\Models\Contact;
use App\Models\PipelineStage;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->live()
                    ->required(),
                Select::make('contact_id')
                    ->options(fn (Get $get): array => Contact::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->when($get('customer_id'), fn (Builder $query, int|string $customerId): Builder => $query->where('customer_id', $customerId))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
                Select::make('pipeline_id')
                    ->relationship('pipeline', 'name')
                    ->live()
                    ->required(),
                Select::make('pipeline_stage_id')
                    ->options(fn (Get $get): array => PipelineStage::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->when($get('pipeline_id'), fn (Builder $query, int|string $pipelineId): Builder => $query->where('pipeline_id', $pipelineId))
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->pluck('name', 'id')
                        ->all())
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('forecast_category')
                    ->options(CrmUi::options('forecast_category'))
                    ->required()
                    ->default('pipeline'),
                DatePicker::make('expected_close_date'),
                Select::make('responsible_user_id')
                    ->relationship('responsible', 'name'),
                TextInput::make('lost_reason'),
                TextInput::make('lost_remarks'),
                TextInput::make('invalid_reason'),
                TextInput::make('invalid_remarks'),
                TextInput::make('notes'),
                ...CustomFieldUi::formSections('opportunity'),
            ]);
    }
}
