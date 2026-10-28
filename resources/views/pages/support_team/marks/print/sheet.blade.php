{{-- Pakistani Result Card — student info + marks table + summary --}}
@php
    $texCol = 'tex'.$ex->term;
    $totalMax = $subjects->count() * 100;
    $pct = ($exr && $exr->total !== null && $totalMax) ? round(($exr->total / $totalMax) * 100, 1) : null;
    $overallGrade = null;
    if ($pct !== null) {
        foreach (\App\Helpers\Mk::getGradeList($class_type->id) as $gr) {
            if ($pct >= $gr->mark_from && $pct <= $gr->mark_to) { $overallGrade = $gr; break; }
        }
    }
@endphp

<table class="rc-info">
    <tbody>
    <tr>
        <td><strong>STUDENT NAME:</strong> {{ strtoupper($sr->user->name) }}</td>
        <td><strong>ADMISSION NO:</strong> {{ $sr->adm_no }}</td>
        <td><strong>CLASS:</strong> {{ strtoupper($my_class->name) }}@if($sr->section) - {{ strtoupper($sr->section->name) }}@endif</td>
    </tr>
    @if($sr->my_parent)
    <tr>
        <td><strong>FATHER / GUARDIAN:</strong> {{ strtoupper($sr->my_parent->name) }}</td>
        <td><strong>GUARDIAN PHONE:</strong> {{ $sr->my_parent->phone }}</td>
        <td><strong>SESSION:</strong> {{ $ex->year }}</td>
    </tr>
    @endif
    <tr>
        <td><strong>EXAMINATION:</strong> {{ strtoupper($ex->name) }}</td>
        <td><strong>TERM:</strong> {!! strtoupper(Mk::getSuffix($ex->term)) !!} TERM</td>
        <td><strong>AGE:</strong> {{ $sr->age ?: ($sr->user->dob ? date_diff(date_create($sr->user->dob), date_create('now'))->y : '-') }} YEARS</td>
    </tr>
    </tbody>
</table>

<table class="rc-table">
    <thead>
    <tr>
        <th style="width:34px;">S.NO</th>
        <th style="text-align:left;">SUBJECT</th>
        <th>TEST 1<br>(20)</th>
        <th>TEST 2<br>(20)</th>
        <th>ASSESSMENT<br>(40)</th>
        <th>EXAM<br>(60)</th>
        <th>TOTAL<br>(100)</th>
        <th>GRADE</th>
        <th>POSITION</th>
        <th style="text-align:left;">REMARKS</th>
    </tr>
    </thead>
    <tbody>
    @foreach($subjects as $sub)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td style="text-align:left; font-weight:bold;">{{ $sub->name }}</td>
            @forelse($marks->where('subject_id', $sub->id)->where('exam_id', $ex->id) as $mk)
                <td>{{ $mk->t1 ?: '-' }}</td>
                <td>{{ $mk->t2 ?: '-' }}</td>
                <td>{{ $mk->tca ?: '-' }}</td>
                <td>{{ $mk->exm ?: '-' }}</td>
                <td><strong>{{ $mk->$texCol ?: '-' }}</strong></td>
                <td><strong>{{ $mk->grade ? $mk->grade->name : '-' }}</strong></td>
                <td>{!! ($mk->grade && $mk->sub_pos) ? Mk::getSuffix($mk->sub_pos) : '-' !!}</td>
                <td style="text-align:left;">{{ $mk->grade ? $mk->grade->remark : '-' }}</td>
            @empty
                <td colspan="8">-</td>
            @endforelse
        </tr>
    @endforeach
    </tbody>
</table>

<table class="rc-summary">
    <tbody>
    <tr>
        <td><strong>TOTAL MARKS:</strong> {{ $exr->total }} / {{ $totalMax }}</td>
        <td><strong>PERCENTAGE:</strong> {{ $pct !== null ? $pct.'%' : '-' }}</td>
        <td><strong>OVERALL GRADE:</strong> {{ $overallGrade ? $overallGrade->name.' ('.$overallGrade->remark.')' : '-' }}</td>
        <td><strong>CLASS POSITION:</strong> {!! $exr->pos ? Mk::getSuffix($exr->pos) : '-' !!}</td>
        <td><strong>CLASS AVERAGE:</strong> {{ $exr->class_ave }}</td>
    </tr>
    </tbody>
</table>
