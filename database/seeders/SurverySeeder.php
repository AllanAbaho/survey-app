<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use MattDaneshvar\Survey\Models\Survey;

class SurverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $survey = Survey::create(['name' => 'AML Survey', 'settings' => ['limit-per-participant' => -1]]);

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
    }
}
