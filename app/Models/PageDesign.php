<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageDesign extends Model
{
    use HasFactory;

    protected $fillable = ['layout_id', 'page_id', 'subpage_id', 'header_height', 'banner_height', 'banner_id', 'category_weight', 'category_height', 'footer_height', 'logo_position', 'menu_position'];

    public function banners()
    {
        return $this->hasMany(PageDesignBanner::class)->orderBy('sort_order');
    }


    public function banner()
    {
        return $this->belongsTo(PageDesignBanner::class, 'banner_id');
    }
}
