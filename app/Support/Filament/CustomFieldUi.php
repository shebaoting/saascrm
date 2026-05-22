<?php

namespace App\Support\Filament;

use App\Models\Contact;
use App\Models\CustomField;
use App\Models\CustomFieldLayout;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;
use App\Support\CrmAccess;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomFieldUi
{
    public static function modelType(string $modelClass): string
    {
        return match ($modelClass) {
            Lead::class => 'lead',
            Customer::class => 'customer',
            Contact::class => 'contact',
            Opportunity::class => 'opportunity',
            Quote::class => 'quote',
            Order::class => 'order',
            default => Str::of(class_basename($modelClass))->snake()->toString(),
        };
    }

    /**
     * @return Collection<int, CustomField>
     */
    public static function fields(string $modelType): Collection
    {
        $tenantId = CrmAccess::tenantId();

        if (! $tenantId) {
            return collect();
        }

        return CustomField::query()
            ->where('tenant_id', $tenantId)
            ->where('model_type', $modelType)
            ->where('is_visible', true)
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (CustomField $field): bool => self::fieldIsVisible($modelType, self::customFieldKey($field)));
    }

    public static function customFieldKey(CustomField $field): string
    {
        return 'custom_fields.'.$field->identifier;
    }

    public static function applyLayout(Component $component, string $modelType, string $fieldKey): Component
    {
        return $component
            ->hidden(fn (): bool => ! self::fieldIsVisible($modelType, $fieldKey))
            ->disabled(fn (): bool => self::fieldIsReadOnly($modelType, $fieldKey));
    }

    public static function formSections(string $modelType): array
    {
        $fields = self::fields($modelType);

        if ($fields->isEmpty()) {
            return [];
        }

        return $fields
            ->groupBy(fn (CustomField $field): string => $field->group_name ?: '扩展字段')
            ->map(function (Collection $group, string $groupName): Section {
                $hasRequiredFields = $group->contains(fn (CustomField $field): bool => (bool) $field->is_required);

                return Section::make($groupName)
                    ->schema($group->map(fn (CustomField $field): Component => self::formComponent($field))->all())
                    ->columns(['md' => 2])
                    ->compact()
                    ->collapsible()
                    ->collapsed(! $hasRequiredFields)
                    ->persistCollapsed(! $hasRequiredFields)
                    ->columnSpanFull();
            })
            ->values()
            ->all();
    }

    public static function infolistSections(string $modelType): array
    {
        $fields = self::fields($modelType);

        if ($fields->isEmpty()) {
            return [];
        }

        return $fields
            ->groupBy(fn (CustomField $field): string => $field->group_name ?: '扩展字段')
            ->map(fn (Collection $group, string $groupName): Section => Section::make($groupName)
                ->schema($group->map(fn (CustomField $field): TextEntry => self::infolistEntry($field))->all())
                ->columns(2)
                ->columnSpanFull())
            ->values()
            ->all();
    }

    public static function formComponent(CustomField $field): Component
    {
        $name = self::customFieldKey($field);

        $component = match ($field->type) {
            'textarea' => Textarea::make($name)->rows(3)->columnSpanFull(),
            'number' => TextInput::make($name)->numeric(),
            'date' => DatePicker::make($name),
            'datetime' => DateTimePicker::make($name),
            'select' => Select::make($name)->options(self::options($field)),
            'multi_select' => CheckboxList::make($name)->options(self::options($field))->columns(2)->columnSpanFull(),
            'boolean' => Toggle::make($name),
            default => TextInput::make($name),
        };

        return self::applyLayout(
            $component
                ->label($field->name)
                ->required((bool) $field->is_required)
                ->dehydratedWhenHidden(false),
            $field->model_type,
            $name,
        );
    }

    public static function infolistEntry(CustomField $field): TextEntry
    {
        return TextEntry::make(self::customFieldKey($field))
            ->label($field->name)
            ->formatStateUsing(fn (mixed $state): ?string => self::displayValue($field, $state))
            ->placeholder('-');
    }

    public static function tableColumns(string $modelType): array
    {
        $configuredColumns = self::listColumns($modelType);

        return self::fields($modelType)
            ->filter(fn (CustomField $field): bool => $field->is_list_visible || in_array(self::customFieldKey($field), $configuredColumns, true))
            ->map(function (CustomField $field) use ($configuredColumns): TextColumn {
                $fieldKey = self::customFieldKey($field);
                $isHiddenByDefault = $configuredColumns !== []
                    ? ! in_array($fieldKey, $configuredColumns, true)
                    : ! $field->is_list_visible;

                return TextColumn::make('custom_field_'.$field->identifier)
                    ->label($field->name)
                    ->getStateUsing(fn (Model $record): ?string => self::displayValue($field, data_get($record->custom_fields, $field->identifier)))
                    ->searchable(query: fn (Builder $query, string $search): Builder => self::whereJsonValue($query, $field->identifier, $search, exact: false))
                    ->toggleable(isToggledHiddenByDefault: $isHiddenByDefault);
            })
            ->values()
            ->all();
    }

    public static function tableFilters(string $modelType): array
    {
        return self::fields($modelType)
            ->filter(fn (CustomField $field): bool => (bool) $field->is_filterable)
            ->map(fn (CustomField $field) => self::tableFilter($field))
            ->values()
            ->all();
    }

    public static function tableFilter(CustomField $field): Filter|SelectFilter
    {
        $name = 'custom_field_'.$field->identifier;

        if (in_array($field->type, ['select', 'boolean'], true)) {
            $options = $field->type === 'boolean'
                ? ['1' => '是', '0' => '否']
                : self::options($field);

            return SelectFilter::make($name)
                ->label($field->name)
                ->options($options)
                ->query(fn (Builder $query, array $data): Builder => self::whereJsonValue($query, $field->identifier, $data['value'] ?? null, exact: true));
        }

        return Filter::make($name)
            ->label($field->name)
            ->schema([
                TextInput::make('value')
                    ->label($field->name),
            ])
            ->query(fn (Builder $query, array $data): Builder => self::whereJsonValue($query, $field->identifier, $data['value'] ?? null, exact: false));
    }

    public static function requiredFieldOptions(string $modelType): array
    {
        $standard = match ($modelType) {
            'opportunity' => [
                'customer_id' => '客户',
                'contact_id' => '联系人',
                'amount' => '金额',
                'expected_close_date' => '预计成交日期',
                'responsible_user_id' => '负责人',
                'notes' => '备注',
            ],
            'quote' => [
                'customer_id' => '客户',
                'contact_id' => '联系人',
                'total_amount' => '总金额',
                'profit_margin' => '毛利率',
                'valid_until' => '有效期至',
            ],
            'order' => [
                'customer_id' => '客户',
                'contact_id' => '联系人',
                'total_amount' => '总金额',
                'payment_status' => '收款状态',
            ],
            default => [],
        };

        $custom = self::fields($modelType)
            ->mapWithKeys(fn (CustomField $field): array => [self::customFieldKey($field) => $field->name])
            ->all();

        return $standard + $custom;
    }

    public static function fieldLabel(string $modelType, string $fieldKey): string
    {
        $normalized = self::normalizeFieldKey($fieldKey);

        if (str_starts_with($normalized, 'custom_fields.')) {
            $identifier = Str::after($normalized, 'custom_fields.');

            $field = self::fields($modelType)->firstWhere('identifier', $identifier);

            return $field?->name ?: $identifier;
        }

        return self::requiredFieldOptions($modelType)[$normalized] ?? CrmUi::label($normalized);
    }

    public static function valueIsBlank(mixed $value): bool
    {
        if (is_array($value)) {
            return $value === [];
        }

        return blank($value);
    }

    public static function fieldValue(Model $record, string $fieldKey): mixed
    {
        return data_get($record, self::normalizeFieldKey($fieldKey));
    }

    public static function normalizeFieldKey(string $fieldKey): string
    {
        if (str_starts_with($fieldKey, 'custom_fields.') || str_contains($fieldKey, '.')) {
            return $fieldKey;
        }

        return $fieldKey;
    }

    public static function fieldIsVisible(string $modelType, string $fieldKey): bool
    {
        $hidden = Arr::wrap(data_get(self::layout($modelType), 'hidden_fields', []));

        return ! in_array(self::normalizeFieldKey($fieldKey), $hidden, true);
    }

    public static function fieldIsReadOnly(string $modelType, string $fieldKey): bool
    {
        $readonly = Arr::wrap(data_get(self::layout($modelType), 'readonly_fields', []));

        return in_array(self::normalizeFieldKey($fieldKey), $readonly, true);
    }

    public static function listColumns(string $modelType): array
    {
        return array_values(array_filter(Arr::wrap(data_get(self::layout($modelType), 'list_columns', []))));
    }

    public static function detailFields(string $modelType): array
    {
        return array_values(array_filter(Arr::wrap(data_get(self::layout($modelType), 'detail_fields', []))));
    }

    public static function layout(string $modelType): array
    {
        $tenantId = CrmAccess::tenantId();

        if (! $tenantId) {
            return [];
        }

        $roleIds = self::currentRoleIds();

        $layouts = CustomFieldLayout::query()
            ->where('tenant_id', $tenantId)
            ->where('model_type', $modelType)
            ->where(function (Builder $query) use ($roleIds): void {
                $query->whereNull('role_id');

                if ($roleIds !== []) {
                    $query->orWhereIn('role_id', $roleIds);
                }
            })
            ->orderByRaw('case when role_id is null then 0 else 1 end')
            ->get();

        return $layouts->reduce(function (array $carry, CustomFieldLayout $layout): array {
            $value = is_string($layout->layout)
                ? json_decode($layout->layout, true)
                : $layout->layout;

            return array_replace_recursive($carry, is_array($value) ? $value : []);
        }, []);
    }

    public static function options(CustomField $field): array
    {
        $data = $field->data ?: [];
        $options = $data['options'] ?? $data;

        if (is_string($options)) {
            return collect(preg_split('/\r\n|\r|\n/', $options) ?: [])
                ->filter(fn (string $line): bool => filled(trim($line)))
                ->mapWithKeys(function (string $line): array {
                    [$value, $label] = array_pad(preg_split('/\s*[=:]\s*/', trim($line), 2) ?: [], 2, null);
                    $label ??= $value;

                    return [$value => $label];
                })
                ->all();
        }

        if (! is_array($options)) {
            return [];
        }

        if (array_is_list($options)) {
            return collect($options)
                ->mapWithKeys(function (mixed $option): array {
                    if (is_array($option)) {
                        $value = $option['value'] ?? $option['key'] ?? $option['label'] ?? null;
                        $label = $option['label'] ?? $option['name'] ?? $value;

                        return filled($value) ? [$value => $label] : [];
                    }

                    return [(string) $option => (string) $option];
                })
                ->all();
        }

        return collect($options)
            ->reject(fn (mixed $value, string $key): bool => in_array($key, ['placeholder', 'help', 'default'], true))
            ->mapWithKeys(fn (mixed $label, string $value): array => [$value => is_scalar($label) ? (string) $label : $value])
            ->all();
    }

    public static function displayValue(CustomField $field, mixed $value): ?string
    {
        if (self::valueIsBlank($value)) {
            return null;
        }

        if ($field->type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '是' : '否';
        }

        if (in_array($field->type, ['select', 'multi_select'], true)) {
            $options = self::options($field);

            return collect(Arr::wrap($value))
                ->map(fn (mixed $item): string => $options[$item] ?? (string) $item)
                ->join('、');
        }

        if (is_array($value)) {
            return collect($value)->join('、');
        }

        return (string) $value;
    }

    public static function whereJsonValue(Builder $query, string $identifier, mixed $value, bool $exact): Builder
    {
        if (blank($value) && $value !== '0' && $value !== 0) {
            return $query;
        }

        $driver = $query->getConnection()->getDriverName();
        $qualified = $query->getModel()->qualifyColumn('custom_fields');
        $path = self::jsonPath($identifier);

        if ($driver === 'sqlite') {
            $operator = $exact ? '=' : 'like';
            $needle = $exact ? $value : '%'.$value.'%';

            return $query->whereRaw("json_extract({$qualified}, ?) {$operator} ?", [$path, $needle]);
        }

        if ($driver === 'pgsql') {
            $operator = $exact ? '=' : 'ilike';
            $needle = $exact ? $value : '%'.$value.'%';

            return $query->whereRaw("({$qualified}->>?) {$operator} ?", [$identifier, $needle]);
        }

        $column = 'custom_fields->'.$identifier;

        return $exact
            ? $query->where($column, $value)
            : $query->where($column, 'like', '%'.$value.'%');
    }

    private static function jsonPath(string $identifier): string
    {
        return '$."'.str_replace('"', '\"', $identifier).'"';
    }

    private static function currentRoleIds(): array
    {
        $user = Auth::user();
        $tenantId = CrmAccess::tenantId();

        if (! $user instanceof User || ! $tenantId) {
            return [];
        }

        return $user->roles()
            ->wherePivot('tenant_id', $tenantId)
            ->pluck('roles.id')
            ->all();
    }
}
