<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBookmark extends Model
{
    protected $fillable = ['title', 'url', 'description', 'favicon', 'image_url'];
}
