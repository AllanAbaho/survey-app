<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MattDaneshvar\Survey\Models\Entry;
use MattDaneshvar\Survey\Models\Survey;
use PDF;
use App\Models\Answers;


class SurveyController extends Controller
{
    protected function survey($id)
    {
        return Survey::where('id', $id)->first();
    }
    protected function dashboard()
    {
        $surveys = Survey::get();
        return view('dashboard', ['surveys' => $surveys]);
    }
    public function takeSurvey($id)
    {
        if (!$this->survey($id)) {
            return redirect()->route('dashboard');
        }
        return view('take-survey', ['survey' => $this->survey($id)]);
    }
    public function viewSurvey($id)
    {
        if (!$this->survey($id)) {
            return redirect()->route('dashboard');
        }
        return view('view-survey', ['survey' => $this->survey($id)]);
    }

    public function storeSurvey(Request $request, $id)
    {

        // dd($request->all());
        $myRequest = $request->all();
        array_shift($myRequest);
        $answers = array_chunk($myRequest, 2);

        $questions = $this->survey($id)->questions()->get();
        $question_keys = [];
        foreach($questions as $question){
            $question_keys[] = $question->id;
        }
        // dd($question_keys, $answers);

        // $this->survey($id)->explanation = $request->explanation;
        // $answers = $this->validate($request, $this->survey($id)->rules);
        // dd($answers);

        // (new Entry())->for($this->survey($id))->by(Auth::user())->fromArray($answers)->push();

        $entry = new Entry;
        $entry->survey_id = $id;
        $entry->participant_id = Auth::id();
        if($entry->save()){
            for($i=0; $i<count($answers); $i++){
                $insertedAnswer = new Answers();
                $insertedAnswer->question_id = $question_keys[$i];
                $insertedAnswer->entry_id = $entry->id;
                $insertedAnswer->value = $answers[$i][0];
                $insertedAnswer->explanation = $answers[$i][1];
                $insertedAnswer->save();
            }    
        }
        return redirect()->route('dashboard')->with('success', 'Thank you for your submission');
    }

    public function downloadPdf($id)
    {

        $pdf = PDF::loadView('download', ['survey' => $this->survey($id)])->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->download($this->survey($id)->name . '.pdf');
    }

    public function startSurvey()
    {
        return view('start-survey');
    }

    public function submitSurvey(Request $request)
    {
        $name = $request->name;
        $survey = Survey::create(['name' => 'Survey For ' . $name]);
        $survey->questions()->create([
            'content' => 'Does the Client appear to be living beyond his/her means',
            'type' => 'radio-and-text',
            'options' => ['Yes', 'No'],
            'rules' => 'required'
        ]);
        $survey->questions()->create([
            'content' => 'Does the Client appear to be living beyond his/her means',
            'type' => 'radio-and-text',
            'options' => ['Yes', 'No'],
            'rules' => 'required'
        ]);
        $survey->save();
        return redirect()->route('take-survey', ['id' => $survey->id]);
    }
}
