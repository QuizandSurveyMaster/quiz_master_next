<?php
/*
These functions are used for installing and uninstalling all necessary databases, options, page, etc.. for the plugin to work properly.
*/
function mlw_quiz_activate()
{
	global $wpdb;

	$table_name = $wpdb->prefix . "mlw_quizzes";

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 

	{

		$sql = "CREATE TABLE " . $table_name . " (

			quiz_id mediumint(9) NOT NULL AUTO_INCREMENT,

			quiz_name TEXT NOT NULL,
			
			message_before TEXT NOT NULL,
			
			message_after TEXT NOT NULL,
			
			message_comment TEXT NOT NULL,
			
			message_end_template TEXT NOT NULL,

			user_email_template TEXT NOT NULL,
			
			admin_email_template TEXT NOT NULL,
			
			submit_button_text TEXT NOT NULL,
			
			name_field_text TEXT NOT NULL,
			
			business_field_text TEXT NOT NULL,
			
			email_field_text TEXT NOT NULL,
			
			phone_field_text TEXT NOT NULL,
			
			comment_field_text TEXT NOT NULL,
			
			email_from_text TEXT NOT NULL,
			
			question_answer_template TEXT NOT NULL,
			
			leaderboard_template TEXT NOT NULL,

			system INT NOT NULL,
			
			randomness_order INT NOT NULL,
			
			loggedin_user_contact INT NOT NULL,

			show_score INT NOT NULL,

			send_user_email INT NOT NULL,

			send_admin_email INT NOT NULL,
			
			contact_info_location INT NOT NULL,

			user_name INT NOT NULL,

			user_comp INT NOT NULL,

			user_email INT NOT NULL,

			user_phone INT NOT NULL,

			admin_email TEXT NOT NULL,
			
			comment_section INT NOT NULL,
			
			question_from_total INT NOT NULL,
			
			total_user_tries INT NOT NULL,
			
			total_user_tries_text TEXT NOT NULL,
			
			certificate_template TEXT NOT NULL,
			
			social_media INT NOT NULL,
			
			social_media_text TEXT NOT NULL,
			
			pagination INT NOT NULL,
			
			pagination_text TEXT NOT NULL,
			
			timer_limit INT NOT NULL,
			
			quiz_stye TEXT NOT NULL,
			
			question_numbering INT NOT NULL,
			
			quiz_settings TEXT NOT NULL,
			
			theme_selected TEXT NOT NULL,
			
			last_activity DATETIME NOT NULL,

			quiz_views INT NOT NULL,

			quiz_taken INT NOT NULL,

			deleted INT NOT NULL,

			PRIMARY KEY  (quiz_id)

		)
		
		CHARACTER SET utf8";

		$results = $wpdb->query( $sql );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	global $wpdb;

	$table_name = $wpdb->prefix . "mlw_questions";

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 

	{

		$sql = "CREATE TABLE " . $table_name . " (

			question_id mediumint(9) NOT NULL AUTO_INCREMENT,

			quiz_id INT NOT NULL,

			question_name TEXT NOT NULL,
			
			answer_array TEXT NOT NULL,
			
			answer_one TEXT NOT NULL,
			
			answer_one_points INT NOT NULL,

			answer_two TEXT NOT NULL,
			
			answer_two_points INT NOT NULL,

			answer_three TEXT NOT NULL,

			answer_three_points INT NOT NULL,

			answer_four TEXT NOT NULL,

			answer_four_points INT NOT NULL,

			answer_five TEXT NOT NULL,

			answer_five_points INT NOT NULL,

			answer_six TEXT NOT NULL,

			answer_six_points INT NOT NULL,

			correct_answer INT NOT NULL,
			
			question_answer_info TEXT NOT NULL,
			
			comments INT NOT NULL,
			
			hints TEXT NOT NULL,
			
			question_order INT NOT NULL,
			
			question_type INT NOT NULL,
			
			question_settings TEXT NOT NULL,

			deleted INT NOT NULL,

			PRIMARY KEY  (question_id)

		)
		
		CHARACTER SET utf8";

		$results = $wpdb->query( $sql );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	global $wpdb;

	$table_name = $wpdb->prefix . "mlw_results";

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 

	{

		$sql = "CREATE TABLE " . $table_name . " (

			result_id mediumint(9) NOT NULL AUTO_INCREMENT,

			quiz_id INT NOT NULL,

			quiz_name TEXT NOT NULL,

			quiz_system INT NOT NULL,

			point_score INT NOT NULL,

			correct_score INT NOT NULL,

			correct INT NOT NULL,

			total INT NOT NULL,

			name TEXT NOT NULL,

			business TEXT NOT NULL,

			email TEXT NOT NULL,

			phone TEXT NOT NULL,
			
			user INT NOT NULL,

			time_taken TEXT NOT NULL,
			
			time_taken_real DATETIME NOT NULL,
			
			quiz_results TEXT NOT NULL,

			deleted INT NOT NULL,

			PRIMARY KEY  (result_id)

		)
		
		CHARACTER SET utf8";

		$results = $wpdb->query( $sql );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	else
	
	{
		if($wpdb->get_var("SHOW COLUMNS FROM ".$table_name." LIKE 'time_taken_real'") != "time_taken_real")
		{
			$sql = "ALTER TABLE ".$table_name." ADD time_taken_real DATETIME NOT NULL AFTER time_taken";
			
			$results = $wpdb->query( $sql );
			
			$sql = "ALTER TABLE ".$table_name." ADD quiz_results TEXT NOT NULL AFTER time_taken_real";
			
			$results = $wpdb->query( $sql );
			
			$update_sql = "UPDATE ".$table_name." SET quiz_results='This quiz was taken before this plugin began saving answers.', hints=''";
			
			$results = $wpdb->query( $update_sql );
		}		
	}
	
	global $wpdb;

	$table_name = $wpdb->prefix . "mlw_qm_audit_trail";

	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 

	{
		//Creating the table ... fresh!

		$sql = "CREATE TABLE " . $table_name . " (

			trail_id mediumint(9) NOT NULL AUTO_INCREMENT,

			action_user TEXT NOT NULL,
			
			action TEXT NOT NULL,
			
			time TEXT NOT NULL,

			PRIMARY KEY  (trail_id)

		)
		
		CHARACTER SET utf8";

		$results = $wpdb->query( $sql );

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}

function mlw_quiz_deactivate()
{


}
?>