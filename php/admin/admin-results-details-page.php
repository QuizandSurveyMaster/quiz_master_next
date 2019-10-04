<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
* This function generates the results details that are shown the results page.
*
* @return type void
* @since 4.4.0
*/
function qsm_generate_result_details() {
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}
	global $mlwQuizMasterNext;
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'results';
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_results_tabs();
	?>
	<div class="wrap">
		<h2><?php _e('Quiz Results', 'quiz-master-next'); ?></h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( $tab_array as $tab ) {
				$active_class = '';
				if ( $active_tab == $tab['slug'] ) {
					$active_class = 'nav-tab-active';
				}
				echo "<a href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $_GET["result_id"] ) . "&&tab=" . $tab['slug'] . "\" class=\"nav-tab $active_class\">" . $tab['title'] . "</a>";
			}
			?>
		</h2>
                <style type="text/css">
                    .result-tab-content p{
                        font-size: 16px;
                    }
                    .qmn_question_answer b {
                        font-size: 18px;
                        margin-bottom: 0;
                        display: block;
                    }
                    .qmn_question_answer{
                        margin-bottom: 30px;
                        font-size: 16px;
                        line-height: 1.5;
                    }
                </style>
                <div class="result-tab-content">
		<?php                                        
			foreach( $tab_array as $tab ) {
				if ( $active_tab == $tab['slug'] ) {
					call_user_func( $tab['function'] );
				}
			}
		?>
		</div>
	</div>
	<?php
}


/**
* This function generates the results details tab that shows the details of the quiz
*
* @param type description
* @return void
* @since 4.4.0
*/
function qsm_generate_results_details_tab() {

	global $wpdb;
	global $mlwQuizMasterNext;

	// Gets results data.
	$result_id    = intval( $_GET["result_id"] );
	$results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $result_id ) );

	// Prepare plugin helper.
	$quiz_id = intval( $results_data->quiz_id );
	$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );

	$previous_results = $wpdb->get_var( "SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE result_id = (SELECT MAX(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 AND result_id < $result_id)" );
	$next_results     = $wpdb->get_var( "SELECT result_id FROM {$wpdb->prefix}mlw_results WHERE result_id = (SELECT MIN(result_id) FROM {$wpdb->prefix}mlw_results WHERE deleted = 0 AND result_id > $result_id)" );

	// If there is previous or next results, show buttons.
        echo '<div style="text-align:right; margin-top: 10px;">';
	if ( ! is_null( $previous_results ) && $previous_results ) {
		echo "<a class='button' href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $previous_results ) . "\" >View Previous Results</a> ";
	}
	if ( ! is_null( $next_results ) && $next_results ) {
		echo " <a class='button' href=\"?page=qsm_quiz_result_details&&result_id=" . intval( $next_results ) . "\" >View Next Results</a>";
	}
        echo '</div>';

	// Get template for admin results.
	$settings = (array) get_option( 'qmn-settings' );
	if ( isset( $settings['results_details_template'] ) ) {            
		$template = htmlspecialchars_decode( $settings['results_details_template'], ENT_QUOTES );
	} else {
		$template = "<h2>Quiz Results for %QUIZ_NAME%</h2>
		<p>%CONTACT_ALL%</p>
		<p>Name Provided: %USER_NAME%</p>
		<p>Business Provided: %USER_BUSINESS%</p>
		<p>Phone Provided: %USER_PHONE%</p>
		<p>Email Provided: %USER_EMAIL%</p>
		<p>Score Received: %AMOUNT_CORRECT%/%TOTAL_QUESTIONS% or %CORRECT_SCORE%% or %POINT_SCORE% points</p>
		<h2>Answers Provided:</h2>
		<p>The user took %TIMER% to complete quiz.</p>
		<p>Comments entered were: %COMMENT_SECTION%</p>
		<p>The answers were as follows:</p>
		%QUESTIONS_ANSWERS%";
	}

	// Prepare responses array.
	if ( is_serialized( $results_data->quiz_results ) && is_array( @unserialize( $results_data->quiz_results ) ) ) {
		$results = unserialize($results_data->quiz_results);
		if ( ! isset( $results["contact"] ) ) {
			$results["contact"] = array();
		}
	} else {
		$template = str_replace( "%QUESTIONS_ANSWERS%" , $results_data->quiz_results, $template);
		$template = str_replace( "%TIMER%" , '', $template);
		$template = str_replace( "%COMMENT_SECTION%" , '', $template);
		$results = array(
			0,
			array(),
			'',
			'contact' => array()
		);
	}

	// Prepare full results array.
	$results_array = array(
		'quiz_id'                => $results_data->quiz_id,
		'quiz_name'              => $results_data->quiz_name,
		'quiz_system'            => $results_data->quiz_system,
		'user_name'              => $results_data->name,
		'user_business'          => $results_data->business,
		'user_email'             => $results_data->email,
		'user_phone'             => $results_data->phone,
		'user_id'                => $results_data->user,
		'timer'                  => $results[0],
		'time_taken'             => $results_data->time_taken,
		'total_points'           => $results_data->point_score,
		'total_score'            => $results_data->correct_score,
		'total_correct'          => $results_data->correct,
		'total_questions'        => $results_data->total,
		'comments'               => $results[2],
		'question_answers_array' => $results[1],
		'contact'                => $results["contact"],
		'results'                => $results,
	);

	// Pass through template variable filter
	$template = apply_filters( 'mlw_qmn_template_variable_results_page', $template, $results_array );
	$template = str_replace( "\n" , "<br>", $template );
	echo $template;

	// Hook for below admin results
	do_action( 'qsm_below_admin_results', $results_array );
}


/**
* Generates the results details tab in the quiz results page
*
* @return void
* @since 4.4.0
*/
function qsm_results_details_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_results_settings_tab( __( "Results", 'quiz-master-next' ), "qsm_generate_results_details_tab" );
}
add_action( "plugins_loaded", 'qsm_results_details_tab' );
?>
