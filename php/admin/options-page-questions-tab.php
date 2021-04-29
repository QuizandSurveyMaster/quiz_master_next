<?php
/**
 * This file handles the "Questions" tab when editing a quiz/survey
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Adds the settings for questions tab to the Quiz Settings page.
 *
 * @return void
 * @since 4.4.0 
 */
function qsm_settings_questions_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Questions', 'quiz-master-next' ), 'qsm_options_questions_tab_content' );
}
add_action( 'plugins_loaded', 'qsm_settings_questions_tab', 5 );


/**
 * Adds the content for the options for questions tab.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_options_questions_tab_content() {

	global $wpdb;
	global $mlwQuizMasterNext;
	$question_categories = $wpdb->get_results( "SELECT DISTINCT category FROM {$wpdb->prefix}mlw_questions", 'ARRAY_A' );
	$quiz_id = intval( $_GET['quiz_id'] );
	$user_id = get_current_user_id();  
	$form_type = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'form_type' );
	$quiz_system = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'system' );
	$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('pages', array());
	$db_qpages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting('qpages', array());
	$qpages = array();
	if (!empty($pages)) {
		$defaultQPage = array('id' => 1, 'quizID' => $quiz_id, 'pagekey' => '', 'hide_prevbtn' => 0, 'questions' => array());
		foreach ($pages as $k => $val) {
			$qpage = isset($db_qpages[$k]) ? $db_qpages[$k] : $defaultQPage;
			$qpage['id'] = $k + 1;
			$qpage['pagekey'] = (isset($qpage['pagekey']) && !empty($qpage['pagekey'])) ? $qpage['pagekey'] : uniqid();
			$qpage['hide_prevbtn'] = (isset($qpage['hide_prevbtn']) && !empty($qpage['hide_prevbtn'])) ? $qpage['hide_prevbtn'] : 0;
			$qpage['questions'] = $val;
			$qpages[] = $qpage;
		}
	} else {
            $defaultQPage = array('id' => 1, 'quizID' => $quiz_id, 'pagekey' => uniqid(), 'hide_prevbtn' => 0, 'questions' => array());		
            $qpages[] = $defaultQPage;
        }
	$qpages = apply_filters('qsm_filter_quiz_page_attributes', $qpages, $pages);
	$json_data = array(
		'quizID'     => $quiz_id,
		'answerText' => __( 'Answer', 'quiz-master-next' ),
		'nonce'      => wp_create_nonce( 'wp_rest' ),
		'pages'      => $pages,
		'qpages' => $qpages,
		'qsm_user_ve' => get_user_meta($user_id, 'rich_editing', true),
		'saveNonce' => wp_create_nonce('ajax-nonce-sandy-page'),
		'categories' => $question_categories,
		'form_type' => $form_type,
		'quiz_system' => $quiz_system,
                'hide_desc_text' => __('Less Description', 'quiz-master-next'),
                'show_desc_text' => __('Add Description', 'quiz-master-next'),
                'show_correct_info_text' => __('Add Correct Answer Info', 'quiz-master-next'),
                'question_bank_nonce' => wp_create_nonce("delete_question_question_bank_nonce"),
				'single_question_nonce' => wp_create_nonce("delete_question_from_database")
	);

	// Scripts and styles.
	wp_enqueue_script( 'micromodal_script', plugins_url( '../../js/micromodal.min.js', __FILE__ ) );
	wp_enqueue_script( 'qsm_admin_question_js', plugins_url( '../../js/qsm-admin-question.js', __FILE__ ), array( 'backbone', 'underscore', 'jquery-ui-sortable', 'wp-util', 'micromodal_script', 'qmn_admin_js' ), $mlwQuizMasterNext->version, true );
	wp_localize_script( 'qsm_admin_question_js', 'qsmQuestionSettings', $json_data );
	wp_enqueue_style( 'qsm_admin_question_css', plugins_url( '../../css/qsm-admin-question.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_enqueue_script( 'math_jax', '//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML' );
	wp_enqueue_editor();
	wp_enqueue_media();

	// Load Question Types.
	$question_types = $mlwQuizMasterNext->pluginHelper->get_question_type_options();

	// Display warning if using competing options.
	$pagination = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'pagination' );
	if ( 0 != $pagination ) {
		?>
		<div class="notice notice-warning">
			<p><?php _e('This quiz has the "How many questions per page would you like?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to 0.', 'quiz-master-next'); ?></p>
		</div>
		<?php
	}
	$from_total = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'question_from_total' );
	if ( 0 != $from_total ) {
		?>
		<div class="notice notice-warning">
			<p><?php _e('This quiz has the "How many questions should be loaded for quiz?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to 0.', 'quiz-master-next'); ?></p>
		</div>
		<?php
	}
	$randomness = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_options', 'randomness_order' );
	if ( 0 != $randomness ) {
		?>
		<div class="notice notice-warning">
			<p><?php _e('This quiz has the "Are the questions random?" option enabled. The pages below will not be used while that option is enabled. To turn off, go to the "Options" tab and set that option to "No".', 'quiz-master-next'); ?></p>
		</div>
		<?php
	}
	?>
        <h3 style="display: none;">Questions</h3>
        <p style="text-align: right;"><a href="https://quizandsurveymaster.com/docs/v7/questions-tab/" target="_blank">View Documentation</a></p>        
	<div class="question-controls">
            <span><b><?php _e('Total Questions:', 'quiz-master-next'); ?></b> <span id="total-questions"></span></span>
            <p class="search-box">
                    <label class="screen-reader-text" for="question_search">Search Questions:</label>
                    <input type="search" id="question_search" name="question_search" value="">
                    <a href="#" class="button"><?php esc_html_e('Search Questions', 'quiz-master-next'); ?></a>
            </p>
	</div>
        <div class="questions quiz_form_type_<?php echo $form_type; ?> quiz_quiz_systen_<?php echo $quiz_system; ?>"><div class="qsm-showing-loader" style="text-align: center;margin-bottom: 20px;"><div class="qsm-spinner-loader"></div></div></div>
        <div class="question-create-page">
            <div>
                    <button class="new-page-button button button-primary"><span class="dashicons dashicons-plus-alt2"></span> <?php _e('Create New Page', 'quiz-master-next'); ?></button>
                    <button style="display: none;" class="save-page-button button button-primary"><?php _e('Save Questions and Pages', 'quiz-master-next'); ?></button>
                    <span class="spinner" id="save-edit-quiz-pages" style="float: none;"></span>
            </div>
        </div>
	<!-- Popup for question bank -->
	<div class="qsm-popup qsm-popup-slide qsm-popup-bank" id="modal-2" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-2-title"><?php _e('Add Question From Question Bank', 'quiz-master-next'); ?></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-2-content">
					<input type="hidden" name="add-question-bank-page" id="add-question-bank-page" value="">
					<div id="question-bank"></div>
				</main>
				<footer class="qsm-popup__footer">
					<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window">Close</button>
				</footer>
			</div>
		</div>
	</div>


	<!-- Popup for editing question -->
	<div class="qsm-popup qsm-popup-slide" id="modal-1" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
                                    <h2 class="qsm-popup__title" id="modal-1-title"><?php _e('Edit Question', 'quiz-master-next'); ?> [ ID: <span id="edit-question-id"></span>  ]</h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-1-content">
                                    <input type="hidden" name="edit_question_id" id="edit_question_id" value="">
                                    <div id="poststuff">
                                        <div id="post-body" class="metabox-holder columns-2">
                                            <div id="post-body-content" style="position: relative;">
                                                <div class="qsm-row">
                                                    <input type="text" id="question_title" class="question-title" name="question-title" value="" placeholder="<?php _e('Type your question here','quiz-master-next'); ?>">
                                                </div>
                                                <a href="#" class="qsm-show-question-desc-box button button-default"><span class="dashicons dashicons-plus-alt2"></span> <?php _e('Add Description', 'quiz-master-next'); ?></a>
                                                <div class="qsm-row" style="display: none;">
                                                    <textarea placeholder="<?php _e('Add your description here', 'quiz-master-next'); ?>" id="question-text"></textarea>
                                                </div>
                                                <hr/>
                                                <div class="qsm-row" style="margin-bottom: 0;">
                                                    <?php
                                                    $description_arr = array(
                                                        array(
                                                            'question_type_id' => 11,
                                                            'description' => __('For this question type, users will see a file upload field on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '14',
                                                            'description' => __('Use %BLANK% variable in the description field to display input boxes.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '12',
                                                            'description' => __('For this question type, users will see a date input field on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '3',
                                                            'description' => __('For this question type, users will see a standard input box on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '5',
                                                            'description' => __('For this question type, users will see a standard textarea input box on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '6',
                                                            'description' => __('Displays a simple section on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '7',
                                                            'description' => __('For this question type, users will see an input box which accepts only number values on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '8',
                                                            'description' => __("For this question type, users will see a checkbox on front end. The text in description field will act like it's label.", 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '9',
                                                            'description' => __('For this question type, users will see a Captcha field on front end.', 'quiz-master-next')
                                                        ),
                                                        array(
                                                            'question_type_id' => '13',
                                                            'description' => __('Use points based grading system for Polar questions.', 'quiz-master-next')
                                                        )
                                                    );
                                                    $description_arr = apply_filters('qsm_question_type_description', $description_arr);
                                                    if( $description_arr ){
                                                        foreach ( $description_arr as $value ) { 
                                                            $question_type_id = $value['question_type_id'];
                                                            ?>
                                                            <p id="question_type_<?php echo $question_type_id; ?>_description" class="question-type-description"><?php echo $value['description']; ?></p>
                                                        <?php                                                        
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <?php
                                                $polar_class = $polar_question_use = '';
                                                if( $form_type == 0 && ($quiz_system == 1 || $quiz_system == 3) ){
                                                    $polar_class = 'qsm_show_question_type_13';
                                                    $polar_question_use = ',13';
                                                }
                                                ?>
                                                <div id="qsm_optoins_wrapper" class="qsm-row qsm_hide_for_other qsm_show_question_type_0 qsm_show_question_type_1 qsm_show_question_type_2 qsm_show_question_type_3 qsm_show_question_type_4 qsm_show_question_type_5 qsm_show_question_type_7 qsm_show_question_type_10 qsm_show_question_type_12 qsm_show_question_type_14 <?php echo $polar_class; ?>">
                                                    <label class="answer-header">
                                                        <?php _e( 'Answers', 'quiz-master-next' ); ?>
                                                        <a class="qsm-question-doc" href="https://quizandsurveymaster.com/docs/v7/questions-tab/#Answers" target="_blank" title="View Documentation">
                                                            <span class="dashicons dashicons-media-document"></span>
                                                        </a>
                                                    </label>
                                                    <div class="correct-header"><?php _e( 'Correct', 'quiz-master-next' ); ?></div>
                                                    <div class="answers" id="answers">

                                                    </div>
                                                    <div class="new-answer-button">
                                                            <a href="#" class="button" id="new-answer-button"><span class="dashicons dashicons-plus"></span> <?php _e( 'Add New Answer!', 'quiz-master-next'); ?></a>
                                                    </div>                                                    
                                                </div>
                                                <hr style="margin-bottom:25px;">
                                                <a href="#" class="qsm-show-correct-info-box button button-default"><span class="dashicons dashicons-plus-alt2"></span> <?php _e('Add Correct Answer Info', 'quiz-master-next'); ?></a>
                                                <div class="qsm-row" style="display: none;">
                                                <?php
                                                $answer_area_option = array(
                                                    'correct_answer_info' => array(
                                                        'label' => __( 'Correct Answer Info', 'quiz-master-next' ),
                                                        'type' => 'textarea',                                                        
                                                        'default' => '',
                                                        'show' => '0,1,2,3,4,5,7,10,12,14' . $polar_question_use,
                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/questions-tab/#Correct-Answer-Info'
                                                    )                                
                                                );
                                                $answer_area_option = apply_filters('qsm_question_advanced_option', $answer_area_option);
                                                foreach($answer_area_option as $qo_key => $single_answer_option){
                                                    echo qsm_display_question_option($qo_key, $single_answer_option);
                                                }
                                                ?>
                                                </div>
												<?php do_action('qsm_question_form_fields', $quiz_id);?>
                                            </div>
                                            <div id="postbox-container-1" class="postbox-container">
                                                <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
                                                    <div id="submitdiv" class="postbox ">                                                        
                                                        <h2 class="hndle ui-sortable-handle">
                                                            <span><?php _e( 'Publish', 'quiz-master-next' ); ?></span>
                                                            <span id="qsm-question-id"></span>
                                                        </h2>
                                                        <div class="inside">
                                                            <div class="submitbox" id="submitpost">
                                                                <div id="minor-publishing">                                                                            
                                                                    <div class="qsm-row">
                                                                            <label>
                                                                                <?php _e( 'Question Type', 'quiz-master-next' ); ?>
                                                                                <?php
                                                                                $document_text = '';
                                                                                $document_text .= '<a class="qsm-question-doc" href="https://quizandsurveymaster.com/docs/v7/questions-tab/#Question-Type" target="_blank" title="'. __('View Documentation', 'quiz-master-next') .'">';
                                                                                $document_text .= '<span class="dashicons dashicons-media-document"></span>';
                                                                                $document_text .= '</a>';
                                                                                echo $document_text;
                                                                                ?>
                                                                            </label>
                                                                            <select name="question_type" id="question_type">
                                                                                    <?php
                                                                                    foreach ( $question_types as $type ) {
                                                                                            echo "<option value='{$type['slug']}'>{$type['name']}</option>";
                                                                                    }
                                                                                    ?>
                                                                            </select>
                                                                            <a class="question_info_tag hidden" target="_blank" href="https://quizandsurveymaster.com/docs/about-quiz-survey-master/question-types/"><?php _e('How to use this option?','quiz-master-next') ?></a>
                                                                            <p class="hidden" id="question_type_info"></p>
                                                                    </div>
                                                                    <?php
                                                                    $simple_question_option = array(
                                                                        'change-answer-editor' => array(
                                                                            'label' => __( 'Answers Type', 'quiz-master-next' ),
                                                                            'type' => 'select',
                                                                            'priority' => '1',
                                                                            'options' => array(
                                                                                'text' => __( 'Text Answers', 'quiz-master-next' ),
                                                                                'rich' => __( 'Rich Answers', 'quiz-master-next' ),                                                                            
                                                                            ),
                                                                            'default' => 'text',
                                                                            'show' => '0,1,2,4,13',
                                                                            //'tooltip' => __('You can use text and rich answer for question answers.', 'quiz-master-next'),.
                                                                            'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/questions-tab/#Answer-Type'
                                                                        ),
                                                                        'required' => array(
                                                                            'label' => __( 'Required?', 'quiz-master-next' ),
                                                                            'type' => 'single_checkbox',
                                                                            'priority' => '2',
                                                                            'options' => array(
                                                                                //'1' => __( 'No', 'quiz-master-next' ),
                                                                                '0' => __( 'Yes', 'quiz-master-next' )
                                                                            ),
                                                                            'default' => '0'
                                                                        ),
                                                                    );
                                                                    $simple_question_option = apply_filters('qsm_question_format_option', $simple_question_option);
                                                                    $keys = array_column($simple_question_option, 'priority');
                                                                    array_multisort($keys, SORT_ASC, $simple_question_option);
                                                                    foreach($simple_question_option as $qo_key => $single_option){
                                                                        echo qsm_display_question_option($qo_key, $single_option);
                                                                    }
                                                                    ?>                                                                    
                                                                </div>
                                                                <div id="major-publishing-actions">
                                                                    <div id="delete-action">
                                                                        <a class="submitdelete deletion" data-micromodal-close aria-label="Close this">Cancel</a>
                                                                    </div>
                                                                    <div id="publishing-action">
                                                                        <span class="spinner" id="save-edit-question-spinner" style="float: none;"></span>
                                                                        <button id="save-popup-button" class="button button-primary">Save Question</button>
                                                                    </div>                                                                        
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="categorydiv" class="postbox">
                                                        <h2 class="hndle ui-sortable-handle">
                                                            <span><?php _e('Select Category', 'quiz-master-next'); ?></span>
                                                            <a class="qsm-question-doc" href="https://quizandsurveymaster.com/docs/v7/questions-tab/#Category" target="_blank" title="View Documentation"><span class="dashicons dashicons-media-document"></span></a>
                                                        </h2>
                                                        <div class="inside">
                                                            <?php
                                                            $category_question_option = array(
                                                                'categories' => array(
                                                                    'label' => __( '', 'quiz-master-next' ),
                                                                    'type' => 'category',
                                                                    'priority' => '5',                                                    
                                                                    'default' => '',
                                                                    'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/questions-tab/#Category'
                                                                )
                                                            );
                                                            $category_question_option = apply_filters('qsm_question_category_option', $category_question_option);
                                                            $keys = array_column($category_question_option, 'priority');
                                                            array_multisort($keys, SORT_ASC, $category_question_option);
                                                            foreach($category_question_option as $qo_key => $single_cat_option){
                                                                echo qsm_display_question_option($qo_key, $single_cat_option);
                                                            }
                                                           ?>                                                            
                                                        </div>
                                                    </div>
                                                    <div id="advanceddiv" class="postbox">
                                                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Advanced Option', 'quiz-master-next'); ?></span></h2>
                                                        <div class="inside">
                                                            <div class="advanced-content">
                                                                <?php
                                                                $advanced_question_option = array(
                                                                    'comments' => array(
                                                                        'label' => __( 'Comment Field', 'quiz-master-next' ),
                                                                        'type' => 'select',
                                                                        'priority' => '3',
                                                                        'options' => array(
                                                                            '0' => __( 'Small Text Field', 'quiz-master-next' ),
                                                                            '2' => __( 'Large Text Field', 'quiz-master-next' ),
                                                                            '1' => __( 'None', 'quiz-master-next' )
                                                                        ),
                                                                        'default' => '1',
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/advanced-options/#Comment-Field'
                                                                    ),
                                                                    'hint' => array(
                                                                        'label' => __( 'Hint', 'quiz-master-next' ),
                                                                        'type' => 'text',
                                                                        'default' => '',
                                                                        'priority' => '4',
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/questions-tab/#Hints'
                                                                    ),
                                                                    'autofill' => array(
                                                                        'label' => __( 'Hide Autofill?', 'quiz-master-next' ),
                                                                        'type' => 'select',
                                                                        'priority' => '6',
                                                                        'options' => array(
                                                                            '0' => __( 'No', 'quiz-master-next' ),                                                        
                                                                            '1' => __( 'Yes', 'quiz-master-next' )
                                                                        ),
                                                                        'default' => '0',
                                                                        'show' => '3, 14',
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/advanced-options/#Hide-Autofill'
                                                                    ),
                                                                    'limit_text' => array(
                                                                        'label' => __('Limit Text', 'quiz-master-next' ),
                                                                        'type' => 'text',
                                                                        'priority' => '7',                                                    
                                                                        'default' => '',
                                                                        'show' => '3, 5, 7, 14',
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/advanced-options/#Limit-Text'
                                                                    ),
                                                                    'limit_multiple_response' => array(
                                                                        'label' => __('Limit Multiple choice', 'quiz-master-next' ),
                                                                        'type' => 'text',
                                                                        'priority' => '8',
                                                                        'default' => '',
                                                                        'show' => '4,10',                                                                        
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/advanced-options/#Limit-Multiple-Choice'
                                                                    ),                                                
                                                                    'file_upload_type' => array(
                                                                        'label' => __('Allow File type', 'quiz-master-next' ),
                                                                        'type' => 'multi_checkbox',
                                                                        'priority' => '10',
                                                                        'options' => array(
                                                                            'text/plain' => __( 'Text File', 'quiz-master-next' ),
                                                                            'image' => __( 'Image', 'quiz-master-next' ),
                                                                            'application/pdf' => __( 'PDF File', 'quiz-master-next' ),
                                                                            'doc' => __( 'Doc File', 'quiz-master-next' ),
                                                                            'excel' => __( 'Excel File', 'quiz-master-next' ),
                                                                            'video/mp4' => __( 'Video', 'quiz-master-next' ),
                                                                        ),
                                                                        'default' => 'image',
                                                                        'show' => '11',
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/advanced-options/#Allow-File-Type'
                                                                    ),
                                                                    'file_upload_limit' => array(
                                                                        'label' => __('File upload limit ( in MB )', 'quiz-master-next' ),
                                                                        'type' => 'number',
                                                                        'priority' => '9',
                                                                        'default' => '',
                                                                        'show' => '11',
                                                                        'documentation_link' => 'https://quizandsurveymaster.com/docs/v7/advanced-options/#File-Upload-Limit'
                                                                    ),
                                                                );
                                                                $advanced_question_option = apply_filters('qsm_question_advanced_option', $advanced_question_option);
                                                                $keys = array_column($advanced_question_option, 'priority');
                                                                array_multisort($keys, SORT_ASC, $advanced_question_option);
                                                                foreach($advanced_question_option as $qo_key => $single_option){
                                                                    echo qsm_display_question_option($qo_key, $single_option);
                                                                }
                                                                ?> 
                                                            </div>
                                                        </div>
                                                    </div>
													<?php do_action('qsm_question_form_fields_side', $quiz_id);?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>					
				</main>				
			</div>
		</div>
	</div>

	<!--Views-->
	
	<!-- Popup for question bank -->
	<div class="qsm-popup qsm-popup-slide qsm-popup-bank" id="modal-page-1" aria-hidden="true">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-1-title">
				<header class="qsm-popup__header">
					<h2 class="qsm-popup__title" id="modal-1-title"><?php _e('Edit Page', 'quiz-master-next'); ?> <span style="display: none;">[ ID: <span id="edit-page-id"></span>  ]</span></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-page-1-content">
					<input type="hidden" name="edit_page_id" id="edit_page_id" value="">
					<div id="page-options">
						<div class="qsm-row">
							<label><?php _e('Page Name', 'quiz-master-next'); ?></label>
							<input type="text" id="pagekey" name="pagekey" value="">
						</div>
						<div class="qsm-row">
							<label><?php _e('Hide Previous Button?', 'quiz-master-next'); ?></label>
							<select name="hide_prevbtn" id="hide_prevbtn">
								<option value="0" selected="selected"><?php _e( 'No', 'quiz-master-next' ); ?></option>
								<option value="1"><?php _e( 'Yes', 'quiz-master-next' ); ?></option>
							</select>
						</div>
						<?php do_action('qsm_action_quiz_page_attributes_fields');?>
					</div>
				</main>
				<footer class="qsm-popup__footer">
					<button id="save-page-popup-button" class="qsm-popup__btn qsm-popup__btn-primary">Save Page</button>
					<button class="qsm-popup__btn" data-micromodal-close aria-label="Close this dialog window">Close</button>
				</footer>
			</div>
		</div>
	</div>

	<!-- View for Page -->
	<script type="text/template" id="tmpl-page">
		<div class="page page-new" data-page-id="{{data.id }}">
			<div class="page-header">
				<div><span class="dashicons dashicons-move"></span> <a href="#" class="edit-page-button" title="Edit Page"><span class="dashicons dashicons-admin-generic"></span></a> <span class="page-number"></span></div>
				<div><a href="#" class="delete-page-button" title="Delete Page"><span class="dashicons dashicons-trash"></span></a></div>
			</div>
			<div class="page-footer">
				<div class="page-header-buttons">
					<a href="#" class="new-question-button button"><span class="dashicons dashicons-plus"></span> <?php _e('Create New Question', 'quiz-master-next'); ?></a>
					<a href="#" class="add-question-bank-button button"><span class="dashicons dashicons-plus"></span> <?php _e('Add Question From Question Bank', 'quiz-master-next'); ?></a>
				</div>
			</div>
		</div>
	</script>
        	
	<!-- View for Question -->
	<script type="text/template" id="tmpl-question">
		<div class="question question-new" data-question-id="{{data.id }}">
			<div class="question-content">
				<div><span class="dashicons dashicons-move"></span></div>
				<div><a href="#" title="Edit Question" class="edit-question-button"><span class="dashicons dashicons-edit"></span></a></div>                                
				<div><a href="#" title="Clone Question" class="duplicate-question-button"><span class="dashicons dashicons-admin-page"></span></a></div>
				<div><a href="#" title="Delete Question" class="delete-question-button" data-question-iid="{{data.id }}"><span class="dashicons dashicons-trash"></span></a></div>
				<div class="question-content-text">{{{data.question}}}</div>
				<div class="question-category"><# if ( 0 !== data.category.length ) { #> <?php _e('Category:', 'quiz-master-next'); ?> {{data.category}} <# } #></div>				
			</div>
		</div>
	</script>

	<!-- View for question in question bank -->
	<script type="text/template" id="tmpl-single-question-bank-question">
		<div class="question-bank-question" data-question-id="{{data.id}}" data-category-name="{{data.category}}">
			<div class="question-bank-selection">
				<input type="checkbox" name="qsm-question-checkbox[]" class="qsm-question-checkbox" />
			</div>
			<div><p>{{{data.question}}}</p><p style="font-size: 12px;color: gray;font-style: italic;"><b>Quiz Name:</b> {{data.quiz_name}}    <# if ( data.category != '' ) { #> <b>Category:</b> {{data.category}} <# } #></p></div>
			<div><a href="#" class="import-button button"><?php _e('Add Question', 'quiz-master-next'); ?></a></div>			
		</div>
	</script>

	<!-- View for single category -->
	<script type="text/template" id="tmpl-single-category">
		<div class="category">
			<label><input type="radio" name="category" class="category-radio" value="{{data.category}}">{{data.category}}</label>
		</div>
	</script>

	<!-- View for single answer -->
	<script type="text/template" id="tmpl-single-answer">
		<div class="answers-single">
			<div><a href="#" class="delete-answer-button"><span class="dashicons dashicons-trash"></span></a></div>
			<div class="answer-text-div">
				<# if ( 'rich' == data.answerType ) { #>
					<textarea id="answer-{{data.question_id}}-{{data.count}}"></textarea>
				<# } else { #>
					<input type="text" class="answer-text" value="{{data.answer}}" placeholder="Your answer"/>
				<# } #>
			</div>
			<# if ( 0 == data.form_type ) { #>
				<# if ( 1 == data.quiz_system || 3 == data.quiz_system ) { #>
					<div><input type="text" class="answer-points" value="{{data.points}}" placeholder="Points"/></div>
				<# } #>
				<# if ( 0 == data.quiz_system || 3 == data.quiz_system ) { #>
					<div><label class="correct-answer"><input type="checkbox" class="answer-correct" value="1" <# if ( 1 == data.correct ) { #> checked="checked" <# } #>/> <?php _e('Correct', 'quiz-master-next'); ?></label></div>
				<# } #>    
			<# } #>
			<?php do_action('qsm_admin_single_answer_option_fields');?>
		</div>
	</script>
	<?php

    ?>
    <div class="qsm-popup qsm-popup-slide" id="modal-7" aria-hidden="false">
        <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close="">
            <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-7-title">
                <header class="qsm-popup__header">
                    <h3 class="qsm-popup__title" id="modal-7-title"><?php _e('Delete Options', 'quiz-master-next'); ?></h3>
                    <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close=""></a>
                </header>
                <hr/>
                <main class="qsm-popup__content" id="modal-7-content">
                    <form action='' method='post' id="delete-question-form">
                        <table class="modal-7-table">
                            <tr>
                                <td><strong style="color:#00449e"><?php _e('Unlink', 'quiz-master-next'); ?></strong></td>
                                <td><?php _e('Removes the question only from this quiz.', 'quiz-master-next'); ?></td>
                            <tr>
                            <tr>
                                <td><strong style="color:#dc3232"><?php _e('Delete', 'quiz-master-next'); ?></Strong></td>
                                <td><?php _e('Removes this question from database and everywhere. This action cannot be reversed.', 'quiz-master-next'); ?></td>
                            <tr>
                        </table>
                    </form>
                </main>
                <hr/>
                <footer class="qsm-popup__footer">
                    <button id="unlink-question-button" class="qsm-popup__btn qsm-popup__btn-primary"><span class="dashicons dashicons-trash"></span><?php _e('Unlink', 'quiz-master-next'); ?></button>
                    <button id="delete-question-button" class="qsm-popup__btn qsm-popup__btn-primary"><span class="dashicons dashicons-warning"></span><?php _e('Delete', 'quiz-master-next'); ?></button>
                    <button id="cancel-button" class="qsm-popup__btn" data-micromodal-close="" aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                </footer>
            </div>
        </div>
    </div>

    <?php
}


add_action( 'wp_ajax_qsm_save_pages', 'qsm_ajax_save_pages' );
//add_action( 'wp_ajax_nopriv_qsm_save_pages', 'qsm_ajax_save_pages' );


/**
 * Saves the pages and order from the Questions tab
 *
 * @since 5.2.0
 */
