<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public const NFSE_NOT_ISSUED = 'not_issued';
    public const NFSE_ISSUED = 'issued';
    public const NFSE_ERROR = 'error';
    public const NFSE_CANCELLED = 'cancelled';

    protected $fillable = [
        'license_id',
        'customer_id',
        'billing_policy_id',
        'reference_month',
        'reference_year',
        'amount',
        'due_date',
        'grace_period_ends_at',
        'status',
        'paid_at',
        'mp_preference_id',
        'mp_payment_id',
        'mp_payment_link',
        'is_test_payment',
        'notes',
        'created_by_id',
        'nfse_status',
        'nfse_chave_acesso',
        'nfse_numero',
        'nfse_xml_path',
        'nfse_pdf_path',
        'nfse_error_message',
        'nfse_issued_at',
    ];

    protected function casts(): array
    {
        return [
            'reference_month' => 'integer',
            'reference_year' => 'integer',
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'grace_period_ends_at' => 'date',
            'paid_at' => 'datetime',
            'is_test_payment' => 'boolean',
            'nfse_issued_at' => 'datetime',
        ];
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The service period this invoice actually covers - one month before
     * its own competência, since billing runs a cycle in advance (see
     * InvoiceGenerator::serviceDescription(), which computes the same
     * thing inline at creation time for the {{competencia}} placeholder).
     */
    public function servicePeriod(): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::createFromDate($this->reference_year, $this->reference_month, 1)->subMonthNoOverflow();
    }

    public function billingPolicy()
    {
        return $this->belongsTo(BillingPolicy::class);
    }

    /**
     * Marks the invoice paid and, if it was the specific invoice that got
     * this license suspended for non-payment (License::suspended_invoice_id),
     * reactivates the license. A license an admin suspended manually for an
     * unrelated reason is never touched here, since suspended_invoice_id
     * would be null (or point at a different invoice) in that case.
     */
    public function markAsPaid(?string $mpPaymentId = null, ?bool $isTestPayment = null): void
    {
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();

        if ($mpPaymentId) {
            $this->mp_payment_id = $mpPaymentId;
        }

        if ($isTestPayment !== null) {
            $this->is_test_payment = $isTestPayment;
        }

        $this->save();

        $license = $this->license;

        if ($license && $license->suspended_invoice_id === $this->id) {
            $license->status = License::STATUS_ACTIVE;
            $license->suspended_invoice_id = null;
            $license->save();
        }
    }
}
