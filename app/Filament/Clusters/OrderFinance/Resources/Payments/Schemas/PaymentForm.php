<?php

namespace App\Filament\Clusters\OrderFinance\Resources\Payments\Schemas;

use App\Models\OrderPaymentPlan;
use App\Support\CrmAccess;
use App\Support\Filament\CrmUi;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->relationship('order', 'order_number')
                    ->live()
                    ->required(),
                Select::make('payment_plan_id')
                    ->options(fn (Get $get): array => OrderPaymentPlan::query()
                        ->where('tenant_id', CrmAccess::tenantId())
                        ->when($get('order_id'), fn (Builder $query, int|string $orderId): Builder => $query->where('order_id', $orderId))
                        ->orderBy('plan_date')
                        ->get()
                        ->mapWithKeys(fn (OrderPaymentPlan $plan): array => [
                            $plan->id => $plan->plan_date?->format('Y-m-d').' / ¥'.number_format((float) $plan->plan_amount, 2),
                        ])
                        ->all()),
                DateTimePicker::make('received_at'),
                TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(CrmUi::options('payment.status'))
                    ->required()
                    ->default('pending'),
                Select::make('payment_method')
                    ->options(CrmUi::options('payment.method')),
                TextInput::make('transaction_no'),
                TextInput::make('notes'),
            ]);
    }
}
