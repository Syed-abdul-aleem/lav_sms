<!-- Global stylesheets -->
<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
<link href="{{ asset('global_assets/css/icons/icomoon/styles.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/bootstrap_limitless.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/layout.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/components.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('assets/css/colors.min.css') }}" rel="stylesheet" type="text/css">
<!-- /global stylesheets -->
<link href="{{ asset('assets/css/lav-responsive.css') }}?v=3" rel="stylesheet" type="text/css">

{{-- Speed (2026-10-31): login page ko theme ki JS files ki zaroorat nahi —
     form sada POST hai, animations CSS se hain. Render-blocking JS hata di
     taake login page foran khule. --}}
