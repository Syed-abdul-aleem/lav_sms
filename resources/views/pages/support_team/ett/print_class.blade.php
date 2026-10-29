<html>
<head>
    <title>Class Timetable - {{ optional($class)->name }} {{ optional($section)->name }} - {{ Qs::getSetting('system_name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}" />
    <style>
        .tt-head { text-align:center; border-bottom:3px solid #14532d; padding-bottom:8px; margin-bottom:10px; }
        .tt-head h1 { margin:0; color:#14532d; font-size:24px; letter-spacing:1px; }
        .tt-head .sub { color:#8a6d1f; font-weight:bold; }
        table.tt { width:100%; border-collapse:collapse; }
        table.tt th { background:#14532d; color:#fff; padding:6px; font-size:13px; }
        table.tt td { border:1px solid #bbb; vertical-align:top; padding:6px; width:16.6%; }
        .p { border-left:3px solid #2f7d46; padding:3px 5px; margin-bottom:6px; background:#fbfdfb; }
        .p.break { border-left-color:#c9a227; background:#fffdf2; }
        .p.assembly { border-left-color:#38618c; background:#f4f8fd; }
        .p .t { font-weight:bold; font-size:11px; color:#14532d; }
        .p .s { font-weight:bold; font-size:13px; }
        .p .m { font-size:11px; color:#555; }
        .timing-line { text-align:center; font-size:13px; margin-bottom:10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="tt-head">
        <h1>{{ strtoupper(Qs::getSetting('system_name')) }}</h1>
        <div class="sub">CLASS TIMETABLE — {{ strtoupper(optional($class)->name . ' ' . optional($section)->name) }} · Session {{ Qs::getSetting('current_session') }}</div>
    </div>
    @if($timing)
        <div class="timing-line">School Timing: <strong>{{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->end_time) }}</strong>@if($timing->friday_end_time) · Friday till {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->friday_end_time) }}@endif @if($timing->break_start) · Break: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->break_start) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->break_end) }}@endif @if($timing->note) · {{ $timing->note }}@endif</div>
    @endif
    @php $showDays = collect($days)->filter(function($d) use ($byDay){ return $d !== 'Sunday' || ($byDay->has('Sunday') && $byDay['Sunday']->count()); }); @endphp
    <table class="tt">
        <thead><tr>@foreach($showDays as $d)<th>{{ $d }}</th>@endforeach</tr></thead>
        <tbody>
            <tr>
            @foreach($showDays as $d)
                <td>
                    @foreach(($byDay[$d] ?? collect()) as $e)
                        <div class="p {{ strtolower($e->kind) }}">
                            <div class="t">{{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->end_time) }}</div>
                            <div class="s">{{ $e->kind === 'Period' ? ($e->subject ?: '—') : $e->kind }}{{ $e->kind !== 'Period' && $e->subject ? ' — '.$e->subject : '' }}</div>
                            @if($e->teacher_name)<div class="m">{{ $e->teacher_name }}</div>@endif
                        </div>
                    @endforeach
                </td>
            @endforeach
            </tr>
        </tbody>
    </table>
    <script>window.onload = function(){ window.print(); };</script>
</div>
</body>
</html>
