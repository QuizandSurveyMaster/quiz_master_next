=== Quiz And Survey Master (Formerly Quiz Master Next) ===
Contributors: fpcorso
Tags: quiz, survey, lead, test, score, exam, questionnaire, question
Requires at least: 4.9
Tested up to: 5.0.2
Requires PHP: 5.4
Stable tag: 6.1.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily and quickly add unlimited quizzes and surveys to your website.

== Description ==

= Demoes! =
Looking for demoes? Check out [Sample Quiz](https://quizandsurveymaster.com/quiz/sample-quiz/?utm_source=readme&utm_medium=plugin&utm_content=sample-quiz&utm_campaign=qsm_plugin) and [Sample Survey](https://quizandsurveymaster.com/quiz/sample-survey/?utm_source=readme&utm_medium=plugin&utm_content=sample-survey&utm_campaign=qsm_plugin)!

= Easily Create Surveys For Your Users =
You can easily create surveys for your users. Everything from customer satisfaction surveys to employee surveys.

= Customize Your Text =
All the text your users see can be **customized**. Everything from the text blocks throughout the quiz or survey to the submit button.

= Different Types Of Questions =
You can have **multiple choice** (radio buttons), **true and false**, **open answer** question, **drop down**, **multiple response** (checkboxes), **fill in the blank**, **number**, **captcha**, and **accept**. More types are being supported in future updates!

= Multiple Results Pages For Each Quiz =
Each quiz or survey can have **unlimited** results pages that can be customized with your text. Show different results pages based on the users score!

= Emails After Completion Of Quiz And Survey =
After the user takes a quiz or survey, you can have the plugin email you and the user with results. This too can be customized with your own text.

= Very Flexible =
Your quiz or survey can be graded with an incorrect/correct system or a points-based system. Or not at all. You ask for contact information at the beginning or the end and you decide which contact fields are required. You can decide to use all the questions or only a select few chosen at random. You can also set the number of questions per page or have all the questions on one page.

= Categories =
You can assign categories to your questions. You can then show the user their score in a **particular** category or an average score of the categories.

= Other Useful Features =

* Allow the user to share the results on *social networks*
* Show all questions on one page or have only a set number of questions per page
* Require user to be logged in
* Schedule when the quiz or survey should be active
* **Require** certain or all questions to be answered
* Limit amount of total entries to quiz or survey
* Can set amount of tries a user has to take the quiz or survey
* Can enable **comment boxes** for each question and/or comment section at the end of the quiz or survey
* Can enable **hints** for questions
* Can show user why the answer is the correct answer
* Questions can be in predetermined order or random
* Keep track how long a user takes on the quiz or survey
* Able to set up time limits on the quiz or survey
* Create and display math formulas
* And **Much** More...

= Make Suggestions Or Contribute =
Quiz And Survey Master is on [GitHub](https://github.com/fpcorso/quiz_master_next/)!

= Quiz And Survey Master Add-ons =
While Quiz And Survey Master is fully functional and is packed full of features that will meet the needs of most, we do offer various extra features including:

**Free Add-ons**

* [Certificates](https://quizandsurveymaster.com/downloads/certificate/?utm_source=readme&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=certificate)
* [Leaderboards](https://quizandsurveymaster.com/downloads/leaderboards/?utm_source=readme&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=leaderboads)

**Premium Add-ons**

* [URL Parameters](http://bit.ly/2I1ZM6g)
* [Google Analytics Tracking](http://bit.ly/2AAgABs)
* [Landing Page](https://quizandsurveymaster.com/downloads/landing-page/?utm_source=readme&utm_medium=plugin&utm_content=landing-page&utm_campaign=qsm_plugin)
* [Export Results](https://quizandsurveymaster.com/downloads/export-results/?utm_source=readme&utm_medium=plugin&utm_content=export-results&utm_campaign=qsm_plugin)
* [Reporting & Analysis](https://quizandsurveymaster.com/downloads/results-analysis/?utm_source=readme&utm_medium=plugin&utm_content=reporting-analysis&utm_campaign=qsm_plugin)
* [MailChimp Integration](https://quizandsurveymaster.com/downloads/mailchimp-integration/?utm_source=readme&utm_medium=plugin&utm_content=mailchimp-integration&utm_campaign=qsm_plugin)
* And **many** more available in our [Quiz And Survey Master Addon Store](https://quizandsurveymaster.com/addons/?utm_source=readme&utm_medium=plugin&utm_content=all-addons&utm_campaign=qsm_plugin)

== Installation ==

* Navigate to Add New Plugin page within your WordPress
* Search for Quiz And Survey Master
* Click Install Now link on the plugin and follow the prompts
* Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do you use that feature? =
We have extensive documentation and videos for the plugin. You can view the documentation here: [Documentation](https://docs.quizandsurveymaster.com). If you still need assistance, feel free to reach out to us!

= I want to edit the output for %QUESTIONS_ANSWERS%. Can I do that? =

Yes, it is possible to edit the output of the variable %QUESTIONS_ANSWERS%. When editing your quiz, go to the “Text” tab. Once on the “Text” tab scroll almost all the way down the page and you will see a text area that allows you to edit the contents of %QUESTIONS_ANSWERS%.

= I want to remove the "Correct Answer" part of my results page, or I want to display the "Correct Answer Info" part on my results page. How can I do that? =

To make these changes, you will edit the %QUESTIONS_ANSWERS% variable. To do that, simply look at the answer to the previous question.

= My preview looks different than my quiz. What can I do? =

This is usually a theme conflict. You can [checkout out our common conflict solutions](https://docs.quizandsurveymaster.com/article/21-common-theme-conflict-fixes) or feel free to contact us.

== Screenshots ==

1. Quiz/Survey Admin Page (With Advertisement Be Gone Add-On)
2. Quiz/Survey Settings Page (With Advertisement Be Gone Add-On)
3. Quiz/Survey Statistics Page (With Advertisement Be Gone Add-On)
4. Example Quiz
5. Example Survey
6. Quiz/Survey Results Page
8. Example Quiz With Styling

== Changelog ==

= 6.1.2 (January 2, 2019) =

= 6.1.1 (December 28, 2018) =
* Fixes issue causing broken quizzes on sites using older versions of WordPress

= 6.1.0 (December 26, 2018) =
* Adds new Gutenberg blocks
* Fixes undefined access at delete_question static function (Thanks [bpanatta](https://github.com/fpcorso/quiz_master_next/pull/746)!

= 6.0.4 (October 2, 2018) =
* Changes links from old documentation to newer documentation

= 6.0.3 (August 20, 2018) =
* Closed Bug: User gets 'trapped' if timer runs out on required question when questions are paginated ([Issue #583](https://github.com/fpcorso/quiz_master_next/issues/583))
* Closed Bug: If user refreshes quiz page when timer is at 0, cannot submit ([Issue #501](https://github.com/fpcorso/quiz_master_next/issues/501))
* Closed Bug: Saving quiz name when editing results in error if no changes are made ([Issue #391](https://github.com/fpcorso/quiz_master_next/issues/391))
* Adds deprecated notice to the quiz setting functions found in the quizCreator object
* Adds hook after results are stored in the database

= 6.0.2 (July 18, 2018) =
* Closed Bug: PHP warning thrown on Help page ([Issue #711](https://github.com/fpcorso/quiz_master_next/issues/711))
* Closed Bug: Timer not working on certain sites when using questions per page option ([Issue #709](https://github.com/fpcorso/quiz_master_next/issues/709))
* Closed Bug: Blank page shown when contact fields and message fields are empty ([Issue #707](https://github.com/fpcorso/quiz_master_next/issues/707))

= 6.0.1 (July 11, 2018) =
* Closed Bug: Quiz comment box shows HTML in label when using newer pagination system ([Issue #704](https://github.com/fpcorso/quiz_master_next/issues/704))

= 6.0.0 (June 20, 2018) =
* Closed Enhancement: Remove Tools tab ([Issue #689](https://github.com/fpcorso/quiz_master_next/issues/689))
* Closed Enhancement: Bump Minimum PHP Version To 5.4 ([Issue #607](https://github.com/fpcorso/quiz_master_next/issues/607))
* Closed Enhancement: Move Leaderboards to free addon ([Issue #380](https://github.com/fpcorso/quiz_master_next/issues/380))

([Read Full Changelog](https://github.com/fpcorso/quiz_master_next/blob/master/CHANGELOG.md))

== Upgrade Notice ==

= 6.0.3 =
Upgrade for several bug fixes