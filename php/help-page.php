<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function generates the help page.
*
* @return void
* @since 4.4.0
*/
function mlw_generate_help_page()
{
	if ( !current_user_can('moderate_comments') )
	{
		return;
	}

	wp_enqueue_style( 'qmn_admin_style', plugins_url( '../css/qmn_admin.css' , __FILE__ ) );

	///Creates the widgets
	add_meta_box("wpss_mrts", __('Need Help?', 'quiz-master-next'), "qmn_documentation_meta_box_content", "meta_box_help");
	add_meta_box("wpss_mrts", __('Support', 'quiz-master-next'), "qmn_support_meta_box_content", "meta_box_support");
	add_meta_box("wpss_mrts", __('System Info', 'quiz-master-next'), "qmn_system_meta_box_content", "meta_box_sys_info");
	?>
	<div class="wrap">
	<h2><?php _e('Help Page', 'quiz-master-next'); ?></h2>
	<?php echo mlw_qmn_show_adverts(); ?>

	<!--Display Widget Boxes-->
	<div style="float:left; width:50%;" class="inner-sidebar1">
		<?php do_meta_boxes('meta_box_help','advanced','');  ?>
	</div>

	<div style="float:left; width:50%;" class="inner-sidebar1">
		<?php do_meta_boxes('meta_box_support','advanced','');  ?>
	</div>

	<div style="float:left; width:100%;" class="inner-sidebar1">
		<?php do_meta_boxes('meta_box_sys_info','advanced','');  ?>
	</div>

	</div>
<?php
}

/**
* This function creates the text that is displayed on the help page.
*
* @param type description
* @return void
* @since 4.4.0
*/
function qmn_documentation_meta_box_content()
{
	?>
	<p><?php _e('Need help with the plugin? Try any of the following:', 'quiz-master-next'); ?></p>
	<ul>
		<li>To report a bug, issue, or make a feature request, please create an issue in our <a href="https://github.com/fpcorso/quiz_master_next/issues">Github Issue Tracker</a></li>
		<li>For assistance in using the plugin, read our <a href="http://quizandsurveymaster.com/documentation/?utm_source=qsm-help-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=documentation" target="_blank">documentation</a> or view videos in our <a href="http://quizandsurveymaster.com/online-academy/?utm_source=qsm-help-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=online_academy" target="_blank">online academy</a></li>
		<li>For support, fill out the form in the Support widget to send us an email</li>
		<li>For support, fill out the form on our <a href="http://quizandsurveymaster.com/contact-us/?utm_source=qsm-help-page&utm_medium=plugin&utm_campaign=qsm_plugin&utm_content=contact_us" target="_blank">Contact Us Page</a></li>
		<li>For support, create a topic in the <a href="https://wordpress.org/support/plugin/quiz-master-next">WordPress Support Forums</a></li>
	</ul>
	<?php
}

