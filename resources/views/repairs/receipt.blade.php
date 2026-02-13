<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบรับเครื่องซ่อม - {{ $repair->repair_number }}</title>
    @php
    $double = request()->boolean('double');
    $pageW = $double ? '210mm' : '148mm';
    $pageH = $double ? '297mm' : '210mm';
    $pageSize = $double ? '210mm 297mm' : '148mm 210mm';
    $margin = $double ? '10mm' : '8mm';
    $panelGap = $double ? '10mm' : '12px';
    $copies = $double ? 2 : 1;
    $copyLabels = $double ? ['เอกสารฉบับจริง', 'สำเนาเอกสาร'] : ['เอกสารฉบับจริง'];
    $trackUrl = route('repairs.track', ['repair_number' => $repair->repair_number]);
    @endphp
    <link rel="stylesheet" href="{{ asset('css/repair-receipt.css') }}">
    </link>
</head>

<body>@if(request()->boolean('auto_print')) <script>
    window.addEventListener('load', () => {
        const qrs = Array.from(document.querySelectorAll('img.qr'));

        const waiters = qrs.map(img => new Promise(resolve => {
            if (img.complete) return resolve();
            img.addEventListener('load', () => resolve());
            img.addEventListener('error', () => resolve());
        }));
        Promise.all(waiters).then(() => setTimeout(() => window.print(), 150));
    });
    </script>@endif <div class="container">
        <div class="no-print"
            style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
            <div class="sub">เอกสารต้นฉบับ • พิมพ์วันที่ {
                {
                now()->format('d/m/Y')
                }
                }

            </div>
            <div style="display:flex; gap:8px;"><a href="{{ route('repairs.show', $repair) }}"
                    class="print-btn">กลับไปหน้ารายละเอียด</a><a href="#" class="print-btn"
                    onclick="window.print()">พิมพ์ใบรับเครื่อง</a></div>
        </div>@for($i =0; $i < $copies; $i++) <div class="panel">
            <div class="header">
                <div>
                    <div class="pill-black">ใบรับเครื่องซ่อม • {
                        {
                        $repair->repair_number
                        }
                        }

                    </div>
                    <div class="sub" style="margin-top:6px;">ชื่อผู้รับ: {
                        {
                        $repair->customer_name
                        }
                        }

                        • เบอร์ติดต่อ: {
                        {
                        $repair->customer_phone
                        }
                        }

                        • วันที่พิมพ์: {
                        {
                        now()->format('d/m/Y')
                        }
                        }

                    </div>
                </div>
                <div class="meta">
                    <div class="stamp"> {
                        {
                        $copyLabels[$i]
                        }
                        }

                    </div>
                    <div style="font-weight:800; margin-top:6px;"> {
                        {
                        $repair->branch->name ?? 'สาขา'
                        }
                        }

                    </div>@if( !empty($repair->branch->address)) <div class="sub"> {
                        {
                        $repair->branch->address
                        }
                        }

                    </div>@endif @if( !empty($repair->branch->phone)) <div class="sub">โทร {
                        {
                        $repair->branch->phone
                        }
                        }

                    </div>@endif @if( !empty($repair->branch->tax_id)) <div class="sub">เลขผู้เสียภาษี: {
                        {
                        $repair->branch->tax_id
                        }
                        }

                    </div>@endif
                </div>
            </div>
            <div class="section white card">
                <div class="grid">
                    <div>
                        <div class="row">
                            <div class="label">อุปกรณ์</div>
                            <div class="value"> {
                                {
                                $repair->device_brand
                                }
                                }

                                {
                                {
                                $repair->device_model
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">ประเภท</div>
                            <div class="value"> {
                                {
                                $repair->device_type
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">สี</div>
                            <div class="value"> {
                                {
                                $repair->device_color ?: '-'
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">หมายเลข SN/IMEI</div>
                            <div class="value"> {
                                {
                                $repair->device_serial ?: ($repair->device_imei ?: '-')
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">รหัสปลดล็อค</div>
                            <div class="value"> {
                                {
                                $repair->device_password ?: '-'
                                }
                                }

                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="row">
                            <div class="label">วันที่รับ</div>
                            <div class="value"> {
                                {
                                optional($repair->received_at)->format('d/m/Y') ?? $repair->created_at->format('d/m/Y')
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">ราคาประเมินซ่อม</div>
                            <div class="value">฿ {
                                {
                                number_format($repair->estimated_cost ?? 0, 0)
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">มัดจำ</div>
                            <div class="value">฿ {
                                {
                                number_format($repair->deposit ?? 0, 0)
                                }
                                }

                            </div>
                        </div>
                        <div class="row">
                            <div class="label">อาการเสีย</div>
                            <div class="value"> {
                                {
                                $repair->problem_description
                                }
                                }

                            </div>
                        </div>
                    </div>
                </div>
            </div>@php $acc =is_array($repair->device_accessories) ? implode(', ', $repair->device_accessories) :
            ($repair->device_accessories ?? '');

            @endphp @if( !empty($acc)) <div class="section white card">
                <div class="section-title">อุปกรณ์ที่ติดมาด้วย</div>
                <div class="row">
                    <div class="label">รายการ</div>
                    <div class="value"> {
                        {
                        $acc
                        }
                        }

                    </div>
                </div>
            </div>@endif <div class="section card">
                <div class="section-title">เงื่อนไขการรับบริการโดยสรุป</div>
                <div class="terms">
                    <ol style="margin:4px 0 0 18px;">
                        <li>รับคืนภายใน 7 วันหลังแจ้ง หรือภายใน 30 วันจากวันรับบริการ</li>
                        <li>ต้องมีใบนี้เพื่อรับอุปกรณ์คืน</li>
                        <li>รับประกันเริ่ม 1 วันหลังแจ้งรับ</li>
                        <li>ไม่ครอบคลุมอุบัติเหตุ/การใช้งานผิดประเภท</li>
                    </ol>
                </div>
                <div class="footer">
                    <div>
                        <div class="sign"></div>
                        <div class="sub">ลูกค้าเซ็นรับทราบ</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="sign"></div>
                        <div class="sub">All in Service Mac</div>
                    </div>
                </div>
            </div>
            <div class="grid" style="margin-top:10px; align-items:start;">
                <div>
                    <div class="sub">รหัสอ้างอิง</div>
                    <div class="barcode"> {
                        {
                        $repair->repair_number
                        }
                        }

                    </div>
                </div>
                <div style="text-align:right;"><img class="qr"
                        src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($trackUrl) }}"
                        alt="QR Track"
                        style="display:inline-block; width:110px; height:110px; border:1px solid var(--line); border-radius:8px; background:#fff;">
                    <div class="sub">สแกนเพื่อติดตามสถานะ</div>
                </div>
            </div>
    </div>@endfor </div>
</body>

</html>