<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MattDaneshvar\Survey\Models\Entry;
use MattDaneshvar\Survey\Models\Survey;
use PDF;

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
        $answers = $this->validate($request, $this->survey($id)->rules);

        (new Entry())->for($this->survey($id))->by(Auth::user())->fromArray($answers)->push();

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
        $survey = Survey::create(['name' => 'Survey For ' . $name, 'settings' => ['limit-per-participant' => -1]]);
        $survey->questions()->create([
            'content' => 'Does the Client appear to be living beyond his/her means',
            'type' => 'radio',
            'options' => ['Yes', 'No'],
            'rules' => 'required'
        ]);
        $survey->questions()->create([
            'content' => 'Client has cheques inconsistent with sales (i.e., unusual payments from unlikely sources)',
            'type' => 'radio',
            'options' => ['Yes', 'No'],
            'rules' => ['required']
        ]);
        $survey->questions()->create([
            'content' => 'Client has history of changing bookkeepers or accountants yearly',
            'type' => 'radio',
            'options' => ['Yes', 'No'],
            'rules' => ['required']
        ]);
        $survey->questions()->create([
            'content' => 'Client is uncertain about location of company records',
            'type' => 'radio',
            'options' => ['Yes', 'No'],
            'rules' => ['required']
        ]);
        $survey->questions()->create([
            'content' => 'Company carries non-existent or satisfied debt that is continually shown as current on financial statements',
            'type' => 'radio',
            'options' => ['Yes', 'No'],
            'rules' => ['required']
        ]);

        $survey->save();
        return redirect()->route('take-survey', ['id' => $survey->id]);
    }
}
