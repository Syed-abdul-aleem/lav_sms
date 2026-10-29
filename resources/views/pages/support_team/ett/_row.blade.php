<tr class="ett-row">
    <td>
        <select name="rows[{{ $idx }}][day]" class="form-control row-day">
            @foreach($days as $d)
                <option value="{{ $d }}" {{ ($e && $e->day === $d) || (!$e && $d === 'Monday') ? 'selected' : '' }}>{{ $d }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="time" name="rows[{{ $idx }}][start_time]" class="form-control row-start" value="{{ $e->start_time ?? '' }}"></td>
    <td><input type="time" name="rows[{{ $idx }}][end_time]" class="form-control row-end" value="{{ $e->end_time ?? '' }}"></td>
    <td><input type="text" name="rows[{{ $idx }}][subject]" class="form-control row-subject" list="ett-subjects" placeholder="Subject (masalan English)" value="{{ $e->subject ?? '' }}"></td>
    <td>
        <select name="rows[{{ $idx }}][teacher_id]" class="form-control row-teacher">
            <option value="">— Teacher (optional) —</option>
            @foreach($teachers as $t)
                <option value="{{ $t->id }}" {{ $e && $e->teacher_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <select name="rows[{{ $idx }}][kind]" class="form-control row-kind">
            @foreach($kinds as $k)
                <option value="{{ $k }}" {{ $e && $e->kind === $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
        </select>
    </td>
    <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">✕</button></td>
</tr>
