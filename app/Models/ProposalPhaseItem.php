<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalPhaseItem extends Model
{
    protected $fillable = [
        'proposal_phase_id',
        'description',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function phase()
    {
        return $this->belongsTo(ProposalPhase::class, 'proposal_phase_id');
    }
}
