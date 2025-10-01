<?php 
/**
 * File for the QMNQuizManager class
 *
 * @package QSM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class QSM_Assets_Loader {

	public $common_css = QSM_PLUGIN_CSS_URL . '/common.css';

	public static $default_MathJax_script = "MathJax = {
		tex: {
			inlineMath: [['$','$'],['\\\\(','\\\\)']],
			processEscapes: true
		},
		options: {
			ignoreHtmlClass: 'tex2jax_ignore|editor-rich-text'
		}
		};";

	public $mathjax_url				   = QSM_PLUGIN_JS_URL . '/mathjax/tex-mml-chtml.js';

	public $mathjax_version			   = '3.2.0';

	public function __construct() {
		
		add_action( 'wp_footer', array( $this, 'enqueue_scripts' ),10 );	
		
	}

	public function enqueue_scripts() {
		
		global $wpdb,$mlwQuizMasterNext,$post,$qmn_allowed_visit,$qmn_json_data,$shortcode_quiz_ids;
		if(!is_array($shortcode_quiz_ids)) {
			return;
		}
		foreach($shortcode_quiz_ids as $quiz_id){
			
			$quiz			= intval( $quiz_id );  

			$has_proper_quiz = $mlwQuizMasterNext->pluginHelper->has_proper_quiz( $quiz );

			if ( false === $has_proper_quiz['res'] ) {
				continue;
			}
			$qmn_quiz_options = $has_proper_quiz['qmn_quiz_options'];
			$qmn_array_for_variables = array(
			'quiz_id'     => $qmn_quiz_options->quiz_id,
			'quiz_name'   => $qmn_quiz_options->quiz_name,
			'quiz_system' => $qmn_quiz_options->system,
			'user_ip'     => $this->get_user_ip(),
			);

			if ( isset( $_GET['result_id'] ) && '' !== $_GET['result_id'] ) {
				$this->enqueue_result_assets($qmn_quiz_options);
			}
			else{
			$this->enqueue_quiz_template_style($qmn_quiz_options);
			}
			
			$this->enqueue_quiz_assets($qmn_quiz_options,$qmn_array_for_variables);
			$this->prepare_quiz_json($qmn_quiz_options,$qmn_array_for_variables);    
			$this->add_pagination_template( $qmn_quiz_options, $quiz_id );
		}
			
	}
	private function is_result_page() {
		global $mlwQuizMasterNext,$wpdb;
			if ( isset( $_GET['result_id'] ) && '' !== $_GET['result_id'] ) {
				$result_unique_id = sanitize_text_field( wp_unslash( $_GET['result_id'] ) );
					$result		   = $wpdb->get_row( $wpdb->prepare( "SELECT `result_id`, `quiz_id` FROM {$wpdb->prefix}mlw_results WHERE unique_id = %s", $result_unique_id ), ARRAY_A );
				if ( ! empty( $result ) && isset( $result['result_id'] ) ) {
				return true;
			}
		}
		return false;
	}
	private function enqueue_result_assets( $qmn_quiz_options ) {
		global $mlwQuizMasterNext;
		wp_enqueue_style( 'qmn_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );
		wp_style_add_data( 'qmn_quiz_common_style', 'rtl', 'replace' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_script( 'qsm_quiz', QSM_PLUGIN_JS_URL . '/qsm-quiz.js', array( 'wp-util', 'underscore', 'jquery', 'jquery-ui-tooltip' ), $mlwQuizMasterNext->version, false );
		wp_enqueue_script( 'qsm_common', QSM_PLUGIN_JS_URL . '/qsm-common.js', array(), $mlwQuizMasterNext->version, true );
		$disable_mathjax = isset( $qmn_quiz_options['disable_mathjax'] ) ? $qmn_quiz_options['disable_mathjax'] : '';
		if ( 1 != $disable_mathjax ) {
			wp_enqueue_script( 'math_jax', $this->mathjax_url, false, $this->mathjax_version, true );
			wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
		}
	}
	private function enqueue_quiz_assets( $qmn_quiz_options ) {
		global $mlwQuizMasterNext;

		wp_enqueue_style( 'qmn_quiz_animation_style', QSM_PLUGIN_CSS_URL . '/animate.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_style( 'qmn_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );
		wp_style_add_data( 'qmn_quiz_common_style', 'rtl', 'replace' );
		wp_enqueue_style( 'dashicons' );

		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_style( 'jquery-redmond-theme', QSM_PLUGIN_CSS_URL . '/jquery-ui.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_style( 'qmn_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );


		wp_enqueue_script( 'progress-bar', QSM_PLUGIN_JS_URL . '/progressbar.min.js', array(), '1.1.0', true );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-slider-rtl-js', QSM_PLUGIN_JS_URL . '/jquery.ui.slider-rtl.js', array(), $mlwQuizMasterNext->version, true );
		wp_enqueue_style( 'jquery-ui-slider-rtl-css', QSM_PLUGIN_CSS_URL . '/jquery.ui.slider-rtl.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_script( 'jquery-touch-punch' );
		wp_enqueue_script( 'qsm_model_js', QSM_PLUGIN_JS_URL . '/micromodal.min.js', array(), $mlwQuizMasterNext->version, false );
		wp_enqueue_script( 'qsm_encryption', QSM_PLUGIN_JS_URL . '/crypto-js.js', array( 'jquery' ), $mlwQuizMasterNext->version, false );
		wp_enqueue_script( 'qsm_quiz', QSM_PLUGIN_JS_URL . '/qsm-quiz.js', array( 'wp-util', 'underscore', 'jquery', 'backbone', 'jquery-ui-tooltip', 'progress-bar', 'qsm_encryption' ), $mlwQuizMasterNext->version, false );
		wp_enqueue_script( 'qsm_common', QSM_PLUGIN_JS_URL . '/qsm-common.js', array(), $mlwQuizMasterNext->version, true );
		
		if ( did_action( 'elementor/loaded' ) && isset($_GET['elementor-preview']) && !empty($_GET['elementor-preview']) ) {
			wp_enqueue_script( 'qsm_elementor_preview', QSM_PLUGIN_JS_URL . '/elementor-preview.js', array('qsm_quiz'), $mlwQuizMasterNext->version, true );
		}
		
		wp_localize_script(
			'qsm_quiz',
			'qmn_ajax_object',
			array(
				'site_url'                  => site_url(),
				'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
				'multicheckbox_limit_reach' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->quiz_limit_choice, "quiz_quiz_limit_choice-{$qmn_quiz_options->quiz_id}" ),
				'out_of_text'               => esc_html__( ' out of ', 'quiz-master-next' ),
				'quiz_time_over'            => esc_html__( 'Quiz time is over.', 'quiz-master-next' ),
				'security'                  => wp_create_nonce( 'qsm_submit_quiz' ),
				'start_date'                => current_time( 'h:i:s A m/d/Y' ),
				'validate_process'          => esc_html__( 'Validating file...', 'quiz-master-next' ),
				'remove_file'               => esc_html__( 'Removing file...', 'quiz-master-next' ),
				'remove_file_success'       => esc_html__( 'File removed successfully', 'quiz-master-next' ),
				'validate_success'          => esc_html__( 'File validated successfully', 'quiz-master-next' ),
				'invalid_file_type'         => esc_html__( 'Invalid file type. Allowed types: ', 'quiz-master-next' ),
				'invalid_file_size'         => esc_html__( 'File is too large. Maximum size: ', 'quiz-master-next' ),
			)
		);
		$disable_mathjax = isset( $qmn_quiz_options->disable_mathjax ) ? $qmn_quiz_options->disable_mathjax : '';
		if ( 1 != $disable_mathjax ) {
			wp_enqueue_script( 'math_jax', $this->mathjax_url, array(), $this->mathjax_version, true );
			wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
		}
	}
	private function enqueue_quiz_template_style($qmn_quiz_options ) {

		global $mlwQuizMasterNext;
		if ( 'default' == $qmn_quiz_options->theme_selected ) {
			//$return_display .= '<style type="text/css">' . preg_replace( '#<script(.*?)>(.*?)</script>#is', '', htmlspecialchars_decode( $qmn_quiz_options->quiz_stye, ENT_QUOTES) ) . '</style>';
			wp_enqueue_style( 'qmn_quiz_style', QSM_PLUGIN_CSS_URL . '/qmn_quiz.css', array(), $mlwQuizMasterNext->version );
			wp_style_add_data( 'qmn_quiz_style', 'rtl', 'replace' );
			wp_add_inline_style( 'qmn_quiz_style', $qmn_quiz_options->quiz_stye );
		}
		else {
			$registered_template = $mlwQuizMasterNext->pluginHelper->get_quiz_templates( $qmn_quiz_options->theme_selected );
			// Check direct file first, then check templates folder in plugin, then check templates file in theme.
			// If all fails, then load custom styling instead.
			if ( $registered_template && file_exists( ABSPATH . $registered_template['path'] ) ) {
				wp_enqueue_style( 'qmn_quiz_template', site_url( $registered_template['path'] ), array(), $mlwQuizMasterNext->version );
			} elseif ( $registered_template && file_exists( plugin_dir_path( __FILE__ ) . '../../templates/' . $registered_template['path'] ) ) {
				wp_enqueue_style( 'qmn_quiz_template', plugins_url( '../../templates/' . $registered_template['path'], __FILE__ ), array(), $mlwQuizMasterNext->version );
			} elseif ( $registered_template && file_exists( get_theme_file_path( '/templates/' . $registered_template['path'] ) ) ) {
				wp_enqueue_style( 'qmn_quiz_template', get_stylesheet_directory_uri() . '/templates/' . $registered_template['path'], array(), $mlwQuizMasterNext->version );
			}
			if ( ! empty( $qmn_quiz_options->quiz_stye ) ) {
				wp_add_inline_style( 'qmn_quiz_template', $qmn_quiz_options->quiz_stye );
			}
		}

	}
	private function prepare_quiz_json($qmn_quiz_options,$qmn_array_for_variables) {

	global $mlwQuizMasterNext,$wpdb,$qmn_json_data;
	$qpages                  = array();
			$qpages_arr              = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array() );
			if ( ! empty( $qpages_arr ) ) {
				foreach ( $qpages_arr as $key => $qpage ) {
					unset( $qpage['questions'] );
					if ( isset( $qpage['id'] ) ) {
						$qpages[ $qpage['id'] ] = $qpage;
					}
				}
			}
		$correct_answer_text = sanitize_text_field( $qmn_quiz_options->quick_result_correct_answer_text );
			$correct_answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $correct_answer_text, "quiz_quick_result_correct_answer_text-{$qmn_array_for_variables['quiz_id']}" );
			$wrong_answer_text = sanitize_text_field( $qmn_quiz_options->quick_result_wrong_answer_text );
			$wrong_answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $wrong_answer_text, "quiz_quick_result_wrong_answer_text-{$qmn_array_for_variables['quiz_id']}" );
			$quiz_processing_message = isset( $qmn_quiz_options->quiz_processing_message ) ? $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->quiz_processing_message, "quiz_quiz_processing_message-{$qmn_array_for_variables['quiz_id']}" ) : '';
			$quiz_limit_choice = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->quiz_limit_choice, "quiz_quiz_limit_choice-{$qmn_array_for_variables['quiz_id']}" );
			$qmn_json_data = array(
				'quiz_id'                            => $qmn_array_for_variables['quiz_id'],
				'quiz_name'                          => $qmn_array_for_variables['quiz_name'],
				'disable_answer'                     => $qmn_quiz_options->disable_answer_onselect,
				'ajax_show_correct'                  => $qmn_quiz_options->ajax_show_correct,
				'progress_bar'                       => $qmn_quiz_options->progress_bar,
				'contact_info_location'              => $qmn_quiz_options->contact_info_location,
				'qpages'                             => $qpages,
				'skip_validation_time_expire'        => $qmn_quiz_options->skip_validation_time_expire,
				'timer_limit_val'                    => $qmn_quiz_options->timer_limit,
				'disable_scroll_next_previous_click' => $qmn_quiz_options->disable_scroll_next_previous_click,
				'disable_scroll_on_result'           => $qmn_quiz_options->disable_scroll_on_result,
				'disable_first_page'                 => $qmn_quiz_options->disable_first_page,
				'enable_result_after_timer_end'      => isset( $qmn_quiz_options->enable_result_after_timer_end ) ? $qmn_quiz_options->enable_result_after_timer_end : '',
				'enable_quick_result_mc'             => isset( $qmn_quiz_options->enable_quick_result_mc ) ? $qmn_quiz_options->enable_quick_result_mc : '',
				'end_quiz_if_wrong'                  => isset( $qmn_quiz_options->end_quiz_if_wrong ) ? $qmn_quiz_options->end_quiz_if_wrong : 0,
				'form_disable_autofill'              => isset( $qmn_quiz_options->form_disable_autofill ) ? $qmn_quiz_options->form_disable_autofill : '',
				'disable_mathjax'                    => isset( $qmn_quiz_options->disable_mathjax ) ? $qmn_quiz_options->disable_mathjax : '',
				'enable_quick_correct_answer_info'   => isset( $qmn_quiz_options->enable_quick_correct_answer_info ) ? $qmn_quiz_options->enable_quick_correct_answer_info : 0,
				'quick_result_correct_answer_text'   => $correct_answer_text,
				'quick_result_wrong_answer_text'     => $wrong_answer_text,
				'quiz_processing_message'            => $quiz_processing_message,
				'quiz_limit_choice'                  => $quiz_limit_choice,
				'not_allow_after_expired_time'       => $qmn_quiz_options->not_allow_after_expired_time,
				'scheduled_time_end'                 => strtotime( $qmn_quiz_options->scheduled_time_end ),
				'prevent_reload'                     => $qmn_quiz_options->prevent_reload,
				'limit_email_based_submission'       => isset($qmn_quiz_options->limit_email_based_submission) ? $qmn_quiz_options->limit_email_based_submission : 0,
				'total_user_tries'                   => $qmn_quiz_options->total_user_tries,
				'is_logged_in'                       => is_user_logged_in(),
			);

		if ( 0 != $qmn_quiz_options->pagination ) {

		$total_questions = 0;
		if ( 0 != $qmn_quiz_options->question_from_total ) {
			$total_questions = $qmn_quiz_options->question_from_total;
		} else {
			$questions       = QSM_Questions::load_questions_by_pages( $qmn_quiz_options->quiz_id );
			$total_questions = count( $questions );
		}

		$default_texts = QMNPluginHelper::get_default_texts();
		$quiz_btn_display_text = $default_texts['next_button_text']; // For old quizes set default here
		$quiz_btn_submit_text = $default_texts['submit_button_text']; // For old quizes set default here

		if ( isset($qmn_quiz_options->start_quiz_survey_text) && "" != $qmn_quiz_options->start_quiz_survey_text ) {
			$quiz_btn_display_text = $qmn_quiz_options->start_quiz_survey_text; // For old quizes set default here
		}
		$qmn_json_data['pagination'] = array(
			'amount'                 => $qmn_quiz_options->pagination,
			'section_comments'       => $qmn_quiz_options->comment_section,
			'total_questions'        => $total_questions,
			'previous_text'          => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->previous_button_text, "quiz_previous_button_text-{$qmn_quiz_options->quiz_id}" ) ),
			'next_text'              => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->next_button_text, "quiz_next_button_text-{$qmn_quiz_options->quiz_id}" ) ),
			'start_quiz_survey_text' => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $quiz_btn_display_text, "quiz_start_quiz_text-{$qmn_quiz_options->quiz_id}" ) ),
			'submit_quiz_text'       => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support($qmn_quiz_options->submit_button_text, "quiz_submit_button_text-{$qmn_quiz_options->quiz_id}" ) ),
		);
	}
	if ( 1 !== intval( $qmn_quiz_options->disable_first_page ) && ( ! empty( $qmn_quiz_options->message_before ) || ( 0 == $qmn_quiz_options->contact_info_location && $contact_fields ) ) ) {
		$qmn_json_data['first_page'] = true;
	}
	else {
		$qmn_json_data['first_page'] = false;
	}
	// if ($qmn_allowed_visit && 0 != $qmn_quiz_options->timer_limit ) {
	//   $qmn_json_data['timer_limit'] = $qmn_quiz_options->timer_limit;
	// }


		$qmn_json_data['error_messages'] = array(
		'email_error_text'     => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->email_error_text, "quiz_email_error_text-{$qmn_quiz_options->quiz_id}" ),
		'number_error_text'    => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->number_error_text, "quiz_number_error_text-{$qmn_quiz_options->quiz_id}" ),
		'incorrect_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->incorrect_error_text, "quiz_incorrect_error_text-{$qmn_quiz_options->quiz_id}" ),
		'empty_error_text'     => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->empty_error_text, "quiz_empty_error_text-{$qmn_quiz_options->quiz_id}" ),
		'url_error_text'       => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->url_error_text, "quiz_url_error_text-{$qmn_quiz_options->quiz_id}" ),
		'minlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->minlength_error_text, "quiz_minlength_error_text-{$qmn_quiz_options->quiz_id}" ),
		'maxlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->maxlength_error_text, "quiz_maxlength_error_text-{$qmn_quiz_options->quiz_id}" ),
		'recaptcha_error_text' => __( 'ReCaptcha is missing', 'quiz-master-next' ),
		);
		$qmn_json_data = apply_filters( 'qsm_json_error_message', $qmn_json_data ,$qmn_quiz_options);
		$qmn_json_data = apply_filters( 'qsm_json_data', $qmn_json_data ,$qmn_quiz_options);

		$inline_js  = 'if (window.qmn_quiz_data === undefined) {';
		$inline_js .= 'window.qmn_quiz_data = new Object();';
		$inline_js .= '}';
		$display = apply_filters( 'qmn_begin_quiz_form', '', $qmn_quiz_options, $qmn_array_for_variables );
		$display = apply_filters( 'qmn_begin_quiz', '', $qmn_quiz_options, $qmn_array_for_variables );
		$inline_js .= 'window.qmn_quiz_data["' . $qmn_json_data['quiz_id'] . '"] = ' . wp_json_encode( $qmn_json_data ) . ';';
		wp_add_inline_script('qsm_quiz', $inline_js, 'after');

	}
	private function maybe_add_encryption($qmn_json_data, $qmn_quiz_options) {

	global $mlwQuizMasterNext;
	$qmn_settings_array = maybe_unserialize( $qmn_quiz_options->quiz_settings );
			$quiz_options = maybe_unserialize( $qmn_settings_array['quiz_options'] );
			$correct_answer_logic = ! empty( $quiz_options['correct_answer_logic'] ) ? $quiz_options['correct_answer_logic'] : '';
			$encryption['correct_answer_logic'] = $correct_answer_logic;
			$enc_questions = array();
			if ( ! empty( $qpages_arr ) ) {
				foreach ( $qpages_arr as $item ) {
					$enc_questions = array_merge($enc_questions, $item['questions']);
				}
			}
			$enc_questions = implode(',', $enc_questions);
			$question_array = $wpdb->get_results(
				"SELECT quiz_id, question_id, answer_array, question_answer_info, question_type_new, question_settings
				FROM {$wpdb->prefix}mlw_questions
				WHERE question_id IN ($enc_questions)", ARRAY_A);
			$questions_settings = array();
			foreach ( $question_array as $key => $question ) {
				
				$unserialized_settings = maybe_unserialize( $question['question_settings'] );
				$question_type_new = $question['question_type_new'];
				if ( 11 == $question_type_new ) {
					$questions_settings[ $question['question_id'] ]['file_upload_type'] = $unserialized_settings['file_upload_type'];
					$questions_settings[ $question['question_id'] ]['file_upload_limit'] = $unserialized_settings['file_upload_limit'];
				}
				$encryption[ $question['question_id'] ]['question_type_new'] = $question_type_new;
				$encryption[ $question['question_id'] ]['answer_array'] = maybe_unserialize( $question['answer_array'] );
				$encryption[ $question['question_id'] ]['settings'] = $unserialized_settings;
				$encryption[ $question['question_id'] ]['correct_info_text'] = isset( $question['question_answer_info'] ) ? html_entity_decode( $question['question_answer_info'] ) : '';
				$encryption[ $question['question_id'] ]['correct_info_text'] = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $encryption[ $question['question_id'] ]['correct_info_text'], "correctanswerinfo-{$question['question_id']}" );
			}
			$qmn_json_data['questions_settings'] = $questions_settings;
			if ( ( isset($qmn_json_data['end_quiz_if_wrong']) && 0 < $qmn_json_data['end_quiz_if_wrong'] ) || ( ! empty( $qmn_json_data['enable_quick_result_mc'] ) && 1 == $qmn_json_data['enable_quick_result_mc'] ) || ( ! empty( $qmn_json_data['enable_quick_correct_answer_info'] ) && 0 != $qmn_json_data['enable_quick_correct_answer_info'] ) || ( ! empty( $qmn_json_data['ajax_show_correct'] ) && 1 == $qmn_json_data['ajax_show_correct'] ) ) {
				$quiz_id = $qmn_json_data['quiz_id'];
				$qsm_inline_encrypt_js = '
				if (encryptionKey === undefined) {
						var encryptionKey = {};
				}
				if (data === undefined) {
						var data = {};
				}
				if (jsonString === undefined) {
						var jsonString = {};
				}
				if (encryptedData === undefined) {
						var encryptedData = {};
				}
				encryptionKey['.$quiz_id.'] = "'.hash('sha256',time().$quiz_id).'";

				data['.$quiz_id.'] = '.wp_json_encode($encryption).';
				jsonString['.$quiz_id.'] = JSON.stringify(data['.$quiz_id.']);
				encryptedData['.$quiz_id.'] = CryptoJS.AES.encrypt(jsonString['.$quiz_id.'], encryptionKey['.$quiz_id.']).toString();';
				wp_add_inline_script('qsm_encryption', $qsm_inline_encrypt_js, 'after');        
			}

	}
	private function enqueue_mathjax( $qmn_quiz_options ) {
	global $mlwQuizMasterNext;
	if ( empty( $qmn_quiz_options->disable_mathjax ) || 1 != $qmn_quiz_options->disable_mathjax ) {
		wp_enqueue_script( 'math_jax', $this->mathjax_url, array(), $this->mathjax_version, true );
		wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
	}
	}
	private function add_pagination_template( $qmn_quiz_options ) {
		global $mlwQuizMasterNext;
		$start_button_text = ! empty( $qmn_quiz_options->start_quiz_survey_text ) ? $qmn_quiz_options->start_quiz_survey_text : $qmn_quiz_options->next_button_text;
		$tmpl_pagination = '<div class="qsm-pagination qmn_pagination border margin-bottom">
			<a class="qsm-btn qsm-previous qmn_btn mlw_qmn_quiz_link mlw_previous" href="javascript:void(0)">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->previous_button_text, "quiz_previous_button_text-{$qmn_quiz_options->quiz_id}" ) ) . '</a>
			<span class="qmn_page_message"></span>
			<div class="qmn_page_counter_message"></div>
			<div class="qsm-progress-bar" style="display:none;"><div class="progressbar-text"></div></div>
			<a class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_start" href="javascript:void(0)">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $start_button_text, "quiz_next_button_text-{$qmn_quiz_options->quiz_id}" ) ) . '</a>
			<a class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_next" href="javascript:void(0)">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->next_button_text, "quiz_next_button_text-{$qmn_quiz_options->quiz_id}" ) ) . '</a>
			<input type="submit" class="qsm-btn qsm-submit-btn qmn_btn" value="' . esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->submit_button_text, "quiz_submit_button_text-{$qmn_quiz_options->quiz_id}" ) ) . '" />
		</div>';
		qsm_add_inline_tmpl( 'qsm_quiz', 'tmpl-qsm-pagination-' . esc_attr( $qmn_quiz_options->quiz_id ), $tmpl_pagination );
	}
	public function get_user_ip() {
		$ip            = __( 'Not collected', 'quiz-master-next' );
		$settings      = (array) get_option( 'qmn-settings' );
		$ip_collection = '0';
		if ( isset( $settings['ip_collection'] ) ) {
			$ip_collection = $settings['ip_collection'];
		}
		if ( '1' != $ip_collection ) {
			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ip = getenv( 'HTTP_CLIENT_IP' );
			} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED' );
			} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
				$ip = getenv( 'HTTP_FORWARDED' );
			} elseif ( getenv( 'REMOTE_ADDR' ) ) {
				$ip = getenv( 'REMOTE_ADDR' );
			} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			} else {
				$ip = __( 'Unknown', 'quiz-master-next' );
			}
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		} else {
			return __( 'Invalid IP Address', 'quiz-master-next' );
		}
	}
}