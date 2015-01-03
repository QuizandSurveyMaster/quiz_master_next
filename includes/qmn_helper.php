<?php
class QMNPluginHelper
{
	public $addon_tabs = array();
	public $settings_tabs = array();
	
	public function __construct()
	{
		add_action('mlw_qmn_options_tab', array($this, 'get_settings_tabs'));
		add_action('mlw_qmn_options_tab_content', array($this, 'get_settings_tabs_content'));
	}
	
	public function register_addon_settings_tab($title, $function)
	{
		$slug = strtolower(str_replace( " ", "-", $title));
		$new_tab = array(
			'title' => $title,
			'function' => $function,
			'slug' => $slug
		);
		$this->addon_tabs[] = $new_tab;
	}
	
	public function get_addon_tabs()
	{
		return $this->addon_tabs;
	}
	
	public function register_quiz_settings_tabs($title, $function)
	{
		$slug = strtolower(str_replace( " ", "-", $title));
		$new_tab = array(
			'title' => $title,
			'function' => $function,
			'slug' => $slug
		);
		$this->settings_tabs[] = $new_tab;
	}
	
	public function get_settings_tabs()
	{
		foreach($this->settings_tabs as $tab)
		{
			echo "<li><a href=\"".$tab["slug"]."\">".$tab["title"]."</a></li>";
		}
	}
	
	public function get_settings_tabs_content()
	{
		foreach($this->settings_tabs as $tab)
		{
			echo "<div id=\"".$tab["slug"]."\" class=\"mlw_tab_content\">";
			call_user_func($tab['function']);
			echo "</div>";
		}	
	}
}
?>
