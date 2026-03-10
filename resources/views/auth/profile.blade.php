@extends('layouts.app')

@section('title', 'โปรไฟล์')
@section('page-title', 'โปรไฟล์ของฉัน')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Success --}}
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
        <i class="fas fa-check-circle"></i>
        {{ session('success') }}
    </div>
    @endif

    {{-- Errors --}}
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-600 rounded-lg px-4 py-3 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Profile Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-circle text-indigo-500"></i>
                ข้อมูลส่วนตัว
            </h3>
        </div>
        <form action="{{ route('profile.update') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">ชื่อ-นามสกุล</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">อีเมล</label>
                    <input type="email" value="{{ $user->email }}" disabled
                        class="w-full px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">เบอร์โทร</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition"
                        placeholder="08X-XXX-XXXX">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">รหัสพนักงาน</label>
                    <input type="text" value="{{ $user->employee_code ?? '-' }}" disabled
                        class="w-full px-3 py-2 text-sm border border-gray-100 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-save text-xs"></i>
                    บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-key text-amber-500"></i>
                เปลี่ยนรหัสผ่าน
            </h3>
        </div>
        <form action="{{ route('profile.password') }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">รหัสผ่านปัจจุบัน</label>
                    <input type="password" name="current_password" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition"
                        placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">รหัสผ่านใหม่</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition"
                        placeholder="อย่างน้อย 8 ตัวอักษร">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 outline-none transition"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 transition">
                    <i class="fas fa-key text-xs"></i>
                    เปลี่ยนรหัสผ่าน
                </button>
            </div>
        </form>
    </div>

    {{-- Account Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-info-circle text-gray-400"></i>
                ข้อมูลบัญชี
            </h3>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-y-3 gap-x-6 text-sm">
                <div class="flex justify-between md:block">
                    <dt class="text-gray-400 text-xs uppercase tracking-wide">สิทธิ์การใช้งาน</dt>
                    <dd class="font-semibold text-gray-800 md:mt-0.5">{{ $user->role->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between md:block">
                    <dt class="text-gray-400 text-xs uppercase tracking-wide">สาขา</dt>
                    <dd class="font-semibold text-gray-800 md:mt-0.5">{{ $user->branch->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between md:block">
                    <dt class="text-gray-400 text-xs uppercase tracking-wide">สร้างเมื่อ</dt>
                    <dd class="font-semibold text-gray-800 md:mt-0.5">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="flex justify-between md:block">
                    <dt class="text-gray-400 text-xs uppercase tracking-wide">สถานะ</dt>
                    <dd class="md:mt-0.5">
                        @if($user->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span>ใช้งาน
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span>ปิดการใช้งาน
                        </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
