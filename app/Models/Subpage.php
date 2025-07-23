<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subpage extends Model
{
    use HasFactory;

    protected $fillable = ['page_id', 'title', 'url', 'sellable','active'];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
