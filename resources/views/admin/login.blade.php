<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login · {{ setting('site_name') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|inter:400,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center !bg-bug-500 p-4">
    <div class="dots-light pointer-events-none fixed inset-0"></div>
    <div class="relative w-full max-w-md rounded-[2rem] border-2 border-ink bg-white p-8 shadow-[8px_8px_0_0_var(--color-ink)] sm:p-10">
        <div class="text-center">
            <img src="{{ asset('images/logo-mark.svg') }}" alt="" class="mx-auto h-16 w-16">
            <h1 class="mt-4 font-display text-3xl font-extrabold">Admin login</h1>
            <p class="mt-1 text-sm text-ink/60">Manage your Bargain Bugs store</p>
        </div>
        <form method="post" action="{{ route('admin.login') }}" class="mt-8 grid gap-4">
            @csrf
            <label class="grid gap-2 text-sm font-bold">Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input" autocomplete="username">
            </label>
            <label class="grid gap-2 text-sm font-bold">Password
                <input type="password" name="password" required class="input" autocomplete="current-password">
            </label>
            @error('email')<p class="text-sm font-semibold text-bug-600">{{ $message }}</p>@enderror
            <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="remember" class="h-4 w-4 accent-bug-500"> Remember me</label>
            <button class="btn-primary mt-2 !py-4">Log in</button>
        </form>
        <p class="mt-6 text-center text-sm"><a href="{{ route('home') }}" class="font-semibold text-ink/60 hover:text-ink">← Back to website</a></p>
    </div>
</body>
</html>
