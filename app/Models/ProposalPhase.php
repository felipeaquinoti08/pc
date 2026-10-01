<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalPhase extends Model
{
    protected $fillable = [
        'proposal_id',
        'title',
        'subtitle',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }

    public function items()
    {
        return $this->hasMany(ProposalPhaseItem::class)->orderBy('order');
    }
}
