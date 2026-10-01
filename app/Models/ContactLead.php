<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactLead extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED = 'closed';

    // Mirrors the DB column default so a freshly-created in-memory instance
    // (before it's ever saved/refreshed) already has a valid status instead
    // of null - Eloquent doesn't pull DB-level defaults back into the model.
    protected $attributes = [
        'status' => self::STATUS_NEW,
    ];

    protected $fillable = [
        'customer_id',
        'status',
        'name',
        'email',
        'phone',
        'company',
        'message',
        'source',
        'ip_address',
        'user_agent',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW => 'Novo',
            self::STATUS_IN_PROGRESS => 'Em Andamento',
            self::STATUS_CLOSED => 'Fechado',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? self::statuses()[self::STATUS_NEW];
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'badge-light-warning',
            self::STATUS_CLOSED => 'badge-light-success',
            default => 'badge-light-primary',
        };
    }
}
