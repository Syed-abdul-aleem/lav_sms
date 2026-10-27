<?php
namespace App\Models;
use App\User;
use Eloquent;
class SalaryPayment extends Eloquent
{
    protected $fillable = ['user_id', 'month', 'basic', 'allowances', 'deductions', 'amt_paid', 'method', 'receipt_no', 'paid_on', 'recorded_by'];
    protected $casts = ['paid_on' => 'date'];
    public function user() { return $this->belongsTo(User::class); }
    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }
    public function getNetAttribute() { return (int)$this->basic + (int)$this->allowances - (int)$this->deductions; }
    public function getBalanceAttribute() { return $this->net - (int)$this->amt_paid; }
}
