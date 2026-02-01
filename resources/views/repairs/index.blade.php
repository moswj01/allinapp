@extends('layouts.app')

@section('title', 'งานซ่อม - Kanban')
@section('page-title', 'งานซ่อม (Kanban Board)')

@push('styles')
<style>
    .kanban-column {
        min-height: 500px;
        max-height: calc(100vh - 280px);
    }

    .kanban-card {
        cursor: grab;
    }

    .kanban-card:active {
        cursor: grabbing;
    }

    .kanban-card.dragging {
        opacity: 0.5;
    }

    .kanban-column.drag-over {
        background-color: #e0e7ff;
    }
</style>
@endpush

@section('content')
<div class="space-y-4">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center space-x-4">
            <a href="{{ route('repairs.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>
                รับงานซ่อมใหม่
            </a>
        </div>
        <div class="flex items-center space-x-2">
            <input type="text"
                placeholder="ค้นหาเลขที่งาน, ลูกค้า..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="flex gap-4 overflow-x-auto pb-4" x-data="kanbanBoard()">
        @php
        $kanbanStatuses = [
        'pending' => ['name' => 'รอซ่อม', 'color' => 'gray', 'icon' => 'inbox'],
        'waiting_parts' => ['name' => 'รออะไหล่', 'color' => 'orange', 'icon' => 'box'],
        'quoted' => ['name' => 'เสนอราคา', 'color' => 'purple', 'icon' => 'file-invoice'],
        'confirmed' => ['name' => 'ลูกค้ายืนยัน', 'color' => 'blue', 'icon' => 'check-circle'],
        'in_progress' => ['name' => 'กำลังซ่อม', 'color' => 'yellow', 'icon' => 'wrench'],
        'qc' => ['name' => 'ตรวจ QC', 'color' => 'cyan', 'icon' => 'clipboard-check'],
        'completed' => ['name' => 'ซ่อมเสร็จ', 'color' => 'green', 'icon' => 'check-double'],
        ];
        @endphp

        @foreach($kanbanStatuses as $statusKey => $statusInfo)
        <div class="flex-shrink-0 w-80">
            <div class="bg-{{ $statusInfo['color'] }}-100 rounded-t-xl px-4 py-3 border-b-4 border-{{ $statusInfo['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-{{ $statusInfo['icon'] }} text-{{ $statusInfo['color'] }}-600 mr-2"></i>
                        <h3 class="font-semibold text-{{ $statusInfo['color'] }}-800">{{ $statusInfo['name'] }}</h3>
                    </div>
                    <span class="bg-{{ $statusInfo['color'] }}-200 text-{{ $statusInfo['color'] }}-800 px-2 py-1 rounded-full text-xs font-bold">
                        {{ isset($repairs[$statusKey]) ? $repairs[$statusKey]->count() : 0 }}
                    </span>
                </div>
            </div>

            <div
                class="kanban-column bg-gray-50 rounded-b-xl p-3 space-y-3 overflow-y-auto scrollbar-thin"
                data-status="{{ $statusKey }}"
                @dragover.prevent="dragOver($event)"
                @dragleave="dragLeave($event)"
                @drop="drop($event, '{{ $statusKey }}')">
                @foreach($repairs[$statusKey] ?? [] as $repair)
                <div
                    class="kanban-card bg-white rounded-lg shadow-sm p-4 border border-gray-200 hover:shadow-md transition-shadow"
                    draggable="true"
                    data-repair-id="{{ $repair->id }}"
                    @dragstart="dragStart($event, {{ $repair->id }})"
                    @dragend="dragEnd($event)">
                    <!-- Card Header -->
                    <div class="flex items-center justify-between mb-2">
                        <a href="{{ route('repairs.show', $repair) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">
                            {{ $repair->repair_number }}
                        </a>
                        @if($repair->priority === 'urgent')
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full">
                            <i class="fas fa-exclamation mr-1"></i>ด่วน
                        </span>
                        @elseif($repair->priority === 'vip')
                        <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full">
                            <i class="fas fa-star mr-1"></i>VIP
                        </span>
                        @endif
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-3">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $repair->customer_name }}</p>
                        <p class="text-xs text-gray-500">{{ $repair->customer_phone }}</p>
                    </div>

                    <!-- Device Info -->
                    <div class="bg-gray-50 rounded-lg px-3 py-2 mb-3">
                        <p class="text-xs text-gray-600">
                            <i class="fas fa-mobile-alt mr-1"></i>
                            {{ $repair->device_brand }} {{ $repair->device_model }}
                        </p>
                        <p class="text-xs text-gray-500 truncate mt-1">{{ Str::limit($repair->problem_description, 50) }}</p>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <div class="flex items-center">
                            @if($repair->technician)
                            <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center mr-1" title="{{ $repair->technician->name }}">
                                <span class="text-indigo-600 font-medium">{{ substr($repair->technician->name, 0, 1) }}</span>
                            </div>
                            @else
                            <span class="text-orange-500"><i class="fas fa-user-slash mr-1"></i>ยังไม่มอบหมาย</span>
                            @endif
                        </div>
                        <span title="วันที่รับ">
                            <i class="far fa-clock mr-1"></i>
                            {{ $repair->received_at->diffForHumans() }}
                        </span>
                    </div>

                    @if($repair->total_cost > 0)
                    <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between">
                        <span class="text-xs text-gray-500">ค่าซ่อม:</span>
                        <span class="text-sm font-semibold text-green-600">฿{{ number_format($repair->total_cost, 0) }}</span>
                    </div>
                    @endif
                </div>
                @endforeach

                @if(!isset($repairs[$statusKey]) || $repairs[$statusKey]->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-inbox text-3xl mb-2"></i>
                    <p class="text-sm">ไม่มีงาน</p>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    function kanbanBoard() {
        return {
            draggedId: null,

            dragStart(event, repairId) {
                this.draggedId = repairId;
                event.target.classList.add('dragging');
                event.dataTransfer.effectAllowed = 'move';
            },

            dragEnd(event) {
                event.target.classList.remove('dragging');
                document.querySelectorAll('.kanban-column').forEach(col => {
                    col.classList.remove('drag-over');
                });
            },

            dragOver(event) {
                event.currentTarget.classList.add('drag-over');
            },

            dragLeave(event) {
                event.currentTarget.classList.remove('drag-over');
            },

            async drop(event, newStatus) {
                event.currentTarget.classList.remove('drag-over');

                if (!this.draggedId) return;

                try {
                    const response = await fetch('{{ route("repairs.kanban") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            repair_id: this.draggedId,
                            status: newStatus
                        })
                    });

                    if (response.ok) {
                        // Move the card visually
                        const card = document.querySelector(`[data-repair-id="${this.draggedId}"]`);
                        const column = document.querySelector(`[data-status="${newStatus}"]`);
                        if (card && column) {
                            column.appendChild(card);
                            // Update count badges
                            this.updateCounts();
                        }
                    } else {
                        alert('ไม่สามารถอัปเดตสถานะได้');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
                }

                this.draggedId = null;
            },

            updateCounts() {
                document.querySelectorAll('.kanban-column').forEach(column => {
                    const status = column.dataset.status;
                    const count = column.querySelectorAll('.kanban-card').length;
                    const badge = column.previousElementSibling.querySelector('span:last-child');
                    if (badge) {
                        badge.textContent = count;
                    }
                });
            }
        }
    }
</script>
@endpush
@endsection