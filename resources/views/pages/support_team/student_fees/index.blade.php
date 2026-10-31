@extends('layouts.master')
@section('page_title', 'Student Fees')
@section('content')

    @if(session('flash_success'))
        <div class="alert alert-success alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('flash_success') }}
        </div>
    @endif
    @if(session('pop_error'))
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('pop_error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-cash2 mr-2"></i> Student Fees — saari classes, ek section</h5>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted mb-3">Kisi bhi class par click karein — us class ke saare students aur har student ka mukammal fee hisab (kul fee, ada shuda, pichla baqaya, kul baqaya) wahan milega. Session: <strong>{{ $year }}</strong></p>

            <div class="row text-center">
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-3">
                        <div class="text-muted">Kul Students</div>
                        <h3 class="mb-0">{{ number_format($grand['students']) }}</h3>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-3">
                        <div class="text-muted">Kul Fee</div>
                        <h3 class="mb-0">Rs {{ number_format($grand['charged']) }}</h3>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-3">
                        <div class="text-muted">Jama Ho Gayi (Paid)</div>
                        <h3 class="mb-0 text-success">Rs {{ number_format($grand['paid']) }}</h3>
                    </div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-3">
                        <div class="text-muted">Kul Baqaya (Remaining)</div>
                        <h3 class="mb-0 text-danger">Rs {{ number_format($grand['balance']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th class="text-center">Students</th>
                        <th class="text-right">Kul Fee (Rs)</th>
                        <th class="text-right">Jama (Rs)</th>
                        <th class="text-right">Baqaya (Rs)</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td class="font-weight-bold">{{ $row['class']->name }}</td>
                        <td class="text-center">{{ $row['students'] }}</td>
                        <td class="text-right">{{ number_format($row['charged']) }}</td>
                        <td class="text-right text-success">{{ number_format($row['paid']) }}</td>
                        <td class="text-right {{ $row['balance'] > 0 ? 'text-danger font-weight-bold' : '' }}">{{ number_format($row['balance']) }}</td>
                        <td class="text-right">
                            <a href="{{ route('student_fees.class', $row['class']->id) }}" class="btn btn-sm btn-primary"><i class="icon-users mr-1"></i> Students Dekhein</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted">
            Note: Ye section purane Payments wale data ko hi asaan shakl me dikhata hai — purana Payments section bhi bilkul waisa hi maujood hai.
        </div>
    </div>

@endsection
