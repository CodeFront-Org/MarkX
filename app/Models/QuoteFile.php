<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuoteFile extends Model
{
    protected $fillable = [
        'quote_id',
        'original_name',
        'file_name',
        'file_type',
        'path',
        'description'
    ];

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Get the route key name for Laravel.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'file_name';
    }
}
