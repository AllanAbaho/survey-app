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
        foreach ($questions as $question) {
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
        if ($entry->save()) {
            for ($i = 0; $i < count($answers); $i++) {
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
        $questions = $this->getQuestions();
        $name = $request->name;
        $survey = Survey::create(['name' => 'Survey For ' . $name]);

        foreach ($questions as $question) {
            $survey->questions()->create([
                'content' => $question,
                'type' => 'radio-and-text',
                'options' => ['Yes', 'No'],
                'rules' => 'required'
            ]);
        }
        $survey->save();
        return redirect()->route('take-survey', ['id' => $survey->id]);
    }

    public function getQuestions()
    {
        return [
            '1. Does our practice have any clients or client representatives who we have not met face-to-face?',
            '2. Does our practice ascertain whether clients are PEPs?',
            '3. Does our practice have any clients who are PEPs?',
            '4. Does our practice have any clients established in a high-risk third country as identified by the European Commission?',
            '5. Does our practice have any clients established in other high risk countries?',
            '6. Does our practice have any clients connected to high risk countries?',
            '7. Does our practice have any clients with an unusual or excessively complex structure given the nature of their business?',
            '8. Does our practice have any clients with nominee shareholders or shares in bearer form?',
            '9. Does our practice have any clients with a structure that is used as a vehicle for holding personal assets?',
            '10. Does our practice have any clients that are cash-intensive?',
            '11. Does our practice have any clients that engage in transactions that are complex and unusually large, with no apparent economic or legal purpose?',
            '12. Does our practice have any clients that engage in property or other high value transactions?',
            '13. Does our practice have any clients that engage in any other types of high risk business?',
            '14. Does our practice have any clients where there are concerns around their behaviour or character, including concerns arising from providing false or stolen identification documentation or information?',
            '15. Does our practice have any clients where there are concerns around their behaviour or character, including concerns arising from their business activity (for example, unusually periodic activity, sudden activity when previously dormant, or unusually frequent changes to their business structure)?',
            '16. Does our practice have any clients where there are concerns around their behaviour or character, including concerns arising from their regulatory history?',
            '17. Does our practice have any clients where there are concerns around their behaviour or character, including concerns arising from professional clearance?',
        ];
    }
}
