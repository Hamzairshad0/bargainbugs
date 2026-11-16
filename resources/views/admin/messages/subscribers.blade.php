@extends('layouts.admin')

@section('title', 'Subscribers')
@section('actions')
    <a href="{{ route('admin.subscribers.export') }}" class="btn-sun btn-sm">⬇ Export CSV</a>
@endsection

@section('content')
    <p class="mb-4 text-sm text-ink/60">People who joined the “Bug Club” newsletter. Export the CSV to import into Mailchimp, Klaviyo or similar.</p>
    <div class="overflow-hidden rounded-3xl border-2 border-ink bg-white shadow-[4px_4px_0_0_var(--color-ink)]">
        <table class="w-full text-sm">
            <thead class="border-b-2 border-ink bg-cream text-left text-xs font-bold uppercase tracking-wider text-ink/60"><tr><th class="p-4">Email</th><th class="p-4">Joined</th></tr></thead>
            <tbody class="divide-y divide-ink/10">
                @forelse ($subscribers as $s)
                    <tr><td class="p-4 font-semibold">{{ $s->email }}</td><td class="p-4 text-ink/60">{{ $s->created_at->format('d M Y') }}</td></tr>
                @empty
                    <tr><td colspan="2" class="p-12 text-center text-ink/50">No subscribers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $subscribers->links() }}</div>
@endsection