/**
* This function creates the content that is displayed on the help page.
*
* @return void
* @since 4.4.0
*/
function qmn_support_meta_box_content()
{
	$quiz_master_email_message = "";
	$mlw_quiz_version = get_option('mlw_quiz_master_version');
	if ( isset( $_POST["support_email"] ) && wp_verify_nonce( $_POST['send_support_ticket_nonce'], 'send_support_ticket') )
	{
		//These variables are not being be used in this site, they are being sent back to my open a support ticket form.
		$user_name = sanitize_text_field( $_POST["username"] );
		$user_email = sanitize_email( $_POST["email"] );
		$user_message = esc_textarea( $_POST["message"] );
		$user_quiz_url = esc_url_raw( $_POST["quiz_url"] );
		if ( !is_email( $user_email ) ) {
			$quiz_master_email_message = "Invalid email address";
		} else {
			$current_user = wp_get_current_user();
			$mlw_site_info = qmn_get_system_info();
			$mlw_message = "$user_message<br> Version: $mlw_quiz_version<br> Quiz URL Provided: $user_quiz_url<br> User ".$current_user->display_name." from ".$current_user->user_email."<br> Wordpress Info: $mlw_site_info";
			$response = wp_remote_post( "http://quizandsurveymaster.com/contact-us/", array(
				'method' => 'POST',
				'timeout' => 45,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => array(),
				'body' => array(
					'mlwUserName' => $user_name,
					'mlwUserComp' => '',
					'mlwUserEmail' => $user_email,
					'question3' => 'Other',
					'question72' => 'No',
					'question2' => $mlw_message,
					'qmn_question_list' => '3Q72Q2Q',
					'total_questions' => 3,
					'complete_quiz' => 'confirmation',
					'qmn_quiz_id' => '1'
				),
				'cookies' => array()
			  )
			);
			if ( is_wp_error( $response ) ) {
			   $error_message = $response->get_error_message();
			   $quiz_master_email_message = "Something went wrong: $error_message";
			} else {
			   $quiz_master_email_message = "**Message Sent**";
			}
		}
	}
	?>
	<script>
		function mlw_validateForm()
		{
			var x=document.forms['emailForm']['email'].value;
			if (x==null || x=='')
			{
				document.getElementById('mlw_support_message').innerHTML = '**Email must be filled out!**';
				return false;
			};
			var x=document.forms['emailForm']['username'].value;
			if (x==null || x=='')
			{
				document.getElementById('mlw_support_message').innerHTML = '**Name must be filled out!**';
				return false;
			};
			var x=document.forms['emailForm']['message'].value;
			if (x==null || x=='')
			{
				document.getElementById('mlw_support_message').innerHTML = '**There must be a message to send!**';
				return false;
			};
			var x=document.forms['emailForm']['email'].value;
			var atpos=x.indexOf('@');
			var dotpos=x.lastIndexOf('.');
			if (atpos<1 || dotpos<atpos+2 || dotpos+2>=x.length)
			{
				document.getElementById('mlw_support_message').innerHTML = '**Not a valid e-mail address!**';
				return false;
			}
		}
	</script>
	<div class='quiz_email_support'>
		<form action="" method='post' name='emailForm' onsubmit='return mlw_validateForm()'>
			<input type='hidden' name='support_email' value='confirmation' />
			<p>We would love to hear from you. Fill out the form below and we will contact you shortly.</p>
			<p name='mlw_support_message' id='mlw_support_message'><?php echo $quiz_master_email_message; ?></p>
			<label>Name (Required):</label><br />
			<input type='text' name='username' value='' /><br />
			<label>Email (Required):</label><br />
			<input type='text' name='email' value='' /><br />
			<label>URL To Quiz (Not Required):</label><br />
			<input type='text' name='quiz_url' value='' /><br />
			<label>Message (Required):</label><br />
			<textarea name="message"></textarea><br />
			<?php wp_nonce_field('send_support_ticket','send_support_ticket_nonce'); ?>
			<input type='submit' class="button-primary" value='Submit Support Ticket' />
		</form>
		<p>Disclaimer: In order to better assist you, this form will also send the system info from below with your message.</p>
	</div>
	<?php
}

/**
* This function echoes out the system info for the user.
*
* @return void
* @since 4.4.0
*/
function qmn_system_meta_box_content()
{
	echo qmn_get_system_info();
}

