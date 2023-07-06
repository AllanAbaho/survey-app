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
    </style>
    <h1><?= $survey->name ?></h1>
    @include('survey::standard', ['survey' => $survey])

</body>

</html>