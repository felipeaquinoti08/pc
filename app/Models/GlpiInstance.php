<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GlpiInstance extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    protected $fillable = [
        'name',
        'base_url',
        'oauth_client_id',
        'oauth_client_secret',
        'username',
        'password',
        'entities_id',
        'status',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'oauth_client_secret' => 'encrypted',
            'password' => 'encrypted',
            'entities_id' => 'integer',
            'status' => 'integer',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function statusLabel(): string
    {
        return $this->isActive() ? 'Ativa' : 'Inativa';
    }
}
