<?php
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
		<h2>Quiz Master Next Addon Settings</h2>
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


function qmn_generate_featured_addons()
{
	wp_enqueue_style( 'qmn_addons_style', plugins_url( 'css/qmn_addons_page.css' , __FILE__ ) );
	?>
	<p><?php _e('These addons extend the functionality of Quiz Master Next', 'quiz-master-next'); ?></p>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Export Results</h3>
		<p class="qmn_addons_desc">This add-on gives you the ability to export your quiz results as a CSV file.</p>
		<a href="http://quizmasternext.com/downloads/export-results/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Advertisement Be Gone</h3>
		<p class="qmn_addons_desc">This add-on will remove all services/add-on advertisements in all of our Master Suite plugins.</p>
		<a href="http://quizmasternext.com/downloads/advertisement-gone/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">MailPoet Integration</h3>
		<p class="qmn_addons_desc">Grow your list of subscribers in MailPoet by using this add-on to add users who take your quizzes!</p>
		<a href="http://quizmasternext.com/downloads/mailpoet-integration/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div class="qmn_addons">
		<h3 class="qmn_addons_title">Advanced Leaderboard</h3>
		<p class="qmn_addons_desc">This add-on gives you 4 new leaderboard shortcodes and 2 new widgets that you can customize per use. You can edit how many results are listed, the name of the leaderboard, and the order the results are listed in.</p>
		<a href="http://quizmasternext.com/downloads/advanced-leaderboard/" target="_blank" class="button">Get This Addon</a>
	</div>
	<div style="clear:both;">
	<br />
	<a href="http://quizmasternext.com/addons/" target="_blank" class="button-primary"><?php _e('Browse All Addons', 'quiz-master-next'); ?></a>
	<?php
}

function qmn_featured_addons_tab()
{
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_addon_settings_tab(__("Featured Addons", 'quiz-master-next'), "qmn_generate_featured_addons");
}
add_action("plugins_loaded", 'qmn_featured_addons_tab');
?>
