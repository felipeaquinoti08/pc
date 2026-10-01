<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalScheduleStep extends Model
{
    protected $fillable = [
        'proposal_id',
        'duration_label',
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
