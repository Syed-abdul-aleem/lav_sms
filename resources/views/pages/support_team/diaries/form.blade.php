<div class="row">
    <div class="col-md-4 mb-2"><label>Class *</label>
        <select name="my_class_id" class="form-control" required>
            <option value="">Choose...</option>
            @foreach($classes as $c)<option value="{{ $c->id }}" {{ (old('my_class_id', $diary->my_class_id ?? '') == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
        </select></div>
    <div class="col-md-4 mb-2"><label>Section</label>
        <select name="section_id" class="form-control">
            <option value="">All sections</option>
            @foreach($sections as $s)<option value="{{ $s->id }}" {{ (old('section_id', $diary->section_id ?? '') == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
        </select></div>
    <div class="col-md-4 mb-2"><label>Subject</label>
        <select name="subject_id" class="form-control">
            <option value="">—</option>
            @foreach($subjects as $s)<option value="{{ $s->id }}" {{ (old('subject_id', $diary->subject_id ?? '') == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
        </select></div>
</div>
<div class="row">
    <div class="col-md-4 mb-2"><label>Date *</label><input type="date" name="date" class="form-control" required value="{{ old('date', isset($diary) ? $diary->date->format('Y-m-d') : date('Y-m-d')) }}"></div>
    <div class="col-md-8 mb-2"><label>Title *</label><input type="text" name="title" class="form-control" required maxlength="191" value="{{ old('title', $diary->title ?? '') }}" placeholder="e.g. Chapter 4 completed, test tomorrow"></div>
</div>
<div class="mb-2"><label>Class work / notes</label><textarea name="description" class="form-control" rows="3" maxlength="5000">{{ old('description', $diary->description ?? '') }}</textarea></div>
<div class="mb-3"><label>Homework</label><textarea name="homework" class="form-control" rows="3" maxlength="5000">{{ old('homework', $diary->homework ?? '') }}</textarea></div>
