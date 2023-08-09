<!DOCTYPE html>
<html>

<head>
    <title> <?= $survey->name ?> Results</title>
</head>

<body>
    <style>
        .text-success {
            display: none !important;
        }

        .card-header:first-child {
            display: none;
        }

        button {
            display: none !important;
        }
    </style>
    <h1><?= $survey->name ?></h1>
    <b>Organisation Type:</b> {{$survey->type}}<br>
    <b>Practice Name:</b> {{$survey->practice_name}}<br>
    <b>Assessment Officer:</b> {{$survey->assessment_officer}}<br>
    <b>Reporting Officer:</b> {{$survey->reporting_officer}}<br>
    <b>Next Review Date:</b> {{$survey->next_review_date}}<br>
    <b>Assessment Date:</b> {{$survey->created_at}}<br><br>

    @foreach($survey->questions as $question)
    {{$question->content}}<br>


    <?php $answer = \App\Models\Answers::where('question_id', $question->id)->first();
    ?>
    <b>Answer:</b> <?= $answer->value; ?><br>
    <b>Risk Level:</b> <?= $answer->risk_level; ?><br>
    <b>Explanation:</b> <?= $answer->explanation; ?><br><br>
    @endforeach
</body>

</html>