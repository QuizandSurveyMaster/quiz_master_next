<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
* This function allows for the editing of quiz options.
*
* @param type description
* @return void
* @since 4.4.0
*/
function mlw_generate_quiz_options()
{
	if ( !current_user_can('moderate_comments') )
	{
		return;
	}
	global $wpdb;
	global $mlwQuizMasterNext;
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_settings_tabs();
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'questions';
	$quiz_id = intval($_GET["quiz_id"]);
	if (isset($_GET["quiz_id"]))
	{
		$table_name = $wpdb->prefix . "mlw_quizzes";
		$mlw_quiz_options = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE quiz_id=%d LIMIT 1", $_GET["quiz_id"]));
	}

	?>

	<script type="text/javascript"
	  src="//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML">
	</script>
	<?php
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );
	wp_enqueue_style( 'qmn_jquery_redmond_theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css' );
	?>
	<style>
		.mlw_tab_content
		{
			padding: 20px 20px 20px 20px;
			margin: 20px 20px 20px 20px;
		}
	</style>
	<div class="wrap">
	<div class='mlw_quiz_options'>
	<h1><?php
	/* translators: The %s corresponds to the name of the quiz */
	echo sprintf(__('Quiz Settings For %s', 'quiz-master-next'), $mlw_quiz_options->quiz_name);
	?></h1>
	<?php
	ob_start();
	if ($quiz_id != "")
	{
		?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach($tab_array as $tab)
			{
				$active_class = '';
				if ($active_tab == $tab['slug'])
				{
					$active_class = 'nav-tab-active';
				}
				echo "<a href=\"?page=mlw_quiz_options&quiz_id=$quiz_id&tab=".$tab['slug']."\" class=\"nav-tab $active_class\">".$tab['title']."</a>";
			}
			?>
		</h2>
		<div class="mlw_tab_content">
			<?php
				foreach($tab_array as $tab)
				{
					if ($active_tab == $tab['slug'])
					{
						call_user_func($tab['function']);
					}
				}
			?>
		</div>
		<?php
	}
	else
	{
		?>
		<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong><?php _e('Error!', 'quiz-master-next'); ?></strong> <?php _e('Please go to the quizzes page and click on the Edit link from the quiz you wish to edit.', 'quiz-master-next'); ?></p>
		</div>
		<?php
	}
	$mlw_output = ob_get_contents();
	ob_end_clean();
	$mlwQuizMasterNext->alertManager->showAlerts();
	echo mlw_qmn_show_adverts();
	echo $mlw_output;
	?>
	</div>
	</div>
<?php
}
?>
