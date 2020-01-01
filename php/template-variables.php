<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This file contains all the variables that are in the plugin. It registers them and then makes them available for use.
*
* This plugin also contains the social media variables and all of there uses.
*
* @since 4.4.0
*/
/*

Results Array For Reference:

$mlw_qmn_result_array = array(
			'quiz_id' => $mlw_quiz_id,
			'quiz_name' => $mlw_quiz_options->quiz_name,
			'quiz_system' => $mlw_quiz_options->system,
			'total_points' => $mlw_points,
			'total_score' => $mlw_total_score,
			'total_correct' => $mlw_correct,
			'total_questions' => $mlw_total_questions,
			'user_name' => $mlw_user_name,
			'user_business' => $mlw_user_comp,
			'user_email' => $mlw_user_email,
			'user_phone' => $mlw_user_phone,
			'user_id' => get_current_user_id(),
			'question_answers_display' => $mlw_question_answers,
			'question_answers_array' => $mlw_qmn_answer_array,
			'timer' => $mlw_qmn_timer,
			'comments' => $mlw_qm_quiz_comments
		);

*/
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_all_contact_fields_variable', 10, 2 );
add_filter( 'mlw_qmn_template_variable_results_page', 'qsm_contact_field_variable', 10, 2 );
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_category_points',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_average_category_points',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_category_score',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_category_average_score',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_category_average_points',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_point_score',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_average_point',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_amount_correct',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_total_questions',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_correct_score',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_quiz_name',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_name',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_business',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_phone',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_user_email',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_question_answers',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_comments',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_timer',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_timer_minutes',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_date',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_date_taken',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qsm_variable_facebook_share',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qsm_variable_twitter_share',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qsm_variable_result_id',10,2);
add_filter('qmn_end_results', 'qsm_variable_poll_result',10,3);

add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_quiz_name',10,2);
add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_date',10,2);
add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_current_user',10,2);

/**
 * Show poll result
 * @param str $content
 * @param arr $mlw_quiz_array
 */
function qsm_variable_poll_result($content, $mlw_quiz_array, $variables){
    $quiz_id = is_object($mlw_quiz_array) ? $mlw_quiz_array->quiz_id : $mlw_quiz_array['quiz_id'];       
    while ( false !== strpos($content, '%POLL_RESULTS_') ) {
        $question_id = mlw_qmn_get_string_between($content, '%POLL_RESULTS_', '%');        
        global $wpdb;
        $table_name = $wpdb->prefix . 'mlw_results';
        $table_question = $wpdb->prefix . 'mlw_questions';
        $total_query = $wpdb->get_row('SELECT count(*) AS total_count FROM ' . $table_name . ' WHERE quiz_id = ' . $quiz_id,ARRAY_A);
        $total_result = $total_query['total_count'];
        $ser_answer = $wpdb->get_row('SELECT answer_array FROM ' . $table_question . ' WHERE question_id = ' . $question_id,ARRAY_A);
        $ser_answer_arry = unserialize($ser_answer['answer_array']);        
        $ser_answer_arry_change = array_filter(array_merge(array(0), $ser_answer_arry));       
        $total_quiz_results = $wpdb->get_results('SELECT quiz_results FROM ' . $table_name . ' WHERE quiz_id = ' . $quiz_id,ARRAY_A);        
        $answer_array = array();
        if($total_quiz_results){
            foreach ($total_quiz_results as $key => $value) {                
                $userdb = unserialize($value['quiz_results']);
                if(!empty($userdb)){
                    $key = array_search($question_id, array_column($userdb[1], 'id'));
                    $answer_array[] = isset($userdb[1][$key]) ? $userdb[1][$key][1] : '';
                }
            }
        }        
        $vals = array_count_values($answer_array);
        $str = '';        
        if($vals){
            $str .= '<h4>Poll Result:</h4>';
            foreach ($vals as $answer_str => $answer_count) {                
                if($answer_str != '' && qsm_find_key_from_array($answer_str, $ser_answer_arry_change)){
                    $percentage = number_format($answer_count / $total_result * 100,2) ;
                    $str .= $answer_str . ' : ' . $percentage .'%<br/>';
                    $str .= '<progress value="'. $percentage .'" max="100">'. $percentage .' %</progress><br/>';
                }
            }
        }
        $content = str_replace( "%POLL_RESULTS_". $question_id ."%" , $str, $content);
    }    
    return $content;
}

