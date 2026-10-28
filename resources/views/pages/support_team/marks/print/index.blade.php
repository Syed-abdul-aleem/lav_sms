<html>
<head>
    <title>Result Card - {{ $sr->user->name }} - {{ Qs::getSetting('system_name') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}" />
</head>
<body>
<div class="container">
    <div id="print">
        <div class="rc-card">
            {{-- School header --}}
            <div class="rc-header">
                @if(!empty($s['logo']))
                    <img class="rc-logo" src="{{ $s['logo'] }}" alt="logo">
                @endif
                <div class="rc-school">{{ strtoupper(Qs::getSetting('system_name')) }}</div>
                <div class="rc-address">{{ ucwords($s['address']) }} @if(!empty($s['phone'])) &nbsp;|&nbsp; Phone: {{ $s['phone'] }} @endif</div>
                <div class="rc-title">RESULT CARD &mdash; {{ strtoupper($ex->name) }} ({{ strtoupper($class_type->name) }})</div>
            </div>

            @include('pages.support_team.marks.print.sheet')

            @include('pages.support_team.marks.print.grading')

            @include('pages.support_team.marks.print.skills')

            <div style="margin-top: 18px; clear: both;"></div>

            @include('pages.support_team.marks.print.comments')

            <div class="rc-signs">
                <div class="rc-sign">Class Teacher<br><br>___________________</div>
                <div class="rc-sign">Principal<br><br>___________________</div>
                <div class="rc-sign">Parent / Guardian<br><br>___________________</div>
            </div>
            <div class="rc-footer">This is a computer-generated result card. &bull; {{ Qs::getSetting('system_name') }} &bull; Session {{ $ex->year }}</div>
        </div>
    </div>
</div>

<script>
    window.print();
</script>
</body>
</html>
