<?php
/*
This page shows the user how-to's for using the plugin
*/
/*
Copyright 2014, My Local Webstop (email : fpcorso@mylocalwebstop.com)
*/

function mlw_generate_help_page()
{
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

function qmn_documentation_meta_box_content()
{
	?>
	<p><?php _e('Need help with the plugin? Try any of the following:', 'quiz-master-next'); ?></p>
	<ul>
		<li>Visit our <a href="http://mylocalwebstop.com/plugin-documentation/">documentation</a> for using this plugin</li>
		<li>Fill out the form in the Support widget to send us an email</li>
		<li>Fill out the form on our <a href="http://mylocalwebstop.com/contact-us/">Contact Us Page</a></li>
		<li>Create a topic in the <a href="https://wordpress.org/support/plugin/quiz-master-next">WordPress Support Forums</a></li>
	</ul>
	<?php
}

function qmn_support_meta_box_content()
{
	$quiz_master_email_message = "";
	$mlw_quiz_version = get_option('mlw_quiz_master_version');
	if(isset($_POST["support_email"]) && $_POST["support_email"] == 'confirmation')
	{
		$user_name = $_POST["username"];
		$user_email = $_POST["email"];
		$user_message = $_POST["message"];
		$user_quiz_url = $_POST["quiz_url"];
		$current_user = wp_get_current_user();
		$mlw_site_info = qmn_get_system_info();
		$mlw_message = $user_message."<br> Version: ".$mlw_quiz_version."<br> Quiz URL Provided: ".$user_quiz_url."<br> User ".$current_user->display_name." from ".$current_user->user_email."<br> Wordpress Info: ".$mlw_site_info;
		$response = wp_remote_post( "http://mylocalwebstop.com/contact-us/", array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array( 'mlwUserName' => $user_name, 'mlwUserComp' => '', 'mlwUserEmail' => $user_email, 'question1' => 'Email', 'question3' => 'Quiz Master Next', 'question2' => $mlw_message, 'qmn_question_list' => '1Q3Q2Q', 'complete_quiz' => 'confirmation' ),
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
	<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=mlw_quiz_help" method='post' name='emailForm' onsubmit='return mlw_validateForm()'>
	<input type='hidden' name='support_email' value='confirmation' />
	<table>
	<tr>
	<td>If there is something you would like to suggest to add or even if you just want
	to let me know if you like the plugin or not, feel free to use the support ticket form below.</td>
	</tr>
	<tr>
	<td><span name='mlw_support_message' id='mlw_support_message' style="color: red;"><?php echo $quiz_master_email_message; ?></span></td>
	</tr>
	<tr>
	<td align='left'><span style='font-weight:bold;';>Name (Required): </span></td>
	</tr>
	<tr>
	<td><input type='text' name='username' value='' /></td>
	</tr>
	<tr>
	<td align='left'><span style='font-weight:bold;';>Email (Required): </span></td>
	</tr>
	<tr>
	<td><input type='text' name='email' value='' /></td>
	</tr>
	<tr>
	<td align='left'><span style='font-weight:bold;';>URL To Quiz (Not Required): </span></td>
	</tr>
	<tr>
	<td><input type='text' name='quiz_url' value='' /></td>
	</tr>
	<tr>
	<td align='left'><span style='font-weight:bold;';>Message (Required): </span></td>
	</tr>
	<tr>
	<td align='left'><TEXTAREA NAME="message" COLS=40 ROWS=6></TEXTAREA></td>
	</tr>
	<tr>
	<td align='left'><input type='submit' class="button-primary" value='Submit Support Ticket' /></td>
	</tr>
	<tr>
	<td align='left'></td>
	</tr>
	</table>
	</form>
	<p>Disclaimer: In order to better assist you, this form will also send the system info from below with your message.</p>
	</div>
	<?php
}

function qmn_system_meta_box_content()
{
	echo qmn_get_system_info();
}

function qmn_get_system_info()
{
	global $wpdb;
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

	$qmn_sys_info .= "<h3>QMN Information</h3><br />";
	$qmn_sys_info .= "Total Quizzes : ".$mlw_stat_total_quiz."<br />";
	$qmn_sys_info .= "Total Active Quizzes : ".$mlw_stat_total_active_quiz."<br />";
	$qmn_sys_info .= "Total Questions : ".$mlw_stat_total_questions."<br />";
	$qmn_sys_info .= "Total Active Questions : ".$mlw_stat_total_active_questions."<br />";


	return $qmn_sys_info;
}

?>
