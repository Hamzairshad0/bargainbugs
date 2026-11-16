<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'is_visible' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $c) {
            if (! $c->slug) {
                $base = Str::slug($c->name) ?: 'category';
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $c->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $c->slug = $slug;
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
