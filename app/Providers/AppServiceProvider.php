<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\ContactMessage;
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
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('partials.pagination');

        View::composer(['layouts.store', 'store.*'], function ($view) {
            try {
                // Cache plain arrays (Laravel 13 refuses to unserialize cached model objects)
                $rows = Cache::remember('nav.categories', 300, fn () => Category::where('is_visible', true)
                    ->withCount(['products' => fn ($q) => $q->visible()])
                    ->orderBy('sort_order')->orderBy('name')->get()
                    ->filter(fn ($c) => $c->products_count > 0)
                    ->map(fn ($c) => $c->getAttributes())->values()->all());
                $cats = Category::hydrate($rows);
            } catch (Throwable) {
                $cats = collect();
            }
            $view->with('navCategories', $cats);
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
