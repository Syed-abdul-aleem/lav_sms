@extends('layouts.master')
@section('page_title', 'Edit Diary Entry')
@section('content')
<div class="card">
    <div class="card-header"><h6 class="card-title">Edit Class Diary Entry</h6></div>
    <div class="card-body">
        <form method="post" action="{{ route('diaries.update', $diary->id) }}">@csrf @method('put')
            @include('pages.support_team.diaries.form', ['diary' => $diary])
            <button class="btn btn-primary" type="submit">Update Diary Entry</button>
            <a href="{{ route('diaries.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
