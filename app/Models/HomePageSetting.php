<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomePageSetting extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'instructions',
        'button_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getActiveSetting()
    {
        return static::where('is_active', true)->first() ?? static::first();
    }
}
