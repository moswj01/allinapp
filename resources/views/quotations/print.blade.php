<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ใบเสนอราคา {{ $quotation->quotation_number }}</title>
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
            align-items: flex-start;
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

        .terms {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .terms h3 {
            font-size: 13px;
            color: #6b7280;
            margin: 0 0 5px;
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

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
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
        <button onclick="window.print()" style="padding:8px 20px;background:#4f46e5;color:white;border:none;border-radius:6px;cursor:pointer;font-size:14px;">🖨️ พิมพ์</button>
        <button onclick="window.close()" style="padding:8px 20px;background:#6b7280;color:white;border:none;border-radius:6px;cursor:pointer;font-size:14px;margin-left:8px;">✕ ปิด</button>
    </div>

    <div class="header">
        <div>
            <div class="company-name">All In Mobile</div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px;">{{ $quotation->branch->name ?? '' }}</div>
        </div>
        <div>
            <div class="doc-title">ใบเสนอราคา / Quotation</div>
            <div style="font-size:13px;text-align:right;margin-top:4px;">เลขที่: {{ $quotation->quotation_number }}</div>
            <div style="font-size:13px;text-align:right;">วันที่: {{ $quotation->created_at->format('d/m/Y') }}</div>
            @if($quotation->valid_until)
            <div style="font-size:13px;text-align:right;">หมดอายุ: {{ $quotation->valid_until->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    <div class="info-grid">
        <div class="info-box">
            <h3>ลูกค้า</h3>
            <div class="info-row"><span>ชื่อ:</span><span>{{ $quotation->customer_name }}</span></div>
            @if($quotation->customer_phone)<div class="info-row"><span>โทร:</span><span>{{ $quotation->customer_phone }}</span></div>@endif
            @if($quotation->customer_email)<div class="info-row"><span>อีเมล:</span><span>{{ $quotation->customer_email }}</span></div>@endif
        </div>
        <div class="info-box">
            <h3>ข้อมูลเอกสาร</h3>
            <div class="info-row"><span>ผู้สร้าง:</span><span>{{ $quotation->createdBy->name ?? '-' }}</span></div>
            @if($quotation->subject)<div class="info-row"><span>หัวข้อ:</span><span>{{ $quotation->subject }}</span></div>@endif
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
            @foreach($quotation->items as $i => $item)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $item->item_name }}@if($item->description)<br><small style="color:#6b7280;">{{ $item->description }}</small>@endif</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" class="text-right">รวมก่อนส่วนลด:</td>
                <td class="text-right">{{ number_format($quotation->subtotal, 2) }}</td>
            </tr>
            @if($quotation->discount_amount > 0)
            <tr class="total-row">
                <td colspan="4" class="text-right">ส่วนลด:</td>
                <td class="text-right" style="color:red;">-{{ number_format($quotation->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if($quotation->tax_amount > 0)
            <tr class="total-row">
                <td colspan="4" class="text-right">ภาษี:</td>
                <td class="text-right">{{ number_format($quotation->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row grand-total">
                <td colspan="4" class="text-right">รวมทั้งหมด:</td>
                <td class="text-right">{{ number_format($quotation->total, 2) }} บาท</td>
            </tr>
        </tfoot>
    </table>

    @if($quotation->terms)
    <div class="terms">
        <h3>เงื่อนไข</h3>
        <p style="margin:0;white-space:pre-line;">{{ $quotation->terms }}</p>
    </div>
    @endif

    <div class="signature">
        <div>
            <div class="sig-line">ผู้เสนอราคา<br>{{ $quotation->createdBy->name ?? '' }}<br>วันที่ {{ $quotation->created_at->format('d/m/Y') }}</div>
        </div>
        <div>
            <div class="sig-line">ผู้อนุมัติ<br>&nbsp;<br>วันที่ ____/____/________</div>
        </div>
    </div>
</body>

</html>