function mlw_qmn_get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function qsm_find_key_from_array($search_value,$array){
    if($array){
        foreach ($array as $key => $value) {
            if($value[0] == $search_value){
                return true;
            }
        }
    }
    return false;
}

/**
 * Adds Facebook sharing link using the %FACEBOOK_SHARE% variable
 */
function qsm_variable_facebook_share( $content, $mlw_quiz_array ) {
	while ( false !== strpos($content, '%FACEBOOK_SHARE%') ) {
		wp_enqueue_script( 'qmn_quiz_social_share', plugins_url( '../../js/qmn_social_share.js' , __FILE__ ) );
		$settings = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '483815031724529';
		if ( isset( $settings['facebook_app_id'] ) ) {
			$facebook_app_id = esc_js( $settings['facebook_app_id'] );
		}

		global $mlwQuizMasterNext;
		$sharing = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'facebook_sharing_text', '' );

		$sharing = apply_filters( 'mlw_qmn_template_variable_results_page', $sharing, $mlw_quiz_array);
                $fb_image = plugins_url('', dirname(__FILE__) ) . '/assets/facebook.png';
		$social_display = "<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('facebook', '".esc_js( $sharing )."', '".esc_js($mlw_quiz_array["quiz_name"])."', '$facebook_app_id');\"><img src='". $fb_image ."' alt='Facebbok Share' /></a>";
		$content = str_replace( "%FACEBOOK_SHARE%" , $social_display, $content);
	}
	return $content;
}

/**
 * Adds Twitter sharing link using the %TWITTER_SHARE% variable
 */
function qsm_variable_twitter_share( $content, $mlw_quiz_array ) {
	while ( false !== strpos($content, '%TWITTER_SHARE%') ) {
		wp_enqueue_script( 'qmn_quiz_social_share', plugins_url( '../../js/qmn_social_share.js' , __FILE__ ) );

		global $mlwQuizMasterNext;
		$sharing = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'twitter_sharing_text', '' );
		$sharing = apply_filters( 'mlw_qmn_template_variable_results_page', $sharing, $mlw_quiz_array);
                $tw_image = plugins_url('', dirname(__FILE__) ) . '/assets/twitter.png';
		$social_display = "<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('twitter', '".esc_js( $sharing )."', '".esc_js($mlw_quiz_array["quiz_name"])."');\"><img src='". $tw_image ."' alt='Twitter Share' /></a>";
		$content = str_replace( "%TWITTER_SHARE%" , $social_display, $content);
	}
	return $content;
}

/**
 * Adds result id using the %RESULT_ID% variable
 */
function qsm_variable_result_id( $content, $mlw_quiz_array ) {
	while ( false !== strpos($content, '%RESULT_ID%') ) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'mlw_results';
                $get_last_id = $wpdb->get_row("SELECT result_id FROM $table_name ORDER BY result_id DESC",ARRAY_A);
		$content = str_replace( "%RESULT_ID%" , $get_last_id['result_id'], $content);
	}
	return $content;
}

