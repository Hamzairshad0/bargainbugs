<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Sample products so the site can be previewed before eBay is connected.
 * Remove them afterwards in Admin → Products (filter: Manual) → select all → Delete.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $cats = collect([
            ['Home & Kitchen', '🏠'], ['Toys & Games', '🧸'], ['Phones & Gadgets', '📱'],
            ['Health & Beauty', '💄'], ['Garden & Outdoor', '🌿'], ['Pet Supplies', '🐾'],
        ])->mapWithKeys(fn ($c, $i) => [$c[0] => Category::firstOrCreate(['name' => $c[0]], ['icon' => $c[1], 'sort_order' => $i])]);

        $items = [
            ['Stainless Steel Electric Kettle 1.7L Rapid Boil', 'Home & Kitchen', 19.99, 34.99, 'New', 12, 'photo-1594213114663-d94db9b17125'],
            ['Wireless Bluetooth Earbuds with Charging Case', 'Phones & Gadgets', 14.49, 29.99, 'New', 3, 'photo-1590658268037-6bf12165a8df'],
            ['Wooden Building Blocks Set — 100 Pieces', 'Toys & Games', 12.99, null, 'New', 8, 'photo-1587654780291-39c9404d746b'],
            ['Ceramic Plant Pots Set of 3 with Drainage', 'Garden & Outdoor', 16.50, 24.00, 'New', 5, 'photo-1485955900006-10f4d324d411'],
            ['Retro Handheld Games Console — 400 Games', 'Toys & Games', 17.99, 27.99, 'New', 2, 'photo-1531525645387-7f14be1bdbbd'],
            ['Vitamin C Brightening Face Serum 30ml', 'Health & Beauty', 7.99, 15.99, 'New', 20, 'photo-1620916566398-39f1143ab7be'],
            ['Smart Watch Fitness Tracker Heart Rate', 'Phones & Gadgets', 24.99, 49.99, 'New', 6, 'photo-1523275335684-37898b6baf30'],
            ['Non-Stick Frying Pan 28cm Induction Safe', 'Home & Kitchen', 13.99, null, 'New', 9, 'photo-1556909114-f6e7ad7d3136'],
            ['Orthopaedic Dog Bed — Large, Washable Cover', 'Pet Supplies', 22.00, 35.00, 'New', 4, 'photo-1601758228041-f3b2795255f1'],
            ['LED Desk Lamp with Wireless Charger', 'Home & Kitchen', 18.99, 26.99, 'New other (see details)', 7, 'photo-1507473885765-e6ed057f782c'],
            ['Kids Remote Control Car 2.4GHz Off-Road', 'Toys & Games', 21.99, null, 'New', 1, 'photo-1594787318286-3d835c1d207f'],
            ['Insulated Water Bottle 750ml — Keeps Cold 24h', 'Garden & Outdoor', 9.99, 18.00, 'New', 15, 'photo-1602143407151-7111542de6e8'],
        ];

        foreach ($items as $i => [$title, $cat, $price, $was, $cond, $qty, $photo]) {
            Product::updateOrCreate(['title' => $title], [
                'source' => 'manual',
                'category_id' => $cats[$cat]->id,
                'price' => $price,
                'original_price' => $was,
                'condition' => $cond,
                'quantity' => $qty,
                'images' => ["https://images.unsplash.com/{$photo}?w=900&h=900&fit=crop&auto=format&q=75"],
                'description' => '<p>Sample product for previewing the website. Real products and descriptions are imported automatically from the Bargain Bugs eBay store.</p><ul><li>Fast UK dispatch</li><li>Secure checkout via eBay</li><li>eBay Money Back Guarantee</li></ul>',
                'ebay_url' => 'https://www.ebay.co.uk/usr/bargainbugs',
                'status' => 'active',
                'is_featured' => $i % 3 === 0,
                'views' => random_int(5, 300),
                'watch_count' => random_int(0, 12),
                'listed_at' => now()->subDays($i * 2),
            ]);
        }

        $this->command?->info('Demo products created.');
    }
}
