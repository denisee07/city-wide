<?php

namespace App\Http\Controllers;

use App\Models\Notification as AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::where('user_id', auth()->id())
            ->orderByDesc('id')->paginate(30);
        $unread = AppNotification::where('user_id', auth()->id())->whereNull('read_at')->count();
        $portal = auth()->user()->isOfficial() ? 'admin' : 'resident';
        return view($portal === 'admin' ? 'admin.notifications.index' : 'resident.notifications.index', compact('notifications', 'unread'));
    }

    public function markRead(AppNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        $notification->markRead();
        if ($notification->action_url) {
            return redirect($notification->action_url);
        }
        return back();
    }

    public function markAllRead()
    {
        AppNotification::where('user_id', auth()->id())->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('status', 'All notifications marked as read.');
    }
}