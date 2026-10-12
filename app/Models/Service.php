<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_type',
        'status',
        'created_by_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function licenses()
    {
        return $this->belongsToMany(License::class);
    }

    public function billingPolicies()
    {
        return $this->morphMany(BillingPolicy::class, 'billable');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    /**
     * Combinações frequência+valor que este serviço oferece (ex: Mensal
     * R$30, Anual R$300), além do `price`/`billing_type` "padrão" acima -
     * usadas como atalho de preenchimento ao criar uma Política de
     * Faturamento pra este serviço, sem precisar duplicar o cadastro do
     * serviço por frequência.
     */
    public function pricingPlans()
    {
        return $this->hasMany(ServicePricingPlan::class);
    }

    /**
     * Identificador fixo e único deste serviço, usado por integrações
     * externas (ex: plugins GLPI) pra provar qual produto específico estão
     * validando - ver LicenseValidationController. Só consultado quando o
     * formulário deixa o slug em branco; editar o nome depois nunca mexe no
     * slug, pra não quebrar quem já tem esse valor hardcoded no código do
     * plugin.
     */
    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'servico';
        $slug = $base;
        $i = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }

        return $slug;
    }
}
