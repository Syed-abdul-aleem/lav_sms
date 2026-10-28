@php
    $texCol = 'tex'.$ex->term;
    $totalMax = $subjects->count() * 100;
    $pct = ($exr->total !== null && $totalMax) ? round(($exr->total / $totalMax) * 100, 1) : null;
@endphp

<div style="border:2px solid #14532d; border-radius:8px; padding:10px 14px; margin-bottom:12px; background:#f0f7f1;">
    <strong style="color:#14532d; font-size:15px;">{{ strtoupper(Qs::getSetting('system_name')) }}</strong> &mdash; Result Card
    <div style="font-size:13px; margin-top:4px;">
        <strong>Student:</strong> {{ $sr->user->name }} &nbsp;|&nbsp;
        <strong>Class:</strong> {{ $my_class->name }} @if($sr->section) {{ $sr->section->name }} @endif &nbsp;|&nbsp;
        <strong>Adm No:</strong> {{ $sr->adm_no }}
        @if($sr->my_parent)
            &nbsp;|&nbsp; <strong>Father/Guardian:</strong> {{ $sr->my_parent->name }} ({{ $sr->my_parent->phone }})
        @endif
    </div>
</div>

<table class="table table-bordered text-center">
    <thead>
    <tr style="background:#14532d; color:#fff;">
        <th rowspan="2">S/N</th>
        <th rowspan="2" style="text-align:left;">SUBJECTS</th>
        <th rowspan="2">TEST 1<br>(20)</th>
        <th rowspan="2">TEST 2<br>(20)</th>
        <th rowspan="2">ASSESSMENT<br>(40)</th>
        <th rowspan="2">EXAM<br>(60)</th>
        <th rowspan="2">TOTAL<br>(100)</th>
        <th rowspan="2">GRADE</th>
        <th rowspan="2">SUBJECT<br>POSITION</th>
        <th rowspan="2">REMARKS</th>
    </tr>
    </thead>

    <tbody>
    @foreach($subjects as $sub)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td style="text-align:left;"><strong>{{ $sub->name }}</strong></td>
            @forelse($marks->where('subject_id', $sub->id)->where('exam_id', $ex->id) as $mk)
                <td>{{ ($mk->t1) ?: '-' }}</td>
                <td>{{ ($mk->t2) ?: '-' }}</td>
                <td>{{ ($mk->tca) ?: '-' }}</td>
                <td>{{ ($mk->exm) ?: '-' }}</td>
                <td><strong>{{ $mk->$texCol ?: '-' }}</strong></td>
                <td><strong>{{ ($mk->grade) ? $mk->grade->name : '-' }}</strong></td>
                <td>{!! ($mk->grade && $mk->sub_pos) ? Mk::getSuffix($mk->sub_pos) : '-' !!}</td>
                <td>{{ ($mk->grade) ? $mk->grade->remark : '-' }}</td>
            @empty
                <td colspan="8">-</td>
            @endforelse
        </tr>
    @endforeach
    <tr style="background:#fdf8ec; font-weight:bold;">
        <td colspan="3">TOTAL: {{ $exr->total }} / {{ $totalMax }}</td>
        <td colspan="3">PERCENTAGE: {{ $pct !== null ? $pct.'%' : '-' }}</td>
        <td colspan="2">AVERAGE: {{ $exr->ave }}</td>
        <td colspan="2">CLASS POSITION: {!! $exr->pos ? Mk::getSuffix($exr->pos) : '-' !!} &nbsp;|&nbsp; CLASS AVG: {{ $exr->class_ave }}</td>
    </tr>
    </tbody>
</table>
