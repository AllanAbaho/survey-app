@component('survey::questions.base', compact('question'))
    @foreach($question->options as $option)
        <div class="custom-control custom-radio">
            <input type="radio"
                   name="{{ $question->key }}"
                   id="{{ $question->key . '-' . Str::slug($option) }}"
                   value="{{ $option }}"
                   class="custom-control-input"
                    {{ ($value ?? old($question->key)) == $option ? 'checked' : '' }}
                    {{ ($disabled ?? false) ? 'disabled' : '' }}
            required>
            <label class="custom-control-label"
                   for="{{ $question->key . '-' . Str::slug($option) }}">{{ $option }}
                <!-- @if($includeResults ?? false)
                    <span class="text-success">
                        ({{ number_format((new \MattDaneshvar\Survey\Utilities\Summary($question))->similarAnswersRatio($option) * 100, 2) }}%)
                    </span>
                @endif -->
            </label>
        </div>
    @endforeach

    <?php 
    $q = ltrim($question->key, 'q');
    $answer = \App\Models\Answers::where('question_id',$q)->first();

    ?>
    <br><div class="mb-3">
    <label for="explanation" class="form-label">Explanation</label><br>
    <input type="text" class="form-control" name="{{ $question->key . '-' . 'explanation'}}"  value="{{$answer ? $answer->explanation : '' }}" {{ ($disabled ?? false) ? 'disabled' : '' }} required>
  </div><br>

@endcomponent