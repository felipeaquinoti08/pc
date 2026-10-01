<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_type',
        'status',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function billingPolicies()
    {
        return $this->morphMany(BillingPolicy::class, 'billable');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }
}
