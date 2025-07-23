<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageDesign extends Model
{
    use HasFactory;

    protected $fillable = ['layout_id','page_id', 'header_height', 'banner_height', 'footer_height','logo_position', 'menu_position'];
}
