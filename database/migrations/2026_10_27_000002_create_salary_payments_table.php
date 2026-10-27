<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateSalaryPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('salary_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id'); // staff member
            $table->string('month', 7); // YYYY-MM
            $table->unsignedInteger('basic')->default(0);
            $table->unsignedInteger('allowances')->default(0);
            $table->unsignedInteger('deductions')->default(0);
            $table->unsignedInteger('amt_paid')->default(0);
            $table->string('method')->nullable();
            $table->string('receipt_no', 30)->unique();
            $table->date('paid_on')->nullable();
            $table->unsignedInteger('recorded_by')->nullable();
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('salary_payments'); }
}
