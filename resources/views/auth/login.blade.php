@extends('layouts.login_master')

@section('content')
    <style>
        body { background: #0b3d20; }
        .login-cover {
            background: radial-gradient(1000px 500px at 15% -10%, rgba(201,162,39,.28), rgba(201,162,39,0) 60%),
                        radial-gradient(900px 600px at 110% 110%, rgba(47,125,70,.55), rgba(47,125,70,0) 60%),
                        linear-gradient(135deg, #082c17 0%, #0f4526 45%, #14532d 100%) !important;
            min-height: 100vh;
        }
        .navbar.navbar-dark { background: transparent !important; box-shadow: none; }
        .navbar.navbar-light { background: rgba(255,255,255,.06) !important; border-top: 1px solid rgba(255,255,255,.12); }
        .navbar.navbar-light .navbar-text, .navbar.navbar-light .navbar-text a,
        .navbar.navbar-light .navbar-nav-link { color: #dcefe2 !important; }
        .bs-card { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,.45); }
        .bs-brand {
            background: linear-gradient(160deg, #0d4423 0%, #14532d 70%, #1a6a3a 100%);
            color: #fff; padding: 38px 34px; display: flex; flex-direction: column;
        }
        .bs-logo { display: flex; align-items: center; gap: 12px; }
        .bs-logo .bs-mark {
            width: 52px; height: 52px; border-radius: 14px; background: #c9a227; color: #0b3d20;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
            box-shadow: 0 8px 20px rgba(0,0,0,.35);
        }
        .bs-name { font-size: 30px; font-weight: 800; letter-spacing: .3px; line-height: 1; }
        .bs-name span { color: #c9a227; }
        .bs-tag { color: #bfe3cc; font-size: 13px; letter-spacing: 2.5px; text-transform: uppercase; margin-top: 4px; }
        .bs-feats { margin-top: 30px; }
        .bs-feats div { display: flex; align-items: center; gap: 10px; margin-bottom: 13px; font-size: 14.5px; color: #e9f5ee; }
        .bs-feats i { color: #c9a227; font-size: 16px; }
        .bs-foot { margin-top: auto; padding-top: 22px; border-top: 1px solid rgba(255,255,255,.18); font-size: 12.5px; color: #bfe3cc; }
        .bs-foot a { color: #ffd94d; font-weight: 700; }
        .bs-form { padding: 38px 38px 30px; }
        .bs-school { font-size: 21px; font-weight: 800; color: #14532d; line-height: 1.25; }
        .bs-input { height: 48px; border-radius: 9px; font-size: 15px; }
        .bs-btn {
            height: 50px; border-radius: 9px; font-size: 16px; font-weight: 700; letter-spacing: .4px;
            background: #14532d; border-color: #14532d;
        }
        .bs-btn:hover, .bs-btn:focus { background: #0f4526; border-color: #0f4526; }
        .bs-note { font-size: 12.5px; color: #e6f2ea; text-align: center; margin-top: 14px; text-shadow: 0 1px 2px rgba(0,0,0,.4); }
        @media (max-width: 991.98px) {
            .bs-form { padding: 28px 22px 22px; }
        }
    </style>

    <div class="page-content login-cover">
        <div class="content-wrapper">
            <div class="content d-flex justify-content-center align-items-center py-4">

                <form class="login-form" method="post" action="{{ route('login') }}" style="width:100%; max-width: 880px;">
                    @csrf
                    <div class="card mb-0 bs-card">
                        <div class="row no-gutters">

                            {{-- Branding panel (desktop) --}}
                            <div class="col-lg-5 d-none d-lg-flex">
                                <div class="bs-brand w-100">
                                    <div class="bs-logo">
                                        <div class="bs-mark"><i class="icon-library2"></i></div>
                                        <div>
                                            <div class="bs-name">Bunyad<span>Soft</span></div>
                                            <div class="bs-tag">School Management System</div>
                                        </div>
                                    </div>

                                    <div class="bs-feats">
                                        <div><i class="icon-checkmark-circle"></i> Students &amp; Parents Portal</div>
                                        <div><i class="icon-checkmark-circle"></i> Fees, Receipts &amp; Staff Salaries</div>
                                        <div><i class="icon-checkmark-circle"></i> Result Cards — Pakistani Board Style</div>
                                        <div><i class="icon-checkmark-circle"></i> Easy Timetables &amp; Class Diary</div>
                                        <div><i class="icon-checkmark-circle"></i> Playgroup to Class 10 — Made for Pakistan</div>
                                    </div>

                                    <div class="bs-foot">
                                        Developed by <strong style="color:#fff;">Syed Abdul Aleem</strong><br>
                                        WhatsApp: <a href="https://wa.me/923295566111" target="_blank">03295566111</a>
                                    </div>
                                </div>
                            </div>

                            {{-- Login form --}}
                            <div class="col-lg-7">
                                <div class="bs-form">
                                    {{-- Compact branding (mobile) --}}
                                    <div class="d-lg-none text-center mb-3">
                                        <div class="bs-name" style="color:#14532d; font-size:26px;">Bunyad<span style="color:#c9a227;">Soft</span></div>
                                        <div class="bs-tag" style="color:#7a8a7e;">School Management System</div>
                                    </div>

                                    <div class="text-center mb-4">
                                        <div class="bs-school">{{ Qs::getSystemName() }}</div>
                                        <span class="d-block text-muted">Login to your account — apne account me login karen</span>
                                    </div>

                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-styled-left alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                            <span class="font-weight-semibold">Oops!</span> {{ implode('<br>', $errors->all()) }}
                                        </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger alert-styled-left alert-dismissible">
                                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <div class="form-group form-group-feedback form-group-feedback-left">
                                        <input type="text" class="form-control bs-input" name="identity" value="{{ old('identity') }}" placeholder="Login ID or Email" autocomplete="username">
                                        <div class="form-control-feedback"><i class="icon-user text-muted"></i></div>
                                    </div>

                                    <div class="form-group form-group-feedback form-group-feedback-left">
                                        <input required name="password" type="password" class="form-control bs-input" placeholder="{{ __('Password') }}" autocomplete="current-password">
                                        <div class="form-control-feedback"><i class="icon-lock2 text-muted"></i></div>
                                    </div>

                                    <div class="form-group d-flex align-items-center">
                                        <div class="form-check mb-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="remember" class="form-input-styled" {{ old('remember') ? 'checked' : '' }} data-fouc>
                                                Remember
                                            </label>
                                        </div>
                                        <a href="{{ route('password.request') }}" class="ml-auto">Forgot password?</a>
                                    </div>

                                    <div class="form-group mb-2">
                                        <button type="submit" class="btn btn-primary btn-block bs-btn">Sign in <i class="icon-circle-right2 ml-2"></i></button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="bs-note">
                        Students ka alag login nahi hota — parents apne login se bachon ki diary, result aur fees sab dekh sakte hain.
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
