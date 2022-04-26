<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles Legacy data
 *
 * @since 4.7.1
 */
class QSM_Legacy {

	/**
	 * Main Constructor
	 *
	 * @uses QSM_Legacy::add_hooks
	 * @since 4.7.1
	 */
	function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds the various class functions to hooks and filters
	 *
	 * @since 4.7.1
	 */
	public function add_hooks() {

		// Add wc-admin report tables to list of WooCommerce tables.
		add_filter( 'qsm_get_db_tables', array( __CLASS__, 'get_tables' ) );
	}

	/**
	 * Return a list of tables.
	 *
	 * @return array QSM tables.
	 */
	public static function get_tables( $tables ) {
		global $wpdb;
		return array_merge(
			$tables, array(
			"{$wpdb->prefix}mlw_quizzes",
			"{$wpdb->prefix}mlw_questions",
			"{$wpdb->prefix}mlw_question_terms",
			"{$wpdb->prefix}mlw_results",
			"{$wpdb->prefix}mlw_qm_audit_trail",
			"{$wpdb->prefix}mlw_themes",
			"{$wpdb->prefix}mlw_quiz_theme_settings",
			)
		);
	}
	
	/**
	 * Installs the plugin and its database tables
	 *
	 * @since 4.7.1
	 */
	public static function install() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		

		$quiz_table_name                 = $wpdb->prefix . 'mlw_quizzes';
		$question_table_name             = $wpdb->prefix . 'mlw_questions';
		$results_table_name              = $wpdb->prefix . 'mlw_results';
		$audit_table_name                = $wpdb->prefix . 'mlw_qm_audit_trail';
		$themes_table_name               = $wpdb->prefix . 'mlw_themes';
		$quiz_themes_settings_table_name = $wpdb->prefix . 'mlw_quiz_theme_settings';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_table_name'" ) != $quiz_table_name ) {
			$sql = "CREATE TABLE $quiz_table_name (
  			quiz_id mediumint(9) NOT NULL AUTO_INCREMENT,
  			quiz_name TEXT NOT NULL,
  			message_before TEXT NOT NULL,
  			message_after LONGTEXT NOT NULL,
  			message_comment TEXT NOT NULL,
  			message_end_template TEXT NOT NULL,
  			user_email_template LONGTEXT NOT NULL,
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
  			quiz_system INT NOT NULL,
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
  			require_log_in INT NOT NULL,
  			require_log_in_text TEXT NOT NULL,
  			limit_total_entries INT NOT NULL,
  			limit_total_entries_text TEXT NOT NULL,
  			scheduled_timeframe TEXT NOT NULL,
  			scheduled_timeframe_text TEXT NOT NULL,
  			disable_answer_onselect INT NOT NULL,
  			ajax_show_correct INT NOT NULL,
  			quiz_views INT NOT NULL,
  			quiz_taken INT NOT NULL,
  			deleted INT NOT NULL,
            quiz_author_id INT NOT NULL,
  			PRIMARY KEY  (quiz_id)
  		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			// enabling multiple category for fresh installation
			$multiple_category = get_option( 'qsm_multiple_category_enabled' );
			if ( ! $multiple_category ) {
				add_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
			}
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$question_table_name'" ) != $question_table_name ) {
			$sql = "CREATE TABLE $question_table_name (
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
  			question_type_new TEXT NOT NULL,
  			question_settings TEXT NOT NULL,
  			category TEXT NOT NULL,
  			deleted INT NOT NULL,
  			deleted_question_bank INT NOT NULL,
  			PRIMARY KEY  (question_id)
  		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$results_table_name'" ) != $results_table_name ) {
			$sql = "CREATE TABLE $results_table_name (
  			result_id mediumint(9) NOT NULL AUTO_INCREMENT,
  			quiz_id INT NOT NULL,
  			quiz_name TEXT NOT NULL,
  			quiz_system INT NOT NULL,
  			point_score FLOAT NOT NULL,
  			correct_score INT NOT NULL,
  			correct INT NOT NULL,
  			total INT NOT NULL,
  			name TEXT NOT NULL,
  			business TEXT NOT NULL,
  			email TEXT NOT NULL,
  			phone TEXT NOT NULL,
  			user INT NOT NULL,
  			user_ip TEXT NOT NULL,
  			time_taken TEXT NOT NULL,
  			time_taken_real DATETIME NOT NULL,
  			quiz_results MEDIUMTEXT NOT NULL,
  			deleted INT NOT NULL,
                        unique_id varchar(255) NOT NULL,
                        form_type INT NOT NULL,
  			PRIMARY KEY  (result_id)
  		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$audit_table_name'" ) != $audit_table_name ) {
			$sql = "CREATE TABLE $audit_table_name (
  			trail_id mediumint(9) NOT NULL AUTO_INCREMENT,
  			action_user TEXT NOT NULL,
  			action TEXT NOT NULL,
			quiz_id TEXT NOT NULL,
			quiz_name TEXT NOT NULL,
  			form_data TEXT NOT NULL,
  			time TEXT NOT NULL,
  			PRIMARY KEY  (trail_id)
  		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}mlw_question_terms'" ) != "{$wpdb->prefix}mlw_question_terms" ) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}mlw_question_terms` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`question_id` int(11) DEFAULT '0',
			`quiz_id` int(11) DEFAULT '0',
			`term_id` int(11) DEFAULT '0',
			`taxonomy` varchar(50) DEFAULT NULL,
			PRIMARY KEY (`id`),
			KEY `question_id` (`question_id`),
			KEY `quiz_id` (`quiz_id`),
			KEY `term_id` (`term_id`),
			KEY `taxonomy` (`taxonomy`)
		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$themes_table_name'" ) != $themes_table_name ) {
			$sql = "CREATE TABLE $themes_table_name (
  			id mediumint(9) NOT NULL AUTO_INCREMENT,
  			theme TEXT NOT NULL,
			theme_name TEXT NOT NULL,
			default_settings TEXT NOT NULL,
			theme_active BOOLEAN NOT NULL,
  			PRIMARY KEY  (id)
  		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_themes_settings_table_name'" ) != $quiz_themes_settings_table_name ) {
			$sql = "CREATE TABLE $quiz_themes_settings_table_name (
  			id mediumint(9) NOT NULL AUTO_INCREMENT,
  			theme_id mediumint(9) NOT NULL,
        quiz_id mediumint(9) NOT NULL,
        quiz_theme_settings TEXT NOT NULL,
        active_theme BOOLEAN NOT NULL,
  			PRIMARY KEY  (id)
  		) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}

		global $mlwQuizMasterNext;
		$mlwQuizMasterNext->register_quiz_post_types();
		// Will be removed
		// Create a folder in upload folder
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/qsm_themes';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0700 );
		}
		flush_rewrite_rules();
	}

}

$QSM_Legacy = new QSM_Legacy();
