<?php
/**
 *
 */
class QMNGlobalSettingsPage
{

	function __construct()
  {
    $this->load_dependencies();
    $this->add_hooks();
  }

  private function load_dependencies()
  {

  }

  private function add_hooks()
  {
		add_action("admin_init", array($this, 'init'));
  }

	public function init()
	{
		register_setting( 'qmn-settings-group', 'qmn-settings' );
    add_settings_section( 'qmn-global-section', 'Main Settings', array($this, 'global_section'), 'qmn_global_settings' );
    add_settings_field( 'usage-tracker', 'Allow Usage Tracking?', array($this, 'usage_tracker_field'), 'qmn_global_settings', 'qmn-global-section' );
	}

	public function global_section()
	{
		echo 'These settings are applied to the entire plugin and all quizzes.';
	}

	public function usage_tracker_field()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$tracking_allowed = '0';
		if (isset($settings['tracking_allowed']))
		{
			$tracking_allowed = esc_attr( $settings['tracking_allowed'] );
		}
		$checked = '';
		if ($tracking_allowed == '1')
		{
			$checked = " checked='checked'";
		}
		echo "<input type='checkbox' name='qmn-settings[tracking_allowed]' id='qmn-settings[tracking_allowed]' value='1'$checked />";
		echo "<label for='qmn-settings[tracking_allowed]'>Allow Quiz Master Next to anonymously track this plugin's usage and help us make this plugin better.</label>";
	}

	public static function display_page()
	{
		?>
		<div class="wrap">
        <h2>Global Settings</h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'qmn-settings-group' ); ?>
            <?php do_settings_sections( 'qmn_global_settings' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
		<?php
	}
}

$qmnGlobalSettingsPage = new QMNGlobalSettingsPage();
?>
