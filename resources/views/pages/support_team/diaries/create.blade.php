@extends('layouts.master')
@section('page_title', 'Add Diary Entry')
@section('content')
<div class="card">
    <div class="card-header"><h6 class="card-title">Add Class Diary Entry</h6></div>
    <div class="card-body">
        <form method="post" action="{{ route('diaries.store') }}">@csrf
            @include('pages.support_team.diaries.form')
            <button class="btn btn-primary" type="submit">Save Diary Entry</button>
            <a href="{{ route('diaries.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
