@extends('layouts.master')
@section('page_title', 'Timetables (Easy)')
@section('content')

    @if(session('flash_success'))
        <div class="alert alert-success alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('flash_success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Timetables — sab kuch aik jaga, banana sab se asaan</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('ett.class.create') }}" class="btn btn-success btn-block"><i class="icon-table2 mr-1"></i> Class Timetable<br><small>single form se banayein</small></a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('ett.timing') }}" class="btn btn-info btn-block"><i class="icon-alarm mr-1"></i> School Timing<br><small>start / end / break</small></a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('ett.teachers') }}" class="btn btn-primary btn-block"><i class="icon-user-tie mr-1"></i> Teachers<br><small>har teacher ka timetable</small></a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('ett.master') }}" class="btn btn-dark btn-block"><i class="icon-grid mr-1"></i> Master<br><small>poora school aik page</small></a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('ett.exam.create') }}" class="btn btn-warning btn-block"><i class="icon-pencil7 mr-1"></i> Exam Timetable<br><small>date wise papers</small></a></div>
            </div>

            @if($timing)
                <div class="alert alert-info mt-2 mb-0">
                    <strong>School Timing:</strong>
                    {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->end_time) }}
                    @if($timing->friday_end_time) · Friday khatam: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->friday_end_time) }} @endif
                    @if($timing->break_start) · Break: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->break_start) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->break_end) }} @endif
                    @if($timing->assembly_start) · Assembly: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->assembly_start) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->assembly_end) }} @endif
                    @if($timing->note) · {{ $timing->note }} @endif
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="card-title font-weight-bold">Class Timetables</h6></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Class</th><th>Section</th><th>Periods</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @foreach($classes as $c)
                    @foreach($c->sections as $s)
                        @php $cnt = $counts[$c->id.'-'.$s->id] ?? 0; @endphp
                        <tr>
                            <td>{{ $c->name }}</td>
                            <td>{{ $s->name }}</td>
                            <td>{{ $cnt ?: '—' }}</td>
                            <td class="text-right">
                                @if($cnt)
                                    <a href="{{ route('ett.class.show', [$c->id, $s->id]) }}" class="btn btn-sm btn-primary">View</a>
                                    <a href="{{ route('ett.class.print', [$c->id, $s->id]) }}" target="_blank" class="btn btn-sm btn-light">Print</a>
                                @endif
                                @if(Qs::userIsTeamSA())
                                    <a href="{{ route('ett.class.create', ['class_id' => $c->id, 'section_id' => $s->id]) }}" class="btn btn-sm btn-success">{{ $cnt ? 'Edit' : 'Create' }}</a>
                                    @if($cnt)
                                        <form method="post" action="{{ route('ett.class.destroy', [$c->id, $s->id]) }}" style="display:inline;" onsubmit="return confirm('Ye timetable delete kar dein?')">
                                            @csrf @method('delete')
                                            <button class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h6 class="card-title font-weight-bold">Exam Timetables</h6></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Class</th><th>Papers</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                @foreach($classes as $c)
                    @php $ec = $exam_counts[$c->id] ?? 0; @endphp
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td>{{ $ec ?: '—' }}</td>
                        <td class="text-right">
                            @if($ec)
                                <a href="{{ route('ett.exam.show', $c->id) }}" class="btn btn-sm btn-primary">View</a>
                                <a href="{{ route('ett.exam.print', $c->id) }}" target="_blank" class="btn btn-sm btn-light">Print</a>
                            @endif
                            @if(Qs::userIsTeamSA())
                                <a href="{{ route('ett.exam.create', ['class_id' => $c->id]) }}" class="btn btn-sm btn-success">{{ $ec ? 'Edit' : 'Create' }}</a>
                                @if($ec)
                                    <form method="post" action="{{ route('ett.exam.destroy', $c->id) }}" style="display:inline;" onsubmit="return confirm('Ye exam timetable delete kar dein?')">
                                        @csrf @method('delete')
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
