<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\Subscriber;

class MessageController extends Controller
{
    public function index()
    {
        return view('admin.messages.index', ['messages' => ContactMessage::latest()->paginate(30)]);
    }

    public function show(ContactMessage $message)
    {
        if (! $message->read_at) {
            $message->update(['read_at' => now()]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message deleted.');
    }

    public function subscribers()
    {
        return view('admin.messages.subscribers', ['subscribers' => Subscriber::latest()->paginate(50)]);
    }

    public function exportSubscribers()
    {
        $rows = Subscriber::orderBy('created_at')->get(['email', 'created_at']);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['email', 'subscribed_at']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->email, $r->created_at]);
            }
            fclose($out);
        }, 'bargainbugs-subscribers-'.date('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
