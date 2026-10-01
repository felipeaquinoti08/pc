<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 1;
    public const STATUS_CLOSED = 0;

    // WhatsApp used as a fallback when a campaign doesn't set its own -
    // see App\Http\Controllers\CampaignController.
    public const DEFAULT_WHATSAPP = '5561993049590';

    protected $fillable = [
        'name',
        'slug',
        'service_id',
        'description',
        'trial_days',
        'quantity',
        'repository_url',
        'instructions',
        'whatsapp_number',
        'status',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'trial_days' => 'integer',
            'quantity' => 'integer',
            'status' => 'integer',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function isAcceptingSignups(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function whatsappNumber(): string
    {
        return $this->whatsapp_number ?: self::DEFAULT_WHATSAPP;
    }

    public function whatsappLink(): string
    {
        return 'https://wa.me/' . preg_replace('/\D/', '', $this->whatsappNumber());
    }

    /**
     * A URL-safe slug, unique among non-deleted campaigns. Only consulted
     * when the admin form leaves the slug blank - editing an existing
     * campaign's name never touches its slug, since that would break the
     * public link people already have.
     */
    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'campanha';
        $slug = $base;
        $i = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
