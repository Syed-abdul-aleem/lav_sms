@extends('layouts.master')
@section('page_title', 'Marks Entry — '.$exam->name)
@section('content')

    <div class="card">
        <div class="card-header bg-white header-elements-inline">
            <h5 class="card-title font-weight-bold"><i class="icon-pencil7 mr-2"></i> Marks Entry — {{ $exam->name }} (Term {{ $exam->term }})</h5>
            <div class="header-elements">
                <a href="{{ route('results.index') }}" class="btn btn-light btn-sm"><i class="icon-arrow-left8 mr-1"></i> Results Home</a>
                <a href="{{ route('results.class', ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $section_id]) }}" class="btn btn-success btn-sm ml-2"><i class="icon-graduation2 mr-1"></i> Class Result</a>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="font-size-lg">Class: <strong>{{ $class->name }}</strong>
                    @if($sections->count() > 1)
                        <span class="ml-2">Section:
                        @foreach($sections as $sec)
                            <a href="{{ route('results.entry', ['exam_id' => $exam->id, 'class_id' => $class->id, 'section_id' => $sec->id]) }}" class="badge {{ $sec->id == $section_id ? 'badge-success' : 'badge-light' }}">{{ $sec->name }}</a>
                        @endforeach
                        </span>
                    @endif
                </div>
                <div class="text-muted">Har subject ke kul marks <strong>100</strong> me se darj karein. Khali box = koi tabdeeli nahi.</div>
            </div>
        </div>

        @if($students->count() == 0)
            <div class="card-body border-top"><div class="alert alert-info mb-0">Is class/section me abhi koi student nahi.</div></div>
        @elseif($subjects->count() == 0)
            <div class="card-body border-top"><div class="alert alert-warning mb-0">Is class ke subjects abhi add nahi hue — pehle <a href="{{ route('subjects.index') }}">Subjects</a> page se subjects add karein.</div></div>
        @else
        <form method="post" action="{{ route('results.entry_store') }}">
            @csrf
            <input type="hidden" name="exam_id" value="{{ $exam->id }}">
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="section_id" value="{{ $section_id }}">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="min-width:180px;">Student</th>
                            @foreach($subjects as $sub)<th class="text-center" style="min-width:95px;">{{ $sub->name }}</th>@endforeach
                            <th class="text-center" style="min-width:80px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $sr)
                        <tr>
                            <td class="font-weight-bold">{{ $sr->user->name }}<br><small class="text-muted">{{ $sr->adm_no }}</small></td>
                            @foreach($subjects as $sub)
                                <td><input type="number" min="0" max="100" step="1" class="form-control text-center mark-input"
                                           name="marks[{{ $sr->user_id }}][{{ $sub->id }}]"
                                           value="{{ $grid[$sr->user_id][$sub->id] ?? '' }}" placeholder="—"></td>
                            @endforeach
                            <td class="text-center font-weight-bold row-total">—</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <button type="submit" class="btn btn-success btn-lg"><i class="icon-checkmark3 mr-1"></i> Marks Save Karein</button>
                <span class="text-muted ml-2">Save karte hi grades, positions aur class result khud ban jayenge. Agar kisi subject ke tests (T1/T2) pehle se darj hain to wo kul marks me shamil rahenge.</span>
            </div>
        </form>
        @endif
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        function refreshRowTotals() {
            document.querySelectorAll('tbody tr').forEach(function (tr) {
                var inputs = tr.querySelectorAll('.mark-input');
                if (!inputs.length) { return; }
                var sum = 0, any = false;
                inputs.forEach(function (i) { if (i.value !== '') { sum += parseInt(i.value || 0, 10); any = true; } });
                var cell = tr.querySelector('.row-total');
                if (cell) { cell.textContent = any ? sum : '—'; }
            });
        }
        document.addEventListener('input', function (e) { if (e.target.classList.contains('mark-input')) { refreshRowTotals(); } });
        refreshRowTotals();
    </script>
@endsection
