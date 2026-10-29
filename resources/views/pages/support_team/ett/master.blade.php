@extends('layouts.master')
@section('page_title', 'Master Timetable')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Master Timetable — poora school aik page par</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            @if($timing)
                <p class="text-muted">School Timing: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->end_time) }}@if($timing->friday_end_time) · Friday: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->friday_end_time) }} tak @endif</p>
            @endif
            @forelse($blocks as $b)
                <h6 class="font-weight-bold mt-3" style="color:#14532d;">{{ optional($b['class'])->name }} {{ optional($b['section'])->name }}</h6>
                @include('pages.support_team.ett._grid', ['byDay' => $b['byDay'], 'days' => $b['days'], 'showClassName' => false])
            @empty
                <p class="text-muted">Abhi koi class timetable nahi bana. <a href="{{ route('ett.class.create') }}">Pehla timetable banayein</a>.</p>
            @endforelse
        </div>
    </div>

@endsection
