{{-- @formatter:off --}}
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'tax_invoice' ? 'ใบกำกับภาษี' : 'ใบเสร็จรับเงิน' }} - {{ $repair->repair_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @php
    $isTax = $type === 'tax_invoice';
    $docTitle = $isTax ? 'ใบกำกับภาษี / Tax Invoice' : 'ใบเสร็จรับเงิน / Receipt';
    $branch = $repair->branch;
    $usedParts = $repair->parts->where('status', 'used');
    $invoiceNo = 'INV-' . $repair->repair_number;
    @endphp
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        @page {
            size: A4;
            margin: 12mm 15mm
        }

        html,
        body {
            font-family: 'Sarabun', ui-sans-serif, system-ui, sans-serif;
            color: #111827;
            background: #fff;
            font-size: 13px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact
        }

        .page {
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
            background: #fff
        }

        .no-print {
            margin: 12px auto;
            max-width: 800px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            background: #fff;
            font-size: 13px;
            cursor: pointer;
            transition: all .15s
        }

        .btn:hover {
            background: #f3f4f6
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
            border-color: #4f46e5
        }

        .btn-primary:hover {
            background: #4338ca
        }

        .btn-green {
            background: #059669;
            color: #fff;
            border-color: #059669
        }

        .btn-green:hover {
            background: #047857
        }

        /* Header */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 16px;
            border-bottom: 3px solid #111827;
            margin-bottom: 16px
        }

        .shop-info h1 {
            font-size: 22px;
            font-weight: 800;
            color: #111827
        }

        .shop-info p {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.6
        }

        .doc-type {
            text-align: right
        }

        .doc-type h2 {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            letter-spacing: .5px
        }

        .doc-type .doc-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #111827;
            color: #fff;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            margin-top: 4px
        }

        .doc-type .tax-badge {
            background: #dc2626
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px
        }

        .info-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px
        }

        .info-box h3 {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 1px solid #f3f4f6
        }

        .info-row {
            display: flex;
            margin: 4px 0
        }

        .info-label {
            width: 100px;
            font-size: 11px;
            color: #6b7280;
            flex-shrink: 0
        }

        .info-value {
            flex: 1;
            font-size: 12px;
            font-weight: 600;
            color: #111827
        }

        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px
        }

        .items-table thead th {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-align: left
        }

        .items-table thead th.right {
            text-align: right
        }

        .items-table thead th.center {
            text-align: center
        }

        .items-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 12px;
            color: #111827
        }

        .items-table tbody td.right {
            text-align: right
        }

        .items-table tbody td.center {
            text-align: center
        }

        .items-table tfoot td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            font-size: 12px
        }

        .items-table tfoot .total-row td {
            font-weight: 800;
            font-size: 14px;
            background: #f9fafb
        }

        .items-table tfoot .grand-row td {
            font-weight: 800;
            font-size: 16px;
            background: #111827;
            color: #fff
        }

        /* Summary */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-bottom: 24px
        }

        .payment-info {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px 14px
        }

        .payment-info h3 {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 8px
        }

        /* Amount Box */
        .amount-box {
            border: 2px solid #111827;
            border-radius: 8px;
            padding: 12px 14px
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
            font-size: 12px
        }

        .amount-row.total {
            font-size: 15px;
            font-weight: 800;
            padding-top: 8px;
            border-top: 2px solid #111827;
            margin-top: 8px
        }

        .amount-row.deposit {
            color: #059669
        }

        .amount-row.balance {
            color: #dc2626;
            font-weight: 700
        }

        /* Footer */
        .sign-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 24px;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb
        }

        .sign-box {
            text-align: center
        }

        .sign-line {
            height: 40px;
            border-bottom: 1px dashed #9ca3af;
            margin-bottom: 4px
        }

        .sign-label {
            font-size: 10px;
            color: #6b7280
        }

        .sign-name {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            margin-top: 2px
        }

        .doc-footer {
            margin-top: 16px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #f3f4f6;
            padding-top: 8px
        }

        .warranty-box {
            border: 1px solid #d1fae5;
            border-radius: 8px;
            background: #ecfdf5;
            padding: 10px 14px;
            margin-bottom: 16px
        }

        .warranty-box h4 {
            font-size: 11px;
            font-weight: 700;
            color: #065f46;
            margin-bottom: 4px
        }

        .warranty-box p {
            font-size: 11px;
            color: #047857;
            line-height: 1.6
        }

        @media print {
            .no-print {
                display: none !important
            }

            .page {
                padding: 0;
                max-width: none
            }

            body {
                background: #fff
            }
        }

        @media screen {
            body {
                background: #f3f4f6;
                padding: 20px 0
            }

            .page {
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, .08)
            }
        }
    </style>
