<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'url', 'sellable', 'active'];

    

    public function subpages()
    {
        return $this->hasMany(Subpage::class, 'page_id');
    }
}
