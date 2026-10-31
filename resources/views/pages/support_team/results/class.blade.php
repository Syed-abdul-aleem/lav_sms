@extends('layouts.master')
@section('page_title', 'Class Result — '.$exam->name)
@section('content')

    <div class="card">
        <div class="card-header bg-white header-elements-inline">
            <h5 class="card-title font-weight-bold"><i class="icon-graduation2 mr-2"></i> Class Result — {{ $exam->name }} (Term {{ $exam->term }})</h5>
            <div class="header-elements">
                <a href="{{ route('results.index') }}" class="btn btn-light btn-sm"><i class="icon-arrow-left8 mr-1"></i> Results Home</a>
                <a href="{{ route('results.entry', ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $section_id]) }}" class="btn btn-primary btn-sm ml-2"><i class="icon-pencil7 mr-1"></i> Marks Edit Karein</a>
                <a target="_blank" href="{{ route('results.class_print', ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $section_id]) }}" class="btn btn-success btn-sm ml-2"><i class="icon-printer mr-1"></i> Print Result</a>
            </div>
        </div>
        <div class="card-body">
            <span class="font-size-lg">Class: <strong>{{ $class->name }}</strong></span>
            @if($sections->count() > 1)
                <span class="ml-3">Section:
                @foreach($sections as $sec)
                    <a href="{{ route('results.class', ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $sec->id]) }}" class="badge {{ $sec->id == $section_id ? 'badge-success' : 'badge-light' }}">{{ $sec->name }}</a>
                @endforeach
                </span>
            @endif
            @php $classAve = collect($rows)->avg('ave'); @endphp
            <span class="ml-3">Students: <strong>{{ count($rows) }}</strong></span>
            <span class="ml-3">Class Average: <strong>{{ $rows ? number_format($classAve, 1) : '—' }}%</strong></span>
        </div>

        @if(count($rows) == 0)
            <div class="card-body border-top"><div class="alert alert-info mb-0">Is class/section me abhi koi student nahi.</div></div>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width:60px;">Pos</th>
                        <th>Student</th>
                        @foreach($subjects as $sub)<th class="text-center">{{ $sub->name }}</th>@endforeach
                        <th class="text-center">Total</th>
                        <th class="text-center">%</th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Result Card</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td class="text-center font-weight-bold">{{ $r['pos'] ?: '—' }}</td>
                        <td class="font-weight-bold">{{ $r['sr']->user->name }}<br><small class="text-muted">{{ $r['sr']->adm_no }}</small></td>
                        @foreach($subjects as $sub)
                            <td class="text-center">{{ $r['cells'][$sub->id] ?? '—' }}</td>
                        @endforeach
                        <td class="text-center font-weight-bold">{{ rtrim(rtrim(number_format($r['total'], 1), '0'), '.') }} / {{ $subjects->count() * 100 }}</td>
                        <td class="text-center font-weight-bold">{{ number_format($r['ave'], 1) }}%</td>
                        <td class="text-center"><span class="badge badge-success">{{ $r['grade'] }}</span><br><small class="text-muted">{{ $r['remark'] }}</small></td>
                        <td class="text-center"><a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('marks.show', [Qs::hash($r['sr']->user_id), Qs::getCurrentSession()]) }}"><i class="icon-file-text2 mr-1"></i> Card</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            <small class="text-muted">Result Card button se har student ki mukammal marksheet khulti hai — wahan se us exam ka print bhi mil jata hai. Marks ghalat hon to "Marks Edit Karein" se theek kar lein, result khud update ho jayega.</small>
        </div>
        @endif
    </div>

@endsection
