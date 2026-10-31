@extends('layouts.master')
@section('page_title', 'Staff Salaries')
@section('content')
<div class="card">
    <div class="card-header header-elements-inline">
        <h6 class="card-title">Staff Salaries &amp; Payroll</h6>
        <form method="get" class="d-flex">
            <input type="month" name="month" value="{{ $month }}" class="form-control mr-2">
            <button class="btn btn-secondary" type="submit">View</button>
        </form>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4"><div class="alert alert-info mb-0">Month: <strong>{{ $month }}</strong></div></div>
            <div class="col-md-4"><div class="alert alert-primary mb-0">Total Net: <strong>{{ number_format($total_net) }}</strong></div></div>
            <div class="col-md-4"><div class="alert alert-success mb-0">Total Paid: <strong>{{ number_format($total_paid) }}</strong></div></div>
        </div>

        <h6 class="font-weight-bold">Record a Salary Payment</h6>
        <form method="post" action="{{ route('salaries.store') }}" class="row mb-4">@csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <div class="col-md-3 mb-2"><label>Staff Member *</label>
                <select name="user_id" id="staffSelect" class="form-control" required>
                    <option value="">Choose...</option>
                    @foreach($staff as $st)<option value="{{ $st->id }}" data-salary="{{ $st->staff->first()->salary ?? 0 }}">{{ $st->name }} ({{ ucwords(str_replace('_',' ',$st->user_type)) }})</option>@endforeach
                </select>
                <small id="salaryHint" class="form-text text-muted"></small></div>
            <div class="col-md-2 mb-2"><label>Basic *</label><input type="number" name="basic" id="basicInput" class="form-control" min="0" required></div>
            <div class="col-md-2 mb-2"><label>Allowances</label><input type="number" name="allowances" class="form-control" min="0" value="0"></div>
            <div class="col-md-2 mb-2"><label>Deductions</label><input type="number" name="deductions" class="form-control" min="0" value="0"></div>
            <div class="col-md-2 mb-2"><label>Amount Paid *</label><input type="number" name="amt_paid" class="form-control" min="0" required></div>
            <div class="col-md-2 mb-2"><label>Method</label>
                <select name="method" class="form-control"><option>Bank Transfer</option><option>Cash</option><option>Cheque</option><option>JazzCash/Easypaisa</option></select></div>
            <div class="col-md-2 mb-2"><label>Paid On</label><input type="date" name="paid_on" class="form-control" value="{{ date('Y-m-d') }}"></div>
            <div class="col-md-2 mb-2"><label>&nbsp;</label><button class="btn btn-primary btn-block" type="submit">Record Payment</button></div>
        </form>

        <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Receipt</th><th>Staff</th><th>Role</th><th>Basic</th><th>Allow.</th><th>Deduct.</th><th>Net</th><th>Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            @forelse($payments as $p)
                <tr>
                    <td>{{ $p->receipt_no }}</td>
                    <td>{{ $p->user->name }}<br><small class="text-muted">{{ $p->user->phone }}</small></td>
                    <td>{{ ucwords(str_replace('_',' ',$p->user->user_type)) }}</td>
                    <td>{{ number_format($p->basic) }}</td><td>{{ number_format($p->allowances) }}</td><td>{{ number_format($p->deductions) }}</td>
                    <td class="font-weight-bold">{{ number_format($p->net) }}</td>
                    <td class="text-success font-weight-bold">{{ number_format($p->amt_paid) }}</td>
                    <td class="{{ $p->balance > 0 ? 'text-danger' : '' }}">{{ number_format($p->balance) }}</td>
                    <td>@if($p->balance <= 0)<span class="badge badge-success">Paid</span>@elseif($p->amt_paid > 0)<span class="badge badge-warning">Partial</span>@else<span class="badge badge-danger">Unpaid</span>@endif</td>
                    <td>
                        <a target="_blank" href="{{ route('salaries.slip', $p->id) }}" class="btn btn-sm btn-outline-secondary">Slip</a>
                        <a href="{{ route('salaries.pdf', $p->id) }}" class="btn btn-sm btn-outline-danger">PDF</a>
                        @if(Qs::userIsSuperAdmin())
                        <form method="post" action="{{ route('salaries.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('Reverse (delete) this salary payment? Super Admin only.')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger">Reverse</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="text-center">No salary payments recorded for {{ $month }} yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @parent
    <script>
        // Staff select karte hi us ki tay shuda salary Basic me khud aa jati hai
        var staffSel = document.getElementById('staffSelect');
        if (staffSel) {
            staffSel.addEventListener('change', function () {
                var sal = parseInt(this.options[this.selectedIndex].getAttribute('data-salary') || '0', 10);
                var basic = document.getElementById('basicInput');
                var hint = document.getElementById('salaryHint');
                if (sal > 0) {
                    if (basic) { basic.value = sal; }
                    if (hint) { hint.textContent = 'Tay shuda salary: Rs ' + sal.toLocaleString() + ' — Basic me bhar di hai, chaaho to badal saktay ho.'; }
                } else if (hint) {
                    hint.textContent = 'Is staff ki tay shuda salary set nahi — Users me ja kar salary likh dein, phir har month khud aayegi.';
                }
            });
        }
    </script>
@endsection
