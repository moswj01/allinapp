@extends('layouts.app')

@section('title', 'การแจ้งเตือน')
@section('page-title', 'การแจ้งเตือน')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">การแจ้งเตือน</h2>
            <p class="text-gray-500">
                @if($unreadCount > 0)
                <span class="text-red-600 font-semibold">{{ $unreadCount }} รายการที่ยังไม่อ่าน</span>
                @else
                อ่านทั้งหมดแล้ว
                @endif
            </p>
        </div>
        <div class="flex items-center space-x-3">
            @if($unreadCount > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm"><i class="fas fa-check-double mr-1"></i>อ่านทั้งหมด</button>
            </form>
            @endif
            @if(request('unread') === '1')
            <a href="{{ route('notifications.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm">แสดงทั้งหมด</a>
            @else
            <a href="{{ route('notifications.index', ['unread' => 1]) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 text-sm"><i class="fas fa-filter mr-1"></i>ยังไม่อ่าน</a>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @forelse($notifications as $notification)
        <div class="flex items-start p-5 border-b hover:bg-gray-50 {{ $notification->is_read ? 'bg-white' : 'bg-blue-50' }}">
            <div class="flex-shrink-0 mr-4 mt-1">
                @php
                $iconMap = [
                'info' => 'fas fa-info-circle text-blue-500',
                'success' => 'fas fa-check-circle text-green-500',
                'warning' => 'fas fa-exclamation-triangle text-yellow-500',
                'error' => 'fas fa-times-circle text-red-500',
                'repair_assigned' => 'fas fa-tools text-indigo-500',
                'repair_status_changed' => 'fas fa-sync text-blue-500',
                'repair_completed' => 'fas fa-check-circle text-green-500',
                'low_stock_alert' => 'fas fa-exclamation-triangle text-orange-500',
                'reorder_alert' => 'fas fa-shopping-cart text-red-500',
                'payment_received' => 'fas fa-money-bill text-green-500',
                'ar_overdue' => 'fas fa-clock text-red-500',
                'transfer_received' => 'fas fa-exchange-alt text-blue-500',
                'po_approved' => 'fas fa-clipboard-check text-green-500',
                ];
                $icon = $iconMap[$notification->type] ?? 'fas fa-bell text-gray-500';
                @endphp
                <i class="{{ $icon }} text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-900 {{ !$notification->is_read ? 'font-bold' : '' }}">
                        {{ $notification->title }}
                        @if(!$notification->is_read)
                        <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-1"></span>
                        @endif
                    </h4>
                    <span class="text-xs text-gray-400 flex-shrink-0 ml-4">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                <div class="flex items-center mt-2 space-x-3">
                    @if(!$notification->is_read)
                    <form action="{{ route('notifications.read', $notification) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800"><i class="fas fa-check mr-1"></i>อ่านแล้ว</button>
                    </form>
                    @endif
                    @if($notification->link)
                    <a href="{{ route('notifications.read', $notification) }}" class="text-xs text-indigo-600 hover:text-indigo-800"><i class="fas fa-external-link-alt mr-1"></i>ดูรายละเอียด</a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center text-gray-500">
            <i class="fas fa-bell-slash text-5xl mb-4 text-gray-300"></i>
            <p class="text-lg">ไม่มีการแจ้งเตือน</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="mt-4">{{ $notifications->links() }}</div>
    @endif
</div>
@endsection