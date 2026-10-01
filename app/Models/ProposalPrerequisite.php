<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalPrerequisite extends Model
{
    protected $fillable = [
        'proposal_id',
        'description',
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
}
