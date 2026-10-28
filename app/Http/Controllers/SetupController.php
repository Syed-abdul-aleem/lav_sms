<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * TEMPORARY one-time production setup endpoint.
 * Runs migrations + seeders against the external Postgres database
 * (Supabase) after DATABASE_URL is configured on Vercel.
 * Protected by the SETUP_TOKEN environment variable; returns 404
 * when the token is missing or wrong. Removed after first use.
 */
class SetupController extends Controller
{
    public function run(Request $req)
    {
        $expected = env('SETUP_TOKEN');
        if (!$expected || !hash_equals((string)$expected, (string)$req->query('token'))) {
            abort(404);
        }
        if (!env('DATABASE_URL')) {
            return response('NOT_PGSQL: DATABASE_URL is not set', 400);
        }
        @set_time_limit(0);
        $step = $req->query('step', 'migrate');
        try {
            if ($step === 'fresh') {
                $code = Artisan::call('migrate:fresh', ['--force' => true]);
                return response("FRESH_EXIT=$code\n" . Artisan::output() . "\nFRESH_DONE", 200)->header('Content-Type', 'text/plain');
            }
            if ($step === 'migrate') {
                $code = Artisan::call('migrate', ['--force' => true]);
                return response("MIGRATE_EXIT=$code\n" . Artisan::output() . "\nMIGRATE_DONE", 200)->header('Content-Type', 'text/plain');
            }
            if ($step === 'seed_one') {
                $only = preg_replace('/[^A-Za-z0-9]/', '', (string)$req->query('only'));
                $class = 'Database\\Seeders\\' . $only;
                if (!$only || !class_exists($class)) {
                    return response('UNKNOWN_SEEDER', 400);
                }
                $code = Artisan::call('db:seed', ['--class' => $class, '--force' => true]);
                return response("SEED_ONE_EXIT=$code [$only]
" . Artisan::output() . "
SEED_ONE_DONE", 200)->header('Content-Type', 'text/plain');
            }
            if ($step === 'seed') {
                $code = Artisan::call('db:seed', ['--force' => true]);
                return response("SEED_EXIT=$code\n" . Artisan::output() . "\nSEED_DONE", 200)->header('Content-Type', 'text/plain');
            }
            return response('UNKNOWN_STEP', 400);
        } catch (\Throwable $e) {
            return response('SETUP_ERROR: ' . $e->getMessage(), 500)->header('Content-Type', 'text/plain');
        }
    }
}