function mlw_qmn_variable_point_score($content, $mlw_quiz_array)
{
	$content = str_replace( "%POINT_SCORE%" , $mlw_quiz_array["total_points"], $content);
	return $content;
}
function mlw_qmn_variable_average_point($content, $mlw_quiz_array)
{
	if ($mlw_quiz_array["total_questions"] != 0)
	{
		$mlw_average_points = round($mlw_quiz_array["total_points"]/$mlw_quiz_array["total_questions"], 2);
	}
	else
	{
		$mlw_average_points = 0;
	}
	$content = str_replace( "%AVERAGE_POINT%" , $mlw_average_points, $content);
	return $content;
}
function mlw_qmn_variable_amount_correct($content, $mlw_quiz_array)
{
	$content = str_replace( "%AMOUNT_CORRECT%" , $mlw_quiz_array["total_correct"], $content);
	return $content;
}
function mlw_qmn_variable_total_questions($content, $mlw_quiz_array)
{
	$content = str_replace( "%TOTAL_QUESTIONS%" , $mlw_quiz_array["total_questions"], $content);
	return $content;
}
function mlw_qmn_variable_correct_score($content, $mlw_quiz_array)
{
	$content = str_replace( "%CORRECT_SCORE%" , $mlw_quiz_array["total_score"], $content);
	return $content;
}
function mlw_qmn_variable_quiz_name($content, $mlw_quiz_array)
{
	$content = str_replace( "%QUIZ_NAME%" , $mlw_quiz_array["quiz_name"], $content);
	return $content;
}
function mlw_qmn_variable_user_name($content, $mlw_quiz_array)
{
	$content = str_replace( "%USER_NAME%" , $mlw_quiz_array["user_name"], $content);
	return $content;
}
function mlw_qmn_variable_current_user($content, $mlw_quiz_array)
{
	$current_user = wp_get_current_user();
	$content = str_replace( "%USER_NAME%" , $current_user->display_name, $content);
	return $content;
}
function mlw_qmn_variable_user_business($content, $mlw_quiz_array)
{
	$content = str_replace( "%USER_BUSINESS%" , $mlw_quiz_array["user_business"], $content);
	return $content;
}
function mlw_qmn_variable_user_phone($content, $mlw_quiz_array)
{
	$content = str_replace( "%USER_PHONE%" , $mlw_quiz_array["user_phone"], $content);
	return $content;
}
function mlw_qmn_variable_user_email($content, $mlw_quiz_array)
{
	$content = str_replace( "%USER_EMAIL%" , $mlw_quiz_array["user_email"], $content);
	return $content;
}

/**
 * Returns user value for supplied contact field
 *
 * @since 5.0.0
 * @return string The HTML for the content
 */
function qsm_contact_field_variable( $content, $results_array ) {
	preg_match_all( "~%CONTACT_(.*?)%~i", $content, $matches );
	for ( $i = 0; $i < count( $matches[0] ); $i++ ) {
		$content = str_replace( "%CONTACT_" . $matches[1][ $i ] . "%" , $results_array["contact"][ $matches[1][ $i ] - 1 ]["value"], $content);
	}
	return $content;
}

/**
 * Returns user values for all contact fields
 *
 * @since 5.0.0
 * @return string The HTML for the content
 */
function qsm_all_contact_fields_variable( $content, $results ) {
	$return = '';
	for ( $i = 0; $i < count( $results["contact"] ); $i++ ) {
		$return .= $results["contact"][ $i ]["label"] . ": " . $results["contact"][ $i ]["value"] . "<br>";
	}
	$content = str_replace( "%CONTACT_ALL%" , $return, $content );
	return $content;
}

/**
 * Converts the %QUESTIONS_ANSWERS% into the template
 *
 * @param string $content The content to be checked for the template
 * @param array  $mlw_quiz_array The array for the response data
 */
