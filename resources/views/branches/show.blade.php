@extends('layouts.app')

@section('title', 'ข้อมูลสาขา')
@section('page-title', 'สาขา: ' . $branch->name)

@section('content')
<div class="space-y-6">
    <!-- Branch Info -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $branch->name }}</h2>
                <p class="text-gray-500">รหัส: {{ $branch->code }}</p>
            </div>
            <div class="flex space-x-2">
                @if($branch->is_active)
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">เปิดใช้งาน</span>
                @else
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">ปิดใช้งาน</span>
                @endif
                <a href="{{ route('branches.edit', $branch) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-edit mr-2"></i>แก้ไข
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">ข้อมูลติดต่อ</h4>
                <div class="space-y-2">
                    <p><i class="fas fa-phone text-gray-400 w-6"></i> {{ $branch->phone ?? '-' }}</p>
                    <p><i class="fas fa-envelope text-gray-400 w-6"></i> {{ $branch->email ?? '-' }}</p>
                    <p><i class="fas fa-map-marker-alt text-gray-400 w-6"></i> {{ $branch->address ?? '-' }}</p>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">สถิติ</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 bg-indigo-50 rounded-lg text-center">
                        <p class="text-3xl font-bold text-indigo-600">{{ $branch->users_count ?? count($users) }}</p>
                        <p class="text-sm text-gray-500">พนักงาน</p>
                    </div>
                    <div class="p-4 bg-yellow-50 rounded-lg text-center">
                        <p class="text-3xl font-bold text-yellow-600">{{ $monthlyRepairs }}</p>
                        <p class="text-sm text-gray-500">งานซ่อม/เดือน</p>
                    </div>
                    <div class="p-4 bg-green-50 rounded-lg text-center">
                        <p class="text-3xl font-bold text-green-600">฿{{ number_format($monthlySales) }}</p>
                        <p class="text-sm text-gray-500">ยอดขาย/เดือน</p>
                    </div>
                    <div class="p-4 bg-blue-50 rounded-lg text-center">
                        <p class="text-3xl font-bold text-blue-600">{{ $todayRepairs }}</p>
                        <p class="text-sm text-gray-500">งานซ่อมวันนี้</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff List -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">
            <i class="fas fa-users text-indigo-500 mr-2"></i>พนักงานในสาขา
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">บทบาท</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">อีเมล</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->role?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @if($user->is_active)
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">ใช้งาน</span>
                            @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full">ปิดใช้งาน</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">ยังไม่มีพนักงานในสาขา</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-start">
        <a href="{{ route('branches.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
    </div>
</div>
@endsection