<html>
<head>
    <title>Exam Timetable - {{ optional($class)->name }} - {{ Qs::getSetting('system_name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}" />
    <style>
        .tt-head { text-align:center; border-bottom:3px solid #14532d; padding-bottom:8px; margin-bottom:10px; }
        .tt-head h1 { margin:0; color:#14532d; font-size:24px; letter-spacing:1px; }
        .tt-head .sub { color:#8a6d1f; font-weight:bold; }
        table.tt { width:100%; border-collapse:collapse; }
        table.tt th { background:#14532d; color:#fff; padding:7px; font-size:13px; text-align:left; }
        table.tt td { border:1px solid #bbb; padding:7px; font-size:13px; }
    </style>
</head>
<body>
<div class="container">
    <div class="tt-head">
        <h1>{{ strtoupper(Qs::getSetting('system_name')) }}</h1>
        <div class="sub">EXAM TIMETABLE (DATE SHEET) — {{ strtoupper(optional($class)->name) }} · Session {{ Qs::getSetting('current_session') }}</div>
    </div>
    <table class="tt">
        <thead><tr><th>Date</th><th>Day</th><th>Time</th><th>Subject / Paper</th><th>Invigilator</th></tr></thead>
        <tbody>
        @foreach($entries as $e)
            <tr>
                <td>{{ date('d M Y', strtotime($e->exam_date)) }}</td>
                <td>{{ date('l', strtotime($e->exam_date)) }}</td>
                <td>{{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->end_time) }}</td>
                <td><strong>{{ $e->subject }}</strong></td>
                <td>{{ $e->teacher_name ?: '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <script>window.onload = function(){ window.print(); };</script>
</div>
</body>
</html>
