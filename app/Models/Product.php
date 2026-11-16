<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_hidden' => 'boolean',
            'lock_content' => 'boolean',
            'listed_at' => 'datetime',
            'ends_at' => 'datetime',
            'ebay_synced_at' => 'datetime',
            'ebay_seen_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $p) {
            if (! $p->slug) {
                $p->slug = static::uniqueSlug($p->title, $p->id);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit($title, 80, '')) ?: 'item';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Products a visitor may see on the storefront. */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_hidden', false)->where('status', 'active');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageAttribute(): string
    {
        return $this->images[0] ?? asset('images/placeholder.svg');
    }

    public function getGalleryAttribute(): array
    {
        return $this->images ?: [asset('images/placeholder.svg')];
    }

    public function getOnSaleAttribute(): bool
    {
        return $this->original_price && (float) $this->original_price > (float) $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        return $this->on_sale ? (int) round(100 - ((float) $this->price / (float) $this->original_price * 100)) : null;
    }

    public function getIsNewAttribute(): bool
    {
        return $this->listed_at && $this->listed_at->gt(now()->subDays(7));
    }

    public function getBuyUrlAttribute(): ?string
    {
        return $this->ebay_url ?: ($this->ebay_item_id ? 'https://www.ebay.co.uk/itm/'.$this->ebay_item_id : null);
    }

    public function getExcerptAttribute(): string
    {
        return Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $this->description)))), 160);
    }

    public function money(mixed $amount = null): string
    {
        $symbol = ['GBP' => '£', 'USD' => '$', 'EUR' => '€'][$this->currency] ?? $this->currency.' ';

        return $symbol.number_format((float) ($amount ?? $this->price), 2);
    }
}