function mlw_qmn_variable_question_answers( $content, $mlw_quiz_array ) {

	// Checks if the variable is present in the content.
	while ( strpos( $content, '%QUESTIONS_ANSWERS%' ) !== false ) {
		global $mlwQuizMasterNext;
		global $wpdb;
		$display = '';
		$qmn_question_answer_template = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', 'question_answer_template', '%QUESTION%<br>%USER_ANSWER%' );
		$questions = QSM_Questions::load_questions_by_pages( $mlw_quiz_array['quiz_id'] );
		$qmn_questions = array();
		foreach ( $questions as $question ) {
			$qmn_questions[ $question['question_id'] ] = $question['question_answer_info'];
		}
                
		// Cycles through each answer in the responses.
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( $answer["correct"] === "correct" ){
				$user_answer_class = "qmn_user_correct_answer";
				$question_answer_class = "qmn_question_answer_correct";
			} else {
				$user_answer_class = "qmn_user_incorrect_answer";
				$question_answer_class = "qmn_question_answer_incorrect";
			}
			$mlw_question_answer_display = htmlspecialchars_decode($qmn_question_answer_template, ENT_QUOTES);
			$mlw_question_answer_display = str_replace( "%QUESTION%" , '<b>' . htmlspecialchars_decode($answer[0], ENT_QUOTES) . '</b>', $mlw_question_answer_display);
                        if($answer['question_type'] == 11){
                            $file_extension = substr($answer[1], -4);
                            if($file_extension == '.jpg' || $file_extension == 'jepg' || $file_extension == '.png' || $file_extension == '.gif'){
                                $mlw_question_answer_display = str_replace( "%USER_ANSWER%" , "<span class='$user_answer_class'><img src='$answer[1]'/></span>", $mlw_question_answer_display);
                            }else{
                                $mlw_question_answer_display = str_replace( "%USER_ANSWER%" , "<span class='$user_answer_class'>".htmlspecialchars_decode($answer[1], ENT_QUOTES).'</span>', $mlw_question_answer_display);
                            }                            
                        }else{
                            $mlw_question_answer_display = str_replace( "%USER_ANSWER%" , "<span class='$user_answer_class'>".htmlspecialchars_decode($answer[1], ENT_QUOTES).'</span>', $mlw_question_answer_display);
                        }			
			$mlw_question_answer_display = str_replace( "%CORRECT_ANSWER%" , htmlspecialchars_decode($answer[2], ENT_QUOTES), $mlw_question_answer_display);
			$mlw_question_answer_display = str_replace( "%USER_COMMENTS%" , $answer[3], $mlw_question_answer_display);
			$mlw_question_answer_display = str_replace( "%CORRECT_ANSWER_INFO%" , htmlspecialchars_decode($qmn_questions[$answer['id']], ENT_QUOTES), $mlw_question_answer_display);
			$display .= "<div class='qmn_question_answer $question_answer_class'>".apply_filters('qmn_variable_question_answers', $mlw_question_answer_display, $mlw_quiz_array).'</div>';
		}
		$content = str_replace( "%QUESTIONS_ANSWERS%" , $display, $content);
	}
	return $content;
}

function mlw_qmn_variable_comments($content, $mlw_quiz_array)
{
	$content = str_replace( "%COMMENT_SECTION%" , $mlw_quiz_array["comments"], $content);
	return $content;
}
function mlw_qmn_variable_timer($content, $mlw_quiz_array)
{
	$content = str_replace( "%TIMER%" , $mlw_quiz_array["timer"], $content);
	return $content;
}
function mlw_qmn_variable_timer_minutes($content, $mlw_quiz_array)
{
	$mlw_minutes = round($mlw_quiz_array["timer"]/60,2);
	$content = str_replace( "%TIMER_MINUTES%" , $mlw_minutes, $content);
	return $content;
}

/**
 * Replaces the variable %CURRENT_DATE% and displays the current date
 *
 * @param string $content The contents of the results page
 * @param array $results The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function mlw_qmn_variable_date( $content, $results ) {
	$date = date_i18n( get_option( 'date_format' ), time() );
	$content = str_replace( "%CURRENT_DATE%" , $date, $content);
	return $content;
}

/**
 * Replaces the variable %DATE_TAKEN% and returns the date the user submitted his or her responses
 *
 * @param string $content The contents of the results page
 * @param array $results The array of all the results from user taking the quiz
 * @return string Returns the contents for the results page
 */
