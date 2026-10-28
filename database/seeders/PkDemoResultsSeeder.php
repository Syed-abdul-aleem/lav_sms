<?php
namespace Database\Seeders;

use App\Helpers\Qs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PkDemoResultsSeeder extends Seeder
{
    /**
     * Demo First Term exam with marks for the demo students (so the new
     * Pakistani result card has something to show), plus a demo term fee
     * with a mix of fully-paid and partial payment records.
     * Idempotent: its own rows are wiped and rebuilt on every run.
     */
    public function run()
    {
        $year = Qs::getSetting('current_session');

        $examId = DB::table('exams')->where('term', 1)->where('year', $year)->value('id');
        if (!$examId) {
            $examId = DB::table('exams')->insertGetId([
                'name' => 'First Term Examination', 'term' => 1, 'year' => $year,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        DB::table('marks')->where('exam_id', $examId)->delete();
        DB::table('exam_records')->where('exam_id', $examId)->delete();

        $grades = DB::table('grades')->get();
        $gradeFor = function ($score) use ($grades) {
            foreach ($grades as $g) {
                if ($score >= $g->mark_from && $score <= $g->mark_to) return $g->id;
            }
            return null;
        };

        $quality = [88, 76, 92, 81, 68, 95]; // target % per demo student, in seed order
        $students = DB::table('student_records')->orderBy('id')->get();
        $now = date('Y-m-d H:i:s');
        $markRows = [];
        $totals = []; // student_id => total

        foreach ($students as $si => $sr) {
            $subjects = DB::table('subjects')->where('my_class_id', $sr->my_class_id)->orderBy('id')->get();
            $q = $quality[$si % count($quality)];
            $total = 0;
            foreach ($subjects as $sidx => $sub) {
                $delta = (($si * 7 + $sidx * 3) % 13) - 6;
                $score = max(35, min(99, $q + $delta));
                $t1 = min(20, (int) round($score * 0.19));
                $t2 = min(20, (int) round($score * 0.19));
                $tca = $t1 + $t2;
                $exm = max(0, min(60, $score - $tca));
                $tex = $tca + $exm;
                $total += $tex;
                $markRows[] = [
                    'student_id' => $sr->user_id, 'subject_id' => $sub->id,
                    'my_class_id' => $sr->my_class_id, 'section_id' => $sr->section_id,
                    'exam_id' => $examId, 't1' => $t1, 't2' => $t2, 'tca' => $tca,
                    'exm' => $exm, 'tex1' => $tex, 'grade_id' => $gradeFor($tex),
                    'year' => $year, 'created_at' => $now, 'updated_at' => $now,
                ];
            }
            $totals[$sr->user_id] = ['total' => $total, 'count' => max(1, $subjects->count()), 'sr' => $sr];
        }
        foreach (array_chunk($markRows, 100) as $chunk) {
            DB::table('marks')->insert($chunk);
        }

        // Subject positions within class+section+subject
        $posGroups = [];
        foreach ($markRows as $m) {
            $posGroups[$m['my_class_id'] . '-' . $m['section_id'] . '-' . $m['subject_id']][] = $m;
        }
        foreach ($posGroups as $group) {
            usort($group, function ($a, $b) { return $b['tex1'] <=> $a['tex1']; });
            foreach ($group as $rank => $m) {
                DB::table('marks')->where('exam_id', $examId)->where('student_id', $m['student_id'])
                    ->where('subject_id', $m['subject_id'])->update(['sub_pos' => $rank + 1]);
            }
        }

        // Exam records: totals, averages, class position, comments
        $byClass = [];
        foreach ($totals as $uid => $t) { $byClass[$t['sr']->my_class_id . '-' . $t['sr']->section_id][$uid] = $t; }
        foreach ($byClass as $group) {
            $sorted = $group;
            uasort($sorted, function ($a, $b) { return $b['total'] <=> $a['total']; });
            $classAve = count($group) ? array_sum(array_map(function ($t) { return $t['total'] / $t['count']; }, $group)) / count($group) : 0;
            $rank = 0;
            foreach ($sorted as $uid => $t) {
                $rank++;
                $ave = $t['total'] / $t['count'];
                $comment = $ave >= 85 ? 'Excellent result. Keep it up.' : ($ave >= 70 ? 'Very good performance.' : ($ave >= 55 ? 'Good, but can do better with more effort.' : 'Needs more hard work and regular study.'));
                DB::table('exam_records')->insert([
                    'exam_id' => $examId, 'student_id' => $uid,
                    'my_class_id' => $t['sr']->my_class_id, 'section_id' => $t['sr']->section_id,
                    'total' => $t['total'], 'ave' => number_format($ave, 1), 'class_ave' => number_format($classAve, 1),
                    'pos' => $rank, 't_comment' => $comment, 'p_comment' => $comment,
                    'year' => $year, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // Demo term fee (PKR) per represented class band + payment records
        $feeByType = ['PP' => 15000, 'P' => 18000, 'M' => 22000, 'S' => 28000];
        $classIds = $students->pluck('my_class_id')->unique()->all();
        foreach ($classIds as $cid) {
            $ct = DB::table('my_classes')->join('class_types', 'class_types.id', '=', 'my_classes.class_type_id')
                ->where('my_classes.id', $cid)->value('class_types.code');
            $amount = $feeByType[$ct] ?? 18000;
            $paymentId = DB::table('payments')->where('ref_no', 'PKFEE-' . $cid)->value('id');
            if (!$paymentId) {
                $paymentId = DB::table('payments')->insertGetId([
                    'title' => 'First Term Fee', 'amount' => $amount, 'ref_no' => 'PKFEE-' . $cid,
                    'method' => 'Cash', 'my_class_id' => $cid,
                    'description' => 'Term fee for session ' . $year, 'year' => $year,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            foreach ($students->where('my_class_id', $cid) as $sr) {
                $exists = DB::table('payment_records')->where('payment_id', $paymentId)->where('student_id', $sr->user_id)->exists();
                if ($exists) continue;
                $full = ($sr->id % 2) === 1;
                $paidAmt = $full ? $amount : 10000;
                $prId = DB::table('payment_records')->insertGetId([
                    'payment_id' => $paymentId, 'student_id' => $sr->user_id,
                    'ref_no' => 'REC-' . $paymentId . '-' . $sr->user_id,
                    'amt_paid' => $paidAmt, 'balance' => $amount - $paidAmt,
                    'paid' => $full ? 1 : 0, 'year' => $year,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('receipts')->insert([
                    'pr_id' => $prId, 'amt_paid' => $paidAmt, 'balance' => $amount - $paidAmt,
                    'year' => $year, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }
}
