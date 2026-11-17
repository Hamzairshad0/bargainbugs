<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Product;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Navigation categories: built once per request (not once per view) and cached for 5 minutes.
        $this->app->singleton('nav.categories', function () {
            try {
                // Cache plain arrays (Laravel 13 refuses to unserialize cached model objects)
                $rows = Cache::remember('nav.categories', 300, fn () => Category::where('is_visible', true)
                    ->withCount(['products' => fn ($q) => $q->visible()])
                    ->orderBy('sort_order')->orderBy('name')->get()
                    ->filter(fn ($c) => $c->products_count > 0)
                    ->map(fn ($c) => $c->getAttributes() + [
                        // cover = admin image, or the newest product photo in the category (small size)
                        'cover' => $c->image_url ?: Product::sized(Product::visible()->where('category_id', $c->id)
                            ->latest('listed_at')->value('images')[0] ?? null, 500),
                    ])->values()->all());

                return Category::hydrate($rows);
            } catch (Throwable) {
                return collect();
            }
        });
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('partials.pagination');

        View::composer(['layouts.store', 'store.*', 'partials.*', 'errors::*', 'errors.*'], function ($view) {
            $view->with('navCategories', app('nav.categories'));
        });

        View::composer('layouts.admin', function ($view) {
            try {
                $view->with('unreadMessages', ContactMessage::whereNull('read_at')->count());
            } catch (Throwable) {
                $view->with('unreadMessages', 0);
            }
        });
    }
}
