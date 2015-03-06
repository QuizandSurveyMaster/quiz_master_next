<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
This function creates the leaderboard from the shortcode.
*/
function mlw_quiz_leaderboard_shortcode($atts)
{
	extract(shortcode_atts(array(
		'mlw_quiz' => 0
	), $atts));
	$mlw_quiz_id = $mlw_quiz;
	$mlw_quiz_leaderboard_display = "";


	global $wpdb;
	$sql = "SELECT * FROM " . $wpdb->prefix . "mlw_quizzes" . " WHERE quiz_id=".$mlw_quiz_id." AND deleted='0'";
	$mlw_quiz_options = $wpdb->get_results($sql);
	foreach($mlw_quiz_options as $mlw_eaches) {
		$mlw_quiz_options = $mlw_eaches;
		break;
	}
	$sql = "SELECT * FROM " . $wpdb->prefix . "mlw_results WHERE quiz_id=".$mlw_quiz_id." AND deleted='0'";
	if ($mlw_quiz_options->system == 0)
	{
		$sql .= " ORDER BY correct_score DESC";
	}
	if ($mlw_quiz_options->system == 1)
	{
		$sql .= " ORDER BY point_score DESC";
	}
	$sql .= " LIMIT 10";
	$mlw_result_data = $wpdb->get_results($sql);

	$mlw_quiz_leaderboard_display = $mlw_quiz_options->leaderboard_template;
	$mlw_quiz_leaderboard_display = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_quiz_leaderboard_display);

	$leader_count = 0;
	foreach($mlw_result_data as $mlw_eaches) {
		$leader_count++;
		if ($leader_count == 1) {$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
		if ($leader_count == 2) {$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
		if ($leader_count == 3) {$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
		if ($leader_count == 4) {$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
		if ($leader_count == 5) {$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
		if ($mlw_quiz_options->system == 0)
		{
			if ($leader_count == 1) {$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 2) {$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 3) {$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 4) {$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 5) {$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
		}
		if ($mlw_quiz_options->system == 1)
		{
			if ($leader_count == 1) {$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 2) {$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 3) {$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 4) {$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
			if ($leader_count == 5) {$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
		}
	}
	$mlw_quiz_leaderboard_display = str_replace( "%QUIZ_NAME%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
	$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);

	return $mlw_quiz_leaderboard_display;
}
?>
