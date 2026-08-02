<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Inbox: received thread-starters (parent_id is null) with unread counts
        $inbox = Message::where('receiver_id', $user->id)
            ->whereNull('parent_id')
            ->with(['sender', 'receiver', 'replies' => function ($q) use ($user) {
                $q->where('receiver_id', $user->id)->where('is_read', false);
            }])
            ->orderBy('updated_at', 'desc')
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
            ->orderBy('updated_at', 'desc')
            ->get();

        // Merge and deduplicate
        $inbox = $inbox->merge($inboxFromReplies)->unique('id')->sortByDesc('updated_at');

        // Sent: messages sent by user (thread starters)
        $sent = Message::where('sender_id', $user->id)
            ->whereNull('parent_id')
            ->with(['sender', 'receiver'])
            ->orderBy('updated_at', 'desc')
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

        // Group recipients by role, department, and same supervisor
        $grouped = collect();

        // Group by role
        $byRole = $recipients->groupBy(function ($r) {
            return $r->role->name ?? 'No Role';
        });
        foreach ($byRole as $role => $users) {
            $grouped[$role] = $users;
        }

        // Group by department
        $byDept = $recipients->filter(fn($r) => $r->department)->groupBy(function ($r) {
            return $r->department->name;
        });

        // Group by same supervisor (colleagues)
        $colleagues = collect();
        if ($user->supervisor_id) {
            $colleagues = $recipients->filter(fn($r) => $r->supervisor_id === $user->supervisor_id && $r->id !== $user->id);
        }

        // Bulk / grouped messaging is available to supervisors and admins
        $canBulk = $user->isSupervisor() || $user->isAdmin() || $user->isSuperAdmin();

        // Counts for nice bulk-group labels (subordinates relative to current user)
        $subordinatesCount = 0;
        $internsCount = 0;
        $employeesCount = 0;
        if ($canBulk) {
            $subordinatesCount = $user->subordinates()->count();
            $internsCount = $user->subordinates()->where('is_intern', true)->count();
            $employeesCount = $user->subordinates()->where('is_intern', false)->count();
        }

        return view('messages.create', compact(
            'recipients',
            'grouped',
            'byDept',
            'colleagues',
            'canBulk',
            'subordinatesCount',
            'internsCount',
            'employeesCount'
        ));
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

        $message = Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        \App\Models\User::find($request->receiver_id)?->notify(new \App\Notifications\SystemNotification(
            'New message from ' . $user->name,
            \Illuminate\Support\Str::limit($request->body, 80),
            route('messages.show', $message->id),
            'mail'
        ));

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

        \App\Models\User::find($receiverId)?->notify(new \App\Notifications\SystemNotification(
            'New reply from ' . $user->name,
            \Illuminate\Support\Str::limit($request->body, 80),
            route('messages.show', $rootMessage->id),
            'mail'
        ));

        // Touch the parent so it sorts to top
        $rootMessage->touch();

        return redirect()->route('messages.show', $rootMessage->id)->with('success', 'Reply sent.');
    }

    public function bulkStore(Request $request)
    {
        $user = auth()->user();

        // Only supervisors and admins may send bulk / grouped messages
        if (!($user->isSupervisor() || $user->isAdmin() || $user->isSuperAdmin())) {
            abort(403, 'You are not allowed to send bulk messages.');
        }

        $request->validate([
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string|max:5000',
            'group' => 'nullable|in:subordinates,interns,employees',
            'receiver_ids' => 'nullable|array',
            'receiver_ids.*' => 'integer|exists:users,id',
        ]);

        // Resolve the recipient id list
        $recipientIds = collect();

        if ($request->filled('group')) {
            switch ($request->group) {
                case 'subordinates':
                    $recipientIds = $user->subordinates()->pluck('id');
                    break;
                case 'interns':
                    $recipientIds = $user->subordinates()->where('is_intern', true)->pluck('id');
                    break;
                case 'employees':
                    $recipientIds = $user->subordinates()->where('is_intern', false)->pluck('id');
                    break;
            }
        } elseif ($request->filled('receiver_ids')) {
            // Explicit selection: intersect with allowed set to prevent abuse
            $allowedIds = $this->getAllowedRecipients($user)->pluck('id');
            $recipientIds = collect($request->receiver_ids)
                ->map(fn($id) => (int) $id)
                ->intersect($allowedIds);
        }

        // Dedupe, exclude self and nulls
        $recipientIds = $recipientIds
            ->filter()
            ->map(fn($id) => (int) $id)
            ->unique()
            ->reject(fn($id) => $id === $user->id)
            ->values();

        if ($recipientIds->isEmpty()) {
            return back()->withErrors(['group' => 'No valid recipients found for the selected group.'])->withInput();
        }

        $recipients = User::whereIn('id', $recipientIds)->get();

        DB::transaction(function () use ($recipients, $user, $request) {
            foreach ($recipients as $recipient) {
                $message = Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $recipient->id,
                    'subject' => $request->subject,
                    'body' => $request->body,
                    'parent_id' => null,
                ]);

                $recipient->notify(new \App\Notifications\SystemNotification(
                    'New message from ' . $user->name,
                    \Illuminate\Support\Str::limit($request->body, 80),
                    route('messages.show', $message->id),
                    'mail'
                ));
            }
        });

        $count = $recipients->count();

        return redirect()->route('messages.index')
            ->with('success', "Message sent to {$count} recipient(s).");
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

        // Employees / Interns can message:
        //  (a) their direct supervisor
        //  (b) their department HOD
        //  (c) all Admin + Super Admin users (HR)
        //  (d) teammates: users sharing the same supervisor_id, and same-department members
        $recipientIds = collect();

        // (a) direct supervisor
        if ($user->supervisor_id) {
            $recipientIds->push($user->supervisor_id);
        }

        // (b) department HOD
        if ($user->department && $user->department->hod_id) {
            $recipientIds->push($user->department->hod_id);
        }

        // (c) all Admin + Super Admin users (HR)
        $adminIds = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['Admin', 'Super Admin']);
        })->pluck('id');
        $recipientIds = $recipientIds->merge($adminIds);

        // (d) teammates sharing the same supervisor
        if ($user->supervisor_id) {
            $sameSupervisorIds = User::where('supervisor_id', $user->supervisor_id)
                ->where('id', '!=', $user->id)
                ->pluck('id');
            $recipientIds = $recipientIds->merge($sameSupervisorIds);
        }

        // (d) same-department members
        if ($user->department_id) {
            $sameDeptIds = User::where('department_id', $user->department_id)
                ->where('id', '!=', $user->id)
                ->pluck('id');
            $recipientIds = $recipientIds->merge($sameDeptIds);
        }

        // Dedupe, exclude self and nulls
        $recipientIds = $recipientIds
            ->filter()
            ->unique()
            ->reject(function ($id) use ($user) {
                return $id === $user->id;
            });

        return User::whereIn('id', $recipientIds)->orderBy('name')->get();
    }
}
