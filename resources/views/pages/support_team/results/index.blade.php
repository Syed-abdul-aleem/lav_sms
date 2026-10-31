@extends('layouts.master')
@section('page_title', 'Results (Easy)')
@section('content')

    <div class="card">
        <div class="card-header bg-white header-elements-inline">
            <h5 class="card-title font-weight-bold"><i class="icon-graduation2 mr-2"></i> Results (Easy) — Exam, Marks aur Result aik hi jagah</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4 mb-2"><div class="border rounded p-3 h-100"><div class="font-size-lg font-weight-bold text-success">Step 1</div>Exam banayein (ya purana select karein)</div></div>
                <div class="col-md-4 mb-2"><div class="border rounded p-3 h-100"><div class="font-size-lg font-weight-bold text-success">Step 2</div>Class chun kar poori class ke marks aik page par darj karein</div></div>
                <div class="col-md-4 mb-2"><div class="border rounded p-3 h-100"><div class="font-size-lg font-weight-bold text-success">Step 3</div>Class result dekhein, print karein — har student ka result card bhi wahin se</div></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-white"><h6 class="card-title font-weight-bold"><i class="icon-plus3 mr-2"></i> Naya Exam Banayein</h6></div>
                <div class="card-body">
                    <form method="post" action="{{ route('results.exam_store') }}">
                        @csrf
                        <div class="form-group">
                            <label class="font-weight-bold">Exam ka Naam</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Mid Term Exam" list="exam-names">
                            <datalist id="exam-names">
                                <option value="Monthly Test"></option>
                                <option value="Mid Term Exam"></option>
                                <option value="Final Term Exam"></option>
                                <option value="Annual Exam"></option>
                            </datalist>
                        </div>
                        <div class="form-group">
                            <label class="font-weight-bold">Term</label>
                            <select name="term" class="form-control" required>
                                <option value="1">Term 1</option>
                                <option value="2">Term 2</option>
                                <option value="3">Term 3</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-block"><i class="icon-plus3 mr-1"></i> Exam Banayein</button>
                        <small class="text-muted d-block mt-2">Note: har term ka aik hi exam hota hai — agar us term ka exam pehle se hai to wahi istemal hoga.</small>
                    </form>
                    <hr>
                    <p class="text-muted mb-0"><small>Grades (A+, A, B…) aik dafa setup hote hain — <a href="{{ route('grades.index') }}">Grades page</a> se check kar lein. Exams ki list/edit ke liye <a href="{{ route('exams.index') }}">Exam List</a>.</small></p>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-white"><h6 class="card-title font-weight-bold"><i class="icon-pencil7 mr-2"></i> Marks Darj Karein / Result Dekhein</h6></div>
                <div class="card-body">
                    <form method="get">
                        <div class="form-group">
                            <label class="font-weight-bold">Exam</label>
                            <select name="exam_id" class="form-control" required>
                                <option value="">— Exam select karein —</option>
                                @foreach($exams as $ex)
                                    <option value="{{ $ex->id }}">{{ $ex->name }} (Term {{ $ex->term }})</option>
                                @endforeach
                            </select>
                            @if($exams->count() == 0)<small class="text-danger">Abhi koi exam nahi — pehle left side se naya exam banayein.</small>@endif
                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label class="font-weight-bold">Class</label>
                                    <select name="class_id" id="pick-class" class="form-control" required onchange="syncSections()">
                                        <option value="">— Class select karein —</option>
                                        @foreach($my_classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="font-weight-bold">Section</label>
                                    <select name="section_id" id="pick-section" class="form-control"></select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <button type="submit" formaction="{{ route('results.entry') }}" class="btn btn-primary btn-block"><i class="icon-pencil7 mr-1"></i> Marks Darj Karein</button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="submit" formaction="{{ route('results.class') }}" class="btn btn-success btn-block"><i class="icon-graduation2 mr-1"></i> Class Result Dekhein</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        var sectionsData = [@foreach($sections as $s){id:{{ $s->id }}, class_id:{{ $s->my_class_id }}, name:"{{ $s->name }}"},@endforeach];
        function syncSections() {
            var cid = document.getElementById('pick-class').value;
            var sel = document.getElementById('pick-section');
            sel.innerHTML = '';
            sectionsData.filter(function (s) { return s.class_id == cid; }).forEach(function (s) {
                var o = document.createElement('option'); o.value = s.id; o.textContent = s.name; sel.appendChild(o);
            });
        }
        syncSections();
    </script>
@endsection