</head>

<body>
    <!-- Action Bar -->
    <div class="no-print">
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="{{ route('repairs.show', $repair) }}" class="btn"><i class="fas fa-arrow-left"></i> กลับ</a>
            @if($isTax)
            <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'receipt']) }}" class="btn"><i class="fas fa-receipt"></i> ใบเสร็จรับเงิน</a>
            @else
            <a href="{{ route('repairs.invoice', ['repair' => $repair, 'type' => 'tax_invoice']) }}" class="btn btn-green"><i class="fas fa-file-invoice"></i> ใบกำกับภาษี</a>
            @endif
        </div>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> พิมพ์</button>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <div class="page">
        <!-- Document Header -->
        <div class="doc-header">
            <div class="shop-info">
                <h1>{{ $branch->name ?? 'All in Service Mac' }}</h1>
                @if($branch)
                <p>
                    @if($branch->address){{ $branch->address }}<br>@endif
                    @if($branch->phone)โทร: {{ $branch->phone }}@endif
                    @if($branch->email) | {{ $branch->email }}@endif
                </p>
                @if($isTax && $branch->tax_id)
                <p style="margin-top:4px;font-weight:700;font-size:12px;">เลขประจำตัวผู้เสียภาษี: {{ $branch->tax_id }}</p>
                @endif
                @endif
            </div>
            <div class="doc-type">
                <h2>{{ $docTitle }}</h2>
                <div class="doc-badge {{ $isTax ? 'tax-badge' : '' }}">
                    {{ $isTax ? 'TAX INVOICE' : 'ORIGINAL' }}
                </div>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <h3><i class="fas fa-user" style="margin-right:4px;"></i> ข้อมูลลูกค้า</h3>
                <div class="info-row">
                    <div class="info-label">ชื่อ</div>
                    <div class="info-value">{{ $repair->customer->name ?? $repair->customer_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">โทรศัพท์</div>
                    <div class="info-value">{{ $repair->customer_phone }}</div>
                </div>
                @if($repair->customer_line_id)
                <div class="info-row">
                    <div class="info-label">LINE</div>
                    <div class="info-value">{{ $repair->customer_line_id }}</div>
                </div>
                @endif
                @if($isTax && $repair->customer)
                @if($repair->customer->address)
                <div class="info-row">
                    <div class="info-label">ที่อยู่</div>
                    <div class="info-value">{{ $repair->customer->address }}</div>
                </div>
                @endif
                @if($repair->customer->tax_id)
                <div class="info-row">
                    <div class="info-label">เลขผู้เสียภาษี</div>
                    <div class="info-value">{{ $repair->customer->tax_id }}</div>
                </div>
                @endif
                @endif
            </div>

            <div class="info-box">
                <h3><i class="fas fa-file-alt" style="margin-right:4px;"></i> ข้อมูลเอกสาร</h3>
                <div class="info-row">
                    <div class="info-label">เลขที่</div>
                    <div class="info-value">{{ $invoiceNo }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">เลขงานซ่อม</div>
                    <div class="info-value">{{ $repair->repair_number }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">วันที่ออก</div>
                    <div class="info-value">{{ now()->format('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">อุปกรณ์</div>
                    <div class="info-value">{{ $repair->device_brand }} {{ $repair->device_model }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">SN/IMEI</div>
                    <div class="info-value">{{ $repair->device_serial ?: ($repair->device_imei ?: '-') }}</div>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:40px;" class="center">ลำดับ</th>
                    <th>รายการ</th>
                    <th class="center" style="width:70px;">จำนวน</th>
                    <th class="right" style="width:100px;">ราคา/หน่วย</th>
                    <th class="right" style="width:110px;">จำนวนเงิน</th>
                </tr>
            </thead>
            <tbody>
                @php $lineNo = 0; @endphp

                {{-- Service charge --}}
                @if($repair->service_cost > 0)
                @php $lineNo++; @endphp
                <tr>
                    <td class="center">{{ $lineNo }}</td>
                    <td>
                        <div style="font-weight:600;">ค่าบริการซ่อม</div>
                        <div style="font-size:10px;color:#6b7280;">
                            {{ $repair->device_type }} {{ $repair->device_brand }} {{ $repair->device_model }}
                            — {{ $repair->problem_description }}
                        </div>
                        @if($repair->solution)
                        <div style="font-size:10px;color:#059669;">
                            <i class="fas fa-check-circle"></i> {{ $repair->solution }}
                        </div>
                        @endif
                    </td>
                    <td class="center">1</td>
                    <td class="right">{{ number_format($repair->service_cost ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($repair->service_cost ?? 0, 2) }}</td>
                </tr>
                @endif

                {{-- Parts --}}
                @foreach($usedParts as $part)
                @php $lineNo++; @endphp
                <tr>
                    <td class="center">{{ $lineNo }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $part->part_name }}</div>
                        @if($part->notes)
                        <div style="font-size:10px;color:#6b7280;">{{ $part->notes }}</div>
                        @endif
                    </td>
                    <td class="center">{{ $part->quantity }}</td>
                    <td class="right">{{ number_format($part->unit_price ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($part->total_price ?? 0, 2) }}</td>
                </tr>
                @endforeach

                {{-- If no service and no parts show problem desc --}}
                @if($lineNo === 0)
                @php $lineNo++; @endphp
                <tr>
                    <td class="center">{{ $lineNo }}</td>
                    <td>
                        <div style="font-weight:600;">บริการซ่อม {{ $repair->device_brand }} {{ $repair->device_model }}</div>
                        <div style="font-size:10px;color:#6b7280;">{{ $repair->problem_description }}</div>
                    </td>
                    <td class="center">1</td>
                    <td class="right">{{ number_format($repair->total_cost ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($repair->total_cost ?? 0, 2) }}</td>
                </tr>
                @endif

                {{-- Blank rows for cleaner look --}}
                @for($i = $lineNo; $i < 3; $i++)
                    <tr>
                    <td class="center">&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    </tr>
                    @endfor
            </tbody>
            <tfoot>
                @php
                $subtotal = ($repair->service_cost ?? 0);
                $vatRate = $isTax ? 7 : 0;
                $amountBeforeVat = $isTax ? round($subtotal / 1.07, 2) : $subtotal;
                $vatAmount = $isTax ? round($subtotal - $amountBeforeVat, 2) : 0;
                @endphp

                @if($isTax)
                <tr>
                    <td colspan="4" class="right" style="font-weight:600;">มูลค่าสินค้า/บริการก่อน VAT</td>
                    <td class="right" style="font-weight:600;">{{ number_format($amountBeforeVat, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="right" style="font-weight:600;">ภาษีมูลค่าเพิ่ม (7%)</td>
                    <td class="right" style="font-weight:600;">{{ number_format($vatAmount, 2) }}</td>
                </tr>
                @endif

                @if($repair->discount > 0)
                <tr>
                    <td colspan="4" class="right" style="font-weight:600;color:#dc2626;">ส่วนลด</td>
                    <td class="right" style="font-weight:600;color:#dc2626;">-{{ number_format($repair->discount ?? 0, 2) }}</td>
                </tr>
                @endif

                <tr class="grand-row">
                    <td colspan="4" style="text-align:right;">ยอดรวมทั้งสิ้น</td>
                    <td style="text-align:right;">฿{{ number_format($repair->total_cost ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Payment & Amount Summary -->
        <div class="summary-grid">
            <div class="payment-info">
                <h3><i class="fas fa-money-bill-wave" style="margin-right:4px;"></i> ข้อมูลการชำระเงิน</h3>
                @php
                $payMethods = [
                'cash' => 'เงินสด',
                'transfer' => 'โอนเงิน',
                'qr' => 'QR Code',
                'card' => 'บัตรเครดิต/เดบิต',
                ];
                @endphp
                <div class="info-row">
                    <div class="info-label">ช่องทางชำระ</div>
                    <div class="info-value">{{ $payMethods[$repair->payment_method] ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">สถานะ</div>
                    <div class="info-value">
                        @if($repair->payment_status === 'paid')
                        <span style="color:#059669;font-weight:700;">ชำระครบแล้ว</span>
                        @elseif($repair->payment_status === 'partial')
                        <span style="color:#d97706;font-weight:700;">ชำระบางส่วน</span>
                        @else
                        <span style="color:#dc2626;font-weight:700;">ยังไม่ชำระ</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="amount-box">
                <div class="amount-row">
                    <span>ค่าบริการ</span>
                    <span>{{ number_format($repair->service_cost ?? 0, 2) }}</span>
                </div>
                @if($repair->discount > 0)
                <div class="amount-row" style="color:#dc2626;">
                    <span>ส่วนลด</span>
                    <span>-{{ number_format($repair->discount ?? 0, 2) }}</span>
                </div>
                @endif
                <div class="amount-row total">
                    <span>ยอดรวม</span>
                    <span>฿{{ number_format($repair->total_cost ?? 0, 2) }}</span>
                </div>
                @if($repair->deposit > 0)
                <div class="amount-row deposit">
                    <span>มัดจำ</span>
                    <span>-{{ number_format($repair->deposit ?? 0, 2) }}</span>
                </div>
                @endif
                @if($repair->paid_amount > 0 && $repair->paid_amount != $repair->deposit)
                <div class="amount-row deposit">
                    <span>ชำระแล้ว</span>
                    <span>-{{ number_format($repair->paid_amount ?? 0, 2) }}</span>
                </div>
                @endif
                @php $balance = ($repair->total_cost ?? 0) - ($repair->paid_amount ?? 0); @endphp
                @if($balance > 0)
                <div class="amount-row balance">
                    <span>ยอดค้างชำระ</span>
                    <span>฿{{ number_format($balance, 2) }}</span>
                </div>
                @elseif($balance <= 0)
                    <div class="amount-row" style="color:#059669;font-weight:700;">
                    <span>ชำระครบแล้ว</span>
                    <span>✓</span>
            </div>
            @endif
        </div>
    </div>

    <!-- Warranty Info -->
    @if($repair->warranty_days > 0)
    <div class="warranty-box">
        <h4><i class="fas fa-shield-alt" style="margin-right:4px;"></i> การรับประกัน</h4>
        <p>
            ระยะประกัน: {{ $repair->warranty_days }} วัน
            @if($repair->warranty_expires_at)
            | หมดประกัน: {{ $repair->warranty_expires_at->format('d/m/Y') }}
            @endif
            @if($repair->warranty_conditions)
            <br>เงื่อนไข: {{ $repair->warranty_conditions }}
            @endif
        </p>
    </div>
    @endif

    <!-- Signature -->
    <div class="sign-grid">
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="sign-label">ผู้รับบริการ (ลูกค้า)</div>
            <div class="sign-name">{{ $repair->customer->name ?? $repair->customer_name }}</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="sign-label">ช่างผู้ดำเนินการ</div>
            <div class="sign-name">{{ $repair->technician->name ?? '-' }}</div>
        </div>
        <div class="sign-box">
            <div class="sign-line"></div>
            <div class="sign-label">ผู้ออกเอกสาร</div>
            <div class="sign-name">{{ $branch->name ?? 'All in Service Mac' }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="doc-footer">
        {{ $branch->name ?? 'All in Service Mac' }}
        @if($branch && $branch->phone) | โทร: {{ $branch->phone }} @endif
        @if($branch && $branch->address) | {{ $branch->address }} @endif
        <br>
        เอกสารออกโดยระบบ All-in-App • {{ now()->format('d/m/Y H:i') }}
        @if($isTax)
        | เลขผู้เสียภาษี: {{ $branch->tax_id ?? '-' }}
        @endif
    </div>
    </div>
</body>

</html>
{{-- @formatter:on --}}