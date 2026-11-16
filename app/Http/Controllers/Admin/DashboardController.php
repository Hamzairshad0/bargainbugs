<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Subscriber;
use App\Models\SyncLog;
use App\Services\EbayClient;

class DashboardController extends Controller
{
    public function index(EbayClient $ebay)
    {
        $stats = [
            'live' => Product::visible()->count(),
            'total' => Product::count(),
            'sold' => Product::where('status', 'sold')->count(),
            'featured' => Product::where('is_featured', true)->visible()->count(),
            'views' => (int) Product::sum('views'),
            'clicks' => (int) Product::sum('clicks'),
            'messages' => ContactMessage::whereNull('read_at')->count(),
            'subscribers' => Subscriber::count(),
            'value' => (float) Product::visible()->selectRaw('SUM(price * quantity) as v')->value('v'),
        ];

        $topViewed = Product::visible()->orderByDesc('views')->take(5)->get();
        $topClicked = Product::orderByDesc('clicks')->where('clicks', '>', 0)->take(5)->get();
        $recent = Product::latest()->take(6)->get();
        $lastSync = SyncLog::latest('id')->first();
        $messages = ContactMessage::latest()->take(4)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'topViewed' => $topViewed,
            'topClicked' => $topClicked,
            'recent' => $recent,
            'lastSync' => $lastSync,
            'messages' => $messages,
            'ebayConnected' => $ebay->isConnected(),
            'ebayConfigured' => $ebay->isConfigured(),
            'ebayUser' => Setting::get('ebay_user_id'),
        ]);
    }
}
