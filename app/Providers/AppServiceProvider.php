<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share notification data with all views using the app layout
        View::composer('layouts.app', function ($view) {
            if (Auth::check()) {
                $userId = Auth::id();
                $unreadCount = Notification::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $recentNotifications = Notification::where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();

                $view->with('unreadNotificationCount', $unreadCount);
                $view->with('recentNotifications', $recentNotifications);
            }
        });
    }
}