function mlw_qmn_variable_date_taken( $content, $results ) {
	$date = date_i18n( get_option( 'date_format' ), strtotime( $results["time_taken"] ) );
	$content = str_replace( "%DATE_TAKEN%" , $date, $content);
	return $content;
}

/*
*	Replaces variable %CATEGORY_POINTS% with the points for that category
*
* Filter function that replaces variable %CATEGORY_POINTS% with the points from the category inside the variable tags. i.e. %CATEGORY_POINTS%category 1%/CATEGORY_POINTS%
*
* @since 4.0.0
* @param string $content The contents of the results page
* @param array $mlw_quiz_array The array of all the results from user taking the quiz
* @return string Returns the contents for the results page
*/
function qmn_variable_category_points($content, $mlw_quiz_array)
{
	$return_points = 0;
	while (strpos($content, '%CATEGORY_POINTS%') !== false || false !== strpos($content, '%CATEGORY_POINTS_'))
	{
		$return_points = 0;
		preg_match("~%CATEGORY_POINTS%(.*?)%/CATEGORY_POINTS%~i",$content,$answer_text);
                if(empty($answer_text)){
                    $category_name = mlw_qmn_get_string_between($content, '%CATEGORY_POINTS_', '%');
                }else{
                    $category_name = $answer_text[1];
                }
                
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if ($answer["category"] == $category_name)
			{
				$return_points += $answer["points"];
			}
		}
                if(empty($answer_text)){
                    $content = str_replace( '%CATEGORY_POINTS_'.$category_name.'%' , $return_points, $content);
                }else{
                    $content = str_replace( $answer_text[0] , $return_points, $content);
                }		
	}
	return $content;
}

/*
*	Replaces variable %CATEGORY_POINTS% with the average points for that category
*
* Filter function that replaces variable %CATEGORY_POINTS% with the average points from the category inside the variable tags. i.e. %CATEGORY_POINTS%category 1%/CATEGORY_POINTS%
*
* @since 4.0.0
* @param string $content The contents of the results page
* @param array $mlw_quiz_array The array of all the results from user taking the quiz
* @return string Returns the contents for the results page
*/
function qmn_variable_average_category_points( $content, $mlw_quiz_array ) {
	$return_points = 0;
	while ( strpos( $content, '%AVERAGE_CATEGORY_POINTS%' ) !== false || false !== strpos($content, '%AVERAGE_CATEGORY_POINTS_') ) {
		$return_points = 0;
		$total_questions = 0;
		preg_match( "~%AVERAGE_CATEGORY_POINTS%(.*?)%/AVERAGE_CATEGORY_POINTS%~i", $content, $answer_text );
                if(empty($answer_text)){
                    $category_name = mlw_qmn_get_string_between($content, '%AVERAGE_CATEGORY_POINTS_', '%');
                }else{
                    $category_name = $answer_text[1];
                }
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( $answer["category"] == $category_name ) {
				$total_questions += 1;
				$return_points += $answer["points"];
			}
		}
		if ( $total_questions !== 0 ) {
			$return_points = round( $return_points / $total_questions, 2 );
		} else {
			$return_points = 0;
		}
                if(empty($answer_text)){
                    $content = str_replace( '%AVERAGE_CATEGORY_POINTS_'.$category_name.'%' , $return_points, $content);
                }else{
                    $content = str_replace( $answer_text[0] , $return_points, $content);
                }		
	}
	return $content;
}

