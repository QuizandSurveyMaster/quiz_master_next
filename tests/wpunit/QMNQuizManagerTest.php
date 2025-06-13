<?php

// Ensure we are in the WordPress testing environment
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/../../../' );
}

// Load WordPress testing environment if not already loaded by Codeception
// This might be handled by bootstrap.php in Codeception's wpunit setup
if ( file_exists( ABSPATH . 'wp-tests-config.php' ) ) {
    // We are likely in a WP test environment
} else {
    // Fallback for local execution if needed, though Codeception should handle this.
    // define( 'WP_TESTS_CONFIG_FILE_PATH', ABSPATH . 'wp-tests-config.php' );
}


// Use the WpunitTester if available through Codeception's DI
// For standalone WP_UnitTestCase, you'd extend that.
// Assuming Codeception setup handles loading of WP environment.

class QMNQuizManagerTest extends \Codeception\Test\Unit
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    protected $quiz_id;
    protected $question_ids = [];
    protected static $qmn_quiz_manager;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        // Ensure the class is loaded. Adjust path if necessary.
        // This might be handled by Codeception's autoloader or bootstrap.
        if (!class_exists('QMNQuizManager')) {
            require_once dirname( __FILE__ ) . '/../../php/classes/class-qmn-quiz-manager.php';
        }
        if (!class_exists('QSM_Quiz_Settings')) {
            require_once dirname( __FILE__ ) . '/../../php/classes/class-qsm-settings.php';
        }
        if (!class_exists('QMNPluginHelper')) {
            require_once dirname( __FILE__ ) . '/../../php/classes/class-qmn-plugin-helper.php';
        }
        // Mock or get an instance of the main plugin class
        global $mlwQuizMasterNext;
        if (!isset($mlwQuizMasterNext)) {
            // This is a simplified mock. In a real scenario, this would be more complex
            // or handled by a proper test bootstrap for the plugin.
            $mlwQuizMasterNext = new stdClass();
            $mlwQuizMasterNext->quiz_settings = new QSM_Quiz_Settings();
            $mlwQuizMasterNext->pluginHelper = new QMNPluginHelper();
            // Mock other necessary properties and methods if QMNPluginHelper constructor has dependencies
        }
        self::$qmn_quiz_manager = new QMNQuizManager();
    }

    protected function _before()
    {
        // Potentially reset or re-initialize $mlwQuizMasterNext for each test if needed
        // For now, assuming setUpBeforeClass is sufficient for the global.
    }

    protected function _after()
    {
        global $wpdb;
        if ($this->quiz_id) {
            $wpdb->delete($wpdb->prefix . 'mlw_quizzes', ['quiz_id' => $this->quiz_id]);
            $wpdb->delete($wpdb->prefix . 'mlw_questions', ['quiz_id' => $this->quiz_id]);
            // Clean up any associated post if quizzes are CPTs
            // $quiz_post = get_posts(['meta_key' => 'quiz_id', 'meta_value' => $this->quiz_id, 'post_type' => 'qsm_quiz']);
            // if ($quiz_post) wp_delete_post($quiz_post[0]->ID, true);
        }
        foreach ($this->question_ids as $question_id) {
            $wpdb->delete($wpdb->prefix . 'mlw_questions', ['question_id' => $question_id]);
        }
        $this->question_ids = [];
        $this->quiz_id = null;

        // Reset $_POST
        $_POST = [];
    }

    /**
     * Helper to create a quiz with specific grading system.
     */
    protected function create_quiz_with_grading_system($name, $grading_system_value = null, $other_options = [])
    {
        global $wpdb;
        global $mlwQuizMasterNext;

        $quiz_data = [
            'quiz_name' => $name,
            'quiz_settings' => '', // Will be populated
        ];
        $wpdb->insert($wpdb->prefix . 'mlw_quizzes', $quiz_data);
        $this->quiz_id = $wpdb->insert_id;

        $quiz_settings_data = [
            'quiz_options' => [],
            'quiz_text' => QMNPluginHelper::get_default_texts(), // Add default texts
            // Initialize other sections if necessary
        ];

        if ($grading_system_value !== null) {
            $quiz_settings_data['quiz_options']['grading_system'] = $grading_system_value;
        }
        $quiz_settings_data['quiz_options'] = array_merge($quiz_settings_data['quiz_options'], $other_options);

        // Ensure default options are present if not overridden
        // This simulates what QSM_Install::register_default_settings and QSM_Quiz_Settings::load_settings would do
        $default_quiz_options = $this->get_default_quiz_options();
        $quiz_settings_data['quiz_options'] = wp_parse_args($quiz_settings_data['quiz_options'], $default_quiz_options);


        $wpdb->update(
            $wpdb->prefix . 'mlw_quizzes',
            ['quiz_settings' => maybe_serialize($quiz_settings_data)],
            ['quiz_id' => $this->quiz_id]
        );

        // Prepare the quiz settings object for this quiz
        // This is crucial for get_section_setting to work correctly
        $mlwQuizMasterNext->quiz_settings->prepare_quiz($this->quiz_id);

        return $this->quiz_id;
    }

    /**
     * Helper to get default quiz options similar to QSM_Install.
     */
    protected function get_default_quiz_options() {
        // A simplified version of what QSM_Install::register_default_settings does
        // We only need the keys and their default values for the 'quiz_options' section
        return [
            'form_type' => 0,
            'system' => 0, // This is the global grading system, not our quiz-specific one
            'score_roundoff' => 0,
            'grading_system' => 'correct_incorrect', // Our new field's default
            'correct_answer_logic' => 0,
            'enable_deselect_option' => 0,
            'form_disable_autofill' => 0,
            'disable_mathjax' => 0,
            'randomness_order' => 0,
            'scheduled_time_start' => '',
            'scheduled_time_end' => '',
            'not_allow_after_expired_time' => 0,
            'question_from_total' => 0,
            'question_per_category' => 0,
            'limit_category_checkbox' => 0,
            'randon_category' => '',
            'select_category_question' => '',
            'default_answers' => 1,
            'require_log_in' => 0,
            'comment_section' => 1,
            'prevent_reload' => 0,
            'timer_limit' => 0,
            'enable_result_after_timer_end' => 0,
            'skip_validation_time_expire' => 0,
            'total_user_tries' => 0,
            'limit_total_entries' => 0,
            'enable_retake_quiz_button' => 0,
            'store_responses' => 1,
            'send_email' => 1,
            'check_already_sent_email' => 0,
            'progress_bar' => 0,
            'enable_quick_result_mc' => 1, // Default was 1 in my previous changes
            'enable_quick_correct_answer_info' => 0,
            'pagination' => 0,
            'question_numbering' => 0,
            'show_category_on_front' => 0,
            'show_optin' => 0,
            'show_text_html' => 0,
            'hide_correct_answer' => 0,
            'show_question_featured_image_in_result' => 0,
            'disable_description_on_result' => 0,
            'disable_scroll_on_result' => 0,
            'quiz_animation' => '',
            'enable_pagination_quiz' => 0,
            'disable_scroll_next_previous_click' => 0,
            'result_page_fb_image' => QSM_PLUGIN_URL . 'assets/icon-200x200.png',
            'ajax_show_correct' => 0,
            'preferred_date_format' => 'F j, Y',
            'contact_info_location' => 0,
            'loggedin_user_contact' => 0,
            'contact_disable_autofill' => 0,
            'disable_first_page' => 0,
            // Legacy options from QSM_Install - these might not all be in 'quiz_options' but good to have a reference
            'social_media' => 0,
            'user_name' => 2,
            'user_comp' => 2,
            'user_email' => 2,
            'user_phone' => 2,
        ];
    }


    /**
     * Helper to create a question.
     * Note: This is a simplified version. Real question creation might be more complex.
     */
    protected function create_question($quiz_id, $args = [])
    {
        global $wpdb;
        $defaults = [
            'quiz_id' => $quiz_id,
            'question_name' => 'Sample Question?',
            'answer_array' => serialize([
                ['Answer A', 1, 1], // text, points, is_correct
                ['Answer B', 0, 0],
            ]),
            'correct_answer' => 1, // Index of correct answer in answer_array (1-based)
            'question_type_new' => 'multiple_choice',
            'question_settings' => serialize(['required' => 0]), // Example setting
            'category' => 'Default',
            'deleted' => 0,
            'question_order' => 1,
        ];
        $data = wp_parse_args($args, $defaults);

        $wpdb->insert($wpdb->prefix . 'mlw_questions', $data);
        $question_id = $wpdb->insert_id;
        $this->question_ids[] = $question_id;
        return $question_id;
    }

    protected function simulate_quiz_submission_data($quiz_id, $questions_with_answers) {
        $_POST = []; // Clear previous POST data
        $_POST['qmn_quiz_id'] = $quiz_id;
        $_POST['qsm_nonce'] = wp_create_nonce('qsm_submit_quiz_' . $quiz_id); // Create a valid nonce
        $_POST['qsm_unique_key'] = uniqid();
        $_POST['complete_quiz'] = 'confirmation'; // Important for submit_results path

        $question_id_list_for_post = [];
        foreach ($questions_with_answers as $qid => $answer_data) {
            $question_id_list_for_post[] = $qid;
            if (isset($answer_data['user_answer'])) { // For single choice
                $_POST['mlw_question'][$qid] = $answer_data['user_answer'];
            } elseif (isset($answer_data['user_answers'])) { // For multiple response
                 $_POST['mlw_question'][$qid] = $answer_data['user_answers'];
            }
            if (isset($answer_data['comment'])) {
                $_POST['mlwComment' . $qid] = $answer_data['comment'];
            }
        }
        $_POST['qmn_question_list'] = implode('Q', $question_id_list_for_post) . 'Q';
        $_POST['total_questions'] = count($question_id_list_for_post); // Assuming all questions are answered
    }

    // Test methods will go here
    public function test_no_grading_system()
    {
        global $wpdb;
        global $mlwQuizMasterNext;

        $quiz_id = $this->create_quiz_with_grading_system('No Grading Test Quiz', 'none');
        $q1_id = $this->create_question($quiz_id, [
            'answer_array' => serialize([ // Correct answer is A (index 0)
                ['Answer A', 10, 1],
                ['Answer B', 0, 0],
            ]),
            'correct_answer' => 1, // 1-based index
        ]);
        $q2_id = $this->create_question($quiz_id, [
            'question_name' => 'Another Question',
            'answer_array' => serialize([ // Correct answer is C (index 2)
                ['Answer C', 5, 1],
                ['Answer D', 0, 0],
            ]),
            'correct_answer' => 1,
        ]);

        // Simulate user answers (e.g., user chose "Answer A" for q1, "Answer D" for q2)
        $user_answers = [
            $q1_id => ['user_answer' => '0'], // Index of "Answer A"
            $q2_id => ['user_answer' => '1'], // Index of "Answer D"
        ];
        $this->simulate_quiz_submission_data($quiz_id, $user_answers);

        // Prepare data for check_answers
        // $options is typically $qmn_quiz_options from display_shortcode
        // $quiz_data is $qmn_array_for_variables

        // This requires $mlwQuizMasterNext->quiz_settings to be prepared for $quiz_id
        // which create_quiz_with_grading_system should handle.
        $qsm_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();

        $qsm_quiz_data_for_check = [
            'quiz_id' => $quiz_id,
            'quiz_name' => 'No Grading Test Quiz',
            'user_ip' => '127.0.0.1',
            'user_id' => 1, // Assuming a logged-in user for simplicity
            'hidden_questions' => [],
             // ... other necessary fields for $quiz_data if any
        ];

        $checked_answers_result = QMNQuizManager::check_answers($qsm_quiz_options, $qsm_quiz_data_for_check);

        $this->assertEquals(0, $checked_answers_result['total_points'], 'Total points should be 0 for "none" grading.');
        $this->assertEquals(0, $checked_answers_result['total_score'], 'Total score should be 0 for "none" grading.');
        $this->assertEquals(0, $checked_answers_result['total_correct'], 'Total correct should be 0 for "none" grading.');

        foreach ($checked_answers_result['question_answers_array'] as $question_result) {
            $this->assertEquals('ungraded', $question_result['correct'], 'Question status should be "ungraded".');
            $this->assertEquals(0, $question_result['points'], 'Points for each question should be 0.');
        }

        // Simulate storing results
        $results_array_for_db = [
            0, // timer
            $checked_answers_result['question_answers_array'],
            '', // comments
            'contact'  => [],
            'timer_ms' => 0,
            'pagetime' => [],
            'hidden_questions' => [],
            'total_possible_points' => $checked_answers_result['total_possible_points'],
            'total_attempted_questions' => $checked_answers_result['total_attempted_questions'],
            'minimum_possible_points' => $checked_answers_result['minimum_possible_points'],
            'quiz_start_date' => '',
        ];

        $insert_data_for_db = [
            'qmn_array_for_variables' => $checked_answers_result, // check_answers output is merged into this
            'results_array'           => $results_array_for_db,
            'unique_id'               => uniqid(),
            'form_type'               => 0, // Assuming Quiz type
            'http_referer'            => 'http://example.com/test-quiz',
            'page_name'               => 'Test Quiz Page',
        ];
        // Manually add quiz_id to qmn_array_for_variables as it's expected by add_quiz_results
        $insert_data_for_db['qmn_array_for_variables']['quiz_id'] = $quiz_id;
        $insert_data_for_db['qmn_array_for_variables']['quiz_name'] = 'No Grading Test Quiz';
        $insert_data_for_db['qmn_array_for_variables']['quiz_system'] = $qsm_quiz_options->system; // global system
        $insert_data_for_db['qmn_array_for_variables']['user_name'] = 'Test User';
        $insert_data_for_db['qmn_array_for_variables']['user_business'] = 'Test Biz';
        $insert_data_for_db['qmn_array_for_variables']['user_email'] = 'test@example.com';
        $insert_data_for_db['qmn_array_for_variables']['user_phone'] = '1234567890';
        $insert_data_for_db['qmn_array_for_variables']['user_id'] = 1;
        $insert_data_for_db['qmn_array_for_variables']['user_ip'] = '127.0.0.1';
        $insert_data_for_db['qmn_array_for_variables']['time_taken'] = '00:01:00';


        self::$qmn_quiz_manager->add_quiz_results($insert_data_for_db);
        $result_id = $wpdb->insert_id;

        $this->assertGreaterThan(0, $result_id, "Failed to insert quiz results.");

        $saved_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id), ARRAY_A);

        $this->assertEquals(0, $saved_result['point_score'], 'DB point_score should be 0 for "none" grading.');
        $this->assertEquals(0, $saved_result['correct_score'], 'DB correct_score should be 0 for "none" grading.');
        $this->assertEquals(0, $saved_result['correct'], 'DB correct count should be 0 for "none" grading.');
    }

    public function test_correct_incorrect_grading_system()
    {
        global $wpdb;
        global $mlwQuizMasterNext;

        // For 'correct_incorrect', points assigned to answers are typically 0 or 1 (for correctness)
        // but the system primarily counts correct answers for the score.
        $quiz_id = $this->create_quiz_with_grading_system('Correct/Incorrect Test Quiz', 'correct_incorrect');
        $q1_id = $this->create_question($quiz_id, [
            'answer_array' => serialize([
                ['Answer A (Correct)', 1, 1],
                ['Answer B (Incorrect)', 0, 0],
            ]),
            'correct_answer' => 1,
        ]);
        $q2_id = $this->create_question($quiz_id, [
            'question_name' => 'Another CI Question',
            'answer_array' => serialize([
                ['Answer C (Incorrect)', 0, 0],
                ['Answer D (Correct)', 1, 1],
            ]),
            'correct_answer' => 2,
        ]);
        $q3_id = $this->create_question($quiz_id, [
            'question_name' => 'Third CI Question',
            'answer_array' => serialize([
                ['Answer E (Correct)', 1, 1],
                ['Answer F (Incorrect)', 0, 0],
            ]),
            'correct_answer' => 1,
        ]);


        // User answers: Q1 Correct, Q2 Correct, Q3 Incorrect
        $user_answers = [
            $q1_id => ['user_answer' => '0'], // Correct
            $q2_id => ['user_answer' => '1'], // Correct
            $q3_id => ['user_answer' => '1'], // Incorrect
        ];
        $this->simulate_quiz_submission_data($quiz_id, $user_answers);

        $qsm_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
        $qsm_quiz_data_for_check = ['quiz_id' => $quiz_id, 'user_id' => 1, 'hidden_questions' => []];

        $checked_answers_result = QMNQuizManager::check_answers($qsm_quiz_options, $qsm_quiz_data_for_check);

        // In "correct_incorrect" mode, points_earned might reflect the sum of points (if defined as 1 for correct)
        // or could be simply the count of correct answers. The code uses 'points' from display_review.
        // If answers have 1 point for correct, 0 for incorrect:
        $this->assertEquals(2, $checked_answers_result['total_points'], 'Total points should be 2.');
        $this->assertEquals(2, $checked_answers_result['total_correct'], 'Total correct should be 2.');
        // Total score is percentage: (2 correct / 3 questions) * 100
        $expected_score = round((2 / 3) * 100, 2);
        $this->assertEquals($expected_score, $checked_answers_result['total_score'], 'Total score percentage is incorrect.');

        $this->assertEquals('correct', $checked_answers_result['question_answers_array'][0]['correct']);
        $this->assertEquals(1, $checked_answers_result['question_answers_array'][0]['points']);
        $this->assertEquals('correct', $checked_answers_result['question_answers_array'][1]['correct']);
        $this->assertEquals(1, $checked_answers_result['question_answers_array'][1]['points']);
        $this->assertEquals('incorrect', $checked_answers_result['question_answers_array'][2]['correct']);
        $this->assertEquals(0, $checked_answers_result['question_answers_array'][2]['points']);

        // Simulate storing results
        $results_array_for_db = [0, $checked_answers_result['question_answers_array'], '', ['contact'=>[]],0,[],[],0,0,0,''];
        $insert_data_for_db = [
            'qmn_array_for_variables' => array_merge($qsm_quiz_data_for_check, $checked_answers_result),
            'results_array'           => $results_array_for_db,
            'unique_id'               => uniqid(), 'form_type' => 0, 'http_referer' => '', 'page_name' => '',
        ];
        $insert_data_for_db['qmn_array_for_variables']['quiz_id'] = $quiz_id; // Ensure quiz_id is in here
        $insert_data_for_db['qmn_array_for_variables']['quiz_name'] = 'Correct/Incorrect Test Quiz';
        $insert_data_for_db['qmn_array_for_variables']['quiz_system'] = $qsm_quiz_options->system;


        self::$qmn_quiz_manager->add_quiz_results($insert_data_for_db);
        $result_id = $wpdb->insert_id;
        $saved_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id), ARRAY_A);

        $this->assertEquals(2, $saved_result['point_score'], 'DB point_score should be 2.');
        $this->assertEquals($expected_score, $saved_result['correct_score'], 'DB correct_score percentage is incorrect.');
        $this->assertEquals(2, $saved_result['correct'], 'DB correct count should be 2.');
    }

    public function test_points_grading_system()
    {
        global $wpdb;
        global $mlwQuizMasterNext;

        $quiz_id = $this->create_quiz_with_grading_system('Points Test Quiz', 'points');
        $q1_id = $this->create_question($quiz_id, [
            'answer_array' => serialize([
                ['Answer A', 10, 1], // Points: 10, Correct flag: 1
                ['Answer B', 5, 0],  // Points: 5, Correct flag: 0
                ['Answer C', 0, 0],   // Points: 0, Correct flag: 0
            ]),
            // 'correct_answer' is less relevant here if points are the primary factor,
            // but QSM might still use it for the 'total_correct' count.
            // Let's assume an answer with points > 0 is 'correct' for counting purposes.
        ]);
        $q2_id = $this->create_question($quiz_id, [
            'question_name' => 'Another Points Question',
            'answer_array' => serialize([
                ['Answer D', 2, 0],
                ['Answer E', 7, 1],
            ]),
        ]);

        // User answers: Q1 -> Answer A (10 points), Q2 -> Answer E (7 points)
        $user_answers = [
            $q1_id => ['user_answer' => '0'], // 10 points
            $q2_id => ['user_answer' => '1'], // 7 points
        ];
        $this->simulate_quiz_submission_data($quiz_id, $user_answers);

        $qsm_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
        // Manually set the global quiz system to points for this test, as check_answers uses it for qsm_max_min_points
        $original_system = $qsm_quiz_options->system;
        $qsm_quiz_options->system = 1; // 1 for Points

        $qsm_quiz_data_for_check = ['quiz_id' => $quiz_id, 'user_id' => 1, 'hidden_questions' => []];
        $checked_answers_result = QMNQuizManager::check_answers($qsm_quiz_options, $qsm_quiz_data_for_check);

        $this->assertEquals(17, $checked_answers_result['total_points'], 'Total points should be 17.');

        // total_correct might be 2 if answers with points > 0 are counted as correct.
        // Or it might be based on the explicit 'correct' flag in answer_array.
        // Based on current check_answers, it sums up 'correct' flags.
        $this->assertEquals(2, $checked_answers_result['total_correct'], 'Total correct should be 2 (based on correct flags).');

        // Total score (percentage) = (points_earned / total_possible_points) * 100
        // Max points for Q1 = 10, Max for Q2 = 7. Total possible = 17
        $expected_score = round((17 / (10 + 7)) * 100, 2);
        $this->assertEquals($expected_score, $checked_answers_result['total_score'], 'Total score percentage is incorrect.');

        $this->assertEquals(10, $checked_answers_result['question_answers_array'][0]['points']);
        $this->assertEquals(7, $checked_answers_result['question_answers_array'][1]['points']);
        // Correct status depends on how QSM handles it for "points" mode. Usually 'correct' if points > 0 or tied to the flag.
        $this->assertEquals('correct', $checked_answers_result['question_answers_array'][0]['correct']); // Based on flag
        $this->assertEquals('correct', $checked_answers_result['question_answers_array'][1]['correct']); // Based on flag


        // Simulate storing results
        $results_array_for_db = [0, $checked_answers_result['question_answers_array'], '', ['contact'=>[]],0,[],[],0,0,0,''];
        $insert_data_for_db = [
            'qmn_array_for_variables' => array_merge($qsm_quiz_data_for_check, $checked_answers_result),
            'results_array'           => $results_array_for_db,
            'unique_id'               => uniqid(), 'form_type' => 0, 'http_referer' => '', 'page_name' => '',
        ];
        $insert_data_for_db['qmn_array_for_variables']['quiz_id'] = $quiz_id;
        $insert_data_for_db['qmn_array_for_variables']['quiz_name'] = 'Points Test Quiz';
        $insert_data_for_db['qmn_array_for_variables']['quiz_system'] = $qsm_quiz_options->system;


        self::$qmn_quiz_manager->add_quiz_results($insert_data_for_db);
        $result_id = $wpdb->insert_id;
        $saved_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id), ARRAY_A);

        $this->assertEquals(17, $saved_result['point_score'], 'DB point_score should be 17.');
        $this->assertEquals($expected_score, $saved_result['correct_score'], 'DB correct_score percentage is incorrect.');
        $this->assertEquals(2, $saved_result['correct'], 'DB correct count should be 2.');

        // Restore original global system setting
        $qsm_quiz_options->system = $original_system;
    }

    public function test_default_grading_system()
    {
        global $wpdb;
        global $mlwQuizMasterNext;

        // Create quiz without specifying grading_system, should default to 'correct_incorrect'
        $quiz_id = $this->create_quiz_with_grading_system('Default Grading Test Quiz', null);

        $q1_id = $this->create_question($quiz_id, [
            'answer_array' => serialize([
                ['Answer A (Correct)', 1, 1],
                ['Answer B (Incorrect)', 0, 0],
            ]),
            'correct_answer' => 1,
        ]);
        $q2_id = $this->create_question($quiz_id, [
            'question_name' => 'Another Default Question',
            'answer_array' => serialize([
                ['Answer C (Incorrect)', 0, 0],
                ['Answer D (Correct)', 1, 1],
            ]),
            'correct_answer' => 2,
        ]);

        // User answers: Q1 Correct, Q2 Incorrect
        $user_answers = [
            $q1_id => ['user_answer' => '0'], // Correct
            $q2_id => ['user_answer' => '0'], // Incorrect
        ];
        $this->simulate_quiz_submission_data($quiz_id, $user_answers);

        $qsm_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
        // The 'grading_system' should be 'correct_incorrect' by default from get_default_quiz_options()
        // or from QSM_Quiz_Settings::load_settings() applying defaults.
        $this->assertEquals('correct_incorrect', $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'grading_system', 'fallback_if_not_set' ));

        $qsm_quiz_data_for_check = ['quiz_id' => $quiz_id, 'user_id' => 1, 'hidden_questions' => []];
        $checked_answers_result = QMNQuizManager::check_answers($qsm_quiz_options, $qsm_quiz_data_for_check);

        $this->assertEquals(1, $checked_answers_result['total_points'], 'Total points should be 1 for default (correct_incorrect).');
        $this->assertEquals(1, $checked_answers_result['total_correct'], 'Total correct should be 1.');
        $expected_score = round((1 / 2) * 100, 2);
        $this->assertEquals($expected_score, $checked_answers_result['total_score'], 'Total score percentage is incorrect for default.');

        $this->assertEquals('correct', $checked_answers_result['question_answers_array'][0]['correct']);
        $this->assertEquals('incorrect', $checked_answers_result['question_answers_array'][1]['correct']);

        // Simulate storing results
        $results_array_for_db = [0, $checked_answers_result['question_answers_array'], '', ['contact'=>[]],0,[],[],0,0,0,''];
        $insert_data_for_db = [
            'qmn_array_for_variables' => array_merge($qsm_quiz_data_for_check, $checked_answers_result),
            'results_array'           => $results_array_for_db,
            'unique_id'               => uniqid(), 'form_type' => 0, 'http_referer' => '', 'page_name' => '',
        ];
        $insert_data_for_db['qmn_array_for_variables']['quiz_id'] = $quiz_id;
        $insert_data_for_db['qmn_array_for_variables']['quiz_name'] = 'Default Grading Test Quiz';
        $insert_data_for_db['qmn_array_for_variables']['quiz_system'] = $qsm_quiz_options->system;


        self::$qmn_quiz_manager->add_quiz_results($insert_data_for_db);
        $result_id = $wpdb->insert_id;
        $saved_result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id), ARRAY_A);

        $this->assertEquals(1, $saved_result['point_score'], 'DB point_score incorrect for default.');
        $this->assertEquals($expected_score, $saved_result['correct_score'], 'DB correct_score incorrect for default.');
        $this->assertEquals(1, $saved_result['correct'], 'DB correct count incorrect for default.');
    }
}
