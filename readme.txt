=== Quiz and Survey Master (QSM) – Quiz Maker & Survey Maker ===
Contributors: quizsurvey,expresstech
Tags: quiz, survey, quiz maker, survey maker, exam
Requires at least: 4.9
Tested up to: 7.0.2
Requires PHP: 5.4
Stable tag: 11.2.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Quiz maker & survey maker for WordPress. Build quizzes, surveys, exams & assessments with scoring, results pages & lead capture — free & easy.

== Description ==

**Quiz and Survey Master (QSM) is a free quiz maker and survey maker for WordPress** — trusted by **40,000+ websites** and rated **4.7★ from 1,200+ reviews**. Build unlimited quizzes, surveys, exams, assessments, and trivia quizzes with a drag-and-drop builder, score-based results pages, and built-in lead capture — no code required.

Whether you're an educator scoring exams, a marketer running a lead-gen quiz, or a business gathering survey feedback, QSM gives you everything you need to build quizzes and surveys that grow with you.

[Live demo »](https://quizandsurveymaster.com) · [Documentation »](https://quizandsurveymaster.com/docs/)

= Why QSM is the go-to WordPress quiz maker =

* **Unlimited quizzes & questions** — build as many quizzes, surveys, and exams as you need, each with unlimited questions.
* **12+ question types** — multiple choice, true/false, dropdowns, open-ended, fill-in-the-blank, file upload, captcha, and more.
* **Drag-and-drop + Block Editor** — assemble quizzes visually, or drop a quiz straight into any post or page with the QSM block.
* **Score-based results pages** — show different results, messages, or redirects for different score ranges (perfect for graded quizzes and personality tests).
* **Flexible grading** — points, percentages, and custom grading systems for exams and certifications.
* **Fully responsive** — every quiz and survey looks great on mobile, tablet, and desktop.

= A complete WordPress survey maker =

Turn QSM into a powerful survey tool to gather feedback and market research. Build survey forms in minutes, share them anywhere, and analyze responses in your dashboard.

* Unlimited surveys and questions
* Custom styling, text blocks, button labels, and template variables
* Export and share findings across platforms

= Capture leads & grow your list =

QSM's **built-in contact form** turns quiz-takers into email subscribers — capture leads before or after a quiz, then follow up automatically with **custom email templates**. Your quiz becomes a lead-generation engine.

= Reporting & analytics =

Track every submission with detailed **quiz reporting and analytics** — scores, response breakdowns, and results — so you can see exactly how people engage.

= Built for =

Educators and schools · training and HR teams · agencies · marketers running trivia and lead-gen quizzes · anyone who needs a free WordPress quiz or survey.

= Elevate your quizzes & surveys with QSM Pro =

Go further with **QSM Pro** — premium question types, advanced reporting, integrations (Mailchimp, ActiveCampaign, and more), conditional logic, certificates, and beautiful premium quiz themes. [Explore QSM Pro »](https://quizandsurveymaster.com/pricing/)

== Installation ==

This section describes how to install the plugin and get it working.

* Navigate to Add New Plugin page within your WordPress
* Search for Quiz And Survey Master
* Click Install Now link on the plugin and follow the prompts
* Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Is Quiz and Survey Master free? =
Yes. The core quiz maker and survey maker is 100% free. Advanced features are available in QSM Pro.

= Can I use QSM as a survey maker, not just a quiz maker? =
Absolutely. QSM builds quizzes, surveys, exams, assessments, polls, and feedback forms from the same interface.

= What question types are supported? =
12+ types, including multiple choice, true/false, dropdown, open-ended, fill-in-the-blank, file upload, and captcha.

= Can I show different results based on the score? =
Yes. Create multiple results pages tied to score ranges — ideal for graded exams, quizzes, and personality tests.

= Can I collect leads and email addresses? =
Yes. The built-in contact form captures leads, and custom email templates let you follow up automatically.

= Does QSM work with the WordPress Block Editor? =
Yes. Add any quiz or survey to a post or page with the QSM block, or use the drag-and-drop builder.

= Where can I get support? =
Free support is on the [WordPress.org support forum](https://wordpress.org/support/plugin/quiz-master-next/); Pro customers get priority support at quizandsurveymaster.com.

= Where do I report security bugs found in this plugin? =

Please report security bugs found in the source code of the Quiz And Survey Master plugin through the [Patchstack Vulnerability Disclosure  Program](https://patchstack.com/database/vdp/9e5fb395-726e-4cf4-86fd-84d1608504e1). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

== Screenshots ==

1. Dashboard
2. Create New Quiz Or Survey
3. Adding Questions / Answers
4. Questions
5. All Quizzes & Surveys
6. Contact Field
7. Style Tab
8. Featured Image
9. Frontend View with Answer
10. Answer
11. Stats
12. Reporting and analysis result
13. Customizing Themes
14. QSM Breeze Theme
15. QSM Fragrance Theme
16. QSM Ivory Theme
17. QSM Pool Theme
18. Database

== Changelog ==
= 11.2.2 (July 18, 2026) =
* Security: Fixed a Contributor+ stored XSS in the Polar question type where the "required" setting was emitted into an unquoted HTML attribute (CVE-2026-14824). Credit: Meher Sudhakar Abbireddi.
* Security: Fixed a Contributor+ cross-quiz IDOR write in the quiz text-message AJAX handler by enforcing a per-quiz ownership check (CVE-2026-14825). Credit: Revanth Hari Narayana Matte.
* Security: Fixed a Contributor+ cross-quiz information disclosure in the email and results REST GET endpoints by enforcing a per-quiz ownership check (CVE-2026-14826). Credit: Revanth Hari Narayana Matte.
* Security: Fixed a Contributor+ SQL injection via the "randon_category" quiz option by sanitizing category IDs before the query (CVE-2026-15963). Credit: PRISM.

= 11.2.1 (July 15, 2026) =
* Feature: Added an option to display the latest result in the Limited Entry Attempts feature.
* Bug: Fixed a JavaScript bug affecting the answer limit for Multiple Choice question types.
* Patch: Improved API request validation for the question_title parameter.  
* Enhancement: Improved Answer Editor filters and Results Answer attributes.

= 11.2.0 (June 29, 2026) =
* Feature: Added the ability to configure page limits for individual pages when using manual pagination
* Bug: Resolved an issue where the progress bar was not displaying correctly for flashcard questions
* Patch: Fixed a stored Cross-Site Scripting (XSS) vulnerability involving the "question_title" and "unique_id" parameters
* Enhancement: Added support for translating quiz links generated with the "quiz_link" shortcode in WPML
* Enhancement: Improved Media page UI compatibility with WordPress 7

= 11.1.5 ( June 12, 2026 ) =
* Feature: Added the %QSM_ADMIN_EMAIL% template variable to include the admin email in email templates
* Feature: Added %QSM_START_QUIZ_DATE% and %QSM_END_QUIZ_DATE% template variables to display the quiz start and end dates
* Enhancement: Improved validation and security measures when saving quiz email templates
* Enhancement: Strengthened security checks for saving email and result templates

= 11.1.4 ( May 29, 2026 ) =
* Bug: Fixed issues with bulk question imports and file reset functionality
* Patch: Resolved an IDOR vulnerability in REST endpoints that could allow unauthorized quiz and question modifications
* Enhancement: Improved QSM Core UI compatibility with WordPress 7.0
* Enhancement: Added category permission validation with enhanced UI error handling

= 11.1.3 ( May 25, 2026 ) =
* Bug: Resolved result display issues with random questions and answers
* Patch: Fixed a Cross-Site Scripting (XSS) vulnerability in rich answer type questions
* Enhancement: Improved the question hints UI in the new quiz renderer
* Enhancement: Strengthened security validation to get all the questions and user login functions

= 11.1.2 ( May 12, 2026 ) =
* Feature: Added option to enable/disable public result sharing links
* Patch: Addressed an SQL injection vulnerability related to the 'order' and 'limit' parameters
* Bug: Fixed multiple quiz display issue on same page
* Bug: Fixed image display issue in result emails
* Enhancement: Improved date question type real time validation
* Enhancement: Added support for correct answer info in bulk question import CSV

= 11.1.1 ( April 14, 2026 ) =
* Bug: Resolved a key navigation issue with new rendering
* Enhancement: Strengthened security checks for contact field values
* Patch: Fixed shortcode injection vulnerability that could allow unauthorized access to quiz results
* Patch: Addressed an SQL injection vulnerability related to the merged_question parameter

= 11.1.0 ( April 06, 2026 ) =
* Feature: Added support for embedded iframe links, enabling quizzes to be used on external websites
* Feature: Introduced QSM APIs to retrieve quiz data and questions, and to create new questions
* Bug: Resolved real time result issue with fill in the blank questions
* Enhancement: Improved query performance for the quiz dashboard widget

= 11.0.0 ( March 19, 2026 ) =
* Feature: Introduced a new quiz rendering system with fully customizable elements
* Feature: Added advanced customization options for quiz templates, including pagination settings
* Feature: Added control over question layout within quiz templates
* Feature: Added customization options for timer behavior and display
* Feature: Added support for progress bar customization
* Feature: Introduced a complete Question Bank management system
* Feature: Added filtering capabilities within the Question Bank
* Feature: Added edit functionality for existing questions
* Feature: Added options to delete and duplicate questions for quick reuse
* Feature: Added ability to create questions directly in the Question Bank without assigning them to a quiz
* Feature: Added bulk import functionality to upload questions via CSV
* Feature: Added a guided tour for creating and managing questions
* Feature: Added option to customize contact form error messages
* Feature: Added option to modify default email templates
* Feature: Added support for customizing result templates
* Enhancement: Improved database structure for quiz results
* Enhancement: Split result data into main result and result meta tables for better organization
* Enhancement: Optimized storage and retrieval of quiz results
* Enhancement: Improved UI for multiple choice and multi-response question types
* Enhancement: Added validation for phone number fields
* Bug: Fixed repeated log insertion issue
* Bug: Fixed incorrect timestamp handling in result submissions
* Patch: Fixed SQL injection vulnerability related to merged_question parameter

= 10.3.5 ( January 28, 2026 ) =
* Patch: Authentication validation issue affecting update results
* Patch: Added authentication checks when enabling multi-category support for questions

= 10.3.4 ( January 06, 2026 ) =
* Patch: Vulnerability where users can modify or delete quizzes/questions beyond their role permissions
* Enhancement: Added support for using variable in quiz redirect URL


= 10.3.3 ( December 19, 2025 ) =
* Patch: Broken Access Control vulnerability

= 10.3.2 ( December 04, 2025 ) =
* Bug: Patch vulnerability with qsm_dashboard_delete_result function
* Bug: Fixed SQL Injection via is_linking parameter
* Enhancement: Strengthened validation and overall security across quiz questions and other admin pages

= 10.3.1 ( November 11, 2025 ) =
* Bug: Resolved issue with the "Next" button when multiple quizzes are present on a single page
* Bug: Fixed inline results display issue occurring when sequential answers are enabled

= 10.3.0 ( November 5, 2025 ) =
* Feature: Added option to randomize questions within each page
* Feature: Added option to randomize the order of pages only
* Bug: Fixed issue with incorrect inline results for multiple fill-in-the-blank questions


= 10.2.8 ( October 3, 2025 ) =
* Feature: Added option to shuffle questions in edit quiz page
* Bug: Resolved conflict between per-user limits and total quiz limits

= 10.2.7 ( September 8, 2025 ) =
* Bug: Fixed an issue where the quiz page displayed blank if the "End Quiz" section was missing
* Enhancement: Improved file upload question handling to block unwanted file upload
* Enhancement: Added support for the %CONTACT_X% variable in to Email input

= 10.2.6 ( August 13, 2025 ) =
* Security: Completed fix for quiz_answer_random_ids vulnerability using secure unserialize() with allowed_classes => false

= 10.2.5 ( August 8, 2025 ) =
* Feature: Added multi-language support for quiz redirect URLs
* Bug: Fixed issue with %CORRECT_ANSWER% variable in user responses
* Bug: Patched vulnerability in the question bank API
* Bug: Patched vulnerability caused by unserialize() when random answers were enabled
* Enhancement: Improved Results List page UI

= 10.2.4 ( July 29, 2025 ) =
* Feature: Added option to limit quiz attempts by user email

= 10.2.3 ( July 15, 2025 ) =
* Bug: Fixed vulnerability with result and email templates
* Bug: Fixed issue with question count in result PDFs

= 10.2.2 ( June 24, 2025 ) =
* Bug: Fixed issue with answer type options
* Enhancement: Improved question bank popup UI

= 10.2.1 ( June 11, 2025 ) =
* Feature: Added option to display a limited number of random answer choices, always including the correct one
* Enhancement: Added setting to toggle quiz reload warning

= 10.2.0 ( May 21, 2025 ) =
* Feature: Added server-side validation for contact form fields
* Feature: Added option to filter questions by type in the question bank
* Enhancement: Added ability to insert answers at any position
* Enhancement: Refined UI and messaging for popups and alerts
* Enhancement: Added alert to prevent accidental reloads

= 10.1.1 ( April 21, 2025 ) =
* Enhancement: Added option to preload quiz and question feature images
* Fix: Improved keyboard navigation for quizzes
* Fix: Compatibility with WordPress 6.8

= 10.1.0 ( April 10, 2025 ) =
* Feature: Implemented server-side validation for contact form fields
* Feature: Added option to use responses in other questions
* Enhancement: Refined the user interface to align with the WordPress 2024 Dark Theme

= 10.0.3 ( March 17, 2025 ) =
* Feature: Added an option to remove orphaned questions and results
* Feature: Added an option to navigate to any result page
* Bug: Resolved an issue with custom CSS
* Enhancement: Updated the display logic for the result page template

= 10.0.2 ( March 02, 2025 ) =
* Hotfix: Fixed issue while creating new post/page
* Enhancement: Improved question saving option in QSM block

= 10.0.1 ( March 01, 2025 ) =
* Hotfix: Resolved JS warning while saving email template

= 10.0.0 ( Fabuary 28, 2025 ) =
* Feature: Added options to import and create email and result templates
* Feature: Enabled saving questions as drafts
* Feature: Added the ability to link a question across multiple quizzes
* Feature: Added an option to share quizzes on LinkedIn
* Feature: Added a setting to prevent duplicate emails when multiple logic conditions are met
* Feature: Added an option to disable scrolling while submitting a quiz
* Bug: Fixed an issue with inline drop-down question types
* Enhancement: Improved question search feature in question bank
* Enhancement: Improved UI across Quiz, Text, Option, and Email, Result pages
* Enhancement: Enhanced the UI for Dashboard and other related pages

= 9.2.4 ( December 30, 2024 ) =
* Bug: Resolved issue with %TOTAL_QUESTIONS% variable
* Bug: Resolved issue with %MAXIMUM_POINTS% variable

= 9.2.3 ( December 13, 2024 ) =
* Bug: Resolved issue where contact form not showing at quiz end
* Bug: Resolved quiz timer issue for single-page quizzes
* Bug: Fixed PHP warning related to the CONTACT_X variable
* Enhancement: Enhanced the Captcha question type canvas for RTL websites
* Enhancement: Refined user role permissions for improved access control

= 9.2.2 ( November 06, 2024 ) =
* Bug: Fixed issue with text displaying before quiz options
* Bug: Resolved issue with left/right arrow keys in the quiz input box
* Enhancement: Updated API to retrieve results by user ID

= 9.2.1 ( October 16, 2024 ) =
* Bug: Resolved vulnerability issue with question settings
* Bug: Fixed issue with fill in the blanks question type while using random answers
* Bug: Fixed php warning with user permission function

= 9.2.0 ( October 07, 2024 ) =
* Bug: Resolved HTML tag issue with the %USER_ANSWER% variable
* Bug: Fixed the issue with contact form position settings
* Bug: Resolved warning issue in the %TIME_FINISHED% variable
* Enhancement: Optimized performance for random question logic
* Enhancement: Improved user permissions and added additional security checks

= 9.1.3 ( September 12, 2024 ) =
* Feature: Added placeholder customization for short answer and paragraph questions
* Bug: Resolved vulnerability issue with question settings
* Bug: Fixed issue with undefined page redirection

= 9.1.2 ( August 22, 2024 ) =
* Bug: Resolved issue preventing global settings from being saved
* Bug: Resolved progress bar issue in quiz popups
* Enhancement: Reordered real-time answer settings
* Enhancement: Added control in video shortcode

= 9.1.1 ( July 26, 2024 ) =
* Feature: Added option to blacklist email domains in contact form
* Bug: Fixed vulnerability with redirect result URL
* Bug: Fixed issue with question sorting
* Enhancement: Improved quiz navigation with keyboard
* Enhancement: Checked compatibility with WordPress 6.6 and PHP 8.3

= 9.1.0 ( July 11, 2024 ) =
* Feature: Added case-sensitive option to paragraph and short answers question types
* Bug: Fixed vulnerability with quiz settings
* Bug: Fixed php error with quiz categories
* Enhancement: Added option to check database structure

= 9.0.5 ( June 19, 2024 ) =
* Bug: Resolved a security vulnerability
* Bug: Fixed the wpApiSettings JavaScript error
* Bug: Fixed an issue with & character in email templates
* Bug: Fixed the date contact field issue
* Enhancement: Improved contact field UI

= 9.0.4 ( June 10, 2024 ) =
* Enhancement: Improved HTML code management on the result page

= 9.0.3 ( June 08, 2024 ) =
* Bug: Fixed issue with QSM block first question title
* Bug: Fixed issue with next button in single question quiz
* Enhancement: Improved notification messages for better clarity and enhanced user experience

= 9.0.2 ( June 05, 2024 ) =
* Bug: Fixed security vulnerability
* Bug: Resolved an issue causing incorrect display of shortcodes due to invalid quiz IDs
* Bug: Improved user permission checks and input validation for question deletion
* Bug: Fixed validation issue with contact fields
* Feature: Users can resubmit or delete failed submissions
* Enhancement: Improved logic for handling migration queries
* Enhancement: Improved functionality for clicking the Enter key
* Enhancement: Added a Failed Submissions list to the QSM menu
* Enhancement: Improved admin notifications

= 9.0.1 (April 25, 2024) =
* Bug: Fixed date format in %ANSWER_X% variable
* Bug: Resolved PHP warning in Quiz Block editor
* Bug: Fixed an issue in showing the correct answers in a quiz while using random answers
* Enhancement: Improved text feature to accept other language characters in quiz page names
* Enhancement: Added support for using emojis in quiz questions, result pages, and email templates

= 9.0.0 (March 27, 2024) =
* Feature: Implemented a minimum length requirement for text-based question types
* Feature: Introduced a placeholder option for contact fields
* Feature: Added option to set default setting to multiple quizzes
* Feature: Added keyboard navigation functionality to quizzes
* Feature: Implemented support for rounding off points-based variables
* Feature: Integrated APIs for submitting and accessing quizzes and results
* Feature: Added option to duplicate result and email templates
* Bug: Fixed multiple option contact fields translation issue
* Bug: Fixed fill in the blanks question type translation issue
* Bug: Fixed an issue related to quiz submission when using the same quiz twice on the same page
* Bug: Fixed issue with limit questions categorized incorrectly
* Enhancement: Enhanced the user interface of option tabs for improved usability
* Enhancement: Improved the user interface of the upload files question type
* Enhancement: Improve result and email page UI

([Read Full Changelog](https://quizandsurveymaster.com/qsm-changelog/))

== Upgrade Notice ==

= 6.2.1 =
Upgrade to fix cut submit button on mobile issue.
