<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List the authenticated user's notifications (newest first, paginated).
     */
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $unreadCount = auth()->user()->unreadNotifications->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a single notification as read, then redirect.
     *
     * Honors an optional ?redirect= query param (used by the "View details"
     * link so an unread item is marked read before landing on its target URL);
     * otherwise falls back to the previous page.
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->filled('redirect')) {
            $target = $request->query('redirect');

            // Open-redirect guard: only honor internal, same-app relative paths.
            // Must start with a single '/' (not '//' or '/\', which browsers treat
            // as protocol-relative absolute URLs to another host) and must not
            // contain a scheme like "http:" or "javascript:".
            if (is_string($target)
                && preg_match('#^/(?![/\\\\])#', $target)
                && ! preg_match('#^\s*[a-z][a-z0-9+.\-]*:#i', $target)) {
                return redirect()->to($target);
            }

            return redirect()->route('notifications.index');
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark every unread notification as read, then redirect back.
     */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Delete a single notification, then redirect back.
     */
    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', 'Notification deleted.');
    }
}
