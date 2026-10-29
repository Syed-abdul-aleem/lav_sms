<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void$field
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /*
     *  Login with Username or Email
     * */
    /**
     * Students do not get their own logins in this system.
     * Parents handle everything (diary, results, fees) from the parent login,
     * so any student account that authenticates is signed straight back out.
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->user_type === 'student') {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['identity' => 'Student logins are not available. Parents can view diary, results and fees from the parent login.']);
        }
    }


    /*
     * Brute-force protection that also works on serverless hosting.
     * The framework's default throttle uses the cache, which resets on
     * every serverless invocation (CACHE_DRIVER=array) — so failed login
     * attempts are recorded in the database instead: 6 failed attempts
     * for the same login ID + IP within 10 minutes locks further tries
     * until the window passes. Successful login clears the record.
     */
    protected $maxAttempts = 6;
    protected $decayMinutes = 10;

    protected function ensureLoginAttemptsTable()
    {
        if (!Schema::hasTable('login_attempts')) {
            Schema::create('login_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('throttle_key')->index();
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    protected function hasTooManyLoginAttempts(Request $request)
    {
        $this->ensureLoginAttemptsTable();
        $since = now()->subMinutes($this->decayMinutes);
        $count = DB::table('login_attempts')
            ->where('throttle_key', $this->throttleKey($request))
            ->where('created_at', '>=', $since)
            ->count();
        return $count >= $this->maxAttempts;
    }

    protected function incrementLoginAttempts(Request $request)
    {
        $this->ensureLoginAttemptsTable();
        DB::table('login_attempts')->insert([
            'throttle_key' => $this->throttleKey($request),
            'created_at' => now(),
        ]);
        // Opportunistic cleanup so the table stays tiny.
        DB::table('login_attempts')->where('created_at', '<', now()->subHours(2))->delete();
    }

    protected function clearLoginAttempts(Request $request)
    {
        $this->ensureLoginAttemptsTable();
        DB::table('login_attempts')
            ->where('throttle_key', $this->throttleKey($request))
            ->delete();
    }

    protected function sendLockoutResponse(Request $request)
    {
        $oldest = DB::table('login_attempts')
            ->where('throttle_key', $this->throttleKey($request))
            ->where('created_at', '>=', now()->subMinutes($this->decayMinutes))
            ->min('created_at');
        $seconds = $oldest
            ? max(1, \Illuminate\Support\Facades\Date::parse($oldest)->addMinutes($this->decayMinutes)->diffInSeconds(now()))
            : $this->decayMinutes * 60;

        throw ValidationException::withMessages([
            $this->username() => [__('auth.throttle', ['seconds' => $seconds, 'minutes' => (int) ceil($seconds / 60)])],
        ])->status(\Illuminate\Http\Response::HTTP_TOO_MANY_REQUESTS);
    }

    public function username()
    {
        $identity = request()->identity;
        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$field => $identity]);
        return $field;
    }
}
