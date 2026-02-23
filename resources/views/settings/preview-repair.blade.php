<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตัวอย่าง {{ $template['repair_receipt_title'] }}</title>
    @php
    $brandColor = $template['repair_receipt_brand_color'] ?? '#111827';
    $accentColor = $template['repair_receipt_accent_color'] ?? '#0ea5e9';
    $bgColor = $template['repair_receipt_bg_color'] ?? '#f9fafb';
    $shopName = $template['repair_receipt_shop_name'] ?: 'ชื่อร้าน/สาขา';
    $shopInfo = $template['repair_receipt_shop_info'] ?? '';
    $docTitle = $template['repair_receipt_title'] ?? 'ใบรับเครื่องซ่อม';
    $signLeft = $template['repair_receipt_sign_left'] ?? 'ลูกค้าเซ็นรับทราบ';
    $signRight = $template['repair_receipt_sign_right'] ?? 'All in Service Mac';
    $termsTitle = $template['repair_receipt_terms_title'] ?? 'เงื่อนไขการรับบริการโดยสรุป';
    $termsText = $template['repair_receipt_terms'] ?? '';
    $showQr = filter_var($template['repair_receipt_show_qr'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showBarcode = filter_var($template['repair_receipt_show_barcode'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showAccessories = filter_var($template['repair_receipt_show_accessories'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showPassword = filter_var($template['repair_receipt_show_password'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $showLogo = filter_var($template['repair_receipt_show_logo'] ?? true, FILTER_VALIDATE_BOOLEAN);
    $logoUrl = $template['repair_receipt_logo_url'] ?? '';
    $paperSize = $template['repair_receipt_paper_size'] ?? 'A5';
    $copies = intval($template['repair_receipt_copies'] ?? 2);
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        :root {
            --brand: {
                    {
                    $brandColor
                }
            }

            ;
            --muted: #6b7280;
            --line: #e5e7eb;

            --bg: {
                    {
                    $bgColor
                }
            }

            ;

            --accent: {
                    {
                    $accentColor
                }
            }
        }

        @page {
            size: {
                    {
                    $paperSize ==='A4' ? 'A4 portrait': 'A5 landscape'
                }
            }

            ;
            margin: 5mm
        }

        html,
        body {
            font-family: 'Sarabun', ui-sans-serif, system-ui, sans-serif;
            color: var(--brand);
            background: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact
        }

        .page-wrap {
            max-width: 800px;
            margin: 20px auto;
        }

        .panel {
            width: 210mm;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin-bottom: 12px;
            box-sizing: border-box
        }

        .pill-black {
            display: inline-block;
            background: var(--brand);
            color: #fff;
            border-radius: 8px;
            padding: 6px 12px;
            font-weight: 800;
            font-size: 13px
        }

        .meta {
            text-align: right
        }

        .stamp {
            display: inline-block;
            border: 1.5px solid var(--line);
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 10px;
            color: #374151;
            background: #fff
        }

        .sub {
            font-size: 9px;
            color: var(--muted)
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px
        }

        .section {
            margin-top: 6px;
            padding: 8px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--bg)
        }

        .section.white {
            background: #fff
        }

        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--brand);
            margin-bottom: 4px
        }

        .row {
            display: flex;
            align-items: flex-start;
            margin: 3px 0
        }

        .label {
            width: 110px;
            color: var(--muted);
            font-size: 11px;
            flex-shrink: 0
        }

        .value {
            flex: 1;
            font-size: 13px;
            font-weight: 600;
            word-break: break-word
        }

        .terms {
            font-size: 9px;
            color: #374151;
            line-height: 1.5
        }

        .footer-sign {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 8px
        }

        .sign {
            height: 36px;
            border-bottom: 1px dashed #9ca3af;
            margin-bottom: 3px;
            background: #fff
        }

        .barcode {
            display: inline-block;
            padding: 4px 6px;
            border: 1px dashed var(--line);
            border-radius: 6px;
            background: #fff;
            font-family: monospace;
            font-size: 9px
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 6px
        }

        .bottom-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: auto;
            padding-top: 6px
        }

        .shop-logo {
            max-height: 32px;
            margin-bottom: 4px;
        }

        .shop-info-text {
            font-size: 9px;
            color: var(--muted);
            white-space: pre-line;
        }

        .toolbar {
            max-width: 800px;
            margin: 20px auto 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        @media print {
            .toolbar {
                display: none !important
            }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <div class="sub" style="font-size:13px;">ตัวอย่าง {{ $docTitle }} (ข้อมูลสมมติ)</div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('receipt-templates.index') }}" class="btn">← กลับไปหน้าออกแบบ</a>
            <button class="btn" onclick="window.print()">🖨️ ทดสอบพิมพ์</button>
        </div>
    </div>

    <div class="page-wrap">
        @for($i = 0; $i < $copies; $i++)
            <div class="panel">
            <div class="header">
                <div style="min-width:0;">
                    @if($showLogo && $logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="shop-logo">
                    @endif
                    <div class="pill-black">{{ $docTitle }} • RPR-2025-0001</div>
                    <div style="margin-top:4px;font-size:12px;font-weight:600;color:#374151;">{{ $shopName }} • สมชาย ใจดี • 081-234-5678</div>
                    @if($shopInfo)
                    <div class="shop-info-text">{{ $shopInfo }}</div>
                    @endif
                </div>
                <div class="meta" style="flex-shrink:0;">
                    <div class="stamp">{{ ['เอกสารฉบับจริง','สำเนาเอกสาร'][$i] ?? 'สำเนา' }}</div>
                    <div style="font-weight:800;font-size:11px;margin-top:4px;">{{ $shopName }}</div>
                    <div class="sub">โทร 081-234-5678</div>
                </div>
            </div>

            <div class="section white">
                <div class="grid">
                    <div>
                        <div class="row">
                            <div class="label">อุปกรณ์</div>
                            <div class="value">iPhone 15 Pro Max</div>
                        </div>
                        <div class="row">
                            <div class="label">ประเภท</div>
                            <div class="value">สมาร์ทโฟน</div>
                        </div>
                        <div class="row">
                            <div class="label">สี</div>
                            <div class="value">Natural Titanium</div>
                        </div>
                        <div class="row">
                            <div class="label">SN/IMEI</div>
                            <div class="value">ABCDEF123456</div>
                        </div>
                        @if($showPassword)
                        <div class="row">
                            <div class="label">รหัสปลดล็อค</div>
                            <div class="value">1234</div>
                        </div>
                        @endif
                    </div>
                    <div>
                        <div class="row">
                            <div class="label">วันที่รับ</div>
                            <div class="value">{{ date('d/m/Y') }}</div>
                        </div>
                        <div class="row">
                            <div class="label">ราคาประเมิน</div>
                            <div class="value">฿ 3,500</div>
                        </div>
                        <div class="row">
                            <div class="label">มัดจำ</div>
                            <div class="value">฿ 1,000</div>
                        </div>
                        <div class="row">
                            <div class="label">อาการเสีย</div>
                            <div class="value">จอแตก ต้องเปลี่ยนจอ</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($showAccessories)
            <div class="section white">
                <div class="section-title">อุปกรณ์ที่ติดมาด้วย</div>
                <div class="row">
                    <div class="label">รายการ</div>
                    <div class="value">ที่ชาร์จ, เคส</div>
                </div>
            </div>
            @endif

            <div class="section">
                <div class="section-title">{{ $termsTitle }}</div>
                <div class="terms">
                    @if($termsText)
                    <ol style="margin:2px 0 0 16px;">
                        @foreach(explode("\n", $termsText) as $termLine)
                        @if(trim($termLine))
                        <li>{{ preg_replace('/^\d+\.\s*/', '', trim($termLine)) }}</li>
                        @endif
                        @endforeach
                    </ol>
                    @endif
                </div>
                <div class="footer-sign">
                    <div>
                        <div class="sign"></div>
                        <div class="sub">{{ $signLeft }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="sign"></div>
                        <div class="sub">{{ $signRight }}</div>
                    </div>
                </div>
            </div>

            <div class="bottom-row">
                @if($showBarcode)
                <div>
                    <div class="sub">รหัสอ้างอิง</div>
                    <div class="barcode">RPR-2025-0001</div>
                </div>
                @else
                <div></div>
                @endif
                @if($showQr)
                <div style="text-align:right;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=preview" alt="QR" style="display:inline-block;width:70px;height:70px;border:1px solid var(--line);border-radius:6px;background:#fff;">
                    <div class="sub">สแกนติดตามสถานะ</div>
                </div>
                @endif
            </div>
    </div>
    @endfor
    </div>
</body>

</html>