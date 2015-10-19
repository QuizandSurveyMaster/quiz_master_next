<?php

/**
*Creates the add on page that is displayed in the add on settings page
*
* @return void
* @since 4.4.0
*/
if ( ! defined( 'ABSPATH' ) ) exit;
function qmn_addons_page()
{
	if ( !current_user_can('moderate_comments') )
	{
		return;
	}
	global $mlwQuizMasterNext;
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'featured-addons';
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_addon_tabs();
	?>
	<div class="wrap">
		<h2>Quiz And Survey Master Addon Settings</h2>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach($tab_array as $tab)
			{
				$active_class = '';
				if ($active_tab == $tab['slug'])
				{
					$active_class = 'nav-tab-active';
				}
				echo "<a href=\"?page=qmn_addons&tab=".$tab['slug']."\" class=\"nav-tab $active_class\">".$tab['title']."</a>";
			}
			?>
		</h2>
		<div>
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
	</div>
	<?php
}

/**
* Displays the contents of the featured add ons page.
*
* @return void
* @since 4.4.0
*/
function qmn_generate_featured_addons()
{
	wp_enqueue_style( 'qmn_addons_style', plugins_url( '../css/qmn_addons_page.css' , __FILE__ ) );
	?>
	<p><?php _e('These addons extend the functionality of Quiz And Survey Master', 'quiz-master-next'); ?></p>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Results Analysis</h3>
		<p class="qmn_addons_desc">Analyze your quiz's or survey's results to see the percentage of users who chose each answer displayed on useful charts, number of submissions, average points earned, and average score earned.</p>
		<a href="http://quizandsurveymaster.com/downloads/results-analysis/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Export Results</h3>
		<p class="qmn_addons_desc">This add-on gives you the ability to export your quiz results as a CSV file.</p>
		<a href="http://quizandsurveymaster.com/downloads/export-results/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Advertisement Be Gone</h3>
		<p class="qmn_addons_desc">This add-on will remove all services/add-on advertisements in all of our Master Suite plugins.</p>
		<a href="http://quizandsurveymaster.com/downloads/advertisement-gone/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">MailPoet Integration</h3>
		<p class="qmn_addons_desc">Grow your list of subscribers in MailPoet by using this add-on to add users who take your quizzes!</p>
		<a href="http://quizandsurveymaster.com/downloads/mailpoet-integration/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Slack Integration</h3>
		<p class="qmn_addons_desc">This addon will allow you to post a message to your slack when a user takes a quiz or test.</p>
		<a href="http://quizandsurveymaster.com/downloads/slack-integration/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Gradebook</h3>
		<p class="qmn_addons_desc">Need a gradebook that will show your users average scores and their quizzes? Then this addon is for you!</p>
		<a href="http://quizandsurveymaster.com/downloads/gradebook/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Daily Limit</h3>
		<p class="qmn_addons_desc">This Quiz And Survey Master add-on allows you to restrict users to only a set amount of entries per day.</p>
		<a href="http://quizandsurveymaster.com/downloads/daily-limit/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Extra Template Variables</h3>
		<p class="qmn_addons_desc">This addon gives you several more template variables to use in your emails and results pages.</p>
		<a href="http://quizandsurveymaster.com/downloads/extra-template-variables/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div style="clear:both;">
	<br />
	<a href="http://quizandsurveymaster.com/addons/" target="_blank" class="button-primary"><?php _e('Browse All Addons', 'quiz-master-next'); ?></a>
	<?php
}


/**
* This function registers the feature add ons tab.
*
* @return void
* @since 4.4.0
*/
function qmn_featured_addons_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_addon_settings_tab(__("Featured Addons", 'quiz-master-next'), "qmn_generate_featured_addons");
}
add_action("plugins_loaded", 'qmn_featured_addons_tab');
?>
