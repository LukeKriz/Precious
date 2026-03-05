<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MainDesignFooter extends Model
{
    use HasFactory;

    protected $table = 'main_design_footer';

    protected $fillable = [
        'footer_background',
        'footer_color',
        'footer_columns',
        'footer_content',
    ];

    protected $casts = [
        'footer_columns' => 'integer',
        'footer_content' => 'array', // ✅ JSON <-> array
    ];

    public function mainDesign()
    {
        // main_design.footer_id -> main_design_footer.id
        return $this->hasOne(MainDesign::class, 'footer_id');
    }
}
