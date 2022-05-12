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
		$upload      = wp_upload_dir();
		$upload_dir  = $upload['basedir'];
		$upload_dir  = $upload_dir . '/qsm_themes';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0700 );
		}
		flush_rewrite_rules();
	}

	public static function update() {
		global $wpdb, $mlwQuizMasterNext;
		$data = $mlwQuizMasterNext->version;
		if ( ! get_option( 'qmn_original_version' ) ) {
			add_option( 'qmn_original_version', $data );
		}
		if ( get_option( 'mlw_quiz_master_version' ) != $data ) {
			$charset_collate = $wpdb->get_charset_collate();
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

			$table_name = $wpdb->prefix . 'mlw_quizzes';
			// Update 0.5
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'comment_section'" ) != 'comment_section' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD comment_field_text TEXT NOT NULL AFTER phone_field_text';
				$results     = $wpdb->query( $sql );
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD comment_section INT NOT NULL AFTER admin_email';
				$results     = $wpdb->query( $sql );
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD message_comment TEXT NOT NULL AFTER message_after';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET comment_field_text='Comments', comment_section=1, message_comment='Enter You Text Here'";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 0.9.4
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'randomness_order'" ) != 'randomness_order' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD randomness_order INT NOT NULL AFTER system';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET randomness_order=0';
				$results     = $wpdb->query( $update_sql );
			}

			// Update 0.9.5
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_answer_template'" ) != 'question_answer_template' ) {
				$sql                         = 'ALTER TABLE ' . $table_name . ' ADD question_answer_template TEXT NOT NULL AFTER comment_field_text';
				$results                     = $wpdb->query( $sql );
				$mlw_question_answer_default = '%QUESTION%<br /> Answer Provided: %USER_ANSWER%<br /> Correct Answer: %CORRECT_ANSWER%<br /> Comments Entered: %USER_COMMENTS%<br />';
				$update_sql                  = 'UPDATE ' . $table_name . " SET question_answer_template='" . $mlw_question_answer_default . "'";
				$results                     = $wpdb->query( $update_sql );
			}

			// Update 0.9.6
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'contact_info_location'" ) != 'contact_info_location' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD contact_info_location INT NOT NULL AFTER send_admin_email';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET contact_info_location=0';
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.0
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'email_from_text'" ) != 'email_from_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD email_from_text TEXT NOT NULL AFTER comment_field_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET email_from_text='Wordpress'";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.3.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'loggedin_user_contact'" ) != 'loggedin_user_contact' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD loggedin_user_contact INT NOT NULL AFTER randomness_order';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET loggedin_user_contact=0';
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.5.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_from_total'" ) != 'question_from_total' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_from_total INT NOT NULL AFTER comment_section';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET question_from_total=0';
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.6.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'total_user_tries'" ) != 'total_user_tries' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD total_user_tries INT NOT NULL AFTER question_from_total';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET total_user_tries=0';
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'total_user_tries_text'" ) != 'total_user_tries_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD total_user_tries_text TEXT NOT NULL AFTER total_user_tries';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET total_user_tries_text='Enter Your Text Here'";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.8.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'message_end_template'" ) != 'message_end_template' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD message_end_template TEXT NOT NULL AFTER message_comment';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET message_end_template=''";
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'certificate_template'" ) != 'certificate_template' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD certificate_template TEXT NOT NULL AFTER total_user_tries_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET certificate_template='Enter your text here!'";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.9.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'social_media'" ) != 'social_media' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD social_media INT NOT NULL AFTER certificate_template';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET social_media='0'";
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'social_media_text'" ) != 'social_media_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD social_media_text TEXT NOT NULL AFTER social_media';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET social_media_text='I just score a %CORRECT_SCORE%% on %QUIZ_NAME%!'";
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'pagination'" ) != 'pagination' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD pagination INT NOT NULL AFTER social_media_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET pagination=0';
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'pagination_text'" ) != 'pagination_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD pagination_text TEXT NOT NULL AFTER pagination';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET pagination_text='Next'";
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'timer_limit'" ) != 'timer_limit' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD timer_limit INT NOT NULL AFTER pagination_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . ' SET timer_limit=0';
				$results     = $wpdb->query( $update_sql );
			}

			// Update 2.1.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'quiz_stye'" ) != 'quiz_stye' ) {
				$sql                 = 'ALTER TABLE ' . $table_name . ' ADD quiz_stye TEXT NOT NULL AFTER timer_limit';
				$results             = $wpdb->query( $sql );
				$mlw_style_default   = '
  				div.mlw_qmn_quiz input[type=radio],
  				div.mlw_qmn_quiz input[type=submit],
  				div.mlw_qmn_quiz label {
  					cursor: pointer;
  				}
  				div.mlw_qmn_quiz input:not([type=submit]):focus,
  				div.mlw_qmn_quiz textarea:focus {
  					background: #eaeaea;
  				}
  				div.mlw_qmn_quiz {
  					text-align: left;
  				}
  				div.quiz_section {

  				}
  				div.mlw_qmn_timer {
  					position:fixed;
  					top:200px;
  					right:0px;
  					width:130px;
  					color:#00CCFF;
  					border-radius: 15px;
  					background:#000000;
  					text-align: center;
  					padding: 15px 15px 15px 15px
  				}
  				div.mlw_qmn_quiz input[type=submit],
  				a.mlw_qmn_quiz_link
  				{
  					    border-radius: 4px;
  					    position: relative;
  					    background-image: linear-gradient(#fff,#dedede);
  						background-color: #eee;
  						border: #ccc solid 1px;
  						color: #333;
  						text-shadow: 0 1px 0 rgba(255,255,255,.5);
  						box-sizing: border-box;
  					    display: inline-block;
  					    padding: 5px 5px 5px 5px;
     						margin: auto;
  				}';
				$update_sql          = 'UPDATE ' . $table_name . " SET quiz_stye='" . $mlw_style_default . "'";
				$results             = $wpdb->query( $update_sql );
			}

			// Update 2.2.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_numbering'" ) != 'question_numbering' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_numbering INT NOT NULL AFTER quiz_stye';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET question_numbering='0'";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 2.8.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'quiz_settings'" ) != 'quiz_settings' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD quiz_settings TEXT NOT NULL AFTER question_numbering';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET quiz_settings=''";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 3.0.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'theme_selected'" ) != 'theme_selected' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD theme_selected TEXT NOT NULL AFTER quiz_settings';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET theme_selected='default'";
				$results     = $wpdb->query( $update_sql );
			}

			// Update 3.3.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'last_activity'" ) != 'last_activity' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD last_activity DATETIME NOT NULL AFTER theme_selected';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET last_activity='%s'", gmdate( 'Y-m-d H:i:s' ) );
				$results     = $wpdb->query( $update_sql );
			}

			// Update 3.5.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'require_log_in'" ) != 'require_log_in' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD require_log_in INT NOT NULL AFTER last_activity';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET require_log_in='%d'", '0' );
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'require_log_in_text'" ) != 'require_log_in_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD require_log_in_text TEXT NOT NULL AFTER require_log_in';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( 'UPDATE ' . $table_name . " SET require_log_in_text='%s'", 'Enter Text Here' );
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'limit_total_entries'" ) != 'limit_total_entries' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD limit_total_entries INT NOT NULL AFTER require_log_in_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET limit_total_entries='%d'", '0' );
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'limit_total_entries_text'" ) != 'limit_total_entries_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD limit_total_entries_text TEXT NOT NULL AFTER limit_total_entries';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET limit_total_entries_text='%s'", 'Enter Text Here' );
				$results     = $wpdb->query( $update_sql );
			}

			// Update 7.3.8
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'quiz_author_id'" ) != 'quiz_author_id' ) {
				$sql     = 'ALTER TABLE ' . $table_name . ' ADD quiz_author_id TEXT NOT NULL AFTER deleted';
				$results = $wpdb->query( $sql );
			}

			// Update 3.7.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'scheduled_timeframe'" ) != 'scheduled_timeframe' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD scheduled_timeframe TEXT NOT NULL AFTER limit_total_entries_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET scheduled_timeframe=''";
				$results     = $wpdb->query( stripslashes( esc_sql( $update_sql ) ) );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'scheduled_timeframe_text'" ) != 'scheduled_timeframe_text' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD scheduled_timeframe_text TEXT NOT NULL AFTER scheduled_timeframe';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET scheduled_timeframe_text='%s'", 'Enter Text Here' );
				$results     = $wpdb->query( $update_sql );
			}

			// Update 4.3.0
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'disable_answer_onselect'" ) != 'disable_answer_onselect' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD disable_answer_onselect INT NOT NULL AFTER scheduled_timeframe_text';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET disable_answer_onselect=%d", '0' );
				$results     = $wpdb->query( $update_sql );
			}
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'ajax_show_correct'" ) != 'ajax_show_correct' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD ajax_show_correct INT NOT NULL AFTER disable_answer_onselect';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET ajax_show_correct=%d", '0' );
				$results     = $wpdb->query( $update_sql );
			}

			global $wpdb;
			$table_name = $wpdb->prefix . 'mlw_questions';
			// Update 0.5
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'comments'" ) != 'comments' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD comments INT NOT NULL AFTER correct_answer';
				$results     = $wpdb->query( $sql );
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD hints TEXT NOT NULL AFTER comments';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET comments=%d, hints=''", '1' );
				$results     = $wpdb->query( $update_sql );
			}
			// Update 0.8
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_order'" ) != 'question_order' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_order INT NOT NULL AFTER hints';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET question_order=%d", '0' );
				$results     = $wpdb->query( $update_sql );
			}

			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_type'" ) != 'question_type' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_type INT NOT NULL AFTER question_order';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET question_type=%d", '0' );
				$results     = $wpdb->query( $update_sql );
			}

			// Update 1.1.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_answer_info'" ) != 'question_answer_info' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_answer_info TEXT NOT NULL AFTER correct_answer';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET question_answer_info=''";
				$results     = $wpdb->query( stripslashes( esc_sql( $update_sql ) ) );
			}

			// Update 2.5.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'answer_array'" ) != 'answer_array' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD answer_array TEXT NOT NULL AFTER question_name';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET answer_array=''";
				$results     = $wpdb->query( stripslashes( esc_sql( $update_sql ) ) );
			}

			// Update 3.1.1
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_settings'" ) != 'question_settings' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_settings TEXT NOT NULL AFTER question_type';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET question_settings=''";
				$results     = $wpdb->query( stripslashes( esc_sql( $update_sql ) ) );
			}

			// Update 4.0.0
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'category'" ) != 'category' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD category TEXT NOT NULL AFTER question_settings';
				$results     = $wpdb->query( $sql );
				$update_sql  = 'UPDATE ' . $table_name . " SET category=''";
				$results     = $wpdb->query( stripslashes( esc_sql( $update_sql ) ) );
			}

			// Update 4.0.0
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'question_type_new'" ) != 'question_type_new' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD question_type_new TEXT NOT NULL AFTER question_type';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET question_type_new=%s", 'question_type' );
				$results     = $wpdb->query( $update_sql );
			}

			// Update 7.1.11
			$user_email_template_data = $wpdb->get_row( 'SHOW COLUMNS FROM ' . $wpdb->prefix . "mlw_quizzes LIKE 'user_email_template'" );
			if ( 'text' === $user_email_template_data->Type ) {
				$sql     = 'ALTER TABLE ' . $wpdb->prefix . 'mlw_quizzes  MODIFY user_email_template LONGTEXT';
				$results = $wpdb->query( $sql );
			}

			// Update 7.3.11
			$user_message_after_data = $wpdb->get_row( 'SHOW COLUMNS FROM ' . $wpdb->prefix . "mlw_quizzes LIKE 'message_after'" );
			if ( 'text' === $user_message_after_data->Type ) {
				$sql     = 'ALTER TABLE ' . $wpdb->prefix . 'mlw_quizzes MODIFY message_after LONGTEXT';
				$results = $wpdb->query( $sql );
			}

			// Update 2.6.1
			$results = $wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'mlw_qm_audit_trail CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;' );
			$results = $wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'mlw_questions CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci' );
			$results = $wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'mlw_quizzes CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci' );
			$results = $wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'mlw_results CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci' );

			global $wpdb;
			$table_name  = $wpdb->prefix . 'mlw_results';
			$audit_table = $wpdb->prefix . 'mlw_qm_audit_trail';

			// Update 2.6.4
			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $table_name . " LIKE 'user'" ) != 'user' ) {
				$sql         = 'ALTER TABLE ' . $table_name . ' ADD user INT NOT NULL AFTER phone';
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET user=%d", '0' );
				$results     = $wpdb->query( $update_sql );
			}

			// Update 4.7.0
			if ( $wpdb->get_var( "SHOW COLUMNS FROM $table_name LIKE 'user_ip'" ) != 'user_ip' ) {
				$sql         = "ALTER TABLE $table_name ADD user_ip TEXT NOT NULL AFTER user";
				$results     = $wpdb->query( $sql );
				$update_sql  = $wpdb->prepare( "UPDATE {$table_name} SET user_ip='%s'", 'Unknown' );
				$results     = $wpdb->query( $update_sql );
			}
			// Update 7.1.11
			$user_message_after_data = $wpdb->get_row( 'SHOW COLUMNS FROM ' . $wpdb->prefix . "mlw_results LIKE 'point_score'" );
			if ( 'FLOAT' != $user_message_after_data->Type ) {
				$results = $wpdb->query( 'ALTER TABLE ' . $wpdb->prefix . 'mlw_results MODIFY point_score FLOAT NOT NULL;' );
			}

			if ( $wpdb->get_var( 'SHOW COLUMNS FROM ' . $audit_table . " LIKE 'quiz_id'" ) != 'quiz_id' ) {
				$sql     = 'ALTER TABLE ' . $audit_table . ' ADD quiz_id TEXT NOT NULL AFTER action';
				$results = $wpdb->query( $sql );
				$sql     = 'ALTER TABLE ' . $audit_table . ' ADD quiz_name TEXT NOT NULL AFTER quiz_id';
				$results = $wpdb->query( $sql );
				$sql     = 'ALTER TABLE ' . $audit_table . ' ADD form_data TEXT NOT NULL AFTER quiz_name';
				$results = $wpdb->query( $sql );
			}
			// Update 5.0.0
			$settings = (array) get_option( 'qmn-settings', array() );
			if ( ! isset( $settings['results_details_template'] ) ) {
				$settings['results_details_template'] = '<h2>Quiz Results for %QUIZ_NAME%</h2>
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
         %QUESTIONS_ANSWERS%';
				update_option( 'qmn-settings', $settings );
			}

			update_option( 'mlw_quiz_master_version', $data );
		}
		if ( ! get_option( 'mlw_advert_shows' ) ) {
			add_option( 'mlw_advert_shows', 'true' );
		}
	}

}

$QSM_Legacy = new QSM_Legacy();
