@extends('layouts.master')
@section('page_title', 'Create Class Timetable (Easy)')
@section('content')

    @if($errors->any())
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <span class="font-weight-semibold">Oops!</span> {!! implode('<br>', $errors->all()) !!}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Class Timetable — single form</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">Bas ye karna hai: Class + Section choose karen, har period ki aik line bharen — <strong>Din, Time Start, Time End, Subject</strong> (Teacher optional hai). Break ke liye Type = Break kar dein. <strong>"Copy Day"</strong> se aik din ke periods baqi dinon par copy ho jate hain. Save karne par is class ka purana timetable inhi periods se replace ho jayega.</p>

            {{-- Class / Section picker --}}
            <form method="get" action="{{ route('ett.class.create') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label>Class:</label>
                        <select name="class_id" id="pick-class" class="form-control" onchange="syncSections();">
                            @foreach($my_classes as $c)
                                <option value="{{ $c->id }}" {{ $sel_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label>Section:</label>
                        <select name="section_id" id="pick-section" class="form-control">
                            @foreach($sections->where('my_class_id', $sel_class) as $s)
                                <option value="{{ $s->id }}" {{ $sel_section == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-light">Load / Change Class</button>
                    </div>
                </div>
            </form>

            <hr>

            {{-- Copy day helper --}}
            <div class="row mb-2 align-items-end">
                <div class="col-md-3">
                    <label>Copy Day — kis din se:</label>
                    <select id="copy-from" class="form-control">
                        @foreach($days as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Kin dinon par copy karen:</label><br>
                    @foreach($days as $d)
                        @if($d !== 'Sunday')<label class="mr-2"><input type="checkbox" class="copy-to" value="{{ $d }}"> {{ substr($d,0,3) }}</label>@endif
                    @endforeach
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-primary btn-block" onclick="copyDay()">Copy Day ↓</button>
                </div>
            </div>

            {{-- Editor --}}
            <form method="post" action="{{ route('ett.class.store') }}" id="ett-form">
                @csrf
                <input type="hidden" name="my_class_id" value="{{ $sel_class }}">
                <input type="hidden" name="section_id" value="{{ $sel_section }}">

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th style="width:13%;">Day</th><th style="width:12%;">Time Start</th><th style="width:12%;">Time End</th><th>Subject</th><th style="width:20%;">Teacher (optional)</th><th style="width:11%;">Type</th><th style="width:4%;"></th></tr>
                        </thead>
                        <tbody id="rows-wrap">
                            @forelse($entries as $i => $e)
                                @include('pages.support_team.ett._row', ['idx' => $i, 'e' => $e])
                            @empty
                                @for($i = 0; $i < 6; $i++)
                                    @include('pages.support_team.ett._row', ['idx' => $i, 'e' => null])
                                @endfor
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <datalist id="ett-subjects">
                    @foreach(collect($subjects)->pluck('name')->unique() as $sn)<option value="{{ $sn }}">@endforeach
                    @foreach(Qs::getPakistaniSubjects() as $sn)<option value="{{ $sn }}">@endforeach
                </datalist>

                <button type="button" class="btn btn-outline-success" onclick="addRow()"><i class="icon-plus3 mr-1"></i> Add Period</button>
                <div class="text-right mt-2">
                    <button type="submit" class="btn btn-primary btn-lg">Save Timetable <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div id="row-template" style="display:none;">
        <table><tbody>@include('pages.support_team.ett._row', ['idx' => '__IDX__', 'e' => null])</tbody></table>
    </div>

    <script>
        var rowCount = {{ max($entries->count(), 6) }};
        var sectionsData = [@foreach($sections as $s){id:{{ $s->id }}, class_id:{{ $s->my_class_id }}, name:"{{ $s->name }}"},@endforeach];

        function syncSections() {
            var cid = document.getElementById('pick-class').value;
            var sel = document.getElementById('pick-section');
            sel.innerHTML = '';
            sectionsData.filter(function (s) { return s.class_id == cid; }).forEach(function (s) {
                var o = document.createElement('option'); o.value = s.id; o.textContent = s.name; sel.appendChild(o);
            });
        }

        function addRow(prefill) {
            var html = document.getElementById('row-template').innerHTML.replace(/__IDX__/g, rowCount);
            var tmp = document.createElement('tbody');
            tmp.innerHTML = html;
            var tr = tmp.querySelector('tr');
            if (prefill) {
                tr.querySelector('.row-day').value = prefill.day;
                tr.querySelector('.row-start').value = prefill.start;
                tr.querySelector('.row-end').value = prefill.end;
                tr.querySelector('.row-subject').value = prefill.subject;
                tr.querySelector('.row-teacher').value = prefill.teacher;
                tr.querySelector('.row-kind').value = prefill.kind;
            }
            document.getElementById('rows-wrap').appendChild(tr);
            rowCount++;
        }

        function copyDay() {
            var from = document.getElementById('copy-from').value;
            var targets = Array.prototype.map.call(document.querySelectorAll('.copy-to:checked'), function (c) { return c.value; });
            if (!targets.length) { alert('Pehle wo din tick karen jin par copy karna hai.'); return; }
            var source = [];
            document.querySelectorAll('#rows-wrap tr').forEach(function (tr) {
                if (tr.querySelector('.row-day').value === from && tr.querySelector('.row-start').value) {
                    source.push({
                        start: tr.querySelector('.row-start').value,
                        end: tr.querySelector('.row-end').value,
                        subject: tr.querySelector('.row-subject').value,
                        teacher: tr.querySelector('.row-teacher').value,
                        kind: tr.querySelector('.row-kind').value
                    });
                }
            });
            if (!source.length) { alert(from + ' ke koi periods nahi mile — pehle us din ke periods likhen.'); return; }
            targets.forEach(function (day) {
                if (day === from) return;
                source.forEach(function (s) { addRow(Object.assign({}, s, {day: day})); });
            });
        }
    </script>

@endsection
