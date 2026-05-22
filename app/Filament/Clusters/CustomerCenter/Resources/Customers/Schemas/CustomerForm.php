<?php

namespace App\Filament\Clusters\CustomerCenter\Resources\Customers\Schemas;

use App\Support\Filament\CrmUi;
use App\Support\Filament\CustomFieldUi;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('基础信息')
                    ->schema([
                        CustomFieldUi::applyLayout(TextInput::make('name')->required()->maxLength(255), 'customer', 'name'),
                        CustomFieldUi::applyLayout(TextInput::make('short_name')->maxLength(100), 'customer', 'short_name'),
                        CustomFieldUi::applyLayout(TextInput::make('phone')->tel()->maxLength(50), 'customer', 'phone'),
                    ])
                    ->columns(['md' => 2])
                    ->compact()
                    ->columnSpanFull(),
                Section::make('联系人信息')
                    ->schema([
                        Repeater::make('contacts')
                            ->label('联系人')
                            ->hiddenLabel()
                            ->relationship()
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(50),
                                TextInput::make('email')
                                    ->label('邮箱')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('position')
                                    ->maxLength(100),
                            ])
                            ->columns(['md' => 2])
                            ->defaultItems(0)
                            ->addActionLabel('添加联系人')
                            ->reorderable(false)
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->columnSpanFull(),
                    ])
                    ->columns(['md' => 2])
                    ->compact()
                    ->collapsible()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->columnSpanFull(),
                Section::make('更多资料')
                    ->schema([
                        CustomFieldUi::applyLayout(Select::make('customer_type')->options(CrmUi::options('customer.customer_type'))->required()->default('company'), 'customer', 'customer_type'),
                        CustomFieldUi::applyLayout(Select::make('lifecycle_stage')->options(CrmUi::options('customer.lifecycle_stage'))->required()->default('new'), 'customer', 'lifecycle_stage'),
                        Select::make('owner_user_id')
                            ->relationship('owner', 'name')
                            ->default(fn (): ?int => auth()->id())
                            ->required(fn (string $operation): bool => $operation !== 'create')
                            ->hidden(fn (string $operation): bool => $operation === 'create' || ! CustomFieldUi::fieldIsVisible('customer', 'owner_user_id'))
                            ->disabled(fn (): bool => CustomFieldUi::fieldIsReadOnly('customer', 'owner_user_id')),
                        CustomFieldUi::applyLayout(TextInput::make('email')->label('邮箱')->email()->maxLength(255), 'customer', 'email'),
                        CustomFieldUi::applyLayout(TextInput::make('source')->maxLength(100), 'customer', 'source'),
                        CustomFieldUi::applyLayout(TagsInput::make('tags')->columnSpanFull(), 'customer', 'tags'),
                        CustomFieldUi::applyLayout(TextInput::make('country_code')->maxLength(10), 'customer', 'country_code'),
                        CustomFieldUi::applyLayout(TextInput::make('area_id')->maxLength(20), 'customer', 'area_id'),
                    ])
                    ->columns(['md' => 2])
                    ->compact()
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->columnSpanFull(),
                ...CustomFieldUi::formSections('customer'),
            ]);
    }
}
