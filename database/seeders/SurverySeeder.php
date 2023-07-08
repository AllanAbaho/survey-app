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
        $survey = Survey::create(['name' => 'AML Survey']);

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
    }
}
