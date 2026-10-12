<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServicePricingPlan extends Model
{
    protected $fillable = ['service_id', 'frequency', 'price'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
