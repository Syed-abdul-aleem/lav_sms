@extends('layouts.master')
@section('page_title', 'My Dashboard')
@section('content')

    @if(Qs::userIsTeamSA())
       <div class="row">
           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-blue-400 has-bg-image">
                   <div class="media">
                       <div class="media-body">
                           <h3 class="mb-0">{{ $users->where('user_type', 'student')->count() }}</h3>
                           <span class="text-uppercase font-size-xs font-weight-bold">Total Students</span>
                       </div>

                       <div class="ml-3 align-self-center">
                           <i class="icon-users4 icon-3x opacity-75"></i>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-danger-400 has-bg-image">
                   <div class="media">
                       <div class="media-body">
                           <h3 class="mb-0">{{ $users->where('user_type', 'teacher')->count() }}</h3>
                           <span class="text-uppercase font-size-xs">Total Teachers</span>
                       </div>

                       <div class="ml-3 align-self-center">
                           <i class="icon-users2 icon-3x opacity-75"></i>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-success-400 has-bg-image">
                   <div class="media">
                       <div class="mr-3 align-self-center">
                           <i class="icon-pointer icon-3x opacity-75"></i>
                       </div>

                       <div class="media-body text-right">
                           <h3 class="mb-0">{{ $users->where('user_type', 'admin')->count() }}</h3>
                           <span class="text-uppercase font-size-xs">Total Administrators</span>
                       </div>
                   </div>
               </div>
           </div>

           <div class="col-sm-6 col-xl-3">
               <div class="card card-body bg-indigo-400 has-bg-image">
                   <div class="media">
                       <div class="mr-3 align-self-center">
                           <i class="icon-user icon-3x opacity-75"></i>
                       </div>

                       <div class="media-body text-right">
                           <h3 class="mb-0">{{ $users->where('user_type', 'parent')->count() }}</h3>
                           <span class="text-uppercase font-size-xs">Total Parents</span>
                       </div>
                   </div>
               </div>
           </div>
       </div>
       @endif

    @if(Qs::userIsTeamSA() || Qs::userIsTeacher() || Auth::user()->user_type == 'accountant')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">Quick Actions — roz ke kaam, aik click</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @if(Qs::userIsTeamSA())
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('students.family.create') }}" class="btn btn-success btn-block"><i class="icon-users4 mr-1"></i> Add Family</a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('students.create') }}" class="btn btn-primary btn-block"><i class="icon-user-plus mr-1"></i> Admit Student</a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('users.index') }}" class="btn btn-info btn-block"><i class="icon-user-tie mr-1"></i> Add Staff</a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('payments.manage') }}" class="btn btn-warning btn-block"><i class="icon-money mr-1"></i> Fees</a></div>
                @endif
                @if(Qs::userIsTeamSAT())
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('diaries.create') }}" class="btn btn-secondary btn-block"><i class="icon-notebook mr-1"></i> New Diary</a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('marks.index') }}" class="btn btn-dark btn-block"><i class="icon-check mr-1"></i> Enter Marks</a></div>
                @endif
                @if(Auth::user()->user_type == 'accountant')
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('payments.manage') }}" class="btn btn-warning btn-block"><i class="icon-money mr-1"></i> Fees</a></div>
                <div class="col-6 col-md-4 col-lg-2 mb-2"><a href="{{ route('salaries.index') }}" class="btn btn-info btn-block"><i class="icon-wallet mr-1"></i> Salaries</a></div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{--Events Calendar Begins--}}
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title">School Events Calendar</h5>
         {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <div class="fullcalendar-basic"></div>
        </div>
    </div>
    {{--Events Calendar Ends--}}
    @endsection
