<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Inbox: received thread-starters (parent_id is null) with unread counts
        $inbox = Message::where('receiver_id', $user->id)
            ->whereNull('parent_id')
            ->with(['sender', 'replies' => function ($q) use ($user) {
                $q->where('receiver_id', $user->id)->where('is_read', false);
            }])
            ->latest()
            ->get();

        // Also include threads where user received a reply but the original was sent by them
        $inboxFromReplies = Message::whereNull('parent_id')
            ->where('sender_id', $user->id)
            ->whereHas('replies', function ($q) use ($user) {
                $q->where('receiver_id', $user->id);
            })
            ->with(['sender', 'receiver', 'replies' => function ($q) use ($user) {
                $q->where('receiver_id', $user->id)->where('is_read', false);
            }])
            ->latest()
            ->get();

        // Merge and deduplicate
        $inbox = $inbox->merge($inboxFromReplies)->unique('id')->sortByDesc('updated_at');

        // Sent: messages sent by user (thread starters)
        $sent = Message::where('sender_id', $user->id)
            ->whereNull('parent_id')
            ->with('receiver')
            ->latest()
            ->get();

        // Unread count
        $unreadCount = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('messages.index', compact('inbox', 'sent', 'unreadCount'));
    }

    public function show(Message $message)
    {
        $user = auth()->user();

        // Ensure user is part of this conversation
        $isParticipant = $message->sender_id === $user->id || $message->receiver_id === $user->id;
        if (!$isParticipant) {
            // Check if user is participant in any reply
            $isReplyParticipant = $message->replies()
                ->where(function ($q) use ($user) {
                    $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
                })->exists();

            if (!$isReplyParticipant) {
                abort(403, 'You are not authorized to view this message.');
            }
        }

        // If this is a reply, redirect to the parent thread
        if ($message->parent_id) {
            return redirect()->route('messages.show', $message->parent_id);
        }

        // Mark messages as read
        $message->replies()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($message->receiver_id === $user->id && !$message->is_read) {
            $message->update(['is_read' => true, 'read_at' => now()]);
        }

        // Load thread
        $message->load(['sender', 'receiver', 'replies' => function ($q) {
            $q->with(['sender', 'receiver'])->orderBy('created_at', 'asc');
        }]);

        // Determine who the reply should go to
        $replyTo = $message->sender_id === $user->id ? $message->receiver_id : $message->sender_id;

        return view('messages.show', compact('message', 'replyTo'));
    }

    public function create()
    {
        $user = auth()->user();
        $recipients = $this->getAllowedRecipients($user);

        return view('messages.create', compact('recipients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
        ]);

        $user = auth()->user();
        $allowedIds = $this->getAllowedRecipients($user)->pluck('id')->toArray();

        if (!in_array($request->receiver_id, $allowedIds)) {
            return back()->withErrors(['receiver_id' => 'You are not allowed to message this user.'])->withInput();
        }

        Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        return redirect()->route('messages.index')->with('success', 'Message sent successfully.');
    }

    public function reply(Request $request, Message $message)
    {
        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $user = auth()->user();

        // Get the root message (if this is already a reply, go to parent)
        $rootMessage = $message->parent_id ? $message->parent : $message;

        // Determine receiver: the other participant in the thread
        $receiverId = $rootMessage->sender_id === $user->id
            ? $rootMessage->receiver_id
            : $rootMessage->sender_id;

        // Verify user is participant
        $isParticipant = $rootMessage->sender_id === $user->id || $rootMessage->receiver_id === $user->id;
        if (!$isParticipant) {
            abort(403, 'You are not authorized to reply to this message.');
        }

        Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'subject' => $rootMessage->subject,
            'body' => $request->body,
            'parent_id' => $rootMessage->id,
        ]);

        // Touch the parent so it sorts to top
        $rootMessage->touch();

        return redirect()->route('messages.show', $rootMessage->id)->with('success', 'Reply sent.');
    }

    private function getAllowedRecipients(User $user)
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            // Admins can message anyone except themselves
            return User::where('id', '!=', $user->id)->orderBy('name')->get();
        }

        if ($user->isSupervisor()) {
            // Supervisors can message their subordinates and any admin/super admin
            $subordinateIds = $user->subordinates()->pluck('id');
            $adminIds = User::whereHas('role', function ($q) {
                $q->whereIn('name', ['Admin', 'Super Admin']);
            })->pluck('id');

            $recipientIds = $subordinateIds->merge($adminIds)->unique();

            return User::whereIn('id', $recipientIds)
                ->where('id', '!=', $user->id)
                ->orderBy('name')
                ->get();
        }

        // Employees / Interns: can message supervisor and department HOD
        $recipientIds = collect();

        if ($user->supervisor_id) {
            $recipientIds->push($user->supervisor_id);
        }

        if ($user->department && $user->department->hod_id) {
            $recipientIds->push($user->department->hod_id);
        }

        $recipientIds = $recipientIds->unique()->filter(function ($id) use ($user) {
            return $id !== $user->id;
        });

        return User::whereIn('id', $recipientIds)->orderBy('name')->get();
    }
}
