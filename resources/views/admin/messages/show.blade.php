@extends('layouts.admin')

@section('title', 'Message')
@section('actions')
    <a href="{{ route('admin.messages.index') }}" class="btn-ghost btn-sm">← All messages</a>
@endsection

@section('content')
    <div class="card-pop max-w-3xl p-6 sm:p-8">
        <p class="text-sm text-ink/50">{{ $message->created_at->format('l j F Y, H:i') }}</p>
        <h2 class="mt-2 font-display text-2xl font-extrabold">{{ $message->subject ?: '(no subject)' }}</h2>
        <p class="mt-2 text-sm"><b>{{ $message->name }}</b> &lt;<a href="mailto:{{ $message->email }}" class="underline">{{ $message->email }}</a>&gt;</p>
        <div class="mt-6 whitespace-pre-line rounded-2xl bg-cream p-5 leading-7">{{ $message->message }}</div>
        <div class="mt-6 flex flex-wrap gap-3">
            <a href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'Your message to '.setting('site_name'))) }}" class="btn-primary">Reply by email</a>
            <form action="{{ route('admin.messages.destroy', $message) }}" method="post" data-confirm="Delete this message?">
                @csrf @method('DELETE')
                <button class="btn-ghost">Delete</button>
            </form>
        </div>
    </div>
@endsection
