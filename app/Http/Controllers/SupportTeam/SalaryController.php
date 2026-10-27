<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\SalaryPayment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function canManage()
    {
        // Super admin, admin and accountant manage payroll
        return Qs::userIsTeamAccount() || Qs::userIsAdmin() || Qs::userIsSuperAdmin();
    }

    private function staffUsers()
    {
        return User::whereIn('user_type', ['teacher', 'admin', 'accountant', 'librarian'])
            ->with('staff')->orderBy('name')->get();
    }

    public function index(Request $req)
    {
        if (!$this->canManage()) { abort(403); }
        $month = $req->get('month', date('Y-m'));
        $data['month'] = $month;
        $data['staff'] = $this->staffUsers();
        $data['payments'] = SalaryPayment::with('user')->where('month', $month)->orderByDesc('id')->get();
        $data['total_net'] = $data['payments']->sum(function ($p) { return $p->net; });
        $data['total_paid'] = $data['payments']->sum('amt_paid');
        return view('pages.support_team.salaries.index', $data);
    }

    public function store(Request $req)
    {
        if (!$this->canManage()) { abort(403); }
        $req->validate([
            'user_id' => 'required|exists:users,id',
            'month' => 'required|date_format:Y-m',
            'basic' => 'required|integer|min:0',
            'allowances' => 'nullable|integer|min:0',
            'deductions' => 'nullable|integer|min:0',
            'amt_paid' => 'required|integer|min:0',
            'method' => 'nullable|string|max:50',
            'paid_on' => 'nullable|date',
        ]);
        $net = (int)$req->basic + (int)($req->allowances ?: 0) - (int)($req->deductions ?: 0);
        if ($req->amt_paid > max($net, 0)) {
            return back()->withInput()->with('flash_danger', 'Amount paid cannot be more than the net salary (' . $net . ').');
        }
        $next = (SalaryPayment::max('id') ?: 0) + 1;
        SalaryPayment::create($req->only(['user_id', 'month', 'basic', 'method', 'paid_on']) + [
            'allowances' => (int)($req->allowances ?: 0),
            'deductions' => (int)($req->deductions ?: 0),
            'amt_paid' => (int)$req->amt_paid,
            'receipt_no' => 'SAL-' . str_pad($next, 6, '0', STR_PAD_LEFT),
            'recorded_by' => Auth::id(),
        ]);
        return back()->with('flash_success', 'Salary payment recorded.');
    }

    public function destroy($salary_id)
    {
        if (!Qs::userIsSuperAdmin()) { abort(403, 'Only Super Admin can reverse a salary payment.'); }
        SalaryPayment::findOrFail($salary_id)->delete();
        return back()->with('flash_success', 'Salary payment reversed (deleted).');
    }

    public function mySalary()
    {
        if (!Qs::userIsStaff()) { abort(403); }
        $data['payments'] = SalaryPayment::where('user_id', Auth::id())->orderByDesc('month')->orderByDesc('id')->get();
        $data['user'] = Auth::user();
        return view('pages.support_team.salaries.my_salary', $data);
    }

    public function slip($salary_id)
    {
        $p = SalaryPayment::with('user')->findOrFail($salary_id);
        if (!$this->canManage() && (int)$p->user_id !== (int)Auth::id()) { abort(403); }
        $data['p'] = $p;
        $data['sr'] = $p->user->staff->first();
        return view('pages.support_team.salaries.slip', $data);
    }
}
