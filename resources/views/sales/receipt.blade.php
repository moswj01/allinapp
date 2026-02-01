<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จ {{ $sale->sale_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            font-size: 12px;
            width: 80mm;
            padding: 5mm;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .shop-name {
            font-size: 18px;
            font-weight: bold;
        }

        .shop-info {
            font-size: 10px;
            color: #666;
        }

        .receipt-info {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .receipt-info table {
            width: 100%;
        }

        .receipt-info td {
            font-size: 11px;
        }

        .items table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            text-align: left;
            font-size: 11px;
            padding: 5px 0;
            border-bottom: 1px solid #000;
        }

        .items th:last-child {
            text-align: right;
        }

        .items td {
            padding: 5px 0;
            font-size: 11px;
            vertical-align: top;
        }

        .items td:last-child {
            text-align: right;
        }

        .item-name {
            font-weight: 500;
        }

        .item-detail {
            font-size: 10px;
            color: #666;
        }

        .totals {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #000;
        }

        .totals table {
            width: 100%;
        }

        .totals td {
            padding: 3px 0;
        }

        .totals td:last-child {
            text-align: right;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            text-align: center;
        }

        .thank-you {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-info {
            font-size: 10px;
            color: #666;
        }

        @media print {
            body {
                width: 80mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="shop-name">{{ $sale->branch->name ?? 'ร้านค้า' }}</div>
        <div class="shop-info">
            {{ $sale->branch->address ?? '' }}<br>
            โทร: {{ $sale->branch->phone ?? '-' }}
        </div>
    </div>

    <div class="receipt-info">
        <table>
            <tr>
                <td>เลขที่:</td>
                <td style="text-align: right; font-weight: bold;">{{ $sale->sale_number }}</td>
            </tr>
            <tr>
                <td>วันที่:</td>
                <td style="text-align: right;">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>ผู้ขาย:</td>
                <td style="text-align: right;">{{ $sale->createdBy->name ?? '-' }}</td>
            </tr>
            @if($sale->customer)
            <tr>
                <td>ลูกค้า:</td>
                <td style="text-align: right;">{{ $sale->customer->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>รายการ</th>
                    <th>รวม</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>
                        <div class="item-name">{{ $item->item_name }}</div>
                        <div class="item-detail">{{ $item->quantity }} x ฿{{ number_format($item->unit_price, 0) }}</div>
                    </td>
                    <td>฿{{ number_format($item->total, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals">
        <table>
            <tr>
                <td>รวม ({{ $sale->items->count() }} รายการ)</td>
                <td>฿{{ number_format($sale->subtotal, 0) }}</td>
            </tr>
            @if($sale->discount > 0)
            <tr>
                <td>ส่วนลด</td>
                <td>-฿{{ number_format($sale->discount, 0) }}</td>
            </tr>
            @endif
            @if($sale->vat > 0)
            <tr>
                <td>ภาษี</td>
                <td>฿{{ number_format($sale->vat, 0) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>รวมสุทธิ</td>
                <td>฿{{ number_format($sale->total, 0) }}</td>
            </tr>
            <tr>
                <td>ชำระโดย</td>
                <td>
                    @php
                    $methodNames = [
                    'cash' => 'เงินสด',
                    'transfer' => 'โอนเงิน',
                    'qr' => 'QR Payment',
                    'card' => 'บัตรเครดิต',
                    'credit' => 'เครดิต',
                    ];
                    @endphp
                    {{ $methodNames[$sale->payment_method] ?? $sale->payment_method }}
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <div class="thank-you">ขอบคุณที่ใช้บริการ</div>
        <div class="footer-info">
            สินค้าที่ซื้อแล้วไม่สามารถเปลี่ยนคืนได้<br>
            ยกเว้นมีการรับประกันตามเงื่อนไข
        </div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14px; cursor: pointer;">
            🖨️ พิมพ์ใบเสร็จ
        </button>
    </div>

    <script>
        // Auto print
        window.onload = function() {
            // window.print();
        }
    </script>
</body>

</html>