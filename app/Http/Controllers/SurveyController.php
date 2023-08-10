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
        $entry = Entry::where('survey_id', $id)->first();
        return view('take-survey', ['survey' => $this->survey($id), 'entry' => $entry]);
    }
    public function viewSurvey($id)
    {
        if (!$this->survey($id)) {
            return redirect()->route('dashboard');
        }
        return view('download', ['survey' => $this->survey($id)]);
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

    public function updateSurvey(Request $request, $id)
    {

        $myRequest = $request->all();
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

        $entry = Entry::where('survey_id', $id)->first();
        for ($i = 0; $i < count($answers); $i++) {
            $insertedAnswer = Answers::where('question_id', $question_keys[$i])->first();
            $insertedAnswer->question_id = $question_keys[$i];
            $insertedAnswer->entry_id = $entry->id;
            $insertedAnswer->value = $answers[$i][0];
            $insertedAnswer->risk_level = $answers[$i][1];
            $insertedAnswer->explanation = $answers[$i][2];
            $insertedAnswer->save();
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
        $survey = $this->survey($id);
        return view('finish-survey', ['id' => $id, 'survey' => $survey]);
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
            "1. Have you taken into account the Product / service risk factor when assessing your own ML / TF risk?",
            "2. Have you taken into account the Delivery / distribution channel risk factor when assessing your own ML / TF risk?",
            "3. Have you taken into account the Customer risk factor when assessing your own ML / TF risk?",
            "4. Have you taken into account the Country risk factor when assessing your own ML / TF risk?",
            "5. Have you appointed an appropriate staff as a Compliance Officer ('CO') ?",
            "6. Do you ensure that CO is: the focal point for the oversight of all activities relating to the prevention and detection of ML/TF?",
            "7. Do you ensure that CO is: independent of all operational and business functions as far as practicable within any constraint of size of your institution?",
            "8. Do you ensure that CO is: of a sufficient level of seniority and authority within your institution?",
            "9. Do you ensure that CO is: fully conversant in the statutory and regulatory requirements and ML/TF risks arising from your business?",
            "10. Do you ensure that CO is: capable of accessing on a timely basis all required available information to undertake its role?",
            "11. Do you ensure that CO is: equipped with sufficient resources, including staff?",
            "12. Do you ensure that CO is: overseeing your firm's compliance with the relevant AML requirements in Uganda and overseas  branches and subsidiaries?",
            "13. Have you established an independent audit function?",
            "14. If yes, does the function regularly review the AML/CFT systems to ensure effectiveness?",
            "15. If appropriate, have you sought review assistance from external sources regarding your AML/CFT systems?",
            "16. Do you establish, maintain and operate appropriate procedures in order to be satisfied of the integrity of any new  employees?",
            "17. Does your firm have overseas branches and subsidiary undertakings?",
            "18. Do you have a group AML/CFT policy to ensure that all overseas branches and subsidiary undertakings have procedures in place to comply with the CDD and record-keeping requirements similar to those set under the AML Regulations?",
            "19. If yes, is such policy well communicated within your group?",
            "20. In the case where your overseas branches or subsidiary undertakings are unable to comply with the above mentioned policy due to local laws' restrictions, have you done the following - (a) inform the SECP of such failure?",
            "21. In the case where your overseas branches or subsidiary undertakings are unable to comply with the above mentioned policy due to local laws' restrictions, have you done the following - (b) take additional measures to effectively mitigate ML/TF risks faced by them?",
            "22. Does your RBA identify and categorize ML/TF risks at the customer level and establish reasonable measures based on risks identified?",
            "23. Do you consider the following risk factors when determining the ML/TF risk rating of customers - countries identified by the FATF as jurisdictions with strategic AML/CFT deficiencies?",
            "24. Do you consider the following risk factors when determining the ML/TF risk rating of customers - countries subject to sanctions, embargoes or similar measures issued by international authorities?",
            "25. Do you consider the following risk factors when determining the ML/TF risk rating of customers - countries which are vulnerable to corruption?",
            "26. Do you consider the following risk factors when determining the ML/TF risk rating of customers - countries that are believed to have strong links to terrorist activities?",
            "27. Do you consider the following risk factors when determining the ML/TF risk rating of customers - the public profile of the customer indicating involvement with, or connection to, politically exposed persons ('PEPs')?",
            "28. Do you consider the following risk factors when determining the ML/TF risk rating of customers - complexity of the relationship, including use of corporate structures, trusts and the use of nominee and bearer shares where there is no legitimate commercial rationale?",
            "29. Do you consider the following risk factors when determining the ML/TF risk rating of customers - request to use numbered accounts or undue levels of secrecy with a transaction?",
            "30. Do you consider the following risk factors when determining the ML/TF risk rating of customers - involvement in cash-intensive businesses?",
            "31. Do you consider the following risk factors when determining the ML/TF risk rating of customers - nature, scope and location of business activities generating the funds/assets, having regard to sensitive or high-risk activities?",
            "32. Do you consider the following risk factors when determining the ML/TF risk rating of customers - services that inherently have provided more anonymity?",
            "33. Do you consider the following risk factors when determining the ML/TF risk rating of customers - ability to pool underlying customers/funds?",
            "34. Do you consider the following risk factors when determining the ML/TF risk rating of customers - a non-face-to-face account opening approach is used?",
            "35. Do you consider the following risk factors when determining the ML/TF risk rating of customers - business sold through third party agencies or intermediaries?",
            "36. Do you adjust your risk assessment of customers from time to time or based upon information received from a competent authority, and review the extent of the CDD and ongoing monitoring to be applied?",
            "37. Do you maintain all records and relevant documents of the above risk assessment?",
            "38. If yes, are they able to demonstrate to the SECP the following? - (a) how you assess the customer?",
            "38. If yes, are they able to demonstrate to the SECP the following? - (b) the extent of CDD and ongoing monitoring is appropriate based on that customer's ML/TF risk?",
            "39. Do you (a) identify the customer and verify the customer's identity using reliable, independent source documents, data or information?",
            "40. Do you (b) obtain information on the purpose and intended nature of the business relationship established with you unless the purpose and intended nature are obvious?",
            "41. Do you (c) if a person purports to act on behalf of the customer: (i) identify the person and take reasonable measures to verify the person's identity using reliable and independent source documents, data or information?",
            "42. Do you (c) if a person purports to act on behalf of the customer: (ii) verify the person's authority to act on behalf of the customer (e.g. written authority, board resolution)?",
            "43. Do you apply CDD requirements (a) at the outset of a business relationship?",
            "44. Do you apply CDD requirements (b) when you suspect that a customer or a customer's account is involved in ML/TF?",
            "45. Do you apply CDD requirements (c) when you doubt the veracity or adequacy of any information previously obtained for the purpose of identifying the customer or for the purpose of verifying the customer's identity?",
            "46. When an individual is identified as a beneficial owner, do you obtain the following identification information: (a) Full name?",
            "47. When an individual is identified as a beneficial owner, do you obtain the following identification information: (b) Date of birth?",
            "48. When an individual is identified as a beneficial owner, do you obtain the following identification information: (c) Nationality?",
            "49. When an individual is identified as a beneficial owner, do you obtain the following identification information: (d) Identity document type and number?",
            "50. Do you verify the identity of beneficial owner(s) with reasonable measures, based on its assessment of the ML/TF risks, so that you know who the beneficial owner(s) is?",
            "51. When a person purports to act on behalf of a customer and is authorized to give instructions for the movement of funds or assets, do you obtain the identification information and take reasonable measures to verify the information obtained?",
            "52. Do you obtain the written authorization to verify that the individual purporting to represent the customer is authorized to do so?",
            "53. Do you use a streamlined approach on occasions where difficulties have been encountered in identifying and verifying signatories of individuals being represented to comply with the CDD requirements?",
            "54. If yes, do you perform the following: (a) adopt an RBA to assess whether the customer is a low risk customer and that the streamlined approach is only applicable to these low risk customers?",
            "55. If yes, do you perform the following: (b) obtain a signatory list, recording the names of the account signatories, whose identities and authority to act have been confirmed by a department or person within that customer which is independent to the persons whose identities are being verified?",
            "56. Have you rejected any documents provided during CDD and considered making a report to the authorities (e.g. FMU, police) where suspicion on the genuineness of the information cannot be eliminated?",
            "57. Do you always complete the CDD process before establishing business relationships?",
            "58. If No, do you consider: (a) any risk of ML/TF arising from the delayed verification of the customer's or beneficial owner's identity can be effectively managed?",
            "59. If No, do you consider: (b) it is necessary not to interrupt the normal course of business with the customer (e.g. securities transactions)?",
            "60. If No, do you consider: (c) verification is completed as soon as reasonably practicable?",
            "61. If No, do you consider: (d) the business relationship will be terminated if verification cannot be completed as soon as reasonably practicable?",
            "62. Have you adopted appropriate risk management policies and procedures when a customer is permitted to enter into a business relationship prior to verification?",
            "63. If yes, do they include: (a) establishing timeframes for the completion of the identity verification measures and that it is carried out as soon as reasonably practicable?",
            "64. If yes, do they include: (b) placing appropriate limits on the number of transactions and type of transactions that can be undertaken pending verification?",
            "65. If yes, do they include: (c) ensuring that funds are not paid out to any third party?",
            "66. If yes, do they include: (d) other relevant policies and procedures?",
            "67. When terminating a business relationship where funds or other assets have been received, have you returned the funds or assets to the source (where possible) from which they were received?",
            "68. Do you undertake reviews of existing records (a) when a significant transaction is to take place?",
            "69. Do you undertake reviews of existing records (b) when a material change occurs in the way the customer's account is operated?",
            "70. Do you undertake reviews of existing records (c) when your customer documentation standards change substantially?",
            "71. Do you undertake reviews of existing records (d) when you are aware that you lack sufficient information about the customer concerned?",
            "72. Do you undertake reviews of existing records (e) if there are other trigger events that you consider and defined in your policies and procedures, please elaborate further in the text box?",
            "73. Are all high-risk customers subject to a review of their profile?",
            "74. Do you have customers which are natural persons?",
            "75. Do you collect the identification information for customers: (i) Residents?",
            "76. Do you collect the identification information for customers: (ii) Non-residents?",
            "77. Do you collect the identification information for customers: (iii) Non-residents who are not physically present?",
            "78. Do you document the information?",
            "79. In cases where customers may not be able to produce verified evidence of residential address have you adopted alternative methods and applied these on a risk sensitive basis?",
            "80. Do you require additional identity information to be provided or verify additional aspects of identity if the customer, or the product or service, is assessed to present a higher ML/TF risk?",
            "81. Do you have measures to look behind each legal person or trust to identify those who have ultimate control or ultimate beneficial ownership over the business and the customer's assets?",
            "82. Do you fully understand the customer's legal form, structure and ownership, and obtain information on the nature of its business, and reasons for seeking the product or service when the reasons are not obvious?",
            "83. Do you have customers which are corporations?",
            "84. Do you obtain the following information and verification documents in relation to a customer which is a corporation?",
            "85. For companies with multiple layers in their ownership structures, do you have an understanding of the ownership and control structure of the company and fully identify the intermediate layers of the company?",
            "86. Do you take further measures, when the ownership structure of the company is dispersed/complex/multi-layered without an obvious commercial purpose, to verify the identity of the ultimate beneficial owners?",
            "87. Do you have customers which are partnerships or unincorporated bodies?",
            "88. Do you take reasonable measures to verify the identity of the beneficial owners of the partnerships or unincorporated bodies?",
            "89. Do you obtain the information and verification documents in relation to the partnership or unincorporated body?",
            "90. Do you have customers which are in the form of trusts?",
            "91. Do you obtain the information and verification documents to verify the existence, legal form and parties to a trust?",
            "92. Have you taken particular care in relation to trusts created in jurisdictions where there is no or weak money laundering legislation?",
            "93. Have you conducted SDD instead of full CDD measures for your customers?",
            "94. Before the application of SDD on any of the customer categories, have you performed checking on whether they meet the criteria of the respective category?",
            "95. Do you take additional measures or enhanced due diligence ('EDD') when the customer presents a higher risk of ML/TF?",
            "96. If yes, do they include the following: (a) obtaining additional information on the customer and updating more regularly the customer profile including the identification data?",
            "97. If yes, do they include the following: (b) obtaining additional information on the intended nature of the business relationship, the source of wealth and source of funds?",
            "98. If yes, do they include the following: (c) obtaining the approval of senior management to commence or continue the relationship?",
            "99. If yes, do they include the following: (d) conducting enhanced monitoring of the business relationship, by increasing the number and timing of the controls applied and selecting patterns of transactions that need further examination?",
            "100. Do you accept customers that are not physically present for identification purposes to open an account?",
            "101. If yes, have you taken additional measures to compensate for any risk associated with customers not physically present (i.e. face to face) for identification purposes?",
            "102. If yes, do you document such information?",
            "103. Do you define what a PEP (foreign and domestic) is in your AML/CFT policies and procedures?",
            "104. Have you established and maintained effective procedures for determining whether a customer or a beneficial owner of a customer is a PEP (foreign and domestic)?",
            "105. If yes, is screening and searches performed to determine if a customer or a beneficial owner of a customer is a PEP? (e.g. through commercially available databases, publicly available sources and internet / media searches etc)?",
            "106. Do you conduct EDD at the outset of the business relationship and ongoing monitoring when a foreign PEP is identified or suspected?",
            "107. Have you applied the following EDD measures when you know that a particular customer or beneficial owner is a foreign PEP (for both existing and new business relationships) - (a) obtaining approval from your senior management?",
            "108. Have you applied the following EDD measures when you know that a particular customer or beneficial owner is a foreign PEP (for both existing and new business relationships) - (b) taking reasonable measures to establish the customer's or the beneficial owner's source of wealth and the source of the funds?",
            "109. Have you applied the following EDD measures when you know that a particular customer or beneficial owner is a foreign PEP (for both existing and new business relationships) - (c) applying enhanced monitoring to the relationship in accordance with the assessed risks?",
            "110. Have you performed a risk assessment for an individual known to be a domestic PEP to determine whether the individual poses a higher risk of ML/TF?",
            "112. If yes and the domestic PEP poses a higher ML/TF risk, have you applied EDD and monitoring specified in question C.40 above?",
            "113. If yes, have you retained a copy of the assessment for related authorities, other authorities and auditors and reviewed the assessment whenever concerns as to the activities of the individual arise?",
            "114. For foreign and domestic PEPs assessed to present a higher risk, are they subject to a minimum of an annual review and ensure the CDD information remains up-to-date and relevant?",
            "115. Have you used any intermediaries to perform any part of your CDD measures?",
            "116. When you use an intermediary, are you satisfied that it has adequate procedures in place to prevent ML/TF?",
            "117. When you use overseas intermediaries, are you satisfied that it: (a) is required under the law of the jurisdiction concerned to be registered or licensed or is regulated under the law of that jurisdiction?",
            "118. When you use overseas intermediaries, are you satisfied that it: (b) has measures in place to ensure compliance with requirements?",
            "119. When you use overseas intermediaries, are you satisfied that it: (c) is supervised for compliance with those requirements by an authority in that jurisdiction that performs functions similar to those of any of the relevant authorities in PK?",
            "120. In order to ensure the compliance with the requirements set out above for both domestic or overseas intermediaries, do you take the following measures: (a) review the intermediary's AML/CFT policies and procedures?",
            "121. In order to ensure the compliance with the requirements set out above for both domestic or overseas intermediaries, do you take the following measures: (b) make enquiries concerning the intermediary's stature and regulatory track record and the extent to which any group's AML/CFT standards are applied and audited?",
            "122. Do you immediately (with no delay) obtain from intermediaries the data or information that the intermediaries obtained in the course of carrying out the CDD measures?",
            "123. Do you conduct sample tests from time to time to ensure CDD information and documentation is produced by the intermediary upon demand and without undue delay?",
            "124. Have you taken reasonable steps to review intermediaries' ability to perform its CDD whenever you have doubts as to the reliability of intermediaries - (b) a material change occurs in the way in which the customer's account is operated?",
            "125. Have you taken reasonable steps to review intermediaries' ability to perform its CDD whenever you have doubts as to the reliability of intermediaries - (c) you suspect that the customer or the customer's account is involved in ML/TF?",
            "126. Have you taken reasonable steps to review intermediaries' ability to perform its CDD whenever you have doubts as to the reliability of intermediaries - (d) you doubt the veracity or adequacy of any information previously obtained for the purpose of identifying and verifying the customer's identity?",
            "127. Have you taken reasonable steps to review intermediaries' ability to perform its CDD whenever you have doubts as to the reliability of intermediaries - (e) Are other trigger events that you consider and defined in your policies and procedures, please elaborate further in the text box?",
            "128. Do you refrain from maintaining (for any customer) anonymous accounts or accounts in fictitious names?",
            "129. When you do your documentation for assessment or determination of jurisdictional equivalence, do you take the following measures: (a) make reference to up-to-date and relevant information?",
            "129. When you do your documentation for assessment or determination of jurisdictional equivalence, do you take the following measures: (b) retain such record for regulatory scrutiny?",
            "129. When you do your documentation for assessment or determination of jurisdictional equivalence, do you take the following measures: (c) periodically review to ensure it remains up-to-date and valid?",
            "130. Do you continuously monitor your business relationship with a customer by: (a) monitoring the activities (including cash and non-cash transactions) of the customer to ensure that they are consistent with the nature of business, the risk profile and source of funds?",
            "130. Do you continuously monitor your business relationship with a customer by: (b) identifying transactions that are complex, large or unusual or patterns of transactions that have no apparent economic or lawful purpose and which may indicate ML/TF?",
            "131. Do you monitor: (a) the nature and type of transaction (e.g. abnormal size of frequency)?",
            "132. Do you monitor: (b) the nature of a series of transactions (e.g. a number of cash deposits)?",
            "133. Do you monitor: (c) the amount of any transactions, paying particular attention to substantial transactions?",
            "134. Do you monitor: (d) the geographical origin/destination of a payment or receipt?",
            "135. Do you monitor: (e) the customer's normal activity or turnover?",
            "136. Do you regularly identify if the basis of the business relationship changes for customers when: (a) new products or services that pose higher risk are entered into?",
            "136. Do you regularly identify if the basis of the business relationship changes for customers when: (b) new corporate or trust structures are created?",
            "136. Do you regularly identify if the basis of the business relationship changes for customers when: (c) the stated activity or turnover of a customer changes or increases?",
            "136. Do you regularly identify if the basis of the business relationship changes for customers when: (d) the nature of transactions change or the volume or size increases?",
            "136. Do you regularly identify if the basis of the business relationship changes for customers when: (e) if there are other situations, please specify and further elaborate in the text box?",
            "137. In the case where the basis of a business relationship changes significantly, do you carry out further CDD procedures to ensure that the ML/TF risk and basis of the relationship are fully understood?",
            "138. Have you established procedures to conduct a review of a business relationship upon the filing of a report to the FMU and do you update the CDD information thereafter?",
            "139. Have you taken additional measures with identified high risk business relationships (including PEPs) in the form of more intensive and frequent monitoring?",
            "140. If yes, have you considered: (a) whether adequate procedures or management information systems are in place to provide relevant staff with timely information that might include any information on any connected accounts or relationships?",
            "141. If yes, have you considered: (b) how to monitor the sources of funds, wealth and income for higher risk customers and how any changes in circumstances will be recorded?",
            "142. Do you take into account: (a) the size and complexity of its business?",
            "143. Do you take into account: (b) assessment of the ML/TF risks arising from its business?",
            "144. Do you take into account: (c) the nature of its systems and controls?",
            "145. Do you take into account: (d) the monitoring procedures that already exist to satisfy other business needs?",
            "146. Do you take into account: (e) the nature of the products and services (including the means of delivery or communication)?",
            "147. If yes, are the findings and outcomes of these examinations properly documented in writing and readily available for the SECP, competent authorities and auditors?",
            "148. In the case where you have been unable to satisfy that any cash transaction or third party transfer proposed by customers is reasonable and therefore consider it suspicious, do you make a suspicious transaction report to the FMU?",
            "149. Do you have procedures and controls in place to: (a) ensure that no payments to or from a person on a sanctions list that may affect your operations is made?",
            "150. Do you have procedures and controls in place to: (b) screen payment instructions to ensure that proposed payments to designated parties under applicable laws and regulations are not made?",
            "151. If yes, does this include: (a) drawing reference from a number of sources to ensure that you have appropriate systems to conduct checks against relevant lists for screening purposes?",
            "152. If yes, does this include: (b) procedures to ensure that the sanctions list used for screening are up to date?",
            "153. Do you: (a) understand the legal obligations of your institution and establish relevant policies and procedures?",
            "154. Do you: (b) ensure relevant legal obligations are well understood by staff and adequate guidance and training are provided?",
            "155. Do you: (c) ensure the systems and mechanisms for identification of suspicious transactions cover TF as well as ML?",
            "156. Do you maintain a database (internal or through a third party service provider) of names and particulars of terrorist suspects and designated parties which consolidates the various lists that have been made known to it?",
            "157. If yes, have you ensured that: (a) the relevant designations are included in the database?",
            "158. If yes, have you ensured that: (b) the database is subject to timely update whenever there are changes?",
            "159. If yes, have you ensured that: (c) the database is made easily accessible by staff for the purpose of identifying suspicious transactions?",
            "160. Do you perform comprehensive screening of your complete customer base to prevent TF and sanction violations?",
            "161. If yes, does it include: (a) screening customers against current terrorist and sanction designations at the establishment of the relationship?",
            "162. If yes, does it include: (b) screening against your entire client base, as soon as practicable after new terrorist and sanction designation are published by the SECP?",
            "163. Do you conduct enhanced checks before establishing a business relationship or processing a transaction if there are circumstances giving rise to a TF suspicion?",
            "164. Do you document or record electronically the results  related to the comprehensive ongoing screening, payment screening and enhanced checks if performed?",
            "165. Do you have procedures to file reports to the FMU if you suspect that a transaction is terrorist-related, even if there is no evidence of a direct terrorist connection?",
            "166. Do you have policy or system in place to make disclosures/suspicious transaction reports with the FMU?",
            "167. Do you ensure that: (a) in the event of suspicion of ML/TF, a disclosure is made even where no transaction has been conducted by or through your institution?",
            "168. Do you provide sufficient guidance to your staff to enable them to form a suspicion or to recognise when ML/TF is taking place?",
            "169. If yes, do you provide guidance to staff on identifying suspicious activity taking into account: (a) the nature of the transactions and instructions that staff is likely to encounter?",
            "170. If yes, do you provide guidance to staff on identifying suspicious activity taking into account: (b) the type of product or service?",
            "171. If yes, do you provide guidance to staff on identifying suspicious activity taking into account: (c) the means of delivery?",
            "172. Do you ensure your staff are aware and alert with the SECP's guidelines with relation to: (a) potential ML scenarios using Red Flag Indicators?",
            "173. Do you ensure your staff are aware and alert with the SECP's guidelines with relation to: (b) potential ML involving employees of APs?",
            "174. Do you keep the documents/ records relating to customer identity?",
            "175. Do you keep: (a) the identity of the parties to the transaction?",
            "176. Do you keep: (b) the nature and date of the transaction?",
            "177. Do you keep: (c) the type and amount of currency involved?",
            "178. Do you keep: (d) the origin of the funds?",
            "179. Do you keep: (e) the form in which the funds were offered or withdrawn?",
            "180. Do you keep: (f) the destination of the funds?",
            "181. Do you keep: (g) the form of instruction and authority?",
            "182. Do you keep: (h) the type and identifying number of any account involved in the transaction?",
            "183. Are the documents/ records, they kept for a period of five years after the completion of a transaction, regardless of whether the business relationship ends during the period as required under the AML/CFT Regulations?",
            "184. In the case where customer identification and verification documents are held by intermediaries, do you ensure that the intermediaries have systems in place to comply with all the record-keeping requirements?",
            "185. Have you implemented a clear and well articulated policy to ensure that relevant staff receive adequate AML/CFT training?",
            "186. Do you provide AML/CFT training to your staff to maintain their AML/CFT knowledge and competence?",
            "187. If yes, does the training program cover: (a) your institution's and the staff's own personal statutory obligations and the possible consequences for failure to report suspicious transactions under relevant laws and regulations?",
            "188. If yes, does the training program cover: (b) any other statutory and regulatory obligations that concern your institution and the staff under the relevant laws and regulations, and the possible consequences of breaches of these obligations?",
            "189. If yes, does the training program cover: (c) your own policies and procedures relating to AML/CFT, including suspicious transaction identification and reporting?",
            "190. If yes, does the training program cover: (d) any new and emerging techniques, methods and trends in ML/TF to the extent that such information is needed by your staff to carry out their particular roles in your institution with respect to AML/CFT?",
            "191. Do you provide AML/CFT training for all your new staff, irrespective of their seniority and before work commencement?",
            "192. If yes, does the training program cover: (a) an introduction to the background to ML/TF and the importance placed on ML/TF by your institution",
            "193. If yes, does the training program cover: (b) the need for identifying and reporting of any suspicious transactions to the Compliance Officer, and the offence of 'tipping-off'",
            "194. Do you provide AML/CFT training for your members of staff who are dealing directly with the public?",
            "195. If yes, does the training program cover: (a) the importance of their role in the institution's ML/TF strategy, as the first point of contact with potential money launderers?",
            "195. If yes, does the training program cover: (b) your policies and procedures in relation to CDD and record-keeping requirements that are relevant to their job responsibilities?",
            "196. If yes, does the training program cover: (c) training in circumstances that may give rise to suspicion, and relevant policies and procedures, including, for example, lines of reporting and when extra vigilance might be required?",
            "197. Do you provide AML/CFT training for your back-office staff?",
            "198. If yes, does the training program cover: (a) appropriate training on customer verification and relevant processing procedures?",
            "199. If yes, does the training program cover: (b) how to recognise unusual activities including abnormal settlements, payments or delivery instructions?",
            "200. Do you provide AML/CFT training for managerial staff including internal audit officers and COs?",
            "201. If yes, does the training program cover: (a) higher level training covering all aspects of your AML/CFT regime?",
            "202. If yes, does the training program cover: (b) specific training in relation to their responsibilities for supervising or managing staff, auditing the system and performing random checks as well as reporting of suspicious transactions to the FMU?",
            "203. Do you provide AML/CFT training for your Compliance Officer?",
            "204. If yes, does the training program cover: (a) specific training in relation to their responsibilities for assessing suspicious transaction reports submitted to them and reporting of suspicious transactions to the FMU?",
            "205. If yes, does the training program cover: (b) training to keep abreast of AML/CFT requirements/developments generally?",
            "206. Do you maintain the training record details for a minimum of 3 years?",
            "207. If yes, does the training inclued: (a) which staff has been trained?",
            "208. If yes, does the training inclued: (b) when the staff received training?",
            "209. If yes, does the training inclued: (c) the type of training provided?",
            "210. Do you monitor and maintain the effectiveness of the training conducted by staff by: (a) testing staff's understanding of the LC’s / AE’s policies and procedures to combat ML/TF?",
            "211. Do you monitor and maintain the effectiveness of the training conducted by staff by: (b) testing staff's understanding of their statutory and regulatory obligations?",
            "212. Do you monitor and maintain the effectiveness of the training conducted by staff by: (c) testing staff's ability to recognize suspicious transactions?",
            "213. Do you monitor and maintain the effectiveness of the training conducted by staff by: (d) monitoring the compliance of staff with your AML/CFT systems as well as the quality and quantity of internal reports?",
            "214. Do you monitor and maintain the effectiveness of the training conducted by staff by: (e) identifying further training needs based on training / testing assessment results identified above?",
            "215. Do you ask for further explanation of the nature of the wire transfer from the customer if there is suspicion that a customer may be effecting a wire transfer on behalf of a third party?",
            "216. Do you have clear policies on the processing of cross-border and domestic wire transfers?",
            "217. If yes, do the policies address: (a) record-keeping?",
            "218. If yes, do the policies address: (b) the verification of originator's identity information?",
        ];
    }
}
