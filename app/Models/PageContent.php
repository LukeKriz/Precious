<?php
// app/Models/PageContent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
  protected $fillable = [
    'page_id', 'subpage_id', 'schema_version', 'content'
  ];

  protected $casts = [
    'content' => 'array',
  ];
}
