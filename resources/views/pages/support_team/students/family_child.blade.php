<div class="child-block border rounded p-3 mb-3" style="background:#fbfdfb;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Child — detail</strong>
        <button type="button" class="btn btn-sm btn-outline-danger remove-child" style="display:none;" onclick="removeChild(this)">Remove</button>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Child Full Name: <span class="text-danger">*</span></label>
                <input type="text" name="children[{{ $idx }}][name]" class="form-control" placeholder="e.g. Ayesha Imran">
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group">
                <label>Gender: <span class="text-danger">*</span></label>
                <select name="children[{{ $idx }}][gender]" class="form-control">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Class: <span class="text-danger">*</span></label>
                <select name="children[{{ $idx }}][my_class_id]" class="form-control child-class">
                    @foreach($my_classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Section: <span class="text-danger">*</span></label>
                <select name="children[{{ $idx }}][section_id]" class="form-control child-section"></select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Date of Birth (optional):</label>
                <input type="date" name="children[{{ $idx }}][dob]" class="form-control">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Year Admitted (optional):</label>
                <input type="text" name="children[{{ $idx }}][year_admitted]" class="form-control" value="{{ $year ?? date('Y') }}">
            </div>
        </div>
    </div>
</div>
