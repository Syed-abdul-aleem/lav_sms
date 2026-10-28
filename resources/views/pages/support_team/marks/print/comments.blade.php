<div>
    <table class="td-left" style="border-collapse:collapse; width:100%;">
        <tbody>
        <tr>
            <td style="width:220px;"><strong>CLASS TEACHER'S COMMENT:</strong></td>
            <td>{{ $exr->t_comment ?: str_repeat('__', 40) }}</td>
        </tr>
        <tr>
            <td><strong>PRINCIPAL'S COMMENT:</strong></td>
            <td>{{ $exr->p_comment ?: str_repeat('__', 40) }}</td>
        </tr>
        <tr>
            <td><strong>NEXT TERM BEGINS:</strong></td>
            <td>{{ $s['term_begins'] ? date('l, jS F, Y', strtotime(str_replace('/', '-', $s['term_begins']))) : '-' }}</td>
        </tr>
        <tr>
            <td><strong>NEXT TERM FEES:</strong></td>
            <td>Rs {{ number_format((int)($s['next_term_fees_'.strtolower($ct)] ?? 0)) }}/-</td>
        </tr>
        </tbody>
    </table>
</div>
