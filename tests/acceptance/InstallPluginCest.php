<?php 

class InstallPluginCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->amOnPluginsPage();
        $I->amGoingTo('Check if plugin is activated');
        if ($I->seePluginActivated('quiz-master-next')) {
            $I->activatePlugin('quiz-master-next');
            $I->see(' activated.'); //bulk activation also taken care of. 
        }
    }

    // tests
    public function activateAndCreateQuiz(AcceptanceTester $I)
    {
        

        $I->amOnPage('/wp-admin/admin.php?page=quiz-master-next%2Fmlw_quizmaster2.php');
        $I->wait(1); // wait for 3 secs

        $quiz_name = 'Test Quiz - '.uniqid();
        
        $I->click(['id' => 'new_quiz_button']);
        $I->fillField('quiz_name', $quiz_name);
        $I->wait(1);
        $I->click('button[id="create-quiz-button"]');
        $I->see($quiz_name);
        // $I->wait(2); // wait for 3 secs

        $I->click('button[class="new-page-button button"]');
        $I->wait(1);
        $I->click('Create New Question');

        $I->amGoingTo('save the newly created question');
        $I->wait(1);
        $I->click('button[id="save-popup-button"]');
        $I->wait(1);
        $I->see('Your new question!');

        $I->click('Options');
        // $I->wait(1);
        $I->click('label[for="progress_bar-1"]');
        $I->click('//*[@id="wpbody-content"]/div[2]/div[1]/div[2]/form/button[1]');

        $quiz_slug = strtolower($quiz_name);
        $quiz_slug = str_replace(" ", "-", $quiz_slug);
        $I->amOnPage('/quiz/' . $quiz_slug);

        $I->amGoingTo('Check if quiz was loaded properly');
        $I->waitForText('Your new question!', 3);

        // $I->wait(2);
    }
}
