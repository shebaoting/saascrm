<?php

namespace App\Services\Crm;

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Pipeline;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    public function convert(Lead $lead, bool $createOpportunity = false, ?string $opportunityName = null): Customer
    {
        return DB::transaction(function () use ($lead, $createOpportunity, $opportunityName): Customer {
            app()->instance('crm.skip_duplicate_detection', true);

            try {
                $customer = Customer::create([
                    'tenant_id' => $lead->tenant_id,
                    'name' => $lead->company_name ?: $lead->contact_name ?: '未命名客户',
                    'short_name' => $lead->company_name,
                    'customer_type' => $lead->company_name ? 'company' : 'person',
                    'lifecycle_stage' => 'new',
                    'owner_user_id' => $lead->owner_user_id ?: Auth::id(),
                    'source' => $lead->source,
                    'tags' => $lead->tags,
                    'country_code' => $lead->country_code,
                    'area_id' => $lead->area_id,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'last_activity_at' => $lead->last_activity_at,
                    'next_activity_at' => $lead->next_activity_at,
                    'custom_fields' => $lead->custom_fields,
                ]);
            } finally {
                app()->forgetInstance('crm.skip_duplicate_detection');
            }

            if ($lead->contact_name || $lead->phone || $lead->email) {
                Contact::create([
                    'tenant_id' => $lead->tenant_id,
                    'customer_id' => $customer->id,
                    'name' => $lead->contact_name ?: $customer->name,
                    'phone' => $lead->phone,
                    'email' => $lead->email,
                    'wechat_id' => $lead->wechat_id,
                    'is_primary' => true,
                ]);
            }

            if ($createOpportunity) {
                $pipeline = Pipeline::query()
                    ->where('tenant_id', $lead->tenant_id)
                    ->where('is_active', true)
                    ->orderByDesc('is_default')
                    ->first();

                $stage = $pipeline?->stages()->where('is_active', true)->first();

                if ($pipeline && $stage) {
                    Opportunity::create([
                        'tenant_id' => $lead->tenant_id,
                        'customer_id' => $customer->id,
                        'pipeline_id' => $pipeline->id,
                        'pipeline_stage_id' => $stage->id,
                        'name' => $opportunityName ?: $customer->name.' 商机',
                        'amount' => 0,
                        'probability' => $stage->probability,
                        'forecast_category' => 'pipeline',
                        'responsible_user_id' => $lead->owner_user_id ?: Auth::id(),
                    ]);
                }
            }

            $previousStatus = $lead->status;

            $lead->forceFill([
                'status' => 'converted',
                'qualification_status' => 'qualified',
                'converted_customer_id' => $customer->id,
                'converted_at' => now(),
                'converted_by' => Auth::id(),
            ])->save();

            app(ActivityService::class)->recordSystemEvent(
                $lead->tenant_id,
                '线索转客户：'.$customer->name,
                null,
                [
                    'lead' => $lead,
                    'customer' => $customer,
                    'owner_user_id' => $customer->owner_user_id,
                ],
            );

            app(AuditLogService::class)->record('lead_converted', $lead, [
                'status' => $previousStatus,
            ], [
                'status' => 'converted',
                'customer_id' => $customer->id,
            ]);

            return $customer;
        });
    }
}
