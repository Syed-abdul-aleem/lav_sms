<div class="rc-grading">
    <strong>GRADING:</strong>
    @foreach(Mk::getGradeList($class_type->id)->sortByDesc('mark_from') as $gr)
        <span><strong>{{ $gr->name }}</strong> = {{ $gr->mark_from }}&ndash;{{ $gr->mark_to }} ({{ $gr->remark }})</span>
    @endforeach
    <br>
    <strong>Subjects Offered:</strong> {{ Mk::countSubjectsOffered($marks) }} &nbsp;&bull;&nbsp;
    <strong>Distinctions:</strong> {{ Mk::countDistinctions($marks) }} &nbsp;&bull;&nbsp;
    <strong>Credits:</strong> {{ Mk::countCredits($marks) }} &nbsp;&bull;&nbsp;
    <strong>Passes:</strong> {{ Mk::countPasses($marks) }} &nbsp;&bull;&nbsp;
    <strong>Failures:</strong> {{ Mk::countFailures($marks) }}
</div>
