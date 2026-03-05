<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageDesignBanner extends Model
{
    use HasFactory;

    protected $table = 'page_design_banners'; // pokud máš jiný název, uprav

    protected $fillable = [
        'page_design_id',

        // hlavní banner
        'banner_url',
        'banner_type',
        'banner_count',
        'sort_order',

        // ===== BUILDER (pozadí + overlay) =====
        // bg_type: 'image' | 'color'
        'bg_type',
        // bg_color: hex (#rrggbb) nebo null
        'bg_color',
        // overlay_color: hex nebo null
        'overlay_color',
        // overlay_opacity: 0-100
        'overlay_opacity',

        // miniatura
        'thumbnail_url',
        // velikost miniatury v px
        'thumbnail_size',

        // texty banneru
        'heading_h1',
        'heading_h2',

        // ===== TEXT STYLE =====
        // barvy: hex nebo null
        'h1_color',
        'h2_color',
        // velikosti v px
        'h1_size',
        'h2_size',

        // tlačítko
        'button_enabled',
        'button_text',
        'button_url',
    ];

    protected $casts = [
        'button_enabled'   => 'boolean',
        'banner_count'     => 'integer',
        'sort_order'       => 'integer',
        'overlay_opacity'  => 'integer',
        'thumbnail_size'   => 'integer',
        'h1_size'          => 'integer',
        'h2_size'          => 'integer',
    ];

    public function pageDesign()
    {
        return $this->belongsTo(PageDesign::class);
    }
}
