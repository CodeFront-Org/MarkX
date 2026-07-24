<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalChainStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    /**
     * The approver (an rfq_approver user) assigned to this step.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The global approver chain, ordered by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }
}
