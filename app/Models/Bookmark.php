<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bookmark extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        'user_id',
        'collection_id',
        'title',
        'url',
        'description',
        'favicon',
        'image_url',
        'is_favorite',
        'order',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    protected $casts = [
        'is_favorite' => 'boolean',
    ];
}
