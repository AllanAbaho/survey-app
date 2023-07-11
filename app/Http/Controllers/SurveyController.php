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

        $myRequest = $request->all();
        // dd($myRequest);
        array_shift($myRequest);
        $answers = array_chunk($myRequest, 3);

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
                $insertedAnswer->risk_level = $answers[$i][1];
                $insertedAnswer->explanation = $answers[$i][2];
                $insertedAnswer->save();
            }
        }
        return redirect()->route('finish-survey', ['id' => $id])->with('success', 'Thank you for your submission. Please fill in the details below to close questionaire.');
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
        $type = $request->type;
        if ($type == 'firm') {
            $questions = $this->firmQuestions();
            $options = ['Yes', 'No'];
        }
        if ($type == 'company') {
            $questions = $this->companyQuestions();
            $options = ['Yes', 'No', 'N/A'];
        }

        // $practice_name = $request->practice_name;
        // $assessment_officer = $request->assessment_officer;
        // $reporting_officer = $request->reporting_officer;
        // $next_review_date = $request->next_review_date;
        $survey = Survey::create(['name' => 'Survey For ' . $name]);

        foreach ($questions as $question) {
            $survey->questions()->create([
                'content' => $question,
                'type' => 'radio-and-text',
                'options' => $options,
                // 'rules' => 'required'
            ]);
        }
        $survey->type = $type;
        // $survey->practice_name = $practice_name;
        // $survey->assessment_officer = $assessment_officer;
        // $survey->reporting_officer = $reporting_officer;
        // $survey->next_review_date = $next_review_date;
        $survey->save();
        return redirect()->route('take-survey', ['id' => $survey->id]);
    }

    public function finishSurvey($id)
    {
        return view('finish-survey', ['id' => $id]);
    }

    public function closeSurvey(Request $request, $id)
    {
        $practice_name = $request->practice_name;
        $assessment_officer = $request->assessment_officer;
        $reporting_officer = $request->reporting_officer;
        $next_review_date = $request->next_review_date;

        $survey = Survey::where('id', $id)->first();

        $survey->practice_name = $practice_name;
        $survey->assessment_officer = $assessment_officer;
        $survey->reporting_officer = $reporting_officer;
        $survey->next_review_date = $next_review_date;
        $survey->save();
        return redirect()->route('dashboard')->with('success', 'Questionnaire completed successfully');
    }

    public function firmQuestions()
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
            '18. Does our practice provide services that might favor anonymity?',
            '19. Does our practice provide services that involve the provision of nominee directors, nominee shareholders or shadow directors?',
            '20. Does our practice provide services that involve the formation of companies in a non-EEA state?',
            '21. Does our practice provide services that involve handling or managing client money or assets?',
            '22. Does our practice provide payroll services?',
            '23. Does our practice provide any other services that may be high risk?',
            '24. Does our practice operate in any high-risk countries?',
            '25. Does our practice rely on another regulated person to identify and verify the identity of clients (and beneficial owners, if applicable)?',
            '26. Does our practice use an agent or outsourcing service provider to identify and verify the identity of clients (and beneficial owners, if applicable)?',
            '27. Does our practice have any other high risk business relationships that have not been identified by the above client and service risk factors?',
            '28. Has our practice applied EDD measures to all our business relationships and situations identified above as high risk?',
            '29. Does our practice have an AML/CTF policy that reflects the new Regulations?',
            '30. Does our practice (If applicable) Communicate its AML/CTF policy to all employees?',
            '31. Does our practice have a CDD and client risk assessment process that reflects the new Regulations?',
            '32. Do our terms of engagement include	data protection information that new clients must be provided with?',
            '33. Do our terms of engagement include	obtaining client consent to hold personal information for longer than 5 years after the business relationship ends (should we wish to so hold it)?',
            '34. Has our practice provided to all relevant employees awareness of the law relating to money laundering and terrorist financing, and to the requirements of data protection?',
            '35. Has our practice provided to all relevant employees regular training in how to recognise and deal with transactions and other activities or situations which may be related to money laundering or terrorist financing?',
            '36. Has our practice provided to all relevant employees a record in writing of the above awareness and training?',
            '37. Did the client files contain adequate CDD information and meet the practice’s AML/CTF policy?',
            '38. Does the Client appear to be living beyond his/her means?',
            '39. Client has cheques inconsistent with sales (i.e., unusual payments from unlikely sources)?',
            '40. Client has history of changing bookkeepers or accountants yearly?',
            '41. Client is uncertain about location of company records?',
            '42. Company carries non-existent or satisfied debt that is continually shown as current on financial statements?',
            '43. Company has no employees, which is unusual for the type of business?',
            '44. Company is paying unusual consultant fees to offshore companies?',
            '45. Company records consistently reflect sales at less than cost, thus putting the company into a loss position, but the company continues without reasonable explanation of the continued loss?',
            '46. Company shareholder loans are not consistent with business activity?',
            '47. Examination of source documents shows misstatements of business activity that cannot be readily traced through the company books?',
            '48. Company makes large payments to subsidiaries or similarly controlled companies that are not within the normal course of business.?',
            '49. Company acquires large personal and consumer assets (i.e boats, luxury automobiles, personal residences) when this type of transaction is inconsistent with the ordinary business practice of the client or the practice of that particular industry?',
            '50. Company is invoiced by organisations located in a country that does not have adequate money laundering laws and is known as a highly secretive banking and corporate tax haven?',
            '51. Client admits or makes ststements about involvement in criminal activities?',
            '52. Client does not want correspondence sent to home address?',
            '53. Client appears to have accounts with several financial institutions In one area for no apparent reason?',
            '54. Client condusts transactions at different physical locations in an apparent attempt to avoind detection?',
            '55. Client repeatedly uses an address but frequently changes the names involved?',
            '56. Client is accompanied and watched at all relevant times?',
            '57. Client shows uncommon curiorisyty about internal systems, controls and policies?',
            '58. Client has only vague knowledge of the amount of a deposit?',
            '59. Client presents confusing details about the transaction or knows few details about its purpose?',
            '60. Client over justifies or explains the transaction?',
            '61. Client is secretive and reletunt to meet bank officials in person in regard to an account or transactions inviolving the client?',
            '62. Client is involved in transactions that are suspicious but seems to blind to being involved in money laundering activities?',
            '63. Clients home or business telephone number has been disconnected or there is no such number when an attempt is made to contact client shortly after opening account/establishing relationship?',
            '64. Normal attempts to verify the background of a new or prospective client are difficult?',
            '65. Client appears to be acting on behalf of a third party, but does not tell you?',
            '66. Client is involved in activity out-of-keeping for that individual or business?',
            '67. Client insists that a transaction be done quickly?',
            '68. Inconsistences appear in the clients’ presentation of the transaction?',
            '69. The transaction does not appear to make sense or is out of keeping with usual or expected activity for the client?',
            '70. Client appears to have recently established a series of new relationships with different financial entities?',
            '71. Client attempts to develop close rapport with staff?',
            '72. Client uses aliases and a variety of similar but different addresses?',
            '73. Client spells his/her name differently from one transaction to another?',
            '74. Client provides false information or information that you believe is unreliable?',
            '75. Client offers you money, gratuities or unusual favours for the provision of services that may appear unusual or suspicious?',
            '76. You are aware that a client is the subject of a money laundering investigation?',
            '77. You are aware or you become aware, from a reliable source (that can include media or other open sources), that a client is suspected of being involved in illegal activity?',
            '78. A new or prospective client is known to you as having a questionable legal reputation or criminal background?',
            '79. Transaction involves a suspected shell entity (that is, a corporation that has no assets, operations or other reason to exist)?',
            '80. Client initiatives a transaction that is likely to be deemed suspicious and stops midway for whatever reason for example claiming to be in a hurry and will complete transition later but does not?',
            '81. Client stays in banking hall for longer durations than necessary either prior to or after making transaction?',
            '82. The parties to the transaction (owner,benefitiary,etc) are from countries known to support terrorist activities and organisations?',
            '83. Use of false corporations, including shell-companies in transactions?',
            '84. Client and client associations are included in the United Nations 1267 Sanctions List?',
            '85. Media reports that the account holder/client is linked to known terrorist organisations or is engaged in terrorist activities?',
            '86. Beneficial owner of the account/entity not properly identified?',
            '87. Use of nominees, trusts, family member or third-party accounts?',
            '88. Use of false identification by the client or client association?',
            '89. Client attempts to convince an employee not to complete any documentation required fro the transaction?',
            '90. Client makes inquiries that would indicate a desire to avoid reporting?',
            '91. Client has unsusual knowledge of the law in relation to suspicious transaction reporting?',
            '92. Client seems very conversant with money laundering or terrorist financing activity issues?',
            '93. Client is quick to volunterr that funds are “clean” or “not being laundered”?',
            '94. Client appears to be structuring amounts to avoid record keeping, client identification or reporting thresholds?',
            '95. Client provides doubful or vague information?',
            '96. Client produces seemingly false, inaccurate or altered identification?',
            '97. Client refuses to produce personal identification documents?',
            '98. Client is unable to provide original personal identification documents?',
            '99. Client wants to establish identity using something other than his or her personal identification documents?',
            '100. Clients supporting documentation lacks important details such as phone number?',
            '101. Client inordinately delays presenting corporate documents?',
            '102. All identification presented is foreign or cannot be checked for some reason?',
            '103.All identification documents presented appear new or have recent issue dates?',
            '104. Client presents different identification documents at different times?',
            '105. Client alters the transaction after being asked for identity documents?',
            '106. Client starts conducting frequent cash transactions in large amounts when this has not been a normal activity for the client in the past?',
            '107. Client frequently exchages small notes for large ones?',
            '108. Client uses notes in denominations that are unusual for the client, when the norm in that business is different?',
            '109. Client consistently makes cash transactions that are just under the reporting threshold amount in an apparent attempt to avoid the reporting threshold (for example between UGX 19,000,000 and UGX 20,000,000)?',
            '110. Client conduct’s a transaction for an amount that is unusual compared to amounts of past transactions?',
            '111. Client asks you to hold or transmit large sums of money or other assets when this type of activity is unusual for the client?',
            '112. Shared address for individuals involved in cash transactions, particularly when the address is also for a business location, or does not seem to correspond to the stated occupation (for example, student, unemployed, self-employed, etc)?',
            '113. Stated occupation of the client is not in keeping with the level or type of activity ( for example a student or an unemployed individual makes daily maximum cash withdrawals at multiple locations over a wide geographic area)?',
            '114. Cash is transported by a cash courier?',
            '115. Large transactions using a variety of denominations?',
            '116. Transctions seems to be inconsistent with the clients apparent financial standing or usual pattern of activities?',
            '117. Transactions appears to be out of the normal course for industry practice or does not appear to be economically viable for the client?',
            '118. Transaction is unnecessarily complex for its stated purpose?',
            '119. Activity is inconsistent with what would be expected from declared business?',
            '120. A business client refuses to provide information to qualify for a business discount?',
            '121. No business explanation for size of transactions or cash volumes?',
            '122. Transactions of financial connections between businesses that are not usually connected (for example, a company dealing in food products transacting with a company dealing in electronic gadgets)?',
            '123. Transactions involves non-profit or charitable organisation for which there appears to be no logical economic purpose or where there appears to be no link between the stated activity of the organisation and the other parties in the transaction?',
            '124. Opening accounts when the clients address is outside the local service area?',
            '125. Opening accounts in other people’s names?',
            '126. Opening accounts with names very close to other established business entities?',
            '127. Attempting to open or operating accounts under a false name?',
            '128. Account with a large number of small cash deposits and a small number of large cash withdrawals?',
            '129. Funds are being deposited into several accounts, consolidated into one and transferred outside the country?',
            '130. Client frequently uses many deposit locations outside of home branch location?',
            '131. Multiple transactions are carried out on the same day at the same branch with apparent attempt to use different tellers?',
            '132. Activity far exceeds activity projected at the time of opening the accountz?',
            '133. Opening of multiple accounts, some of which appear to remain dormant for extended periods?',
            '134. Account that was reactivated from inactive or dormant status suddenly sees significant activity?',
            '135. Reactivated dormant account containing a minimal sum suddenly receives a deposit or series of deposits followed by frequent cash withdrawals until the transferred sum has been removed?',
            '136. Large transfers from one account to other accounts that appear to be pooling money from different sources?',
            '137. Multiple deposits are made to a client’s account by third parties?',
            '138. Deposits or withdrawals of multiple monetary instruments, particularly if the instruments are sequentially numbered?',
            '139. Frequent deposits of bearer instruments (for example, cheques, money orders) in amounts just below a determined threshold?',
            '140. Unusually large cash deposits by a client with personal or business links to an arear associated with illegal activity?',
            '141. Regular return of cheques for insufficient funds?',
            '142. Correspondent accounts being used as “pass-through’ points from foreign jurisdictions with subsequent number of foreign beneficiaries, particularly when they are in locations of concern, such as countries known or suspected to facilitate money laundering activities?',
            '143. Client and other parties to the transaction have no apparent ties to Uganda?',
            '144. Transaction crosses many international lines?',
            '145. Use of a credit and issued by a foreign bank that does not operate in Uganda by a client that does not live and work in the country of issue?',
            '146. Cash volumes and international remittances in excess of average income for migrant worker clients?',
            '147. Transactions involving high-volume international transfers to third party accounts in countries that are not usual remittance corridors?',
            '148. Transactions involves a country known for highly secretive banking and corporate law?',
            '149. Transactions involving countries deemed by the Financial Action Task Force as requiring enhanced surveillance?',
            '150. Foreign currency exchanges that are associated with subsequent wire/electronic transfers to locations of concern, such as countries known or suspected to facilitate money laundering activities?',
            '151. Deposits followed within a short time by wore/electronic transfer of funds to or through locations of concern, such as countries known or suspected to facilitate money laundering activities?',
            '152. Transaction involves a country where illicit drug production or exporting may be prevalent, or where there is no effective anti-money-laundering system?',
            '153. Transaction involves a country known or suspected to facilitate money laundering activities?',
        ];
    }

    public function companyQuestions()
    {
        return [
            '1. Have you established appropriate compliance management arrangements to facilitate the
            implementation of AML/CFT systems to comply with relevant legal and regulatory obligations as well
            as to manage ML/TF risks?',
            '2. Is your senior management responsible for implementing effective AML/CFT system that can adequately manage the ML/TF risks identified?'
        ];
    }
}
