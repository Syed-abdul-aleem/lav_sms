<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Slip — {{ $sr->user->name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; color: #222; margin: 0; padding: 24px; background: #f4f4f4; }
        .slip { max-width: 780px; margin: 0 auto; background: #fff; border: 2px solid #14532d; border-radius: 10px; overflow: hidden; }
        .head { background: #14532d; color: #fff; text-align: center; padding: 18px 12px 12px; }
        .head h1 { margin: 0; font-size: 24px; letter-spacing: .5px; }
        .head .gold { color: #d4af37; }
        .head p { margin: 3px 0; font-size: 12px; color: #e8e8e8; }
        .title-bar { background: #d4af37; color: #14532d; text-align: center; font-weight: bold; padding: 7px; font-size: 15px; letter-spacing: 2px; }
        .body { padding: 18px 22px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 4px 6px; font-size: 13px; vertical-align: top; }
        .info .lbl { font-weight: bold; width: 22%; color: #14532d; }
        .fees { margin-top: 12px; }
        .fees th { background: #14532d; color: #fff; font-size: 12.5px; padding: 8px 6px; text-align: left; }
        .fees td { border-bottom: 1px solid #ddd; padding: 7px 6px; font-size: 13px; }
        .fees .r { text-align: right; }
        .fees tfoot td { border-top: 2px solid #14532d; border-bottom: none; font-weight: bold; }
        .summary { margin-top: 16px; border: 1.5px solid #d4af37; border-radius: 8px; padding: 12px 16px; background: #fffdf3; }
        .summary table td { padding: 3.5px 4px; font-size: 13.5px; }
        .summary .r { text-align: right; }
        .summary .big td { font-size: 16px; font-weight: bold; color: #b91c1c; border-top: 1.5px solid #d4af37; padding-top: 8px; }
        .note { font-size: 11.5px; color: #555; margin-top: 14px; }
        .signs { margin-top: 34px; width: 100%; }
        .signs td { font-size: 12.5px; color: #333; padding-top: 6px; }
        .sign-line { border-top: 1px solid #333; width: 180px; margin-top: 26px; }
        .toolbar { max-width: 780px; margin: 0 auto 14px; text-align: center; }
        .toolbar a, .toolbar button { display: inline-block; margin: 3px; padding: 9px 16px; border-radius: 6px; border: none; font-size: 14px; cursor: pointer; text-decoration: none; color: #fff; }
        .btn-print { background: #14532d; } .btn-pdf { background: #b91c1c; } .btn-wa { background: #25d366; color: #063 !important; } .btn-back { background: #555; }
        @media print { body { background: #fff; padding: 0; } .toolbar { display: none; } .slip { border: none; border-radius: 0; } }
    </style>
</head>
<body>

@if(empty($pdf_mode))
    @php
        $waText = rawurlencode('Assalam-o-Alaikum! ' . $sr->user->name . ' (' . ($sr->my_class ? $sr->my_class->name : '') . ') ki fee tafseel: Kul Fee Rs ' . number_format($t['charged']) . ', Ada Shuda Rs ' . number_format($t['paid']) . ', Baqaya Rs ' . number_format($t['balance']) . ($t['arrears'] > 0 ? ' (is me pichla baqaya Rs ' . number_format($t['arrears']) . ' shamil hai)' : '') . '. Fee slip (PDF) sath bhej raha hoon. — ' . ($s['system_name'] ?? config('app.name')));
    @endphp
    <div class="toolbar">
        <button class="btn-print" onclick="window.print()">Print Slip</button>
        <a class="btn-pdf" href="{{ route('student_fees.pdf', $sr->user_id) }}">PDF Download</a>
        <a class="btn-wa" target="_blank" href="https://wa.me/{{ $wa_phone ?: '' }}?text={{ $waText }}">WhatsApp Par Bhejein</a>
        @if(!empty($staff))<a class="btn-back" href="{{ route('student_fees.student', $sr->user_id) }}">Wapas Fee Khata</a>@endif
    </div>
@endif

<div class="slip">
    <div class="head">
        <h1>{{ strtoupper($s['system_name'] ?? config('app.name')) }}</h1>
        @if(!empty($s['address']))<p>{{ $s['address'] }}</p>@endif
        <p>@if(!empty($s['phone']))Phone: {{ $s['phone'] }} @endif @if(!empty($s['system_email'])) · Email: {{ $s['system_email'] }}@endif</p>
    </div>
    <div class="title-bar">FEE SLIP</div>

    <div class="body">
        <table class="info">
            <tr>
                <td class="lbl">Student Name:</td><td>{{ $sr->user->name }}</td>
                <td class="lbl">Class / Section:</td><td>{{ $sr->my_class ? $sr->my_class->name : '' }}{{ $sr->section ? ' / ' . $sr->section->name : '' }}</td>
            </tr>
            <tr>
                <td class="lbl">Father / Guardian:</td><td>{{ $sr->my_parent ? $sr->my_parent->name : '—' }}</td>
                <td class="lbl">Admission No:</td><td>{{ $sr->adm_no ?: '—' }}</td>
            </tr>
            <tr>
                <td class="lbl">Session:</td><td>{{ $year }}</td>
                <td class="lbl">Slip Date:</td><td>{{ date('d M Y') }}</td>
            </tr>
        </table>

        <table class="fees">
            <thead>
                <tr><th style="width:6%">#</th><th>Fee</th><th style="width:18%">Date</th><th class="r" style="width:16%">Fee (Rs)</th><th class="r" style="width:16%">Paid (Rs)</th><th class="r" style="width:16%">Balance (Rs)</th></tr>
            </thead>
            <tbody>
            @forelse($records as $i => $r)
                @php
                    $amount = $r->payment ? (int) $r->payment->amount : 0;
                    $paidAmt = (int) ($r->amt_paid ?: 0);
                    $bal = max($amount - $paidAmt, 0);
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $r->payment ? $r->payment->title : 'Fee' }}</td>
                    <td>{{ $r->payment && $r->payment->created_at ? $r->payment->created_at->format('d M Y') : '—' }}</td>
                    <td class="r">{{ number_format($amount) }}</td>
                    <td class="r">{{ number_format($paidAmt) }}</td>
                    <td class="r">{{ number_format($bal) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center">No fee record for this session yet.</td></tr>
            @endforelse
            </tbody>
            <tfoot>
                <tr><td colspan="3" style="text-align:right">Total:</td>
                    <td class="r">{{ number_format($t['charged']) }}</td>
                    <td class="r">{{ number_format($t['paid']) }}</td>
                    <td class="r">{{ number_format($t['balance']) }}</td></tr>
            </tfoot>
        </table>

        <div class="summary">
            <table>
                <tr><td>Pichla Baqaya (Previous Remaining)</td><td class="r">Rs {{ number_format($t['arrears']) }}</td></tr>
                <tr><td>Is Mah Ki Fee (This Month's Fees)</td><td class="r">Rs {{ number_format($t['this_month']) }}</td></tr>
                <tr><td>Kul Fee (Total Fees)</td><td class="r">Rs {{ number_format($t['charged']) }}</td></tr>
                <tr><td>Ada Shuda (Total Paid)</td><td class="r">Rs {{ number_format($t['paid']) }}</td></tr>
                <tr class="big"><td>Kul Baqaya (Total Remaining)</td><td class="r">Rs {{ number_format($t['balance']) }}</td></tr>
            </table>
        </div>

        <p class="note">Note: Pichla Baqaya is month se pehle ki fees ka unpaid hissa hai. Meherbani karke baqaya fee jald ada karein. Koi ghalti nazar aye to school office se rabta karein.</p>

        <table class="signs">
            <tr>
                <td><div class="sign-line"></div>Accountant Signature</td>
                <td style="text-align:right"><div class="sign-line" style="margin-left:auto"></div>School Stamp</td>
            </tr>
        </table>
    </div>
</div>

</body>
</html>
