@extends('layouts.master')
@section('page_title', 'Exam Timetable')
@section('content')

    @if(session('flash_success'))
        <div class="alert alert-success alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('flash_success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">{{ optional($class)->name }} — Exam Timetable</h6>
            <div class="header-elements">
                @if(Qs::userIsTeamSA())
                    <a href="{{ route('ett.exam.create', ['class_id' => $class->id]) }}" class="btn btn-success btn-sm">Edit</a>
                @endif
                <a href="{{ route('ett.exam.print', $class->id) }}" target="_blank" class="btn btn-primary btn-sm">Print</a>
                <a href="{{ route('ett.index') }}" class="btn btn-light btn-sm">All Timetables</a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Day</th><th>Time</th><th>Subject / Paper</th><th>Teacher / Invigilator</th></tr></thead>
                <tbody>
                @forelse($entries as $e)
                    <tr>
                        <td>{{ date('d M Y', strtotime($e->exam_date)) }}</td>
                        <td>{{ date('l', strtotime($e->exam_date)) }}</td>
                        <td>{{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->end_time) }}</td>
                        <td><strong>{{ $e->subject }}</strong></td>
                        <td>{{ $e->teacher_name ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted">Abhi koi paper schedule nahi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
