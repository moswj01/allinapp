<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเสร็จ {{ $sale->sale_number }}</title>
    @php
    $shopName = ($template['sales_receipt_shop_name'] ?? '') ?: ($sale->branch->name ?? 'ร้านค้า');
    $shopInfo = ($template['sales_receipt_shop_info'] ?? '') ?: (($sale->branch->address ?? '') . "\nโทร: " . ($sale->branch->phone ?? '-'));
    $logoUrl = $template['sales_receipt_logo_url'] ?? '';
    $showLogo = filter_var($template['sales_receipt_show_logo'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $paperWidth = ($template['sales_receipt_paper_width'] ?? '80') . 'mm';
    $fontSize = ($template['sales_receipt_font_size'] ?? '12') . 'px';
    $showCustomer = filter_var($template['sales_receipt_show_customer'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showSeller = filter_var($template['sales_receipt_show_seller'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showVat = filter_var($template['sales_receipt_show_vat'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showPayment = filter_var($template['sales_receipt_show_payment'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerThank = $template['sales_receipt_footer_thank'] ?? 'ขอบคุณที่ใช้บริการ';
    $footerText = $template['sales_receipt_footer_text'] ?? "สินค้าที่ซื้อแล้วไม่สามารถเปลี่ยนคืนได้\nยกเว้นมีการรับประกันตามเงื่อนไข";
    $baseFontSize = intval($template['sales_receipt_font_size'] ?? 12);
    @endphp
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
            font-family: 'Sarabun', ui-sans-serif, system-ui, sans-serif;

            font-size: {
                    {
                    $fontSize
                }
            }

            ;

            width: {
                    {
                    $paperWidth
                }
            }

            ;
            padding: 5mm;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }

        .shop-logo {
            max-height: 40px;
            margin-bottom: 5px;
        }

        .shop-name {
            font-size: {
                    {
                    $baseFontSize +6
                }
            }

            px;
            font-weight: bold;
        }

        .shop-info {
            font-size: {
                    {
                    $baseFontSize - 2
                }
            }

            px;
            color: #666;
            white-space: pre-line;
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
            font-size: {
                    {
                    $baseFontSize - 1
                }
            }

            px;
        }

        .items table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            text-align: left;

            font-size: {
                    {
                    $baseFontSize - 1
                }
            }

            px;
            padding: 5px 0;
            border-bottom: 1px solid #000;
        }

        .items th:last-child {
            text-align: right;
        }

        .items td {
            padding: 5px 0;

            font-size: {
                    {
                    $baseFontSize - 1
                }
            }

            px;
            vertical-align: top;
        }

        .items td:last-child {
            text-align: right;
        }

        .item-name {
            font-weight: 500;
        }

        .item-detail {
            font-size: {
                    {
                    $baseFontSize - 2
                }
            }

            px;
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
            font-size: {
                    {
                    $baseFontSize +4
                }
            }

            px;
            font-weight: bold;
        }

        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #000;
            text-align: center;
        }

        .thank-you {
            font-size: {
                    {
                    $baseFontSize +2
                }
            }

            px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .footer-info {
            font-size: {
                    {
                    $baseFontSize - 2
                }
            }

            px;
            color: #666;
            white-space: pre-line;
        }

        @media print {
            body {
                width: {
                        {
                        $paperWidth
                    }
                }

                ;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        @if($showLogo && $logoUrl)
        <img src="{{ $logoUrl }}" alt="Logo" class="shop-logo"><br>
        @endif
        <div class="shop-name">{{ $shopName }}</div>
        <div class="shop-info">{{ $shopInfo }}</div>
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
            @if($showSeller)
            <tr>
                <td>ผู้ขาย:</td>
                <td style="text-align: right;">{{ $sale->createdBy->name ?? '-' }}</td>
            </tr>
            @endif
            @if($showCustomer && $sale->customer)
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
            @if($showVat && $sale->vat > 0)
            <tr>
                <td>ภาษี</td>
                <td>฿{{ number_format($sale->vat, 0) }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td>รวมสุทธิ</td>
                <td>฿{{ number_format($sale->total, 0) }}</td>
            </tr>
            @if($showPayment)
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
            @if($sale->status === 'pending' && $sale->payment_method === 'credit')
            <tr>
                <td>สถานะ</td>
                <td style="color:#e53e3e;font-weight:bold;">รอชำระเงิน</td>
            </tr>
            @if($sale->credit_due_date)
            <tr>
                <td>กำหนดชำระ</td>
                <td>{{ \Carbon\Carbon::parse($sale->credit_due_date)->format('d/m/Y') }}</td>
            </tr>
            @endif
            @endif
            @endif
        </table>
    </div>

    <div class="footer">
        <div class="thank-you">{{ $footerThank }}</div>
        <div class="footer-info">{{ $footerText }}</div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 14px; cursor: pointer;">
            🖨️ พิมพ์ใบเสร็จ
        </button>
    </div>
</body>

</html>