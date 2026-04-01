<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function markAsRead(): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return back();
    }
}
