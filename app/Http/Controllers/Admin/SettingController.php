<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SettingController extends Controller
{
    public const GROUPS = [
        'Brand' => [
            'site_name' => ['Site name', 'text'],
            'tagline' => ['Tagline', 'text'],
            'announcement' => ['Announcement bar (leave empty to hide)', 'text'],
            'meta_description' => ['SEO description (Google & social previews)', 'textarea'],
        ],
        'Home page' => [
            'hero_title' => ['Hero headline (last word gets the yellow highlight)', 'text'],
            'hero_subtitle' => ['Hero text', 'textarea'],
            'hero_cta' => ['Hero button text', 'text'],
            'trust_1' => ['Trust badge 1', 'text'],
            'trust_2' => ['Trust badge 2', 'text'],
            'trust_3' => ['Trust badge 3', 'text'],
            'trust_4' => ['Trust badge 4', 'text'],
        ],
        'About page' => [
            'about_title' => ['About headline', 'text'],
            'about_text' => ['About text (blank line = new paragraph)', 'textarea'],
        ],
        'Contact' => [
            'contact_email' => ['Contact email', 'text'],
            'contact_phone' => ['Phone', 'text'],
            'whatsapp' => ['WhatsApp number (with country code, e.g. 447700900123)', 'text'],
            'address' => ['Location / address', 'text'],
        ],
        'Social & eBay' => [
            'ebay_store_url' => ['eBay store URL', 'url'],
            'instagram' => ['Instagram URL', 'url'],
            'tiktok' => ['TikTok URL', 'url'],
            'facebook' => ['Facebook URL', 'url'],
            'youtube' => ['YouTube URL', 'url'],
            'x' => ['X / Twitter URL', 'url'],
        ],
    ];

    public function edit()
    {
        return view('admin.settings', ['groups' => self::GROUPS]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (self::GROUPS as $fields) {
            foreach ($fields as $key => [, $type]) {
                $rules[$key] = $type === 'url' ? 'nullable|url|max:500' : 'nullable|string|max:5000';
            }
        }
        $data = $request->validate($rules);

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Settings saved.');
    }

    public function account(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|current_password',
            'email' => 'required|email|max:150|unique:users,email,'.$request->user()->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();
        $user->email = $data['email'];
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return back()->with('success', 'Account updated.');
    }
}
