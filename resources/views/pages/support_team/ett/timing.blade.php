@extends('layouts.master')
@section('page_title', 'School Timing')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">School Timing — aik chhota sa form</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">Poore school ki timing yahan se set hoti hai — ye har class timetable aur print par show hoti hai.</p>
            <form method="post" action="{{ route('ett.timing.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>School Start Time:</label>
                            <input type="time" name="start_time" class="form-control" value="{{ $timing->start_time ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>School End Time (Mon–Thu, Sat):</label>
                            <input type="time" name="end_time" class="form-control" value="{{ $timing->end_time ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Friday End Time (Jummah ki wajah se jaldi chhutti):</label>
                            <input type="time" name="friday_end_time" class="form-control" value="{{ $timing->friday_end_time ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Assembly Start:</label>
                            <input type="time" name="assembly_start" class="form-control" value="{{ $timing->assembly_start ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Assembly End:</label>
                            <input type="time" name="assembly_end" class="form-control" value="{{ $timing->assembly_end ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Break Start:</label>
                            <input type="time" name="break_start" class="form-control" value="{{ $timing->break_start ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Break End:</label>
                            <input type="time" name="break_end" class="form-control" value="{{ $timing->break_end ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Note (optional — masalan "Saturday half day"):</label>
                    <input type="text" name="note" class="form-control" value="{{ $timing->note ?? '' }}" maxlength="190">
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary btn-lg">Save School Timing <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

@endsection
