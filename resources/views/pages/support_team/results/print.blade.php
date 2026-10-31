<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Class Result — {{ $exam->name }} — {{ $class->name }}</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1c2b21; margin: 28px; }
        .head { text-align: center; border-bottom: 3px solid #14532d; padding-bottom: 10px; margin-bottom: 14px; }
        .head h1 { margin: 0; color: #14532d; font-size: 26px; }
        .head .sub { color: #8a6d1d; font-weight: 600; letter-spacing: 1px; font-size: 12px; text-transform: uppercase; }
        .meta { text-align: center; margin-bottom: 14px; font-size: 14px; }
        .meta strong { color: #14532d; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #14532d; color: #fff; padding: 7px 6px; text-align: center; }
        th:first-child, td.name { text-align: left; }
        td { border: 1px solid #cfd8d2; padding: 6px; text-align: center; }
        tr:nth-child(even) td { background: #f4f8f5; }
        td.name { font-weight: 600; }
        .pos { font-weight: 800; color: #14532d; }
        .foot { margin-top: 26px; display: flex; justify-content: space-between; font-size: 12px; color: #555; }
        .sig { border-top: 1px solid #333; padding-top: 4px; width: 180px; text-align: center; }
        @media print { body { margin: 10px; } }
    </style>
</head>
<body>
    <div class="head">
        <div class="sub">Class Result Sheet</div>
        <h1>{{ $school }}</h1>
        <div>{{ $exam->name }} — Term {{ $exam->term }} — Session {{ $year }}</div>
    </div>
    <div class="meta">Class: <strong>{{ $class->name }}{{ $section ? ' — Section '.$section->name : '' }}</strong> &nbsp;·&nbsp; Students: <strong>{{ count($rows) }}</strong> &nbsp;·&nbsp; Class Average: <strong>{{ $rows ? number_format(collect($rows)->avg('ave'), 1) : '—' }}%</strong></div>

    <table>
        <thead>
            <tr>
                <th style="width:45px;">Pos</th>
                <th>Student</th>
                @foreach($subjects as $sub)<th>{{ $sub->name }}</th>@endforeach
                <th>Total</th>
                <th>%</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows as $r)
            <tr>
                <td class="pos">{{ $r['pos'] ?: '—' }}</td>
                <td class="name">{{ $r['sr']->user->name }}<br><small>{{ $r['sr']->adm_no }}</small></td>
                @foreach($subjects as $sub)<td>{{ $r['cells'][$sub->id] ?? '—' }}</td>@endforeach
                <td><strong>{{ rtrim(rtrim(number_format($r['total'], 1), '0'), '.') }} / {{ $subjects->count() * 100 }}</strong></td>
                <td>{{ number_format($r['ave'], 1) }}%</td>
                <td><strong>{{ $r['grade'] }}</strong></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="foot">
        <div class="sig">Class Teacher</div>
        <div class="sig">Principal</div>
    </div>
    <script>window.onload = function(){ setTimeout(function(){ window.print(); }, 350); };</script>
</body>
</html>
