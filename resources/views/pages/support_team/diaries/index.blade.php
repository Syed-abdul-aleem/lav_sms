@extends('layouts.master')
@section('page_title', 'Class Diary')
@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Class Diary / Homework</h6>
        @if($can_write)
            <a href="{{ route('diaries.create') }}" class="btn btn-primary btn-sm">+ Add Diary Entry</a>
        @endif
    </div>
    <div class="card-body">
        <form method="get" class="row mb-3">
            <div class="col-md-4 mb-2">
                <select name="class_id" class="form-control">
                    <option value="">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2"><input type="date" name="date" value="{{ request('date') }}" class="form-control"></div>
            <div class="col-md-2 mb-2"><button class="btn btn-secondary btn-block" type="submit">Filter</button></div>
        </form>

        @forelse($diaries as $d)
            <div class="card border-left-3 border-left-primary mb-2">
                <div class="card-body py-2">
                    <div class="d-flex flex-wrap justify-content-between">
                        <h6 class="mb-1">{{ $d->title }} <small class="text-muted">— {{ $d->my_class->name }}{{ $d->section ? ' '.$d->section->name : '' }}{{ $d->subject ? ' · '.$d->subject->name : '' }}</small></h6>
                        <span class="badge badge-info">{{ $d->date->format('D, d M Y') }}</span>
                    </div>
                    @if($d->description)<p class="mb-1"><strong>Class work:</strong> {!! nl2br(e($d->description)) !!}</p>@endif
                    @if($d->homework)<p class="mb-1"><strong>Homework:</strong> {!! nl2br(e($d->homework)) !!}</p>@endif
                    <small class="text-muted">By {{ $d->author->name }} ({{ ucwords(str_replace('_',' ',$d->author->user_type)) }})</small>
                    @if($can_write && (!Qs::userIsTeacher() || (int)$d->user_id === (int)Auth::id() || Qs::userIsTeamSA()))
                        <span class="float-right">
                            <a href="{{ route('diaries.edit', $d->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form method="post" action="{{ route('diaries.destroy', $d->id) }}" class="d-inline" onsubmit="return confirm('Delete this diary entry?')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger">Delete</button></form>
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-info">No diary entries yet for this selection.</div>
        @endforelse
        {{ $diaries->links() }}
    </div>
</div>
@endsection
