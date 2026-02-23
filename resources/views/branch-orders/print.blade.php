<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบสั่งซื้อ {{ $branchOrder->order_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 13px;
            color: #333;
            background: #f0f0f0;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 10mm auto;
            padding: 15mm 20mm;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #4338ca;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-left h1 {
            font-size: 22px;
            font-weight: bold;
            color: #4338ca;
        }

        .header-left p {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        .header-right {
            text-align: right;
        }

        .header-right .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .header-right .order-date {
            font-size: 11px;
            color: #666;
            margin-top: 2px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-approved {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-shipped {
            background: #e9d5ff;
            color: #6b21a8;
        }

        .status-received {
            background: #d1fae5;
            color: #065f46;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Info Section */
        .info-section {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .info-box {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 15px;
        }

        .info-box h3 {
            font-size: 11px;
            font-weight: bold;
            color: #9ca3af;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-box table {
            width: 100%;
        }

        .info-box td {
            padding: 2px 0;
            font-size: 12px;
        }

        .info-box td:first-child {
            color: #6b7280;
            width: 80px;
        }

        .info-box td:last-child {
            text-align: right;
            font-weight: 500;
        }

        /* Items Table */
        .items-section {
            margin-bottom: 20px;
        }

        .items-section h2 {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead th {
            background: #f9fafb;
            padding: 8px 12px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }

        .items-table thead th.text-center {
            text-align: center;
        }

        .items-table thead th.text-right {
            text-align: right;
        }

        .items-table tbody td {
            padding: 8px 12px;
            font-size: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .items-table tbody td.text-center {
            text-align: center;
        }

        .items-table tbody td.text-right {
            text-align: right;
        }

        .items-table tfoot td {
            padding: 10px 12px;
            font-weight: bold;
            border-top: 2px solid #e5e7eb;
        }

        .item-name {
            font-weight: 500;
            color: #111827;
        }

        .item-note {
            font-size: 10px;
            color: #9ca3af;
        }

        /* Notes */
        .notes-section {
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
        }

        .notes-section h3 {
            font-size: 12px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 4px;
        }

        .notes-section p {
            font-size: 12px;
            color: #78350f;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }

        .signature-box {
            width: 30%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #9ca3af;
            margin-top: 50px;
            padding-top: 6px;
        }

        .signature-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
        }

        .signature-sub {
            font-size: 10px;
            color: #9ca3af;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
            }

            .page {
                margin: 0;
                padding: 10mm 15mm;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Print Button */
        .print-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: #4338ca;
            color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .print-bar button {
            padding: 8px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-print {
            background: white;
            color: #4338ca;
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .btn-print:hover {
            background: #e0e7ff;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body>
    <!-- Print Bar -->
    <div class="print-bar no-print">
        <button class="btn-back" onclick="window.close()">← กลับ</button>
        <button class="btn-print" onclick="window.print()">🖨️ พิมพ์ใบสั่งซื้อ</button>
    </div>

    <div class="page" style="margin-top: 60px;">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>ใบสั่งซื้อสินค้า</h1>
                <p>Purchase Order - ระหว่างสาขา</p>
            </div>
            <div class="header-right">
                <div class="order-number">{{ $branchOrder->order_number }}</div>
                <div class="order-date">วันที่: {{ $branchOrder->created_at->format('d/m/Y H:i') }}</div>
                @php
                $statusLabels = \App\Models\BranchOrder::getStatuses();
                $statusClass = 'status-' . $branchOrder->status;
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusLabels[$branchOrder->status] ?? $branchOrder->status }}</span>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="info-box">
                <h3>สาขาที่สั่ง (ผู้ซื้อ)</h3>
                <table>
                    <tr>
                        <td>สาขา:</td>
                        <td>{{ $branchOrder->branch->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ที่อยู่:</td>
                        <td>{{ $branchOrder->branch->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>โทร:</td>
                        <td>{{ $branchOrder->branch->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ผู้สั่ง:</td>
                        <td>{{ $branchOrder->createdBy->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div class="info-box">
                <h3>สาขาใหญ่ (ผู้จัดส่ง)</h3>
                <table>
                    <tr>
                        <td>สาขา:</td>
                        <td>{{ $branchOrder->mainBranch->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>ที่อยู่:</td>
                        <td>{{ $branchOrder->mainBranch->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>โทร:</td>
                        <td>{{ $branchOrder->mainBranch->phone ?? '-' }}</td>
                    </tr>
                    @if($branchOrder->approvedBy)
                    <tr>
                        <td>อนุมัติ:</td>
                        <td>{{ $branchOrder->approvedBy->name }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Items Table -->
        <div class="items-section">
            <h2>รายการสินค้า</h2>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">#</th>
                        <th>สินค้า</th>
                        <th class="text-center" style="width: 70px;">สั่ง</th>
                        @if(in_array($branchOrder->status, ['approved','preparing','shipped','received']))
                        <th class="text-center" style="width: 70px;">อนุมัติ</th>
                        @endif
                        @if(in_array($branchOrder->status, ['shipped','received']))
                        <th class="text-center" style="width: 70px;">จัดส่ง</th>
                        @endif
                        @if($branchOrder->status === 'received')
                        <th class="text-center" style="width: 70px;">รับ</th>
                        @endif
                        <th class="text-right" style="width: 80px;">ราคาทุน</th>
                        <th class="text-right" style="width: 90px;">รวม</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($branchOrder->items as $i => $item)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <span class="item-name">{{ $item->product_name }}</span>
                            @if($item->notes)
                            <br><span class="item-note">{{ $item->notes }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity_requested }}</td>
                        @if(in_array($branchOrder->status, ['approved','preparing','shipped','received']))
                        <td class="text-center">{{ $item->quantity_approved ?? '-' }}</td>
                        @endif
                        @if(in_array($branchOrder->status, ['shipped','received']))
                        <td class="text-center">{{ $item->quantity_shipped ?? '-' }}</td>
                        @endif
                        @if($branchOrder->status === 'received')
                        <td class="text-center">{{ $item->quantity_received ?? '-' }}</td>
                        @endif
                        <td class="text-right">฿{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="text-right">฿{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        @php
                        $colspan = 4;
                        if(in_array($branchOrder->status, ['approved','preparing','shipped','received'])) $colspan++;
                        if(in_array($branchOrder->status, ['shipped','received'])) $colspan++;
                        if($branchOrder->status === 'received') $colspan++;
                        @endphp
                        <td colspan="{{ $colspan }}" style="text-align: right;">รวมทั้งหมด ({{ $branchOrder->items->count() }} รายการ)</td>
                        <td style="text-align: right; font-size: 15px; color: #4338ca;">฿{{ number_format($branchOrder->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Notes -->
        @if($branchOrder->notes)
        <div class="notes-section">
            <h3>📝 หมายเหตุ</h3>
            <p>{{ $branchOrder->notes }}</p>
        </div>
        @endif

        <!-- Timeline -->
        <div style="margin-bottom: 20px; font-size: 11px; color: #6b7280;">
            <strong>ประวัติ:</strong>
            สร้าง: {{ $branchOrder->created_at->format('d/m/Y H:i') }}
            @if($branchOrder->approved_at)
            &nbsp;|&nbsp; อนุมัติ: {{ $branchOrder->approved_at->format('d/m/Y H:i') }} ({{ $branchOrder->approvedBy->name ?? '-' }})
            @endif
            @if($branchOrder->shipped_at)
            &nbsp;|&nbsp; จัดส่ง: {{ $branchOrder->shipped_at->format('d/m/Y H:i') }} ({{ $branchOrder->shippedBy->name ?? '-' }})
            @endif
            @if($branchOrder->received_at)
            &nbsp;|&nbsp; รับสินค้า: {{ $branchOrder->received_at->format('d/m/Y H:i') }} ({{ $branchOrder->receivedBy->name ?? '-' }})
            @endif
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">ผู้สั่งซื้อ</div>
                    <div class="signature-sub">{{ $branchOrder->createdBy->name ?? '________________' }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">ผู้อนุมัติ / ผู้จัดส่ง</div>
                    <div class="signature-sub">{{ $branchOrder->approvedBy->name ?? '________________' }}</div>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-label">ผู้รับสินค้า</div>
                    <div class="signature-sub">{{ $branchOrder->receivedBy->name ?? '________________' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>