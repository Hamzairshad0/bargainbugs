<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    /** Defaults used until the admin changes them in Admin → Settings. */
    public const DEFAULTS = [
        'site_name' => 'Bargain Bugs',
        'tagline' => 'Big bargains. Tiny prices.',
        'announcement' => '🐞 New bargains added every week — follow us so you never miss a deal!',
        'hero_title' => 'Big bargains. Tiny prices.',
        'hero_subtitle' => 'Hand-picked deals on homeware, gadgets, toys and more — straight from our eBay store, with secure checkout and buyer protection on every order.',
        'hero_cta' => 'Shop the bargains',
        'about_title' => 'Small team. Big bargains.',
        'about_text' => "Bargain Bugs is a UK-based online store bringing you quality products at prices that bug the competition.\n\nEvery item is listed on our eBay store, so you can buy with complete confidence: secure payments, eBay Money Back Guarantee and fast UK dispatch.\n\nWe add new stock all the time, so follow us on social media to catch the best deals first.",
        'contact_email' => 'hello@bargainbugs.co.uk',
        'contact_phone' => '',
        'whatsapp' => '',
        'address' => 'United Kingdom',
        'ebay_store_url' => 'https://www.ebay.co.uk/usr/bargainbugs',
        'instagram' => '',
        'tiktok' => '',
        'facebook' => '',
        'youtube' => '',
        'x' => '',
        'trust_1' => 'Secure checkout via eBay',
        'trust_2' => 'Fast UK dispatch',
        'trust_3' => 'eBay Money Back Guarantee',
        'trust_4' => 'New deals every week',
        'ebay_feedback_score' => '',
        'ebay_positive_percent' => '',
        'testimonials' => '',
        'google_site_verification' => '',
        'meta_description' => 'Bargain Bugs — big bargains, tiny prices. Shop our latest deals on homeware, gadgets, toys and more with secure checkout through eBay.',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::allCached();

        if (array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== '') {
            return $all[$key];
        }

        return $default ?? static::DEFAULTS[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
        if (static::$memo !== null) {
            static::$memo[$key] = $value;
        }
    }

    /** Loaded once per request — views call setting() dozens of times, so never hit the cache/DB per call. */
    private static ?array $memo = null;

    public static function allCached(): array
    {
        if (static::$memo !== null) {
            return static::$memo;
        }

        try {
            return static::$memo = Cache::remember('settings.all', 300, fn () => static::pluck('value', 'key')->all());
        } catch (Throwable) {
            return static::$memo = [];
        }
    }
}
