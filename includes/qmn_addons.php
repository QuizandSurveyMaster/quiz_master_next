<?php
function qmn_addons_page()
{
	global $mlwQuizMasterNext;
    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'qmn_available_addons';
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
				echo "<a href=\"?page=sandbox_theme_options&tab=".$tab['slug']."\" class=\"nav-tab $active_class\">".$tab['title']."</a>";
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
?>
