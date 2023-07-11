@component('survey::questions.base', compact('question'))
@foreach($question->options as $option)
<div class="custom-control custom-radio">
    <input type="radio" name="{{ $question->key }}" id="{{ $question->key . '-' . Str::slug($option) }}" value="{{ $option }}" class="custom-control-input" {{ ($value ?? old($question->key)) == $option ? 'checked' : '' }} {{ ($disabled ?? false) ? 'disabled' : '' }} required>
    <label class="custom-control-label" for="{{ $question->key . '-' . Str::slug($option) }}">{{ $option }}
    </label>
</div>
@endforeach

<?php
$q = ltrim($question->key, 'q');
$answer = \App\Models\Answers::where('question_id', $q)->first();

$riskLevels = ['1 - Minimal', '2 - Low', '3 - Medium', '4 - High', '5 - Already Materialised'];

?>
<br>
<div class="mb-3">
    <?php if (!$disabled) : ?>
        <label for="exampleInputEmail1" class="form-label">Select Risk Level</label>
        <select class="form-select" aria-label="Default select risk level" name="{{ $question->key . '-' . 'risk_level'}}">

            <option value="1 - Minimal">1 - Minimal</option>
            <option value="2 - Low">2 - Low</option>
            <option value="3 - Medium">3 - Medium</option>
            <option value="4 - High">4 - High</option>
            <option value="5 - Already Materialised">5 - Already Materialised</option>
        </select>
    <?php else : ?>
        Risk Level: {{$answer->risk_level}}
    <?php endif; ?>
</div>
<div class="mb-3">
    <label for="explanation" class="form-label">Comments</label><br>
    <input type="text" class="form-control" name="{{ $question->key . '-' . 'explanation'}}" value="{{$answer ? $answer->explanation : '' }}" {{ ($disabled ?? false) ? 'disabled' : '' }} required>
</div><br>

@endcomponent