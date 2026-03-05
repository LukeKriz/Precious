<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'base_url',
        'default_title',
        'default_description',
        'og_title',
        'og_description',
        'og_image',
        'favicon_ico',
        'favicon_apple',
        'favicon_pwa',
        'site_noindex',
        'ga4_id',
        'gtm_id',
        'meta_head_custom',
    ];

    protected $casts = [
        'site_noindex' => 'boolean',
    ];
}
