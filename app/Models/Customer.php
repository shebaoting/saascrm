<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends TenantModel
{
    use SoftDeletes;

    protected $casts = [
        'tags' => 'array',
        'custom_fields' => 'array',
        'pool_entered_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'next_activity_at' => 'datetime',
        'first_order_at' => 'datetime',
        'last_order_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_users')
            ->withPivot(['tenant_id', 'role', 'assigned_by', 'assigned_at']);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'model');
    }

    public function poolHistories(): MorphMany
    {
        return $this->morphMany(CustomerPoolHistory::class, 'target');
    }

    public function transferHistories(): MorphMany
    {
        return $this->morphMany(CustomerTransferHistory::class, 'target');
    }

    public function fieldHistories(): MorphMany
    {
        return $this->morphMany(FieldHistory::class, 'model');
    }
}
