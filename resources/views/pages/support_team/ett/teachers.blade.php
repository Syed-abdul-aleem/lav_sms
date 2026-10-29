@extends('layouts.master')
@section('page_title', 'Teacher Timetables')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Teacher Timetables</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">Teacher ka timetable khud-ba-khud ban jata hai — Class Timetable me jin periods ke sath teacher select hota hai, wohi periods us teacher ke timetable me ajate hain. Alag se kuch banana nahi parta.</p>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Teacher</th><th>Periods / Week</th><th class="text-right">Actions</th></tr></thead>
                    <tbody>
                    @foreach($teachers as $t)
                        <tr>
                            <td>{{ $t->name }}</td>
                            <td>{{ $counts[$t->id] ?? 0 }}</td>
                            <td class="text-right">
                                <a href="{{ route('ett.teacher.show', $t->id) }}" class="btn btn-sm btn-primary">View Timetable</a>
                                <a href="{{ route('ett.teacher.print', $t->id) }}" target="_blank" class="btn btn-sm btn-light">Print</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
