<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบสั่งซื้อ {{ $purchaseOrder->po_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }

            @page {
                margin: 15mm;
                size: A4;
            }
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 14px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            border-bottom: 3px solid #4f46e5;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }

        .doc-title {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            color: #4f46e5;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
        }

        .info-box h3 {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            margin: 0 0 8px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
            font-size: 13px;
        }

        td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            font-size: 13px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row td {
            font-weight: bold;
            background: #f9fafb;
        }

        .grand-total td {
            font-size: 16px;
            color: #4f46e5;
        }

        .signature {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 60px;
            text-align: center;
        }

        .sig-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 13px;
        }

        .no-print {
            margin-bottom: 20px;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 20px;background:#4f46e5;color:white;border:none;border-radius:6px;cursor:pointer;">🖨️ พิมพ์</button>
        <button onclick="window.close()" style="padding:8px 20px;background:#6b7280;color:white;border:none;border-radius:6px;cursor:pointer;margin-left:8px;">✕ ปิด</button>
    </div>

    <div class="header">
        <div>
            <div class="company-name">All In Mobile</div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px;">{{ $purchaseOrder->branch->name ?? '' }}</div>
        </div>
        <div>
            <div class="doc-title">ใบสั่งซื้อ / Purchase Order</div>
            <div style="font-size:13px;text-align:right;margin-top:4px;">เลขที่: {{ $purchaseOrder->po_number }}</div>
            <div style="font-size:13px;text-align:right;">วันที่: {{ $purchaseOrder->created_at->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>ซัพพลายเออร์</h3>
            <div class="info-row"><span>ชื่อ:</span><span>{{ $purchaseOrder->supplier->name ?? '-' }}</span></div>
            @if($purchaseOrder->supplier && $purchaseOrder->supplier->phone)<div class="info-row"><span>โทร:</span><span>{{ $purchaseOrder->supplier->phone }}</span></div>@endif
            @if($purchaseOrder->supplier && $purchaseOrder->supplier->email)<div class="info-row"><span>อีเมล:</span><span>{{ $purchaseOrder->supplier->email }}</span></div>@endif
        </div>
        <div class="info-box">
            <h3>ข้อมูลเอกสาร</h3>
            <div class="info-row"><span>ผู้สร้าง:</span><span>{{ $purchaseOrder->createdBy->name ?? '-' }}</span></div>
            @if($purchaseOrder->expected_date)<div class="info-row"><span>คาดว่าจะรับ:</span><span>{{ $purchaseOrder->expected_date->format('d/m/Y') }}</span></div>@endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width:40px;">#</th>
                <th>รายการ</th>
                <th class="text-center" style="width:80px;">จำนวน</th>
                <th class="text-right" style="width:120px;">ราคาต่อหน่วย</th>
                <th class="text-right" style="width:120px;">รวม</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->item_name }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">รวม:</td>
                <td class="text-right">{{ number_format($purchaseOrder->subtotal, 2) }}</td>
            </tr>
            @if($purchaseOrder->discount_amount > 0)<tr class="total-row">
                <td colspan="4" class="text-right">ส่วนลด:</td>
                <td class="text-right" style="color:red;">-{{ number_format($purchaseOrder->discount_amount, 2) }}</td>
            </tr>@endif
            @if($purchaseOrder->tax_amount > 0)<tr class="total-row">
                <td colspan="4" class="text-right">ภาษี:</td>
                <td class="text-right">{{ number_format($purchaseOrder->tax_amount, 2) }}</td>
            </tr>@endif
            <tr class="total-row grand-total">
                <td colspan="4" class="text-right">รวมทั้งหมด:</td>
                <td class="text-right">{{ number_format($purchaseOrder->total, 2) }} บาท</td>
            </tr>
        </tfoot>
    </table>

    @if($purchaseOrder->terms)<div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-bottom:20px;"><strong>เงื่อนไข:</strong><br>{{ $purchaseOrder->terms }}</div>@endif

    <div class="signature">
        <div>
            <div class="sig-line">ผู้สั่งซื้อ<br>{{ $purchaseOrder->createdBy->name ?? '' }}</div>
        </div>
        <div>
            <div class="sig-line">ผู้อนุมัติ<br>{{ $purchaseOrder->approvedBy->name ?? '' }}</div>
        </div>
    </div>
</body>

</html>