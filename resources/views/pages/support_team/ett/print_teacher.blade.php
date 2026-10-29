<html>
<head>
    <title>Teacher Timetable - {{ optional($teacher)->name }} - {{ Qs::getSetting('system_name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}" />
    <style>
        .tt-head { text-align:center; border-bottom:3px solid #14532d; padding-bottom:8px; margin-bottom:10px; }
        .tt-head h1 { margin:0; color:#14532d; font-size:24px; letter-spacing:1px; }
        .tt-head .sub { color:#8a6d1f; font-weight:bold; }
        table.tt { width:100%; border-collapse:collapse; }
        table.tt th { background:#14532d; color:#fff; padding:6px; font-size:13px; }
        table.tt td { border:1px solid #bbb; vertical-align:top; padding:6px; width:16.6%; }
        .p { border-left:3px solid #2f7d46; padding:3px 5px; margin-bottom:6px; background:#fbfdfb; }
        .p .t { font-weight:bold; font-size:11px; color:#14532d; }
        .p .s { font-weight:bold; font-size:13px; }
        .p .m { font-size:11px; color:#555; }
    </style>
</head>
<body>
<div class="container">
    <div class="tt-head">
        <h1>{{ strtoupper(Qs::getSetting('system_name')) }}</h1>
        <div class="sub">TEACHER TIMETABLE — {{ strtoupper(optional($teacher)->name) }} · Session {{ Qs::getSetting('current_session') }}</div>
    </div>
    @php $showDays = collect($days)->filter(function($d) use ($byDay){ return $d !== 'Sunday' || ($byDay->has('Sunday') && $byDay['Sunday']->count()); }); @endphp
    <table class="tt">
        <thead><tr>@foreach($showDays as $d)<th>{{ $d }}</th>@endforeach</tr></thead>
        <tbody>
            <tr>
            @foreach($showDays as $d)
                <td>
                    @foreach(($byDay[$d] ?? collect()) as $e)
                        <div class="p">
                            <div class="t">{{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->end_time) }}</div>
                            <div class="s">{{ $e->subject ?: '—' }}</div>
                            <div class="m">{{ optional($e->my_class)->name }} {{ optional($e->section)->name }}</div>
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
