<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillingPolicy extends Model
{
    use HasFactory, SoftDeletes;

    public const FREQUENCY_ONCE = 'once';
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_SEMIANNUAL = 'semiannual';
    public const FREQUENCY_ANNUAL = 'annual';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'customer_id',
        'billable_type',
        'billable_id',
        'description',
        'frequency',
        'amount',
        'due_day',
        'starts_at',
        'status',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_day' => 'integer',
            'starts_at' => 'date',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function billable()
    {
        return $this->morphTo();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public static function frequencies(): array
    {
        return [
            self::FREQUENCY_ONCE => 'Única',
            self::FREQUENCY_MONTHLY => 'Mensal',
            self::FREQUENCY_SEMIANNUAL => 'Semestral',
            self::FREQUENCY_ANNUAL => 'Anual',
        ];
    }

    public function frequencyLabel(): string
    {
        return self::frequencies()[$this->frequency] ?? $this->frequency;
    }

    public function label(): string
    {
        if ($this->billable instanceof License) {
            return 'Licença — ' . ($this->billable->service?->name ?? $this->billable->license_key);
        }

        if ($this->billable instanceof Service) {
            return $this->billable->name;
        }

        if ($this->billable instanceof Proposal) {
            return 'Proposta — ' . $this->billable->title;
        }

        return $this->description ?: 'Cobrança avulsa';
    }

    /**
     * Every (month, year) that should already have an invoice by today,
     * oldest first. `starts_at` is the contract's start date, not a billing
     * date - the first charge is always the cycle AFTER starts_at (sign a
     * monthly contract in August, the first invoice is September's; a
     * semiannual one, the first invoice is 6 months out), never a
     * pro-rated slice of the signing month. Walking every cycle since
     * starts_at (not just "is today's month due") is what lets this double
     * as a reconciliation check: a policy that sat untouched for 3 months
     * (job outage, was created backdated, etc.) reports all 3 missing
     * periods instead of just the current one.
     */
    public function dueDates(Carbon $today): \Illuminate\Support\Collection
    {
        $intervalMonths = $this->intervalMonths();

        if ($intervalMonths === null) {
            return collect();
        }

        $cursor = $this->starts_at->copy()->startOfMonth()->addMonthsNoOverflow($intervalMonths);
        $currentMonth = $today->copy()->startOfMonth();

        $dates = collect();

        while ($cursor->lte($currentMonth)) {
            $dates->push($cursor->copy());
            $cursor->addMonthsNoOverflow($intervalMonths);
        }

        return $dates;
    }

    /**
     * The due date for a given cycle: `due_day` of that cycle's month,
     * clamped to the last real day of the month (e.g. due_day 31 in
     * February -> the 28th).
     */
    public function dueDateForPeriod(Carbon $periodStart): Carbon
    {
        return $periodStart->copy()->startOfMonth()->addDays(min($this->due_day, $periodStart->daysInMonth) - 1);
    }

    private function intervalMonths(): ?int
    {
        return match ($this->frequency) {
            self::FREQUENCY_MONTHLY => 1,
            self::FREQUENCY_SEMIANNUAL => 6,
            self::FREQUENCY_ANNUAL => 12,
            default => null, // `once` isn't cycle-based - see InvoiceGenerator::reconcile()
        };
    }
}
