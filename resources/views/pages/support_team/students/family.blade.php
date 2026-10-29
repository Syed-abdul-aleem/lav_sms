@extends('layouts.master')
@section('page_title', 'Add Parent & Children')
@section('content')

    @if(session('flash_success'))
        <div class="alert alert-success alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            {{ session('flash_success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-styled-left alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <span class="font-weight-semibold">Oops!</span> {!! implode('<br>', $errors->all()) !!}
        </div>
    @endif

    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-bold">Add Parent &amp; Children — aik hi form, poora khandan</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <p class="text-muted">Pehle parent add karne ki zaroorat nahi. Yahan parent ki detail likhen, neeche us ke jitne bachay hain sab add kar dein — <strong>"+ Add Another Child"</strong> se mazeed bachay isi form me ajayenge. Agar parent pehle se mojood hai to list se select kar lein, sirf bachay add ho jayenge.</p>

            <form method="post" action="{{ route('students.family.store') }}" id="family-form">
                @csrf

                {{-- PARENT --}}
                <h6 class="font-weight-bold text-uppercase" style="color:#14532d;">Step 1 — Parent / Guardian</h6>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <label class="mr-3"><input type="radio" name="parent_mode" value="new" checked onchange="toggleParentMode()"> <strong>Naya Parent</strong></label>
                        <label><input type="radio" name="parent_mode" value="existing" onchange="toggleParentMode()"> <strong>Pehle se mojood Parent</strong> (sirf bachay add karne hain)</label>
                    </div>
                </div>

                <div id="new-parent-fields">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Parent Full Name: <span class="text-danger">*</span></label>
                                <input type="text" name="parent_name" value="{{ old('parent_name') }}" class="form-control" placeholder="e.g. Muhammad Imran">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Mobile Number: <span class="text-danger">*</span></label>
                                <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" class="form-control" placeholder="03XX-XXXXXXX">
                                <span class="form-text text-muted">Password khali chora to yehi mobile number parent ka password ban jayega.</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Email (optional — login ke liye):</label>
                                <input type="email" name="parent_email" value="{{ old('parent_email') }}" class="form-control" placeholder="parent@email.com">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Password (optional):</label>
                                <input type="text" name="parent_password" class="form-control" placeholder="Khali = mobile number">
                            </div>
                        </div>
                    </div>
                </div>

                <div id="existing-parent-fields" style="display:none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Parent select karein: <span class="text-danger">*</span></label>
                                <select name="existing_parent_id" class="form-control select-search" data-placeholder="Choose parent...">
                                    <option value=""></option>
                                    @foreach($parents as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} @if($p->phone) — {{ $p->phone }} @endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                {{-- CHILDREN --}}
                <h6 class="font-weight-bold text-uppercase" style="color:#14532d;">Step 2 — Children (Bachay)</h6>
                <div id="children-wrap">
                    @include('pages.support_team.students.family_child', ['idx' => 0])
                </div>

                <button type="button" class="btn btn-outline-success" onclick="addChild()"><i class="icon-plus3 mr-1"></i> Add Another Child (2nd / 3rd bacha)</button>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">Save Family (Parent + Children) <i class="icon-paperplane ml-2"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div id="child-template" style="display:none;">
        @include('pages.support_team.students.family_child', ['idx' => '__IDX__'])
    </div>

    <script>
        var childCount = 1;
        var sectionsData = [
            @foreach($sections as $s) {id: {{ $s->id }}, class_id: {{ $s->my_class_id }}, name: "{{ $s->name }}"}, @endforeach
        ];

        function fillSections(sel) {
            var wrap = sel.closest('.child-block');
            var classId = wrap.querySelector('.child-class').value;
            var secSel = wrap.querySelector('.child-section');
            var current = secSel.value;
            secSel.innerHTML = '';
            sectionsData.filter(function (s) { return s.class_id == classId; }).forEach(function (s) {
                var o = document.createElement('option');
                o.value = s.id; o.textContent = s.name;
                secSel.appendChild(o);
            });
            if (current) { secSel.value = current; }
        }

        function addChild() {
            var tpl = document.getElementById('child-template').innerHTML.replace(/__IDX__/g, childCount);
            var div = document.createElement('div');
            div.innerHTML = tpl;
            var block = div.firstElementChild;
            block.querySelector('.remove-child').style.display = '';
            document.getElementById('children-wrap').appendChild(block);
            fillSections(block.querySelector('.child-class'));
            childCount++;
        }

        function removeChild(btn) {
            btn.closest('.child-block').remove();
        }

        function toggleParentMode() {
            var mode = document.querySelector('input[name="parent_mode"]:checked').value;
            document.getElementById('new-parent-fields').style.display = (mode === 'new') ? '' : 'none';
            document.getElementById('existing-parent-fields').style.display = (mode === 'existing') ? '' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.child-class').forEach(function (sel) {
                sel.addEventListener('change', function () { fillSections(sel); });
                fillSections(sel);
            });
            toggleParentMode();
        });
        document.addEventListener('change', function (e) {
            if (e.target.classList && e.target.classList.contains('child-class')) { fillSections(e.target); }
        });
    </script>

@endsection