/**
* This function gets the content that is in the system info
*
* @return return $qmn_sys_info This variable contains all of the system info from the admins server.
* @since 4.4.0
*/
function qmn_get_system_info()
{
	global $wpdb;
	global $mlwQuizMasterNext;

	$qmn_sys_info = "";

	$theme_data = wp_get_theme();
	$theme      = $theme_data->Name . ' ' . $theme_data->Version;

	$qmn_sys_info .= "<h3>Site Information</h3><br />";
	$qmn_sys_info .= "Site URL: ".site_url()."<br />";
	$qmn_sys_info .= "Home URL: ".home_url()."<br />";
	$qmn_sys_info .= "Multisite: ".( is_multisite() ? 'Yes' : 'No' )."<br />";

	$qmn_sys_info .= "<h3>WordPress Information</h3><br />";
	$qmn_sys_info .= "Version: ".get_bloginfo( 'version' )."<br />";
	$qmn_sys_info .= "Language: ".( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' )."<br />";
	$qmn_sys_info .= "Active Theme: ".$theme."<br />";
	$qmn_sys_info .= "Debug Mode: ".( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' )."<br />";
	$qmn_sys_info .= "Memory Limit: ".WP_MEMORY_LIMIT."<br />";

	$qmn_sys_info .= "<h3>Plugins Information</h3><br />";
	$qmn_plugin_mu = get_mu_plugins();
    	if( count( $qmn_plugin_mu > 0 ) ) {
    		$qmn_sys_info .= "<h4>Must Use</h4><br />";
	        foreach( $qmn_plugin_mu as $plugin => $plugin_data ) {
	            $qmn_sys_info .= $plugin_data['Name'] . ': ' . $plugin_data['Version'] . "<br />";
	        }
    	}
    	$qmn_sys_info .= "<h4>Active</h4><br />";
	$plugins = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );
	foreach( $plugins as $plugin_path => $plugin ) {
		if( !in_array( $plugin_path, $active_plugins ) )
			continue;
		$qmn_sys_info .= $plugin['Name'] . ': ' . $plugin['Version'] . "<br />";
	}
	$qmn_sys_info .= "<h4>Inactive</h4><br />";
	foreach( $plugins as $plugin_path => $plugin ) {
		if( in_array( $plugin_path, $active_plugins ) )
			continue;
		$qmn_sys_info .= $plugin['Name'] . ': ' . $plugin['Version'] . "<br />";
	}

	$qmn_sys_info .= "<h3>Server Information</h3><br />";
	$qmn_sys_info .= "PHP : ".PHP_VERSION."<br />";
	$qmn_sys_info .= "MySQL : ".$wpdb->db_version()."<br />";
	$qmn_sys_info .= "Webserver : ".$_SERVER['SERVER_SOFTWARE']."<br />";

	$mlw_stat_total_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes LIMIT 1" );
	$mlw_stat_total_active_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_quizzes WHERE deleted=0 LIMIT 1" );
	$mlw_stat_total_questions = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_questions LIMIT 1" );
	$mlw_stat_total_active_questions = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_questions WHERE deleted=0 LIMIT 1" );
	$qmn_total_results = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_results LIMIT 1" );
	$qmn_total_active_results = $wpdb->get_var( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_results WHERE deleted=0 LIMIT 1" );

	$qmn_sys_info .= "<h3>QMN Information</h3><br />";
	$qmn_sys_info .= "Initial Version : ".get_option('qmn_original_version')."<br />";
	$qmn_sys_info .= "Current Version : ".$mlwQuizMasterNext->version."<br />";
	$qmn_sys_info .= "Total Quizzes : ".$mlw_stat_total_quiz."<br />";
	$qmn_sys_info .= "Total Active Quizzes : ".$mlw_stat_total_active_quiz."<br />";
	$qmn_sys_info .= "Total Questions : ".$mlw_stat_total_questions."<br />";
	$qmn_sys_info .= "Total Active Questions : ".$mlw_stat_total_active_questions."<br />";
	$qmn_sys_info .= "Total Results : ".$qmn_total_results."<br />";
	$qmn_sys_info .= "Total Active Results : ".$qmn_total_active_results."<br />";

	$qmn_sys_info .= "<h3>QMN Recent Logs</h3><br />";
	$recent_errors = $mlwQuizMasterNext->log_manager->get_logs();
	if ( $recent_errors ) {
		foreach( $recent_errors as $error ) {
			$qmn_sys_info .= "Log created at ". $error->post_date . " : " . $error->post_title . " - " . $error->post_content . "<br />";
		}
	} else {
		$qmn_sys_info .= "No recent logs<br />";
	}

	return $qmn_sys_info;
}

?>
