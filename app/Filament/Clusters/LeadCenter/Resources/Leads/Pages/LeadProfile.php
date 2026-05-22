<?php

namespace App\Filament\Clusters\LeadCenter\Resources\Leads\Pages;

use App\Filament\Clusters\LeadCenter\Resources\Leads\LeadResource;
use App\Models\Customer;
use App\Models\Lead;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class LeadProfile extends Page
{
    use InteractsWithRecord;

    protected static string $resource = LeadResource::class;

    protected string $view = 'filament.clusters.lead-center.resources.leads.pages.lead-profile';

    protected static ?string $title = '线索详情';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    protected function getViewData(): array
    {
        /** @var Lead $lead */
        $lead = $this->getRecord();

        $lead->load([
            'owner',
            'convertedCustomer',
            'convertedBy',
            'activities.owner',
            'tasks.assignee',
            'attachments',
        ]);

        return [
            'lead' => $lead,
            'metrics' => [
                ['label' => '评分', 'value' => $lead->score],
                ['label' => '活动', 'value' => $lead->activities->count()],
                ['label' => '任务', 'value' => $lead->tasks->count()],
                ['label' => '附件', 'value' => $lead->attachments->count()],
            ],
            'duplicates' => $this->duplicates($lead),
            'timeline' => $lead->activities
                ->sortByDesc('occurred_at')
                ->take(12)
                ->values(),
        ];
    }

    private function duplicates(Lead $lead): array
    {
        $tenantId = $lead->tenant_id;

        $leadMatches = Lead::query()
            ->where('tenant_id', $tenantId)
            ->whereKeyNot($lead->getKey())
            ->where(function (Builder $query) use ($lead): void {
                $query
                    ->when($lead->phone, fn (Builder $query) => $query->orWhere('phone', $lead->phone))
                    ->when($lead->email, fn (Builder $query) => $query->orWhere('email', $lead->email))
                    ->when($lead->company_name, fn (Builder $query) => $query->orWhere('company_name', $lead->company_name));
            })
            ->limit(5)
            ->get(['id', 'company_name', 'contact_name', 'phone', 'email'])
            ->map(fn (Lead $match): array => [
                'type' => '线索',
                'name' => $match->company_name ?: $match->contact_name ?: '#'.$match->id,
                'contact' => $match->phone ?: $match->email ?: '-',
            ])
            ->all();

        $customerMatches = Customer::query()
            ->where('tenant_id', $tenantId)
            ->where(function (Builder $query) use ($lead): void {
                $query
                    ->when($lead->phone, fn (Builder $query) => $query->orWhere('phone', $lead->phone))
                    ->when($lead->email, fn (Builder $query) => $query->orWhere('email', $lead->email))
                    ->when($lead->company_name, fn (Builder $query) => $query->orWhere('name', $lead->company_name));
            })
            ->limit(5)
            ->get(['id', 'name', 'phone', 'email'])
            ->map(fn (Customer $match): array => [
                'type' => '客户',
                'name' => $match->name,
                'contact' => $match->phone ?: $match->email ?: '-',
            ])
            ->all();

        return [...$leadMatches, ...$customerMatches];
    }
}
