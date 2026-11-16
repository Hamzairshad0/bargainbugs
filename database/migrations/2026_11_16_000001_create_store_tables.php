<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('ebay_key')->nullable()->index(); // top-level eBay category name it maps from
            $table->string('icon', 16)->nullable();          // emoji shown on tiles
            $table->string('image_url', 1024)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('ebay_item_id', 32)->nullable()->unique();
            $table->string('source', 16)->default('manual'); // ebay | manual
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('subtitle')->nullable();
            $table->longText('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('original_price', 10, 2)->nullable();
            $table->string('currency', 3)->default('GBP');
            $table->integer('quantity')->default(1);
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->string('condition')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ebay_category_id', 32)->nullable();
            $table->string('ebay_category_name')->nullable();
            $table->json('images')->nullable();
            $table->string('ebay_url', 1024)->nullable();
            $table->string('status', 16)->default('active'); // active | sold | ended
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->boolean('lock_content')->default(false); // sync keeps admin's title/description/category
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('clicks')->default(0);   // "Buy on eBay" clicks
            $table->unsignedInteger('watch_count')->default(0);
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('ebay_synced_at')->nullable();
            $table->timestamp('ebay_seen_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_hidden']);
            $table->index('is_featured');
            $table->index('listed_at');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 24); // full | incremental | item
            $table->string('status', 16)->default('running'); // running | success | failed
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('ended_count')->default(0);
            $table->text('message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('ip', 45)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('sync_logs');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_admin'));
    }
};
