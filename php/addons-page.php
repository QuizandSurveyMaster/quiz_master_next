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
	<a href="http://quizandsurveymaster.com/addons/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin" target="_blank" class="button-primary"><?php _e('Browse All Addons', 'quiz-master-next'); ?></a>
	<br />
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Landing Page</h3>
		<p class="qmn_addons_desc">Display your quizzes and surveys in their own standalone page without distracting menus, themes, or other content from your site.</p>
		<a href="http://quizandsurveymaster.com/downloads/landing-page/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=landing-page&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Reporting and Analysis</h3>
		<p class="qmn_addons_desc">Analyze your quiz's or survey's results to see the percentage of users who chose each answer displayed on useful charts. You can then filter that data or export it.</p>
		<a href="http://quizandsurveymaster.com/downloads/results-analysis/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=reporting-and-analysis&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Export Results</h3>
		<p class="qmn_addons_desc">This add-on gives you the ability to export your quiz results as a CSV file.</p>
		<a href="http://quizandsurveymaster.com/downloads/export-results/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=export-results&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Advertisement Be Gone</h3>
		<p class="qmn_addons_desc">This add-on will remove all services/add-on advertisements throughout the plugin.</p>
		<a href="http://quizandsurveymaster.com/downloads/advertisement-gone/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=advertisement-be-gone&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">MailChimp Integration</h3>
		<p class="qmn_addons_desc">Grow your list of subscribers in MailChimp by using this add-on to add users who take your quizzes and surveys!</p>
		<a href="http://quizandsurveymaster.com/downloads/mailchimp-integration/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=mailchimp-integration&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">User Dashboard</h3>
		<p class="qmn_addons_desc">This add-on gives you the ability to set up a page where users can review their results from all the quizzes they have taken.</p>
		<a href="http://quizandsurveymaster.com/downloads/user-dashboard/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=user-dashboard&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">AWeber Integration</h3>
		<p class="qmn_addons_desc">Grow your list of subscribers in AWeber by using this add-on to add users who take your quizzes!</p>
		<a href="http://quizandsurveymaster.com/downloads/aweber-integration/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=aweber-integration&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Extra Template Variables</h3>
		<p class="qmn_addons_desc">This addon gives you several more template variables to use in your emails and results pages.</p>
		<a href="http://quizandsurveymaster.com/downloads/extra-template-variables/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=extra-template-variables&utm_campaign=qsm_plugin" target="_blank" class="button">Get This Addon</a>
	</div>
	<div style="clear:both;">
	<br />
	<a href="http://quizandsurveymaster.com/addons/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-bottom&utm_campaign=qsm_plugin" target="_blank" class="button-primary"><?php _e('Browse All Addons', 'quiz-master-next'); ?></a>
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
