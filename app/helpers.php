<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('socials')) {
    /** Configured social links, in display order: [key => [label, url]] */
    function socials(): array
    {
        $labels = ['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'facebook' => 'Facebook', 'youtube' => 'YouTube', 'x' => 'X'];
        $out = [];
        foreach ($labels as $key => $label) {
            if ($url = setting($key)) {
                $out[$key] = [$label, $url];
            }
        }

        return $out;
    }
}
