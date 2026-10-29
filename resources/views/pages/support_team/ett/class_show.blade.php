@extends('layouts.master')
@section('page_title', 'Class Timetable')
@section('content')

    @if(session('flash_success'))
        <div class="alert alert-success alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('flash_success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">{{ optional($class)->name }} {{ optional($section)->name }} — Timetable</h6>
            <div class="header-elements">
                @if(Qs::userIsTeamSA())
                    <a href="{{ route('ett.class.create', ['class_id' => $class->id, 'section_id' => $section->id]) }}" class="btn btn-success btn-sm">Edit</a>
                @endif
                <a href="{{ route('ett.class.print', [$class->id, $section->id]) }}" target="_blank" class="btn btn-primary btn-sm">Print</a>
                <a href="{{ route('ett.index') }}" class="btn btn-light btn-sm">All Timetables</a>
            </div>
        </div>
        <div class="card-body">
            @if($timing)
                <p class="text-muted">School: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->end_time) }}@if($timing->break_start) · Break: {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->break_start) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($timing->break_end) }}@endif</p>
            @endif
            @include('pages.support_team.ett._grid', ['showClassName' => false])
        </div>
    </div>

@endsection
