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

    @include('survey::standard', ['survey' => $survey])

</body>

</html>