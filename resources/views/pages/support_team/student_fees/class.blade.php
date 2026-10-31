@extends('layouts.master')
@section('page_title', 'Student Fees — ' . $my_class->name)
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
            <h5 class="card-title"><i class="icon-cash2 mr-2"></i> {{ $my_class->name }} — Students Fee</h5>
            <div class="header-elements">
                <a href="{{ route('student_fees.index') }}" class="btn btn-light btn-sm"><i class="icon-arrow-left7 mr-1"></i> Saari Classes</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Kul Fee</div><h4 class="mb-0">Rs {{ number_format($ct['charged']) }}</h4></div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Jama (Paid)</div><h4 class="mb-0 text-success">Rs {{ number_format($ct['paid']) }}</h4></div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Pichla Baqaya (Arrears)</div><h4 class="mb-0 text-warning">Rs {{ number_format($ct['arrears']) }}</h4></div>
                </div>
                <div class="col-6 col-md-3 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Kul Baqaya</div><h4 class="mb-0 text-danger">Rs {{ number_format($ct['balance']) }}</h4></div>
                </div>
            </div>
            <p class="text-muted mb-0">Kisi student ke naam par click karein — wahan fee add bhi ho sakti hai, fee jama bhi, aur slip (PDF) bhi banegi.</p>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Father / Guardian</th>
                        <th class="text-right">Kul Fee (Rs)</th>
                        <th class="text-right">Ada (Rs)</th>
                        <th class="text-right">Pichla Baqaya (Rs)</th>
                        <th class="text-right">Kul Baqaya (Rs)</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $row)
                    @php $sr = $row['sr']; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('student_fees.student', $sr->user_id) }}" class="font-weight-bold">{{ $sr->user->name }}</a>
                            <br><small class="text-muted">{{ $sr->adm_no ? 'Adm No: ' . $sr->adm_no : '' }}{{ $sr->section ? ' · Section ' . $sr->section->name : '' }}</small>
                        </td>
                        <td>{{ $sr->my_parent ? $sr->my_parent->name : '—' }}</td>
                        <td class="text-right">{{ number_format($row['charged']) }}</td>
                        <td class="text-right text-success">{{ number_format($row['paid']) }}</td>
                        <td class="text-right {{ $row['arrears'] > 0 ? 'text-warning font-weight-bold' : '' }}">{{ number_format($row['arrears']) }}</td>
                        <td class="text-right {{ $row['balance'] > 0 ? 'text-danger font-weight-bold' : '' }}">{{ number_format($row['balance']) }}</td>
                        <td class="text-center">
                            @if($row['charged'] == 0)
                                <span class="badge badge-secondary">No Fee</span>
                            @elseif($row['balance'] == 0)
                                <span class="badge badge-success">Clear</span>
                            @elseif($row['paid'] > 0)
                                <span class="badge badge-warning">Partial</span>
                            @else
                                <span class="badge badge-danger">Unpaid</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <a href="{{ route('student_fees.student', $sr->user_id) }}" class="btn btn-sm btn-primary">Fee Khata</a>
                            <a href="{{ route('student_fees.slip', $sr->user_id) }}" target="_blank" class="btn btn-sm btn-light">Slip</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted">Is class me abhi koi student nahi.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