function qsm_ajax_save_pages() {
    
        $nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce-sandy-page' ) )
            die ( 'Busted!');
        
	global $mlwQuizMasterNext;
	$json = array(
		'status' => 'error',
	);
	$quiz_id = intval( $_POST['quiz_id'] );
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	
	$pages = isset( $_POST['pages'] ) ? $_POST['pages'] : array();
	$qpages = isset($_POST['qpages']) ? $_POST['qpages'] : array();
	$response_qpages = $mlwQuizMasterNext->pluginHelper->update_quiz_setting('qpages', $qpages);
	$response = $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'pages', $pages );
	if ( $response ) {
		$json['status'] = 'success';
	}
	echo wp_json_encode( $json );
	wp_die();
}

add_action( 'wp_ajax_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );
//add_action( 'wp_ajax_nopriv_qsm_load_all_quiz_questions', 'qsm_load_all_quiz_questions_ajax' );

/**
 * Loads all the questions and echos out JSON
 *
 * @since 0.1.0
 * @return void
 */
function qsm_load_all_quiz_questions_ajax() {
	global $wpdb;
	global $mlwQuizMasterNext;

	// Loads questions.
	$questions = $wpdb->get_results( "SELECT question_id, question_name FROM {$wpdb->prefix}mlw_questions WHERE deleted = '0' ORDER BY question_id DESC" );

	// Creates question array.
	$question_json = array();
	foreach ( $questions as $question ) {
		$question_json[] = array(
			'id'       => $question->question_id,
			'question' => $question->question_name,
		);
	}

	// Echos JSON and dies.
	echo wp_json_encode( $question_json );
	wp_die();
}

add_action( 'wp_ajax_qsm_send_data_sendy', 'qsm_send_data_sendy' );
//add_action( 'wp_ajax_nopriv_qsm_send_data_sendy', 'qsm_send_data_sendy' );

/**
 * @version 6.3.2
 * Send data to sendy
 */
function qsm_send_data_sendy(){
    
    $nonce = $_POST['nonce'];
    if ( ! wp_verify_nonce( $nonce, 'ajax-nonce-sendy-save' ) )
        die ( 'Busted!');
    
    $sendy_url = 'http://sendy.expresstech.io';
    $list = '4v8zvoyXyTHSS80jeavOpg';
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    //subscribe
    $postdata = http_build_query(
        array(
        'name' => $name,
        'email' => $email,
        'list' => $list,
        'boolean' => 'true'
        )
    );
    $opts = array('http' => array('method'  => 'POST', 'header'  => 'Content-type: application/x-www-form-urlencoded', 'content' => $postdata));
    $context  = stream_context_create($opts);    
    $result = wp_remote_post($sendy_url.'/subscribe', [ 'body' => array(
        'name' => $name,
        'email' => $email,
        'list' => $list,
        'boolean' => 'true'
        ) ] );
    echo isset($result['response']) && isset($result['response']['code']) && $result['response']['code'] == 200 ? $result['body'] : '';
    exit;
}
    
add_action( 'wp_ajax_qsm_dashboard_delete_result', 'qsm_dashboard_delete_result' );
function qsm_dashboard_delete_result(){
    $result_id = isset($_POST['result_id']) ? sanitize_text_field($_POST['result_id']) : 0;    
    if($result_id){        
        global $wpdb;
        $wpdb->update(
                $wpdb->prefix."mlw_results",
                array(
                        'deleted' => 1,
                ),
                array( 'result_id' => $result_id ),
                array(
                        '%d'
                ),
                array( '%d' )
        );
        echo 'success';
        exit;
    }
    echo 'failed';
    exit;
}

/**
 * Delete question from question bank
 * 
 * @since 7.1.3
 */
function qsm_delete_question_question_bank(){
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "delete_question_question_bank_nonce") ) {
        echo wp_json_encode( array( 'success' => false, 'message' => __( 'Nonce verification failed.','quiz-master-next' ) ) );
	wp_die();
    }
    $question_ids = isset( $_POST['question_ids'] ) ? sanitize_textarea_field($_POST['question_ids']) : '';    
    $question_arr = explode(',', $question_ids);
    $response = array();
    if( $question_arr ){
        global $wpdb;
        foreach ($question_arr as $key => $value) {
            $wpdb->update(
                    $wpdb->prefix."mlw_questions",
                    array(
                            'deleted_question_bank' => 1,
                    ),
                    array( 'question_id' => $value ),
                    array(
                            '%d'
                    ),
                    array( '%d' )
            );
        }
        echo wp_json_encode( array( 'success' => true, 'message' => __( 'Selected Questions are removed from question bank.','quiz-master-next' ) ) );
    }
    exit;
}
add_action( 'wp_ajax_qsm_delete_question_question_bank', 'qsm_delete_question_question_bank' );
/**
 * Delete quiz question from Database 
 * 
 * @since 7.1.11
 */
function qsm_delete_question_from_database(){
    if ( !wp_verify_nonce( $_REQUEST['nonce'], "delete_question_from_database") ) {
        echo wp_json_encode( array( 'success' => false, 'message' => __( 'Nonce verification failed.','quiz-master-next' ) ) );
	wp_die();
    }
    $question_id = $_POST['question_id'];    

    if( $question_id ){
        global $wpdb;
		    $wpdb->delete($wpdb->prefix.'mlw_questions',array('question_id'=> $question_id));
        echo wp_json_encode( array( 'success' => true,'message' => __( 'Question removed Successfully.','quiz-master-next' ) ) );
    }
    exit;
}
add_action( 'wp_ajax_qsm_delete_question_from_database', 'qsm_delete_question_from_database' );
?>
