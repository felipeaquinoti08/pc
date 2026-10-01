<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'document_type',
        'document_number',
        'email',
        'phone',
        'address',
        'number',
        'no_number',
        'complement',
        'neighborhood',
        'city',
        'nfse_municipio_codigo',
        'state',
        'postal_code',
        'country_id',
        'status',
        'notes',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'no_number' => 'boolean',
        ];
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function contactLeads()
    {
        return $this->hasMany(ContactLead::class);
    }

    public function billingPolicies()
    {
        return $this->hasMany(BillingPolicy::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
