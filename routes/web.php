<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\CronController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------- Storefront
Route::controller(StoreController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/shop', 'shop')->name('shop');
    Route::get('/deals', 'shop')->name('deals');
    Route::get('/new', 'shop')->name('new');
    Route::get('/categories', 'categories')->name('categories');
    Route::get('/category/{category}', 'shop')->name('category');
    Route::get('/product/{product}', 'product')->name('product');
    Route::get('/go/{product}', 'go')->name('go');
    Route::get('/about', 'about')->name('about');
    Route::get('/links', 'links')->name('links');
    Route::view('/privacy', 'store.privacy')->name('privacy');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'sendContact')->middleware('throttle:5,1')->name('contact.send');
    Route::post('/subscribe', 'subscribe')->middleware('throttle:5,1')->name('subscribe');
    Route::get('/sitemap.xml', 'sitemap')->name('sitemap');

    // Small JSON / HTML endpoints used by the storefront scripts
    Route::get('/api/suggest', 'suggest')->middleware('throttle:60,1')->name('api.suggest');
    Route::get('/api/cards', 'cards')->middleware('throttle:60,1')->name('api.cards');
});

Route::get('/cron/ebay-sync', [CronController::class, 'ebaySync'])->name('cron.ebay');

// --------------------------------------------------------------------- Admin
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [Admin\AuthController::class, 'show'])->name('login');
    Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::post('products/bulk', [Admin\ProductController::class, 'bulk'])->name('products.bulk');
        Route::post('products/{product}/toggle/{field}', [Admin\ProductController::class, 'toggle'])->name('products.toggle');
        Route::resource('products', Admin\ProductController::class)->except('show');

        Route::resource('categories', Admin\CategoryController::class)->except('show');

        Route::get('ebay', [Admin\EbayController::class, 'index'])->name('ebay');
        Route::get('ebay/connect', [Admin\EbayController::class, 'connect'])->name('ebay.connect');
        Route::post('ebay/disconnect', [Admin\EbayController::class, 'disconnect'])->name('ebay.disconnect');
        Route::post('ebay/sync', [Admin\EbayController::class, 'sync'])->name('ebay.sync');
        Route::post('ebay/import', [Admin\EbayController::class, 'import'])->name('ebay.import');

        Route::get('messages', [Admin\MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [Admin\MessageController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [Admin\MessageController::class, 'destroy'])->name('messages.destroy');
        Route::get('subscribers', [Admin\MessageController::class, 'subscribers'])->name('subscribers');
        Route::get('subscribers/export', [Admin\MessageController::class, 'exportSubscribers'])->name('subscribers.export');

        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings');
        Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::post('account', [Admin\SettingController::class, 'account'])->name('account');
    });
});

// eBay sends the seller back here after they approve access (the RuName "Auth accepted URL")
Route::get('/ebay/callback', [Admin\EbayController::class, 'callback'])->middleware('admin')->name('ebay.callback');
