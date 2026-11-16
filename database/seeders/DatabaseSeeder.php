<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Creates the admin login from ADMIN_EMAIL / ADMIN_PASSWORD.
     * Demo products (local preview only): php artisan db:seed --class=DemoSeeder
     */
    public function run(): void
    {
        $email = config('services.admin.email');
        $password = config('services.admin.password') ?: Str::password(16);

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;
        $user->fill(['name' => 'Admin', 'is_admin' => true]);
        if ($isNew) {
            $user->password = $password;
        }
        $user->save();

        $this->command?->info($isNew
            ? "Admin created: {$email}".(config('services.admin.password') ? ' (password from ADMIN_PASSWORD)' : " / {$password}")
            : "Admin already exists: {$email} (password unchanged)");
    }
}
