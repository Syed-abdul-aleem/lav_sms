<style>
    .ett-table th { background:#14532d; color:#fff; text-align:center; }
    .ett-period { border:1px solid #cfe3d4; border-left:4px solid #2f7d46; border-radius:6px; padding:6px 8px; margin-bottom:8px; background:#fbfdfb; }
    .ett-period.break { border-left-color:#c9a227; background:#fffdf2; }
    .ett-period.assembly { border-left-color:#38618c; background:#f4f8fd; }
    .ett-time { font-weight:700; font-size:12px; color:#14532d; }
    .ett-sub { font-weight:600; }
    .ett-meta { font-size:12px; color:#666; }
</style>
@php $showDays = collect($days)->filter(function($d) use ($byDay){ return $d !== 'Sunday' || ($byDay->has('Sunday') && $byDay['Sunday']->count()); }); @endphp
<div class="table-responsive">
<table class="table ett-table table-bordered">
    <thead>
        <tr>@foreach($showDays as $d)<th style="width:{{ 100 / max(1,$showDays->count()) }}%;">{{ $d }}</th>@endforeach</tr>
    </thead>
    <tbody>
        <tr>
        @foreach($showDays as $d)
            <td>
                @forelse(($byDay[$d] ?? collect()) as $e)
                    <div class="ett-period {{ strtolower($e->kind) }}">
                        <div class="ett-time">{{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->start_time) }} – {{ \App\Http\Controllers\SupportTeam\EasyTimetableController::fmt($e->end_time) }}</div>
                        <div class="ett-sub">{{ $e->kind === 'Period' ? ($e->subject ?: '—') : $e->kind }}{{ $e->kind !== 'Period' && $e->subject ? ' — '.$e->subject : '' }}</div>
                        @if(!empty($showClassName) && $showClassName)
                            <div class="ett-meta">{{ optional($e->my_class)->name }} {{ optional($e->section)->name }}</div>
                        @elseif($e->teacher_name)
                            <div class="ett-meta">{{ $e->teacher_name }}</div>
                        @endif
                    </div>
                @empty
                    <div class="ett-meta text-center">—</div>
                @endforelse
            </td>
        @endforeach
        </tr>
    </tbody>
</table>
</div>
