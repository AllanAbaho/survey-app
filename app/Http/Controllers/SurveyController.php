<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MattDaneshvar\Survey\Models\Entry;
use MattDaneshvar\Survey\Models\Survey;

class SurveyController extends Controller
{
    protected function survey()
    {
        return Survey::where('id', 1)->first();
    }
    public function takeSurvey()
    {
        return view('take-survey', ['survey' => $this->survey()]);
    }

    public function store(Request $request)
    {
        $answers = $this->validate($request, $this->survey()->rules);

        (new Entry())->for($this->survey())->fromArray($answers)->push();

        return back()->with('success', 'Thank you for your submission');
    }
}
