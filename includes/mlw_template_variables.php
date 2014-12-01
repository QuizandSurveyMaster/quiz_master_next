<?php
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
add_filter('mlw_qmn_template_variable_results_page', 'mlw_qmn_variable_date',10,2);

add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_quiz_name',10,2);
add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_date',10,2);
add_filter('mlw_qmn_template_variable_quiz_page', 'mlw_qmn_variable_current_user',10,2);
function mlw_qmn_variable_point_score($content, $mlw_quiz_array)
{
	$content = str_replace( "%POINT_SCORE%" , $mlw_quiz_array["total_point"], $content);
	return $content;
}
function mlw_qmn_variable_average_point($content, $mlw_quiz_array)
{
	$mlw_average_points = $mlw_quiz_array["total_point"]/$mlw_quiz_array["total_questions"];
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
	$content = str_replace( "%QUESTIONS_ANSWERS%" , $mlw_question_answers, $content);
	return $content;
}
function mlw_qmn_variable_comments($content, $mlw_quiz_array)
{
	$content = str_replace( "%COMMENT_SECTION%" , $mlw_qm_quiz_comments, $content);
	return $content;
}
function mlw_qmn_variable_timer($content, $mlw_quiz_array)
{
	$content = str_replace( "%TIMER%" , $mlw_qmn_timer, $content);
	return $content;
}
function mlw_qmn_variable_date($content, $mlw_quiz_array)
{
	$content = str_replace( "%CURRENT_DATE%" , date("F jS Y"), $content);
	return $content;
}
?>
