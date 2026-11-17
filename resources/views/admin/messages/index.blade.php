@extends('layouts.admin')

@section('title', 'Messages')

@section('content')
    <div class="overflow-hidden rounded-[24px] border border-line bg-white shadow-[0_12px_32px_-18px_rgb(21_18_26/.12)]">
        <div class="divide-y divide-ink/10">
            @forelse ($messages as $m)
                <a href="{{ route('admin.messages.show', $m) }}" class="flex items-start gap-4 p-5 hover:bg-cream/60 {{ $m->read_at ? '' : 'bg-bug-50/60' }}">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full border border-line bg-sun font-bold">{{ strtoupper(mb_substr($m->name, 0, 1)) }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm"><b>{{ $m->name }}</b> <span class="text-ink/50">&lt;{{ $m->email }}&gt;</span> @unless ($m->read_at)<span class="badge ml-1 bg-bug-500 text-white">New</span>@endunless</p>
                        <p class="mt-0.5 font-semibold">{{ $m->subject ?: '(no subject)' }}</p>
                        <p class="mt-0.5 truncate text-sm text-ink/60">{{ $m->message }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-ink/50">{{ $m->created_at->diffForHumans() }}</span>
                </a>
            @empty
                <p class="p-12 text-center text-ink/50">No messages yet.</p>
            @endforelse
        </div>
    </div>
    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
