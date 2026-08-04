<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\RecipientResolver;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /** My conversations with title, last message, and unread count. */
    public function conversations()
    {
        $user = auth()->user();

        $conversations = $user->conversations()
            ->with(['participants:id,name', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function (Conversation $c) use ($user) {
                $others = $c->participants->where('id', '!=', $user->id)->values();
                $title = $c->is_group
                    ? ($c->name ?: $others->pluck('name')->join(', '))
                    : ($others->first()->name ?? 'Unknown');
                $lastReadId = (int) ($c->pivot->last_read_message_id ?? 0);
                $unread = $c->messages()
                    ->where('id', '>', $lastReadId)
                    ->where('sender_id', '!=', $user->id)
                    ->count();

                return [
                    'id' => $c->id,
                    'is_group' => $c->is_group,
                    'title' => $title,
                    'avatar' => $c->is_group ? '#' : mb_strtoupper(mb_substr($others->first()->name ?? '?', 0, 1)),
                    'last_message' => $c->latestMessage?->body,
                    'last_at' => $c->latestMessage?->created_at?->diffForHumans(),
                    'unread' => $unread,
                ];
            });

        return response()->json([
            'conversations' => $conversations,
            'unread_total' => $conversations->sum('unread'),
        ]);
    }

    /** Messages in a conversation (optionally only those after ?after=id). */
    public function messages(Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $after = (int) request('after', 0);

        $messages = $conversation->messages()
            ->with('sender:id,name')
            ->when($after, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->limit(300)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'body' => $m->body,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender->name ?? '',
                'mine' => $m->sender_id === auth()->id(),
                'at' => $m->created_at->format('g:i A'),
            ]);

        $this->markRead($conversation);

        return response()->json(['messages' => $messages]);
    }

    /** Send a message to a conversation. */
    public function send(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $data = $request->validate(['body' => 'required|string|max:5000']);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'body' => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->participants()->updateExistingPivot(auth()->id(), [
            'last_read_message_id' => $message->id,
        ]);

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'mine' => true,
                'at' => $message->created_at->format('g:i A'),
            ],
        ]);
    }

    /** Start (or reuse) a conversation. 1 recipient = direct, 2+ = group. */
    public function start(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer',
            'name' => 'nullable|string|max:100',
        ]);

        $allowedIds = RecipientResolver::allowedFor($user)->pluck('id')->all();
        $targetIds = collect($data['user_ids'])
            ->map(fn ($i) => (int) $i)
            ->unique()
            ->reject(fn ($i) => $i === $user->id)
            ->values();

        if ($targetIds->isEmpty() || $targetIds->diff($allowedIds)->isNotEmpty()) {
            return response()->json(['error' => 'One or more recipients are not allowed.'], 422);
        }

        $isGroup = $targetIds->count() > 1;

        if (! $isGroup) {
            $existing = Conversation::findDirect($user->id, $targetIds->first());
            if ($existing) {
                return response()->json(['conversation_id' => $existing->id]);
            }
        }

        $conversation = Conversation::create([
            'is_group' => $isGroup,
            'name' => $isGroup ? ($data['name'] ?: null) : null,
            'created_by' => $user->id,
            'last_message_at' => now(),
        ]);

        $conversation->participants()->attach(
            $targetIds->push($user->id)->unique()->all()
        );

        return response()->json(['conversation_id' => $conversation->id]);
    }

    /** People this user may start a chat with. */
    public function recipients()
    {
        $list = RecipientResolver::allowedFor(auth()->user())->map(fn ($u) => [
            'id' => $u->id,
            'name' => $u->name,
            'role' => $u->role->name ?? 'Other',
            'dept' => $u->department->name ?? '',
        ])->values();

        return response()->json(['recipients' => $list]);
    }

    private function authorizeParticipant(Conversation $conversation): void
    {
        if (! $conversation->participants()->where('users.id', auth()->id())->exists()) {
            abort(403);
        }
    }

    private function markRead(Conversation $conversation): void
    {
        $latestId = $conversation->messages()->max('id');
        if ($latestId) {
            $conversation->participants()->updateExistingPivot(auth()->id(), [
                'last_read_message_id' => $latestId,
            ]);
        }
    }
}
