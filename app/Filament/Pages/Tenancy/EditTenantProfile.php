<?php

namespace App\Filament\Pages\Tenancy;

use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile as BaseEditTenantProfile;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class EditTenantProfile extends BaseEditTenantProfile
{
    public static function getLabel(): string
    {
        return '公司资料';
    }

    public static function canView(Model $tenant): bool
    {
        return auth()->user()?->canAccessTenant($tenant) ?? false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('公司名称')->required()->maxLength(255),
            TextInput::make('short_name')->label('公司简称')->maxLength(100),
            FileUpload::make('logo_path')
                ->label('公司 Logo')
                ->disk('local')
                ->directory(fn (): string => 'tenants/'.Filament::getTenant()->getKey().'/branding')
                ->image()
                ->maxSize(1024),
            TextInput::make('contact_name')->label('联系人')->maxLength(100),
            TextInput::make('contact_phone')->label('联系电话')->tel()->maxLength(50),
            TextInput::make('contact_email')->label('联系邮箱')->email()->maxLength(255),
            Select::make('currency')
                ->label('默认币种')
                ->options(['CNY' => 'CNY', 'USD' => 'USD', 'EUR' => 'EUR'])
                ->default('CNY'),
            TextInput::make('timezone')->label('时区')->default('Asia/Shanghai')->maxLength(64),
            Textarea::make('address')->label('公司地址')->columnSpanFull(),
            Textarea::make('settings.quote_terms')
                ->label('报价默认条款')
                ->rows(5)
                ->columnSpanFull(),
        ]);
    }
}
