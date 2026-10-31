<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Pay;
use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\Setting;
use App\Models\StudentRecord;
use App\Repositories\PaymentRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use PDF;

/**
 * Student Fees (Easy) — one simple section for all student fees.
 *
 * Flow: Classes list -> class ke students -> har student ka fee khata (ledger).
 * Hisab auto hai: kul fee, ada shuda, pichla baqaya (arrears — purane months ki
 * unpaid fee) aur kul baqaya. Fee add bhi student ke andar se hoti hai
 * (sirf us student ke liye, ya poori class ke liye), aur har student ki
 * shareable fee slip (print / PDF) bhi yahin se banti hai.
 *
 * Ye module purani payments/payment_records tables hi use karta hai —
 * purana Payments section bhi bilkul waisa hi chalta rahega.
 * payments.student_id = individual fee marker (NULL = class/general fee).
 */
class StudentFeeController extends Controller
{
    protected $pay, $year;

    public function __construct(PaymentRepo $pay)
    {
        $this->pay = $pay;
        $this->year = Qs::getCurrentSession();
        $this->middleware('teamAccount')->except(['student', 'slip', 'pdfSlip']);
        self::ensureColumn();
    }

    public static function ensureColumn()
    {
        if (!Schema::hasColumn('payments', 'student_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedInteger('student_id')->nullable();
            });
        }
    }

    /* ---------------- helpers ---------------- */

    /** Staff (team account) sab dekh sakte hain; parent sirf apne bache ka */
    protected function canView(int $student_user_id)
    {
        $u = auth()->user();
        if (!$u) { return false; }
        if (Qs::userIsTeamAccount()) { return true; }
        return $u->user_type === 'parent' && Qs::userIsMyChild($student_user_id, $u->id);
    }

    /**
     * Class/general fees ke records har student ke liye maujood hon —
     * jo pair missing hai woh bana do (purane select_class flow ka kaam
     * ab yahan khud ho jata hai). Individual fees (student_id wali)
     * sirf usi student ko milti hain.
     */
    protected function syncClassRecords($class_id)
    {
        $payments = Payment::where('year', $this->year)
            ->whereNull('student_id')
            ->where(function ($q) use ($class_id) {
                $q->where('my_class_id', $class_id)->orWhereNull('my_class_id');
            })->get();
        if ($payments->isEmpty()) { return; }

        $students = StudentRecord::where('my_class_id', $class_id)->pluck('user_id');
        if ($students->isEmpty()) { return; }

        $existing = PaymentRecord::whereIn('student_id', $students)
            ->whereIn('payment_id', $payments->pluck('id'))
            ->get(['student_id', 'payment_id'])
            ->map(function ($r) { return $r->student_id . '-' . $r->payment_id; })
            ->flip();

        $now = now();
        $rows = [];
        foreach ($payments as $p) {
            foreach ($students as $sid) {
                if ($existing->has($sid . '-' . $p->id)) { continue; }
                $rows[] = [
                    'student_id' => $sid,
                    'payment_id' => $p->id,
                    'amt_paid'   => 0,
                    'balance'    => $p->amount,
                    'paid'       => 0,
                    'year'       => $this->year,
                    'ref_no'     => mt_rand(100000, 99999999),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            PaymentRecord::insert($chunk);
        }
    }

    /** Ek student ke saare fee records (is session) with payment */
    protected function studentRecords($student_user_id)
    {
        return PaymentRecord::with('payment')
            ->where('student_id', $student_user_id)
            ->where('year', $this->year)
            ->get()
            ->sortBy(function ($r) { return $r->payment ? $r->payment->created_at : $r->created_at; })
            ->values();
    }

    /**
     * Auto hisab:
     *  charged    = kul fee (is session ki saari fees)
     *  paid       = kul ada shuda
     *  arrears    = pichla baqaya — is month se pehle ki fees ka unpaid hissa
     *  this_month = is month ki fees (charged)
     *  balance    = kul baqaya (arrears + is month ka unpaid)
     */
    protected function totals($records)
    {
        $monthStart = now()->startOfMonth();
        $t = ['charged' => 0, 'paid' => 0, 'arrears' => 0, 'this_month' => 0, 'balance' => 0];
        foreach ($records as $r) {
            $amount = $r->payment ? (int) $r->payment->amount : 0;
            $paid = (int) ($r->amt_paid ?: 0);
            $bal = max($amount - $paid, 0);
            $t['charged'] += $amount;
            $t['paid'] += $paid;
            $t['balance'] += $bal;
            $created = $r->payment && $r->payment->created_at ? $r->payment->created_at : $r->created_at;
            if ($created && $created->lt($monthStart)) {
                $t['arrears'] += $bal;
            } else {
                $t['this_month'] += $amount;
            }
        }
        return $t;
    }

    protected function settings()
    {
        return Setting::all()->flatMap(function ($s) {
            return [$s->type => $s->description];
        });
    }

    protected function parentPhone($sr)
    {
        $phone = $sr && $sr->my_parent ? $sr->my_parent->phone : null;
        if (!$phone) { return null; }
        $p = preg_replace('/\D+/', '', $phone);
        if (strpos($p, '92') === 0) { return $p; }
        if (strpos($p, '0') === 0) { return '92' . substr($p, 1); }
        return $p;
    }

    /* ---------------- pages ---------------- */

    /** Student Fees — saari classes ek jagah, har class ka fee summary */
    public function index()
    {
        $classes = MyClass::with('class_type')->orderBy('name')->get();
        $rows = [];
        $grand = ['students' => 0, 'charged' => 0, 'paid' => 0, 'balance' => 0];
        foreach ($classes as $c) {
            $this->syncClassRecords($c->id);
            $studentIds = StudentRecord::where('my_class_id', $c->id)->pluck('user_id');
            $records = PaymentRecord::with('payment')
                ->whereIn('student_id', $studentIds)
                ->where('year', $this->year)->get();
            $t = $this->totals($records);
            $rows[] = ['class' => $c, 'students' => $studentIds->count()] + $t;
            $grand['students'] += $studentIds->count();
            $grand['charged'] += $t['charged'];
            $grand['paid'] += $t['paid'];
            $grand['balance'] += $t['balance'];
        }
        $d['rows'] = $rows;
        $d['grand'] = $grand;
        $d['year'] = $this->year;
        return view('pages.support_team.student_fees.index', $d);
    }

    /** Ek class ke saare students — har student ka fee hisab ek line me */
    public function classShow($class_id)
    {
        $class = MyClass::find($class_id);
        if (!$class) { return Qs::goWithDanger('student_fees.index'); }

        $this->syncClassRecords($class_id);
        $students = StudentRecord::with(['user', 'my_parent', 'section'])
            ->where('my_class_id', $class_id)->get()
            ->sortBy('user.name')->values();

        $rows = [];
        $ct = ['charged' => 0, 'paid' => 0, 'balance' => 0, 'arrears' => 0];
        foreach ($students as $sr) {
            $t = $this->totals($this->studentRecords($sr->user_id));
            $rows[] = ['sr' => $sr] + $t;
            $ct['charged'] += $t['charged'];
            $ct['paid'] += $t['paid'];
            $ct['balance'] += $t['balance'];
            $ct['arrears'] += $t['arrears'];
        }
        $d['my_class'] = $class;
        $d['rows'] = $rows;
        $d['ct'] = $ct;
        $d['year'] = $this->year;
        return view('pages.support_team.student_fees.class', $d);
    }

    /** Ek student ka fee khata — fees, adaigi, baqaya, sab ek screen par */
    public function student($student_id)
    {
        $student_id = (int) $student_id;
        if (!$this->canView($student_id)) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }
        $sr = StudentRecord::with(['user', 'my_parent', 'my_class', 'section'])
            ->where('user_id', $student_id)->first();
        if (!$sr) { return Qs::goWithDanger('student_fees.index'); }

        if (Qs::userIsTeamAccount()) { $this->syncClassRecords($sr->my_class_id); }

        $records = $this->studentRecords($student_id);
        $d['sr'] = $sr;
        $d['records'] = $records;
        $d['t'] = $this->totals($records);
        $d['year'] = $this->year;
        $d['staff'] = Qs::userIsTeamAccount();
        $d['wa_phone'] = $this->parentPhone($sr);
        $prIds = $records->pluck('id');
        $d['all_receipts'] = $prIds->count()
            ? \App\Models\Receipt::with('pr.payment')->whereIn('pr_id', $prIds)->orderByDesc('id')->get()
            : collect();
        return view('pages.support_team.student_fees.student', $d);
    }

    /* ---------------- actions ---------------- */

    /** Student ke andar nayi fee add — sirf is student ke liye, ya poori class ke liye */
    public function storeFee(Request $req)
    {
        $this->validate($req, [
            'student_id' => 'required|integer',
            'title'      => 'required|string|max:120',
            'amount'     => 'required|integer|min:1',
            'scope'      => 'required|in:student,class',
        ], [], ['title' => 'Fee Title', 'amount' => 'Amount']);

        $sr = StudentRecord::where('user_id', (int) $req->student_id)->first();
        if (!$sr) { return back()->with('pop_error', __('msg.rnf')); }

        $payment = Payment::create([
            'title'       => $req->title,
            'amount'      => (int) $req->amount,
            'my_class_id' => $sr->my_class_id,
            'description' => $req->scope === 'student' ? 'Individual fee (Student Fees)' : 'Class fee (Student Fees)',
            'year'        => $this->year,
            'ref_no'      => Pay::genRefCode(),
            'student_id'  => $req->scope === 'student' ? (int) $req->student_id : null,
        ]);

        $targets = $req->scope === 'student'
            ? collect([(int) $req->student_id])
            : StudentRecord::where('my_class_id', $sr->my_class_id)->pluck('user_id');

        foreach ($targets as $sid) {
            PaymentRecord::firstOrCreate(
                ['student_id' => $sid, 'payment_id' => $payment->id],
                ['amt_paid' => 0, 'balance' => (int) $req->amount, 'paid' => 0,
                 'year' => $this->year, 'ref_no' => mt_rand(100000, 99999999)]
            );
        }

        $msg = $req->scope === 'student'
            ? 'Fee add ho gayi — sirf is student ke khate me.'
            : 'Fee add ho gayi — poori class ke sab students ke khate me.';
        return back()->with('flash_success', $msg);
    }

    /** Fee jama karna — record update + receipt, hisab khud update ho jata hai */
    public function pay(Request $req, $pr_id)
    {
        $pr = PaymentRecord::with('payment')->find($pr_id);
        if (!$pr || !$pr->payment) { return back()->with('pop_error', __('msg.rnf')); }

        $balance = max((int) $pr->payment->amount - (int) ($pr->amt_paid ?: 0), 0);
        $this->validate($req, [
            'amt_paid' => 'required|integer|min:1|max:' . max($balance, 1),
        ], ['amt_paid.max' => 'Adaigi baqaya fee (Rs ' . number_format($balance) . ') se zyada nahi ho sakti.'],
           ['amt_paid' => 'Amount']);

        $amt = (int) ($pr->amt_paid ?: 0) + (int) $req->amt_paid;
        $bal = (int) $pr->payment->amount - $amt;
        $pr->update(['amt_paid' => $amt, 'balance' => $bal, 'paid' => $bal < 1 ? 1 : 0]);

        $this->pay->createReceipt([
            'amt_paid' => (int) $req->amt_paid,
            'balance'  => $bal,
            'pr_id'    => $pr->id,
            'year'     => $this->year,
        ]);

        return back()->with('flash_success', 'Fee jama ho gayi — Rs ' . number_format((int) $req->amt_paid) . '. Baqaya: Rs ' . number_format(max($bal, 0)) . '.');
    }

    /**
     * Individual fee EDIT — sirf woh fee jo sirf is student ki hai
     * (class fees yahan se edit nahi hoti, woh poori class ko affect karti hain).
     * Hisab khud theek ho jata hai: baqaya = nayi fee - ada shuda.
     */
    public function updateFee(Request $req, $payment_id)
    {
        $payment = Payment::find($payment_id);
        if (!$payment || !$payment->student_id) {
            return back()->with('pop_error', 'Ye fee edit nahi ho sakti — sirf individual (sirf is student ki) fees edit hoti hain.');
        }
        $pr = PaymentRecord::where('payment_id', $payment->id)
            ->where('student_id', $payment->student_id)->first();
        if (!$pr) { return back()->with('pop_error', __('msg.rnf')); }

        $paidAmt = (int) ($pr->amt_paid ?: 0);
        $this->validate($req, [
            'title'  => 'required|string|max:120',
            'amount' => 'required|integer|min:' . max($paidAmt, 1),
        ], ['amount.min' => 'Nayi fee ada shuda raqam (Rs ' . number_format($paidAmt) . ') se kam nahi ho sakti.'],
           ['title' => 'Fee Title', 'amount' => 'Amount']);

        $payment->update(['title' => $req->title, 'amount' => (int) $req->amount]);
        $bal = (int) $req->amount - $paidAmt;
        $pr->update(['balance' => $bal, 'paid' => $bal < 1 ? 1 : 0]);

        return back()->with('flash_success', 'Fee edit ho gayi — ' . $req->title . ': Rs ' . number_format((int) $req->amount) . '. Baqaya: Rs ' . number_format(max($bal, 0)) . '.');
    }

    /** Individual fee DELETE — sirf tab jab koi adaigi na hui ho */
    public function destroyFee($payment_id)
    {
        $payment = Payment::find($payment_id);
        if (!$payment || !$payment->student_id) {
            return back()->with('pop_error', 'Ye fee delete nahi ho sakti — sirf individual fees delete hoti hain.');
        }
        $pr = PaymentRecord::where('payment_id', $payment->id)
            ->where('student_id', $payment->student_id)->first();
        if ($pr && (int) ($pr->amt_paid ?: 0) > 0) {
            return back()->with('pop_error', 'Is fee ki adaigi ho chuki hai — delete nahi ho sakti. Zaroorat ho to amount edit kar lein.');
        }
        if ($pr) { $pr->delete(); }
        $payment->delete();
        return back()->with('flash_success', 'Fee delete ho gayi.');
    }

    /** Ghalat jama shuda fee (receipt) delete — record ka hisab khud theek ho jata hai */
    public function destroyReceipt($receipt_id)
    {
        $receipt = \App\Models\Receipt::find($receipt_id);
        if (!$receipt) { return back()->with('pop_error', __('msg.rnf')); }
        $pr = PaymentRecord::find($receipt->pr_id);
        if (!$pr) { $receipt->delete(); return back()->with('flash_success', 'Receipt delete ho gayi.'); }
        $payment = Payment::find($pr->payment_id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($receipt, $pr, $payment) {
            $newPaid = max(0, (int) ($pr->amt_paid ?: 0) - (int) ($receipt->amt_paid ?: 0));
            $amount = $payment ? (int) $payment->amount : (int) ($pr->amt_paid ?: 0);
            $pr->update([
                'amt_paid' => $newPaid,
                'balance' => $amount - $newPaid,
                'paid' => ($amount - $newPaid) < 1 ? 1 : 0,
            ]);
            $receipt->delete();
        });
        return back()->with('flash_success', 'Receipt delete ho gayi — hisab update kar diya gaya hai.');
    }

    /* ---------------- slip (shareable) ---------------- */

    protected function slipData($student_id)
    {
        $sr = StudentRecord::with(['user', 'my_parent', 'my_class', 'section'])
            ->where('user_id', $student_id)->first();
        if (!$sr) { return null; }
        if (Qs::userIsTeamAccount()) { $this->syncClassRecords($sr->my_class_id); }
        $records = $this->studentRecords($student_id);
        return [
            'sr' => $sr,
            'records' => $records,
            't' => $this->totals($records),
            's' => $this->settings(),
            'year' => $this->year,
            'wa_phone' => $this->parentPhone($sr),
        ];
    }

    /** Fee slip — screen par print-friendly page (share/print ke liye) */
    public function slip($student_id)
    {
        $student_id = (int) $student_id;
        if (!$this->canView($student_id)) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }
        $d = $this->slipData($student_id);
        if (!$d) { return Qs::goWithDanger('student_fees.index'); }
        $d['staff'] = Qs::userIsTeamAccount();
        return view('pages.support_team.student_fees.slip', $d);
    }

    /** Fee slip — PDF download (WhatsApp par share karne ke liye) */
    public function pdfSlip($student_id)
    {
        $student_id = (int) $student_id;
        if (!$this->canView($student_id)) {
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }
        $d = $this->slipData($student_id);
        if (!$d) { return Qs::goWithDanger('student_fees.index'); }
        $d['pdf_mode'] = true;
        $name = 'FeeSlip_' . str_replace(' ', '_', $d['sr']->user->name) . '_' . $this->year;
        return PDF::loadView('pages.support_team.student_fees.slip', $d)->download($name . '.pdf');
    }
}
