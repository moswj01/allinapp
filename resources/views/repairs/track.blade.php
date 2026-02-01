<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดตามงานซ่อม - {{ $repair->repair_number }}</title>
    <style>
        :root {
            --brand: #111827;
            --muted: #6b7280;
            --line: #e5e7eb;
            --accent: #2563eb;
            --bg: #f9fafb;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Tahoma, sans-serif;
            color: var(--brand);
            background: var(--bg);
        }

        .container {
            max-width: 720px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
        }

        .header {
            padding: 16px 20px;
            border-bottom: 2px solid var(--brand);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .title {
            font-size: 20px;
            font-weight: 800;
        }

        .sub {
            font-size: 12px;
            color: var(--muted);
        }

        .content {
            padding: 20px;
        }

        .row {
            display: flex;
            margin: 8px 0;
        }

        .label {
            width: 180px;
            color: var(--muted);
            font-size: 13px;
        }

        .value {
            flex: 1;
            font-size: 15px;
            font-weight: 600;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #f0f7ff;
            color: #0b62d6;
            font-weight: 700;
        }

        .footer {
            padding: 14px 20px;
            background: #fafafa;
            border-top: 1px solid var(--line);
            font-size: 12px;
            color: var(--muted);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div>
                <div class="title">ติดตามสถานะงานซ่อม</div>
                <div class="sub">เลขงาน {{ $repair->repair_number }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-weight:800;">{{ $repair->branch->name ?? 'สาขา' }}</div>
                @if(!empty($repair->branch->phone))
                <div class="sub">โทร {{ $repair->branch->phone }}</div>
                @endif
            </div>
        </div>
        <div class="content">
            <div class="row">
                <div class="label">สถานะปัจจุบัน</div>
                <div class="value"><span class="status">{{ $statusLabel }}</span></div>
            </div>
            <div class="row">
                <div class="label">วันที่รับเครื่อง</div>
                <div class="value">{{ optional($repair->received_at)->format('d/m/Y H:i') ?? $repair->created_at->format('d/m/Y H:i') }}</div>
            </div>
            @if($repair->estimated_completion)
            <div class="row">
                <div class="label">กำหนดส่ง</div>
                <div class="value">{{ $repair->estimated_completion->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($repair->completed_at)
            <div class="row">
                <div class="label">ซ่อมเสร็จ</div>
                <div class="value">{{ $repair->completed_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
            @if($repair->delivered_at)
            <div class="row">
                <div class="label">ส่งคืนแล้ว</div>
                <div class="value">{{ $repair->delivered_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif
        </div>
        <div class="footer">
            อัปเดตล่าสุด {{ $repair->updated_at->diffForHumans() }} • รีเฟรชหน้านี้เพื่อดูข้อมูลล่าสุด
        </div>
    </div>
</body>

</html>