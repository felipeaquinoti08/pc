<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per external integration this app depends on - refreshed
 * periodically by App\Services\SystemHealthChecker (see the
 * `system:check-health` scheduled command) and read by the dashboard's
 * "Status do Sistema" card. Never written from a web request, so the
 * dashboard itself stays fast regardless of how slow a government API is.
 */
class ServiceStatus extends Model
{
    public const STATUS_OK = 'ok';
    public const STATUS_DOWN = 'down';
    public const STATUS_UNCONFIGURED = 'unconfigured';

    protected $fillable = [
        'key',
        'label',
        'status',
        'message',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function badgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_OK => 'badge-light-success',
            self::STATUS_DOWN => 'badge-light-danger',
            default => 'badge-light-secondary',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OK => 'Operacional',
            self::STATUS_DOWN => 'Indisponível',
            default => 'Não configurado',
        };
    }

    /**
     * Icon id in public/assets/svg/icon-sprite.svg - purely cosmetic, for
     * the dashboard card.
     */
    public function icon(): string
    {
        return match ($this->key) {
            'nfse_sefin' => 'stroke-note',
            'nfse_adn' => 'stroke-file',
            'mercadopago' => 'stroke-price',
            'mail' => 'stroke-email',
            'storage' => 'stroke-folder',
            'sso' => 'stroke-authenticate',
            default => 'stroke-activity',
        };
    }
}
