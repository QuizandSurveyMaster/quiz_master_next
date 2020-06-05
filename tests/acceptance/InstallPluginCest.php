<?php

use Codeception\Util\Locator;


class InstallPluginCest
{
    public function _before(AcceptanceTester $I)
    {
        
    }

    public function _loginAndActivate(AcceptanceTester $I) {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();
        $I->amGoingTo('Check if plugin is activated');
        if ($I->seePluginActivated('quiz-master-next')) {
            $I->activatePlugin('quiz-master-next');
            $I->see(' activated.'); //bulk activation also taken care of. 
        }
        $I->amOnPage('/wp-admin/admin.php?page=quiz-master-next%2Fmlw_quizmaster2.php');
    }

    public function _createNewQuiz(AcceptanceTester $I) {
        $quiz_name = 'Test Quiz - ' . uniqid();

        $I->click(['id' => 'new_quiz_button']);
        $I->fillField('quiz_name', $quiz_name);
        $I->wait(1);
        $I->click('button[id="create-quiz-button"]');
        $I->see($quiz_name);
        $quiz_slug = strtolower($quiz_name);
        $quiz_slug = str_replace(" ", "-", $quiz_slug);

        return [$quiz_name, $quiz_slug];
    }

    public function _createQuestion(AcceptanceTester $I) {
        $I->amGoingTo('Click on the New Page button');
        $I->click('button[class="new-page-button button"]');
        $I->waitForText('Create New Question', 3);


        $I->amGoingTo('create new question and save it');
        $I->click('Create New Question');
        $I->wait(1);
    }

    public function _addNewMultiChoiceAnswer(AcceptanceTester $I, $count = 4) {
        $faker = Faker\Factory::create();
        $answers = [];

        $I->amGoingTo('Add new answer');
        
        for ($i=1; $i <= $count; $i++) {
            $I->click('Add New Answer!');
            $answer = $faker->sentence($nbWords = 6, $variableNbWords = true);
            $I->fillField(Locator::elementAt("//input[@class='answer-text']", $i), $answer);
            $answers[] = $answer;
        }

        $I->checkOption(Locator::elementAt("//input[@class='answer-correct']", 1));
        return $answers;
    }

    public function _saveQuestion(AcceptanceTester $I)
    {
        $I->click('button[id="save-popup-button"]');
        $I->wait(1);
        $I->dontSeeElement('#save-popup-button');
    }

    public function _loadQuiz(AcceptanceTester $I, $quiz_slug) {
        $I->amOnPage('/quiz/' . $quiz_slug);

        $I->amGoingTo('Check if quiz was loaded properly');
        $I->waitForText('Your new question!', 3);
    }

    public function _submitQuiz(AcceptanceTester $I, $answers)
    {
        
        $I->amGoingTo("Select the first answer");
        $I->waitForJqueryAjax();
        $I->scrollTo(['css' => '.qmn_quiz_radio'], 0, -40);
        // $option = $I->grabTextFrom("//input[@class='qmn_quiz_radio']");
        // $I->executeJS('console.log('.$option.')');
        // $I->amGoingTo($option);
        // $I->waitForText($answers[0]);
        $I->see($answers[0]);
        $I->waitForElementClickable(Locator::elementAt("//input[@class='qmn_quiz_radio']", 1), 3);
        $I->wait(1);
        $I->selectOption("//input[@class='qmn_quiz_radio']", $answers[0]);
        // $I->wait(3);
        $I->click('Submit');
        $I->waitForJqueryAjax();
        $I->see('Answer Provided: '. $answers[0]);
        $I->see('Correct Answer: ' . $answers[0]);
        // $I->wait(3);
    }

    // tests
    public function testProgressBarQuiz(AcceptanceTester $I)
    {
        
        $this->_loginAndActivate($I);
        [$quiz_name, $quiz_slug] = $this->_createNewQuiz($I);
        $this->_createQuestion($I);
        $answers = $this->_addNewMultiChoiceAnswer($I, 4);
        $this->_saveQuestion($I);

        $I->click('Options');
        // $I->wait(1);
        $I->click('label[for="progress_bar-1"]');
        $I->click('//*[@id="wpbody-content"]/div[2]/div[1]/div[2]/form/button[1]');

        // $quiz_slug = "test-quiz-5ec64739d8a52";
        // $answers = ["Aut fuga error aut occaecati eveniet et ex."];
        $this->_loadQuiz($I, $quiz_slug);
        $this->_submitQuiz($I, $answers);

    }

    
}
