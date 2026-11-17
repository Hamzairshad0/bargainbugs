<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login · {{ setting('site_name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700|instrument-serif:400,400i&display=swap" rel="stylesheet">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="grid min-h-screen place-items-center p-4">
    <div class="dots-light pointer-events-none fixed inset-0"></div>
    <div class="pointer-events-none fixed -left-40 -top-40 h-[480px] w-[480px] rounded-full bg-sun blur-3xl"></div>
    <div class="pointer-events-none fixed -bottom-48 -right-40 h-[480px] w-[480px] rounded-full bg-bug-100 blur-3xl"></div>

    <div class="relative w-full max-w-md">
        <a href="{{ route('home') }}" class="mx-auto mb-8 flex w-max items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" alt="" width="48" height="48" class="h-12 w-12">
            <span class="text-xl font-semibold tracking-tight">Bargain <em class="text-plum">Bugs</em></span>
        </a>
        <div class="card-pop p-8 sm:p-10">
            <p class="eyebrow">Store admin</p>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight">Welcome <em class="text-plum">back</em></h1>
            <p class="mt-2 text-sm text-muted">Log in to manage products, eBay sync and settings.</p>
            <form method="post" action="{{ route('admin.login') }}" class="mt-8 grid gap-4">
                @csrf
                <label class="grid gap-2 text-sm font-medium">Email
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="input" autocomplete="username">
                </label>
                <label class="grid gap-2 text-sm font-medium">Password
                    <input type="password" name="password" required class="input" autocomplete="current-password">
                </label>
                @error('email')<p class="text-sm font-medium text-bug-600">{{ $message }}</p>@enderror
                <label class="flex items-center gap-2 text-sm text-ink/70"><input type="checkbox" name="remember" class="h-4 w-4 accent-ink"> Remember me</label>
                <button class="btn-primary mt-2 !py-4">Log in <span aria-hidden="true">→</span></button>
            </form>
        </div>
        <p class="mt-6 text-center text-sm"><a href="{{ route('home') }}" class="text-muted hover:text-ink">← Back to website</a></p>
    </div>
</body>
</html>
