<html><head><title>SalarySlip_{{ $p->receipt_no }}</title>
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/receipt.css') }}"/>
<style>body{font-family:Arial,sans-serif} table{width:100%} td{padding:6px} .bold{font-weight:bold}</style>
</head><body onload="window.print()">
<div class="container">
    <h2 style="color:#1b0c80">{{ strtoupper(Qs::getSetting('system_name') ?: config('app.name')) }}</h2>
    <h3>SALARY SLIP — {{ $p->receipt_no }}</h3>
    <table>
        <tr><td class="bold">Staff Name:</td><td>{{ $p->user->name }}</td><td class="bold">Role:</td><td>{{ ucwords(str_replace('_',' ',$p->user->user_type)) }}</td></tr>
        <tr><td class="bold">Phone:</td><td>{{ $p->user->phone }}</td><td class="bold">Email:</td><td>{{ $p->user->email }}</td></tr>
        @if($sr)<tr><td class="bold">Staff Code:</td><td>{{ $sr->code }}</td><td class="bold">Employment Date:</td><td>{{ $sr->emp_date }}</td></tr>@endif
        <tr><td class="bold">Month:</td><td>{{ $p->month }}</td><td class="bold">Paid On:</td><td>{{ $p->paid_on ? $p->paid_on->format('d M Y') : '-' }} ({{ $p->method }})</td></tr>
    </table>
    <hr>
    <table>
        <tr><td>Basic Salary</td><td style="text-align:right">{{ number_format($p->basic) }}</td></tr>
        <tr><td>Allowances</td><td style="text-align:right">{{ number_format($p->allowances) }}</td></tr>
        <tr><td>Deductions</td><td style="text-align:right">- {{ number_format($p->deductions) }}</td></tr>
        <tr><td class="bold">Net Salary</td><td class="bold" style="text-align:right">{{ number_format($p->net) }}</td></tr>
        <tr><td class="bold">Amount Paid</td><td class="bold" style="text-align:right">{{ number_format($p->amt_paid) }}</td></tr>
        <tr><td>Balance</td><td style="text-align:right">{{ number_format($p->balance) }}</td></tr>
    </table>
</div>
</body></html>
