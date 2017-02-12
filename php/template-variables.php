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
			'comments' => $mlw_qm_quiz_comments,
			'certificate_link' => CERTIFICATE LINK
		);

*/
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
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_certificate_link',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_facebook_share',10,2);
add_filter('mlw_qmn_template_variable_results_page', 'qmn_variable_twitter_share',10,2);

add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_quiz_name',10,2);
add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_date',10,2);
add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_current_user',10,2);

function qmn_variable_facebook_share($content, $mlw_quiz_array)
{
	while (strpos($content, '%FACEBOOK_SHARE%') !== false)
	{
		wp_enqueue_script( 'qmn_quiz_social_share', plugins_url( '../js/qmn_social_share.js' , __FILE__ ) );
		$settings = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '483815031724529';
		if (isset($settings['facebook_app_id']))
		{
			$facebook_app_id = esc_js( $settings['facebook_app_id'] );
		}

		global $wpdb;
		$qmn_quiz_options = $wpdb->get_row($wpdb->prepare('SELECT social_media_text FROM '.$wpdb->prefix.'mlw_quizzes WHERE quiz_id=%d AND deleted=0', $mlw_quiz_array['quiz_id']));

		//Load Social Media Text
		$qmn_social_media_text = "";
		if (is_serialized($qmn_quiz_options->social_media_text) && is_array(@unserialize($qmn_quiz_options->social_media_text)))
		{
			$qmn_social_media_text = @unserialize($qmn_quiz_options->social_media_text);
		}
		else
		{
			$qmn_social_media_text = array(
				'twitter' => $qmn_quiz_options->social_media_text,
				'facebook' => $qmn_quiz_options->social_media_text
			);
		}

		$qmn_social_media_text["facebook"] = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text["facebook"], $mlw_quiz_array);
		$social_display = "<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('facebook', '".esc_js($qmn_social_media_text["facebook"])."', '".esc_js($mlw_quiz_array["quiz_name"])."', '$facebook_app_id');\">Facebook</a>";
		$content = str_replace( "%FACEBOOK_SHARE%" , $social_display, $content);
	}
	return $content;
}

function qmn_variable_twitter_share($content, $mlw_quiz_array)
{
	while (strpos($content, '%TWITTER_SHARE%') !== false)
	{
		wp_enqueue_script( 'qmn_quiz_social_share', plugins_url( '../js/qmn_social_share.js' , __FILE__ ) );

		global $wpdb;
		$qmn_quiz_options = $wpdb->get_row($wpdb->prepare('SELECT social_media_text FROM '.$wpdb->prefix.'mlw_quizzes WHERE quiz_id=%d AND deleted=0', $mlw_quiz_array['quiz_id']));

		//Load Social Media Text
		$qmn_social_media_text = "";
		if (is_serialized($qmn_quiz_options->social_media_text) && is_array(@unserialize($qmn_quiz_options->social_media_text)))
		{
			$qmn_social_media_text = @unserialize($qmn_quiz_options->social_media_text);
		}
		else
		{
			$qmn_social_media_text = array(
				'twitter' => $qmn_quiz_options->social_media_text,
				'facebook' => $qmn_quiz_options->social_media_text
			);
		}

		$qmn_social_media_text["twitter"] = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text["twitter"], $mlw_quiz_array);
		$social_display = "<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('twitter', '".esc_js($qmn_social_media_text["twitter"])."', '".esc_js($mlw_quiz_array["quiz_name"])."');\">Twitter</a>";
		$content = str_replace( "%TWITTER_SHARE%" , $social_display, $content);
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
function mlw_qmn_variable_question_answers($content, $mlw_quiz_array)
{
	while (strpos($content, '%QUESTIONS_ANSWERS%') !== false)
	{
		global $wpdb;
		$display = '';
		$qmn_question_answer_template = $wpdb->get_var( $wpdb->prepare( "SELECT question_answer_template FROM " . $wpdb->prefix . "mlw_quizzes WHERE quiz_id=%d", $mlw_quiz_array['quiz_id'] ) );
		$qmn_questions_sql = $wpdb->get_results( $wpdb->prepare( "SELECT question_id, question_answer_info FROM " . $wpdb->prefix . "mlw_questions WHERE quiz_id=%d", $mlw_quiz_array['quiz_id'] ) );
		$qmn_questions = array();
		foreach($qmn_questions_sql as $question)
		{
			$qmn_questions[$question->question_id] = $question->question_answer_info;
		}
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if ( $answer["correct"] === "correct" ){
				$user_answer_class = "qmn_user_correct_answer";
				$question_answer_class = "qmn_question_answer_correct";
			} else {
				$user_answer_class = "qmn_user_incorrect_answer";
				$question_answer_class = "qmn_question_answer_incorrect";
			}
			$mlw_question_answer_display = htmlspecialchars_decode($qmn_question_answer_template, ENT_QUOTES);
			$mlw_question_answer_display = str_replace( "%QUESTION%" , htmlspecialchars_decode($answer[0], ENT_QUOTES), $mlw_question_answer_display);
			$mlw_question_answer_display = str_replace( "%USER_ANSWER%" , "<span class='$user_answer_class'>".htmlspecialchars_decode($answer[1], ENT_QUOTES).'</span>', $mlw_question_answer_display);
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
function mlw_qmn_variable_date($content, $mlw_quiz_array)
{
	$content = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $content);
	return $content;
}

function mlw_qmn_variable_date_taken( $content, $mlw_quiz_array ) {
	$content = str_replace( "%DATE_TAKEN%" , date("m/d/Y", strtotime( $mlw_quiz_array["time_taken"] ) ), $content);
	return $content;
}

function mlw_qmn_variable_certificate_link($content, $mlw_quiz_array)
{
	while (strpos($content, '%CERTIFICATE_LINK%') != false)
	{
		$content = str_replace( "%CERTIFICATE_LINK%" , $mlw_quiz_array["certificate_link"], $content);
	}
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
	while (strpos($content, '%CATEGORY_POINTS%') !== false)
	{
		$return_points = 0;
		preg_match("~%CATEGORY_POINTS%(.*?)%/CATEGORY_POINTS%~i",$content,$answer_text);
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if ($answer["category"] == $answer_text[1])
			{
				$return_points += $answer["points"];
			}
		}
		$content = str_replace( $answer_text[0] , $return_points, $content);
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
	while ( strpos( $content, '%AVERAGE_CATEGORY_POINTS%' ) !== false ) {
		$return_points = 0;
		$total_questions = 0;
		preg_match( "~%AVERAGE_CATEGORY_POINTS%(.*?)%/AVERAGE_CATEGORY_POINTS%~i", $content, $answer_text );
		foreach ( $mlw_quiz_array['question_answers_array'] as $answer ) {
			if ( $answer["category"] == $answer_text[1] ) {
				$total_questions += 1;
				$return_points += $answer["points"];
			}
		}
		if ( $total_questions !== 0 ) {
			$return_points = round( $return_points / $total_questions, 2 );
		} else {
			$return_points = 0;
		}
		$content = str_replace( $answer_text[0], $return_points, $content );
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
	while (strpos($content, '%CATEGORY_SCORE%') !== false)
	{
		$return_score = 0;
		$total_questions = 0;
		$amount_correct = 0;
		preg_match("~%CATEGORY_SCORE%(.*?)%/CATEGORY_SCORE%~i",$content,$answer_text);
		foreach ($mlw_quiz_array['question_answers_array'] as $answer)
		{
			if ($answer["category"] == $answer_text[1])
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

		$content = str_replace( $answer_text[0] , $return_score, $content);
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
