<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตัวอย่างใบเสร็จ</title>
    @php
    $shopName = ($template['sales_receipt_shop_name'] ?? '') ?: 'ชื่อร้าน/สาขา';
    $shopInfo = ($template['sales_receipt_shop_info'] ?? '') ?: "123 ถ.สุขุมวิท กรุงเทพฯ\nโทร: 081-234-5678";
    $logoUrl = $template['sales_receipt_logo_url'] ?? '';
    $showLogo = filter_var($template['sales_receipt_show_logo'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $paperWidth = ($template['sales_receipt_paper_width'] ?? '80') . 'mm';
    $baseFontSize = intval($template['sales_receipt_font_size'] ?? 12);
    $fontSize = $baseFontSize . 'px';
    $showCustomer = filter_var($template['sales_receipt_show_customer'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showSeller = filter_var($template['sales_receipt_show_seller'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showVat = filter_var($template['sales_receipt_show_vat'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showPayment = filter_var($template['sales_receipt_show_payment'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $footerThank = $template['sales_receipt_footer_thank'] ?? 'ขอบคุณที่ใช้บริการ';
    $footerText = $template['sales_receipt_footer_text'] ?? "สินค้าที่ซื้อแล้วไม่สามารถเปลี่ยนคืนได้\nยกเว้นมีการรับประกันตามเงื่อนไข";
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
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .toolbar {
            width: {
                    {
                    $paperWidth
                }
            }

            ;
            max-width: 95vw;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
        }

        .btn:hover {
            background: #f9fafb;
        }

        .receipt {
            width: {
                    {
                    $paperWidth
                }
            }

            ;
            padding: 5mm;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
            .toolbar {
                display: none !important
            }

            body {
                background: white;
                padding: 0;
            }

            .receipt {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <div style="font-size:13px;color:#6b7280;">ตัวอย่างใบเสร็จ (ข้อมูลสมมติ)</div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('receipt-templates.index') }}" class="btn">← กลับไปหน้าออกแบบ</a>
            <button class="btn" onclick="window.print()">🖨️ ทดสอบพิมพ์</button>
        </div>
    </div>

    <div class="receipt">
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
                    <td style="text-align:right;font-weight:bold;">INV-2025-0001</td>
                </tr>
                <tr>
                    <td>วันที่:</td>
                    <td style="text-align:right;">{{ date('d/m/Y H:i') }}</td>
                </tr>
                @if($showSeller)
                <tr>
                    <td>ผู้ขาย:</td>
                    <td style="text-align:right;">พนักงาน ก.</td>
                </tr>
                @endif
                @if($showCustomer)
                <tr>
                    <td>ลูกค้า:</td>
                    <td style="text-align:right;">สมชาย ใจดี</td>
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
                    <tr>
                        <td>
                            <div class="item-name">เคส iPhone 15 Pro</div>
                            <div class="item-detail">1 x ฿590</div>
                        </td>
                        <td>฿590</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="item-name">ฟิล์มกระจก</div>
                            <div class="item-detail">2 x ฿250</div>
                        </td>
                        <td>฿500</td>
                    </tr>
                    <tr>
                        <td>
                            <div class="item-name">สายชาร์จ USB-C</div>
                            <div class="item-detail">1 x ฿350</div>
                        </td>
                        <td>฿350</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="totals">
            <table>
                <tr>
                    <td>รวม (4 รายการ)</td>
                    <td>฿1,440</td>
                </tr>
                @if($showVat)
                <tr>
                    <td>ภาษี 7%</td>
                    <td>฿101</td>
                </tr>
                @endif
                <tr class="grand-total">
                    <td>รวมสุทธิ</td>
                    <td>฿1,541</td>
                </tr>
                @if($showPayment)
                <tr>
                    <td>ชำระโดย</td>
                    <td>เงินสด</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            <div class="thank-you">{{ $footerThank }}</div>
            <div class="footer-info">{{ $footerText }}</div>
        </div>
    </div>
</body>

</html>