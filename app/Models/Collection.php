<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Collection extends Model
{
    protected $fillable = ['user_id', 'name', 'slug', 'icon', 'color', 'order', 'sort_by', 'background_image', 'background_opacity'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collection) {
            if (empty($collection->slug)) {
                $collection->slug = static::generateUniqueSlug($collection->name, $collection->user_id);
            }
        });

        static::updating(function ($collection) {
            if ($collection->isDirty('name')) {
                $collection->slug = static::generateUniqueSlug($collection->name, $collection->user_id, $collection->id);
            }
        });
    }

    protected static function generateUniqueSlug($name, $userId, $ignoreId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('user_id', $userId)
            ->where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }
}
