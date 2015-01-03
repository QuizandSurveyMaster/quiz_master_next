<?php
class QMNPluginHelper
{
	public $addon_tabs = array();
	
	public function __construct()
	{
	
	}
	
	public function register_addon_settings_tab($title, $function)
	{
		$slug = strtolower(str_replace( " ", "-", $title));
		$new_tab = array(
			'title' = $title,
			'function' = $function,
			'slug' = $slug
		);
		$this->addon_tabs[] = $new_tab;
	}
	
	public function get_addon_tabs()
	{
		return $this->addon_tabs;
	}
}
?>
