<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LicenseActivation extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'license_id',
        'service_id',
        'instance_identifier',
        'instance_url',
        'ip_address',
        'status',
        'activated_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
