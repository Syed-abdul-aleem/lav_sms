@extends('layouts.master')
@section('page_title', 'My Salary')
@section('content')
<div class="card">
    <div class="card-header"><h6 class="card-title">My Salary History — {{ $user->name }}</h6></div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Receipt</th><th>Month</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net</th><th>Paid</th><th>Balance</th><th>Paid On</th><th>Slip</th></tr></thead>
            <tbody>
            @forelse($payments as $p)
                <tr><td>{{ $p->receipt_no }}</td><td>{{ $p->month }}</td><td>{{ number_format($p->basic) }}</td><td>{{ number_format($p->allowances) }}</td><td>{{ number_format($p->deductions) }}</td><td class="font-weight-bold">{{ number_format($p->net) }}</td><td class="text-success">{{ number_format($p->amt_paid) }}</td><td>{{ number_format($p->balance) }}</td><td>{{ $p->paid_on ? $p->paid_on->format('d M Y') : '-' }}</td>
                <td><a target="_blank" href="{{ route('salaries.slip', $p->id) }}" class="btn btn-sm btn-outline-secondary">Print Slip</a></td></tr>
            @empty
                <tr><td colspan="10" class="text-center">No salary records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
