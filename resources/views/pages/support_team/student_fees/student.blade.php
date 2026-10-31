@extends('layouts.master')
@section('page_title', 'Fee Khata — ' . $sr->user->name)
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
    @if($errors->any())
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ $errors->first() }}
        </div>
    @endif

    @php
        $waText = rawurlencode('Assalam-o-Alaikum! ' . $sr->user->name . ' (' . ($sr->my_class ? $sr->my_class->name : '') . ') ki fee tafseel: Kul Fee Rs ' . number_format($t['charged']) . ', Ada Shuda Rs ' . number_format($t['paid']) . ', Baqaya Rs ' . number_format($t['balance']) . ($t['arrears'] > 0 ? ' (is me pichla baqaya Rs ' . number_format($t['arrears']) . ' shamil hai)' : '') . '. Fee slip (PDF) sath bhej raha hoon. — ' . (Qs::getSetting('system_name') ?: config('app.name')));
        $waLink = 'https://wa.me/' . ($wa_phone ?: '') . '?text=' . $waText;
    @endphp

    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-cash2 mr-2"></i> Fee Khata — {{ $sr->user->name }}</h5>
            <div class="header-elements">
                @if($staff)
                    <a href="{{ route('student_fees.class', $sr->my_class_id) }}" class="btn btn-light btn-sm"><i class="icon-arrow-left7 mr-1"></i> Class</a>
                @endif
                <a href="{{ route('student_fees.slip', $sr->user_id) }}" target="_blank" class="btn btn-info btn-sm"><i class="icon-printer mr-1"></i> Fee Slip</a>
                <a href="{{ route('student_fees.pdf', $sr->user_id) }}" class="btn btn-danger btn-sm"><i class="icon-file-pdf mr-1"></i> PDF</a>
                <a href="{{ $waLink }}" target="_blank" class="btn btn-success btn-sm"><i class="icon-bubbles4 mr-1"></i> WhatsApp</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><td class="font-weight-bold" style="width:40%">Student</td><td>{{ $sr->user->name }}</td></tr>
                        <tr><td class="font-weight-bold">Class / Section</td><td>{{ $sr->my_class ? $sr->my_class->name : '' }}{{ $sr->section ? ' / ' . $sr->section->name : '' }}</td></tr>
                        <tr><td class="font-weight-bold">Admission No</td><td>{{ $sr->adm_no ?: '—' }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm">
                        <tr><td class="font-weight-bold" style="width:40%">Father / Guardian</td><td>{{ $sr->my_parent ? $sr->my_parent->name : '—' }}</td></tr>
                        <tr><td class="font-weight-bold">Parent Phone</td><td>{{ $sr->my_parent ? ($sr->my_parent->phone ?: '—') : '—' }}</td></tr>
                        <tr><td class="font-weight-bold">Session</td><td>{{ $year }}</td></tr>
                    </table>
                </div>
            </div>

            <div class="row text-center mt-2">
                <div class="col-6 col-md-4 col-lg-2 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Kul Fee</div><h4 class="mb-0">Rs {{ number_format($t['charged']) }}</h4></div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Pichla Baqaya</div><h4 class="mb-0 text-warning">Rs {{ number_format($t['arrears']) }}</h4></div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Is Mah Ki Fee</div><h4 class="mb-0">Rs {{ number_format($t['this_month']) }}</h4></div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Ada Shuda (Paid)</div><h4 class="mb-0 text-success">Rs {{ number_format($t['paid']) }}</h4></div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Kul Baqaya</div><h4 class="mb-0 text-danger">Rs {{ number_format($t['balance']) }}</h4></div>
                </div>
                <div class="col-6 col-md-4 col-lg-2 mb-2">
                    <div class="border rounded p-2"><div class="text-muted">Status</div>
                        @if($t['charged'] == 0)
                            <h4 class="mb-0"><span class="badge badge-secondary">No Fee</span></h4>
                        @elseif($t['balance'] == 0)
                            <h4 class="mb-0"><span class="badge badge-success">Clear</span></h4>
                        @elseif($t['paid'] > 0)
                            <h4 class="mb-0"><span class="badge badge-warning">Partial</span></h4>
                        @else
                            <h4 class="mb-0"><span class="badge badge-danger">Unpaid</span></h4>
                        @endif
                    </div>
                </div>
            </div>
            <p class="text-muted mb-0"><small>Pichla Baqaya = is month se pehle ki fees ka woh hissa jo abhi tak ada nahi hua. Hisab har fee aur har adaigi ke sath khud update ho jata hai.</small></p>
        </div>
    </div>

    @if($staff)
    <div class="card">
        <div class="card-header"><h6 class="card-title font-weight-bold"><i class="icon-plus-circle2 mr-2"></i> Nayi Fee Add Karein</h6></div>
        <div class="card-body">
            <form method="post" action="{{ route('student_fees.add') }}" id="addFeeForm">
                @csrf
                <input type="hidden" name="student_id" value="{{ $sr->user_id }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-bold">Fee Title</label>
                            <input type="text" name="title" id="feeTitle" class="form-control" required list="feeTitles" placeholder="e.g. Monthly Fee">
                            <datalist id="feeTitles">
                                <option value="Monthly Fee"></option>
                                <option value="Admission Fee"></option>
                                <option value="Exam Fee"></option>
                                <option value="Transport Fee"></option>
                                <option value="Annual Charges"></option>
                                <option value="Late Fee Fine"></option>
                            </datalist>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold">Mahina (optional)</label>
                            <select id="feeMonth" class="form-control">
                                <option value="">—</option>
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                                    <option value="{{ $m }}" {{ date('F') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold">Amount (Rs)</label>
                            <input type="number" name="amount" class="form-control" required min="1" placeholder="e.g. 3000">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold">Kis Ke Liye</label>
                            <select name="scope" class="form-control">
                                <option value="student">Sirf is student ke liye</option>
                                <option value="class">Poori class ke liye</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="font-weight-bold">&nbsp;</label>
                            <button type="submit" class="btn btn-success btn-block"><i class="icon-plus-circle2 mr-1"></i> Fee Add Karein</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header"><h6 class="card-title font-weight-bold"><i class="icon-list mr-2"></i> Fee Tafseel (Ledger)</h6></div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fee</th>
                        <th>Date</th>
                        <th class="text-right">Fee (Rs)</th>
                        <th class="text-right">Ada (Rs)</th>
                        <th class="text-right">Baqaya (Rs)</th>
                        <th class="text-center">Status</th>
                        @if($staff)<th style="min-width:230px">Fee Jama Karein</th>@endif
                        <th class="text-right">Receipts</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($records as $r)
                    @php
                        $amount = $r->payment ? (int) $r->payment->amount : 0;
                        $paidAmt = (int) ($r->amt_paid ?: 0);
                        $bal = max($amount - $paidAmt, 0);
                    @endphp
                    <tr>
                        <td class="font-weight-bold">
                            {{ $r->payment ? $r->payment->title : 'Fee' }}
                            @if($r->payment && $r->payment->student_id)<br><span class="badge badge-info">Individual</span>@endif
                        </td>
                        <td>{{ $r->payment && $r->payment->created_at ? $r->payment->created_at->format('d M Y') : '—' }}</td>
                        <td class="text-right">{{ number_format($amount) }}</td>
                        <td class="text-right text-success">{{ number_format($paidAmt) }}</td>
                        <td class="text-right {{ $bal > 0 ? 'text-danger font-weight-bold' : '' }}">{{ number_format($bal) }}</td>
                        <td class="text-center">
                            @if($bal == 0 && $amount > 0)<span class="badge badge-success">Clear</span>
                            @elseif($paidAmt > 0)<span class="badge badge-warning">Partial</span>
                            @else<span class="badge badge-danger">Unpaid</span>@endif
                        </td>
                        @if($staff)
                        <td>
                            @if($bal > 0)
                                <form method="post" action="{{ route('student_fees.pay', $r->id) }}" class="form-inline">
                                    @csrf
                                    <input type="number" name="amt_paid" class="form-control form-control-sm mr-2" required min="1" max="{{ $bal }}" placeholder="Rs" style="width:110px">
                                    <button type="submit" class="btn btn-sm btn-success">Jama Karein</button>
                                </form>
                            @else
                                <span class="text-success"><i class="icon-checkmark3"></i> Mukammal</span>
                            @endif
                        </td>
                        @endif
                        <td class="text-right">
                            <a href="{{ route('payments.receipts', Qs::hash($r->id)) }}" class="btn btn-sm btn-light">Receipts</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $staff ? 8 : 7 }}" class="text-center text-muted">Abhi is student ki koi fee nahi. @if($staff)Upar wale form se pehli fee add karein.@endif</td></tr>
                @endforelse
                </tbody>
                @if($records->count())
                <tfoot>
                    <tr class="font-weight-bold">
                        <td colspan="2" class="text-right">Kul Hisab:</td>
                        <td class="text-right">{{ number_format($t['charged']) }}</td>
                        <td class="text-right text-success">{{ number_format($t['paid']) }}</td>
                        <td class="text-right text-danger">{{ number_format($t['balance']) }}</td>
                        <td colspan="{{ $staff ? 3 : 2 }}"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        // Mahina chuna ho to fee title ke sath khud lag jata hai, e.g. "Monthly Fee — November 2026"
        document.getElementById('addFeeForm') && document.getElementById('addFeeForm').addEventListener('submit', function () {
            var m = document.getElementById('feeMonth');
            var t = document.getElementById('feeTitle');
            if (m && t && m.value && t.value.indexOf(m.value) === -1) {
                t.value = t.value + ' — ' + m.value + ' {{ date('Y') }}';
            }
        });
    </script>
@endsection
