@extends('layouts.master')
@section('page_title', 'Create Exam Timetable (Easy)')
@section('content')

    @if($errors->any())
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <span class="font-weight-semibold">Oops!</span> {!! implode('<br>', $errors->all()) !!}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Exam Timetable — single form</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">Class choose karen, har paper ki aik line: <strong>Date, Time Start, Time End, Subject</strong> (Teacher/Invigilator optional). Save par is class ka purana exam timetable replace ho jayega.</p>

            <form method="get" action="{{ route('ett.exam.create') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-4">
                        <label>Class:</label>
                        <select name="class_id" class="form-control">
                            @foreach($my_classes as $c)
                                <option value="{{ $c->id }}" {{ $sel_class == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button class="btn btn-light">Load / Change Class</button>
                    </div>
                </div>
            </form>

            <hr>

            <form method="post" action="{{ route('ett.exam.store') }}">
                @csrf
                <input type="hidden" name="my_class_id" value="{{ $sel_class }}">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr><th style="width:16%;">Date</th><th style="width:13%;">Time Start</th><th style="width:13%;">Time End</th><th>Subject / Paper</th><th style="width:22%;">Teacher / Invigilator (optional)</th><th style="width:4%;"></th></tr>
                        </thead>
                        <tbody id="exam-rows">
                            @forelse($entries as $i => $e)
                                <tr>
                                    <td><input type="date" name="rows[{{ $i }}][exam_date]" class="form-control" value="{{ $e->exam_date }}"></td>
                                    <td><input type="time" name="rows[{{ $i }}][start_time]" class="form-control" value="{{ $e->start_time }}"></td>
                                    <td><input type="time" name="rows[{{ $i }}][end_time]" class="form-control" value="{{ $e->end_time }}"></td>
                                    <td><input type="text" name="rows[{{ $i }}][subject]" class="form-control" value="{{ $e->subject }}" placeholder="Subject"></td>
                                    <td>
                                        <select name="rows[{{ $i }}][teacher_id]" class="form-control">
                                            <option value="">— optional —</option>
                                            @foreach($teachers as $t)<option value="{{ $t->id }}" {{ $e->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">✕</button></td>
                                </tr>
                            @empty
                                @for($i = 0; $i < 5; $i++)
                                    <tr>
                                        <td><input type="date" name="rows[{{ $i }}][exam_date]" class="form-control"></td>
                                        <td><input type="time" name="rows[{{ $i }}][start_time]" class="form-control"></td>
                                        <td><input type="time" name="rows[{{ $i }}][end_time]" class="form-control"></td>
                                        <td><input type="text" name="rows[{{ $i }}][subject]" class="form-control" placeholder="Subject"></td>
                                        <td>
                                            <select name="rows[{{ $i }}][teacher_id]" class="form-control">
                                                <option value="">— optional —</option>
                                                @foreach($teachers as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach
                                            </select>
                                        </td>
                                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">✕</button></td>
                                    </tr>
                                @endfor
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-outline-success" onclick="addExamRow()"><i class="icon-plus3 mr-1"></i> Add Paper</button>
                <div class="text-right mt-2">
                    <button type="submit" class="btn btn-primary btn-lg">Save Exam Timetable <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var examCount = {{ max($entries->count(), 5) }};
        function addExamRow() {
            var wrap = document.getElementById('exam-rows');
            var tr = wrap.querySelector('tr').cloneNode(true);
            tr.querySelectorAll('input, select').forEach(function (el) {
                el.name = el.name.replace(/rows\[\d+\]/, 'rows[' + examCount + ']');
                if (el.tagName === 'INPUT') el.value = '';
                if (el.tagName === 'SELECT') el.selectedIndex = 0;
            });
            wrap.appendChild(tr);
            examCount++;
        }
    </script>

@endsection
