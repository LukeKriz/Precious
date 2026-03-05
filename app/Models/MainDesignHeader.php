<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MainDesignHeader extends Model
{
    protected $table = 'main_design_header';

    protected $fillable = [
        'main_design_id',
        'logo_url',
        'logo_width',
        'logo_height',
        'header_background',
        'submenu_background',
        'header_color',
        'header_color_hover',
        'submenu_color',
        'submenu_hover',
    ];

}
