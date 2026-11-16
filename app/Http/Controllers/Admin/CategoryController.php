<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['is_visible' => true, 'icon' => '🛍️'])]);
    }

    public function store(Request $request)
    {
        $category = new Category;
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $this->save($request, $category);

        return redirect()->route('admin.categories.index')->with('success', 'Category saved.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        Cache::forget('nav.categories');

        return back()->with('success', 'Category deleted. Its products are now uncategorised.');
    }

    private function save(Request $request, Category $category): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'slug' => 'nullable|alpha_dash|max:140',
            'icon' => 'nullable|string|max:16',
            'image_url' => 'nullable|url|max:1024',
            'description' => 'nullable|string|max:1000',
            'ebay_key' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->fill($data + ['sort_order' => 0]);
        $category->sort_order = (int) ($data['sort_order'] ?? 0);
        $category->is_visible = $request->boolean('is_visible');
        $category->is_featured = $request->boolean('is_featured');
        if (! empty($data['slug'])) {
            $category->slug = Str::slug($data['slug']);
        }
        $category->save();
        Cache::forget('nav.categories');
    }
}