/*
*	Replaces variable %CATEGORY_SCORE% with the score for that category
*
* Filter function that replaces variable %CATEGORY_SCORE% with the score from the category inside the variable tags. i.e. %CATEGORY_SCORE%category 1%/CATEGORY_SCORE%
*
* @since 4.0.0
* @param string $content The contents of the results page
* @param array $mlw_quiz_array The array of all the results from user taking the quiz
* @return string Returns the contents for the results page
*/
function qmn_variable_category_score($content, $mlw_quiz_array)
{
	$return_score = 0;
	$total_questions = 0;
	$amount_correct = 0;
	while (strpos($content, '%CATEGORY_SCORE%') !== false || false !== strpos($content, '%CATEGORY_SCORE_'))
	{            
		$return_score = 0;
		$total_questions = 0;
		$amount_correct = 0;
		preg_match("~%CATEGORY_SCORE%(.*?)%/CATEGORY_SCORE%~i",$content,$answer_text);
                if(empty($answer_text)){
                    $category_name = mlw_qmn_get_string_between($content, '%CATEGORY_SCORE_', '%');
                }else{
                    $category_name = $answer_text[1];
                }
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if ($answer["category"] == $category_name)
			{
				$total_questions += 1;
				if ($answer["correct"] == 'correct')
				{
					$amount_correct += 1;
				}
			}
		}
		if ($total_questions != 0)
		{
			$return_score = round((($amount_correct/$total_questions)*100), 2);
		}
		else
		{
			$return_score = 0;
		}
                
                if(empty($answer_text)){
                    $content = str_replace( '%CATEGORY_SCORE_'.$category_name.'%' , $return_score, $content);
                }else{
                    $content = str_replace( $answer_text[0] , $return_score, $content);
                }	
		
	}
	return $content;
}

/*
*	Replaces variable %CATEGORY_AVERAGE_SCORE% with the average score for all categories
*
* Filter function that replaces variable %CATEGORY_AVERAGE_SCORE% with the score from all categories.
*
* @since 4.0.0
* @param string $content The contents of the results page
* @param array $mlw_quiz_array The array of all the results from user taking the quiz
* @return string Returns the contents for the results page
*/
function qmn_variable_category_average_score($content, $mlw_quiz_array)
{
	$return_score = 0;
	$total_categories = 0;
	$total_score = 0;
	$category_scores = array();
	while (strpos($content, '%CATEGORY_AVERAGE_SCORE%') !== false)
	{
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if (!isset($category_scores[$answer["category"]]['total_questions']))
			{
				$category_scores[$answer["category"]]['total_questions'] = 0;
			}
			if (!isset($category_scores[$answer["category"]]['amount_correct']))
			{
				$category_scores[$answer["category"]]['amount_correct'] = 0;
			}
			$category_scores[$answer["category"]]['total_questions'] += 1;
			if ($answer["correct"] == 'correct')
			{
				$category_scores[$answer["category"]]['amount_correct'] += 1;
			}
		}
		foreach($category_scores as $category)
		{
			$total_score += $category["amount_correct"]/$category["total_questions"];
			$total_categories += 1;
		}
		if ($total_categories != 0)
		{
			$return_score = round((($total_score/$total_categories)*100), 2);
		}
		else
		{
			$return_score = 0;
		}
		$content = str_replace( "%CATEGORY_AVERAGE_SCORE%" , $return_score, $content);
	}
	return $content;
}

/*
*	Replaces variable %CATEGORY_AVERAGE_POINTS% with the average points for all categories
*
* Filter function that replaces variable %CATEGORY_AVERAGE_POINTS% with the points from all categories.
*
* @since 4.0.0
* @param string $content The contents of the results page
* @param array $mlw_quiz_array The array of all the results from user taking the quiz
* @return string Returns the contents for the results page
*/
function qmn_variable_category_average_points($content, $mlw_quiz_array)
{
	$return_score = 0;
	$total_categories = 0;
	$total_points = 0;
	$category_scores = array();
	while (strpos($content, '%CATEGORY_AVERAGE_POINTS%') !== false)
	{
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if (!isset($category_scores[$answer["category"]]['points']))
			{
				$category_scores[$answer["category"]]['points'] = 0;
			}
			$category_scores[$answer["category"]]['points'] += $answer["points"];
		}
		foreach($category_scores as $category)
		{
			$total_points += $category["points"];
			$total_categories += 1;
		}
		$return_score = $total_points/$total_categories;
		$content = str_replace( '%CATEGORY_AVERAGE_POINTS%' , $return_score, $content);
	}
	return $content;
}
?>
