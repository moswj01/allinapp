{{-- @formatter:off --}}
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template['repair_receipt_title'] }} - {{ $repair->repair_number }}</title>
    @php
    $trackUrl = route('repairs.track', ['repair_number' => $repair->repair_number]);
    $copyLabels = ['เอกสารฉบับจริง','สำเนาเอกสาร'];
    $copies = intval($template['repair_receipt_copies'] ?? 2);
    $brandColor = $template['repair_receipt_brand_color'] ?? '#111827';
    $accentColor = $template['repair_receipt_accent_color'] ?? '#0ea5e9';
    $bgColor = $template['repair_receipt_bg_color'] ?? '#f9fafb';
    $shopName = $template['repair_receipt_shop_name'] ?: ($repair->branch->name ?? 'ร้าน');
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
            @if($paperSize ==='A4') width: 210mm;
            @else width: 210mm;
            height: 148mm;
            @endif font-family: 'Sarabun', ui-sans-serif, system-ui, sans-serif;
            color: var(--brand);
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact
        }

        .page-wrap {
            width: 100%
        }

        .panel {
            @if($paperSize ==='A4') width: 210mm;
            min-height: 148mm;
            @else width: 210mm;
            height: 148mm;
            @endif border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            page-break-after: always;
            box-sizing: border-box
        }

        .panel:last-child {
            page-break-after: auto
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

        .footer {
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

        .print-btn {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid var(--line);
            border-radius: 8px;
            text-decoration: none;
            color: var(--brand);
            background: #fff;
            font-size: 12px
        }

        .barcode {
            display: inline-block;
            padding: 4px 6px;
            border: 1px dashed var(--line);
            border-radius: 6px;
            background: #fff;
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
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

        @media print {
            .no-print {
                display: none !important
            }
        }

        @media screen {
            .page-wrap {
                max-width: 800px;
                margin: 12px auto
            }

            .panel {
                width: 100%;
                height: auto;
                margin-bottom: 12px
            }
        }
    </style>
</head>

<body>
    @if(request()->boolean('auto_print'))
    <script>
        window.addEventListener('load', () => {
            const qrs = Array.from(document.querySelectorAll('img.qr'));
            const waiters = qrs.map(img => new Promise(resolve => {
                if (img.complete) return resolve();
                img.addEventListener('load', () => resolve());
                img.addEventListener('error', () => resolve());
            }));
            Promise.all(waiters).then(() => setTimeout(() => window.print(), 150));
        });
    </script>
    @endif

    <div class="no-print" style="margin:12px auto;max-width:1100px;display:flex;justify-content:space-between;align-items:center;">
        <div class="sub" style="font-size:12px;">{{ $copies > 1 ? 'ต้นฉบับ + สำเนา' : 'ต้นฉบับ' }} • พิมพ์วันที่ {{ now()->format('d/m/Y') }}</div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('repairs.show', $repair) }}" class="print-btn">กลับไปหน้ารายละเอียด</a>
            <a href="#" class="print-btn" onclick="window.print()">🖨️ พิมพ์{{ $docTitle }}</a>
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
                    <div class="pill-black">{{ $docTitle }} • {{ $repair->repair_number }}</div>
                    <div style="margin-top:4px;font-size:12px;font-weight:600;color:#374151;">{{ $shopName }} • {{ $repair->customer_name }} • {{ $repair->customer_phone }}</div>
                    @if($shopInfo)
                    <div class="shop-info-text">{{ $shopInfo }}</div>
                    @endif
                </div>
                <div class="meta" style="flex-shrink:0;">
                    <div class="stamp">{{ $copyLabels[$i] ?? 'สำเนา' }}</div>
                    <div style="font-weight:800;font-size:11px;margin-top:4px;">{{ $repair->branch->name ?? 'สาขา' }}</div>
                    @if(!empty($repair->branch->phone))
                    <div class="sub">โทร {{ $repair->branch->phone }}</div>
                    @endif
                </div>
            </div>

            <div class="section white">
                <div class="grid">
                    <div>
                        <div class="row">
                            <div class="label">อุปกรณ์</div>
                            <div class="value">{{ $repair->device_brand }} {{ $repair->device_model }}</div>
                        </div>
                        <div class="row">
                            <div class="label">ประเภท</div>
                            <div class="value">{{ $repair->device_type }}</div>
                        </div>
                        <div class="row">
                            <div class="label">สี</div>
                            <div class="value">{{ $repair->device_color ?: '-' }}</div>
                        </div>
                        <div class="row">
                            <div class="label">SN/IMEI</div>
                            <div class="value">{{ $repair->device_serial ?: ($repair->device_imei ?: '-') }}</div>
                        </div>
                        @if($showPassword)
                        <div class="row">
                            <div class="label">รหัสปลดล็อค</div>
                            <div class="value">{{ $repair->device_password ?: '-' }}</div>
                        </div>
                        @endif
                    </div>
                    <div>
                        <div class="row">
                            <div class="label">วันที่รับ</div>
                            <div class="value">{{ optional($repair->received_at)->format('d/m/Y') ?? $repair->created_at->format('d/m/Y') }}</div>
                        </div>
                        <div class="row">
                            <div class="label">ราคาประเมิน</div>
                            <div class="value">฿ {{ number_format($repair->estimated_cost ?? 0, 0) }}</div>
                        </div>
                        <div class="row">
                            <div class="label">มัดจำ</div>
                            <div class="value">฿ {{ number_format($repair->deposit ?? 0, 0) }}</div>
                        </div>
                        <div class="row">
                            <div class="label">อาการเสีย</div>
                            <div class="value">{{ $repair->problem_description }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($showAccessories)
            @php
            $accMap = ['charger' => 'ที่ชาร์จ', 'case' => 'เคส', 'sim_card' => 'ซิมการ์ด', 'memory_card' => 'เมมโมรี่การ์ด'];
            $accItems = is_array($repair->device_accessories) ? $repair->device_accessories : [];
            $acc = implode(', ', array_map(fn($v) => $accMap[$v] ?? $v, $accItems));
            @endphp
            @if(!empty($accItems))
            <div class="section white">
                <div class="section-title">อุปกรณ์ที่ติดมาด้วย</div>
                <div class="row">
                    <div class="label">รายการ</div>
                    <div class="value">{{ $acc }}</div>
                </div>
            </div>
            @endif
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
                <div class="footer">
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
                    <div class="barcode">{{ $repair->repair_number }}</div>
                </div>
                @else
                <div></div>
                @endif

                @if($showQr)
                <div style="text-align:right;">
                    <img class="qr" src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode($trackUrl) }}" alt="QR Track" style="display:inline-block;width:70px;height:70px;border:1px solid var(--line);border-radius:6px;background:#fff;">
                    <div class="sub">สแกนติดตามสถานะ</div>
                </div>
                @endif
            </div>
    </div>
    @endfor
    </div>
</body>

</html>
{{-- @formatter:on --}}