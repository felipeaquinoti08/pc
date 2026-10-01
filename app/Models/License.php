<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class License extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_id',
        'service_id',
        'campaign_id',
        'license_key',
        'quantity',
        'starts_at',
        'expires_at',
        'status',
        'suspended_invoice_id',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'expires_at' => 'date',
            'quantity' => 'integer',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function activations()
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public function activeActivations()
    {
        return $this->activations()->where('status', LicenseActivation::STATUS_ACTIVE);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function billingPolicies()
    {
        return $this->morphMany(BillingPolicy::class, 'billable');
    }

    public function suspendedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'suspended_invoice_id');
    }

    /**
     * Whether the license itself (independent of the activation quantity
     * limit) is currently usable: marked active and not past expiration.
     */
    public function isUsable(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        return $this->expires_at === null || $this->expires_at->isFuture() || $this->expires_at->isToday();
    }

    /**
     * A short, human-typeable key. Format is not meaningful, just unique
     * and hard to guess/enumerate.
     */
    public static function generateKey(): string
    {
        do {
            $key = strtoupper(
                substr(bin2hex(random_bytes(3)), 0, 5) . '-' .
                substr(bin2hex(random_bytes(3)), 0, 5) . '-' .
                substr(bin2hex(random_bytes(3)), 0, 5)
            );
        } while (self::where('license_key', $key)->exists());

        return $key;
    }
}
