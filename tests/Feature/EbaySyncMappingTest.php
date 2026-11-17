<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Services\EbaySync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EbaySyncMappingTest extends TestCase
{
    use RefreshDatabase;

    private function item(array $o = []): \SimpleXMLElement
    {
        $o += ['status' => 'Active', 'qty' => 5, 'sold' => 1, 'price' => '12.99', 'title' => 'Lego Classic Bricks Box &amp; More'];
        $xml = <<<XML
<Item>
  <ItemID>123456789012</ItemID>
  <Title>{$o['title']}</Title>
  <Description><![CDATA[<div style="color:red"><script>alert(1)</script><h1>Great item</h1><p onclick="x()">Brand <b>new</b></p><img src="x.jpg"><font>sealed</font></div>]]></Description>
  <Quantity>{$o['qty']}</Quantity>
  <SellingStatus><CurrentPrice currencyID="GBP">{$o['price']}</CurrentPrice><QuantitySold>{$o['sold']}</QuantitySold><ListingStatus>{$o['status']}</ListingStatus></SellingStatus>
  <DiscountPriceInfo><OriginalRetailPrice currencyID="GBP">19.99</OriginalRetailPrice></DiscountPriceInfo>
  <ListingDetails><StartTime>2026-11-10T10:00:00.000Z</StartTime><EndTime>2026-12-10T10:00:00.000Z</EndTime><ViewItemURL>https://www.ebay.co.uk/itm/123456789012</ViewItemURL></ListingDetails>
  <PrimaryCategory><CategoryID>19006</CategoryID><CategoryName>Toys &amp; Games:Building Toys:LEGO</CategoryName></PrimaryCategory>
  <ConditionDisplayName>New</ConditionDisplayName>
  <PictureDetails><PictureURL>https://i.ebayimg.com/images/g/abc/s-l500.jpg</PictureURL><PictureURL>http://i.ebayimg.com/00/s/MTYw/z/xyz/\$_12.JPG?set_id=1</PictureURL></PictureDetails>
  <WatchCount>7</WatchCount>
</Item>
XML;

        return simplexml_load_string($xml);
    }

    public function test_it_imports_an_active_listing(): void
    {
        $p = app(EbaySync::class)->upsert($this->item());

        $this->assertSame('Lego Classic Bricks Box & More', $p->title);
        $this->assertSame('ebay', $p->source);
        $this->assertSame('active', $p->status);
        $this->assertSame(4, $p->quantity);
        $this->assertEquals(12.99, (float) $p->price);
        $this->assertEquals(19.99, (float) $p->original_price);
        $this->assertSame('Toys & Games', $p->category->name);
        $this->assertSame('https://i.ebayimg.com/images/g/abc/s-l1600.jpg', $p->images[0]);
        $this->assertStringContainsString('$_57.JPG', $p->images[1]);
        $this->assertStringStartsWith('https://', $p->images[1]);
        $this->assertStringNotContainsString('script', $p->description);
        $this->assertStringNotContainsString('onclick', $p->description);
        $this->assertStringNotContainsString('style', $p->description);
        $this->assertStringContainsString('<b>new</b>', $p->description);
        $this->assertSame(7, $p->watch_count);
    }

    public function test_sold_out_and_locked_content(): void
    {
        $sync = app(EbaySync::class);
        $p = $sync->upsert($this->item());
        $p->update(['title' => 'My custom title', 'lock_content' => true, 'is_hidden' => true]);

        $sync->upsert($this->item(['qty' => 5, 'sold' => 5, 'price' => '9.99', 'title' => 'eBay title']));
        $p->refresh();

        $this->assertSame('sold', $p->status);
        $this->assertSame('My custom title', $p->title);
        $this->assertEquals(9.99, (float) $p->price);
        $this->assertTrue($p->is_hidden);
    }

    public function test_ended_listings_are_not_imported(): void
    {
        $this->assertNull(app(EbaySync::class)->upsert($this->item(['status' => 'Completed', 'sold' => 0])));
        $this->assertSame(0, Product::count());
    }

    public function test_storefront_pages_render(): void
    {
        app(EbaySync::class)->upsert($this->item());
        $p = Product::first();

        foreach (['/', '/shop', '/deals', '/new', '/categories', '/about', '/contact', '/links', '/sitemap.xml', '/product/'.$p->slug, '/shop?q=lego&sort=price_asc'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/product/'.$p->slug)->assertSee('Buy now on eBay')->assertSee('og:image', false);
        $this->get('/go/'.$p->slug)->assertRedirect('https://www.ebay.co.uk/itm/123456789012');
        $this->assertSame(1, $p->fresh()->clicks);
        $this->assertStringContainsString('s-l500', $p->thumb); // cards use the light image size
    }

    public function test_load_more_filters_and_few_queries(): void
    {
        foreach (range(1, 30) as $i) {
            Product::create(['title' => "Item $i", 'price' => $i, 'original_price' => $i % 2 ? $i + 10 : null, 'quantity' => 2, 'status' => 'active', 'listed_at' => now()->subDays($i)]);
        }

        $this->get('/shop')->assertOk()->assertSee('Load 6 more');
        $this->getJson('/shop?partial=1&page=2')->assertOk()
            ->assertJson(['shown' => 30, 'total' => 30, 'next' => null])
            ->assertJsonPath('html', fn ($h) => substr_count($h, 'class="card"') === 6);
        $this->getJson('/shop?partial=1&deals=1')->assertJson(['total' => 15]);
        $this->getJson('/shop?partial=1&sort=low')->assertJsonPath('html', fn ($h) => str_contains($h, '£1.00'));

        // settings are read once per request, not once per setting() call
        $n = 0;
        \DB::listen(function () use (&$n) { $n++; });
        $this->get('/about')->assertOk();
        $this->assertLessThan(12, $n, "About page ran $n queries");
    }

    public function test_cron_requires_secret(): void
    {
        config(['services.cron_secret' => 'abc']);
        $this->get('/cron/ebay-sync')->assertForbidden();
        $this->get('/cron/ebay-sync?key=wrong')->assertForbidden();
        $this->get('/cron/ebay-sync?key=abc')->assertStatus(409); // not connected yet
        $this->withHeader('Authorization', 'Bearer abc')->get('/cron/ebay-sync')->assertStatus(409);
    }

    public function test_contact_and_subscribe(): void
    {
        $this->post('/contact', ['name' => 'Sam', 'email' => 'sam@example.com', 'message' => 'Hi'])->assertRedirect();
        $this->post('/subscribe', ['email' => 'Sam@Example.com'])->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['email' => 'sam@example.com']);
        $this->assertDatabaseHas('subscribers', ['email' => 'sam@example.com']);
    }

    public function test_admin_requires_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/products')->assertRedirect('/admin/login');
    }
}
