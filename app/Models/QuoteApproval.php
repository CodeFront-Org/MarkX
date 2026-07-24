<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteApproval extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quote_id',
        'user_id',
        'action',
        'is_override',
        'comment',
    ];

    protected $casts = [
        'is_override' => 'boolean',
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('action', 'approved');
    }
}
