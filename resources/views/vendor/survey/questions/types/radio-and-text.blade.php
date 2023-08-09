@component('survey::questions.base', compact('question'))
<?php
$q = ltrim($question->key, 'q');
$answer = \App\Models\Answers::where('question_id', $q)->first();

$riskLevels = ['1 - Minimal', '2 - Low', '3 - Medium', '4 - High', '5 - Already Materialised'];

?>
<div class="mb-3">
    <label for="exampleInputEmail1" class="form-label">Select Option</label>
    <select class="form-select" aria-label="Default select option" name="{{ $question->key . '-' . 'value'}}">
        <option value="">Please select an option</option>
        @foreach($question->options as $option)
        <option value="{{ $option}}" {{$answer && $answer->value ==  $option ? 'selected' : '' }}>{{ $option}}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label for="exampleInputEmail1" class="form-label">Risk Level</label>
    <select class="form-select" aria-label="Default select risk level" name="{{ $question->key . '-' . 'risk_level'}}">
        <option value="">Please select an option</option>
        <option value="1 - Minimal" {{$answer && $answer->risk_level == '1 - Minimal' ? 'selected' : '' }}>1 - Minimal</option>
        <option value="2 - Low" {{$answer && $answer->risk_level == '2 - Low' ? 'selected' : '' }}>2 - Low</option>
        <option value="3 - Medium" {{$answer && $answer->risk_level == '3 - Medium' ? 'selected' : '' }}>3 - Medium</option>
        <option value="4 - High" {{$answer && $answer->risk_level == '4 - High' ? 'selected' : '' }}>4 - High</option>
        <option value="5 - Already Materialised" {{$answer && $answer->risk_level == '5 - Already Materialised' ? 'selected' : '' }}>5 - Already Materialised</option>
    </select>
</div>
<div class="mb-3">
    <label for="explanation" class="form-label">Comments</label><br>
    <input type="text" class="form-control" name="{{ $question->key . '-' . 'explanation'}}" value="{{$answer ? $answer->explanation : '' }}">
</div><br>

@endcomponent