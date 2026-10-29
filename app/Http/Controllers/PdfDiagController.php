<?php

namespace App\Http\Controllers;

use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfDiagController extends Controller
{
    // TEMPORARY diagnostic — removed right after use.
    public function run($token, $pr_hash)
    {
        if (!hash_equals('diag7f3k9x2q', $token)) { abort(404); }
        try {
            $pay = app(PaymentRepo::class);
            $student = app(StudentRepo::class);
            $pr_id = \App\Helpers\Qs::decodeHash($pr_hash);
            $d['pr'] = $pr = $pay->getRecord(['id' => $pr_id])->with('receipt')->first();
            if (!$pr) { return response('no record', 200); }
            $d['receipts'] = $pr->receipt;
            $d['payment'] = $pr->payment;
            $d['sr'] = $student->findByUserId($pr->student_id)->first();
            $d['s'] = Setting::all()->flatMap(function ($s) { return [$s->type => $s->description]; });
            $pdf = Pdf::loadView('pages.support_team.payments.receipt', $d);
            $out = $pdf->output();
            return response('PDF OK bytes=' . strlen($out), 200);
        } catch (\Throwable $e) {
            return response(get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), 200);
        }
    }
}
