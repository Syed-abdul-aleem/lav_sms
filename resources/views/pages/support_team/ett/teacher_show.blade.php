@extends('layouts.master')
@section('page_title', 'Teacher Timetable')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">{{ optional($teacher)->name }} — Weekly Timetable {{ !empty($mine) ? '(My Timetable)' : '' }}</h6>
            <div class="header-elements">
                <a href="{{ route('ett.teacher.print', $teacher->id) }}" target="_blank" class="btn btn-primary btn-sm">Print</a>
                @if(empty($mine))<a href="{{ route('ett.teachers') }}" class="btn btn-light btn-sm">All Teachers</a>@endif
            </div>
        </div>
        <div class="card-body">
            @include('pages.support_team.ett._grid', ['showClassName' => true])
        </div>
    </div>

@endsection
