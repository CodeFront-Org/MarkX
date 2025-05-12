<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyFile extends Model
{    protected $fillable = [
        'original_name',
        'file_name',
        'file_type',
        'category',
        'description',
        'path',
        'user_id'
    ];

    const CATEGORIES = [
        'kra_pin' => 'KRA PIN',
        'county_license' => 'County License',
        'award_letter' => 'Award Letter',
        'pre_qualification' => 'Pre-qualification',
        'other' => 'Other'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
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
