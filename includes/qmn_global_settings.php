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
		add_settings_field( 'cpt-search', 'Disable Quiz Posts From Being Searched?', array($this, 'cpt_search_field'), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-archive', 'Disable Quiz Archive?', array($this, 'cpt_archive_field'), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-slug', 'Quiz Url Slug', array($this, 'cpt_slug_field'), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'facebook-app-id', 'Facebook App Id', array($this, 'facebook_app_id'), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'results-details', 'Template For Admin Results Details', array($this, 'results_details_template'), 'qmn_global_settings', 'qmn-global-section' );
	}

	public function global_section()
	{
		echo 'These settings are applied to the entire plugin and all quizzes.';
		if (isset($_GET["settings-updated"]) && $_GET["settings-updated"])
		{
			flush_rewrite_rules(true);
			echo "<span style='color:red;'>Settings have been updated!</span>";
		}
	}

	public function facebook_app_id()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '483815031724529';
		if (isset($settings['facebook_app_id']))
		{
			$facebook_app_id = esc_attr( $settings['facebook_app_id'] );
		}
		echo "<input type='text' name='qmn-settings[facebook_app_id]' id='qmn-settings[facebook_app_id]' value='$facebook_app_id' />";
	}

	public function cpt_slug_field()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$cpt_slug = 'quiz';
		if (isset($settings['cpt_slug']))
		{
			$cpt_slug = esc_attr( $settings['cpt_slug'] );
		}
		echo "<input type='text' name='qmn-settings[cpt_slug]' id='qmn-settings[cpt_slug]' value='$cpt_slug' />";
	}

	public function cpt_search_field()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$cpt_search = '0';
		if (isset($settings['cpt_search']))
		{
			$cpt_search = esc_attr( $settings['cpt_search'] );
		}
		$checked = '';
		if ($cpt_search == '1')
		{
			$checked = " checked='checked'";
		}
		echo "<input type='checkbox' name='qmn-settings[cpt_search]' id='qmn-settings[cpt_search]' value='1'$checked />";
	}

	public function cpt_archive_field()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$cpt_archive = '0';
		if (isset($settings['cpt_archive']))
		{
			$cpt_archive = esc_attr( $settings['cpt_archive'] );
		}
		$checked = '';
		if ($cpt_archive == '1')
		{
			$checked = " checked='checked'";
		}
		echo "<input type='checkbox' name='qmn-settings[cpt_archive]' id='qmn-settings[cpt_archive]' value='1'$checked />";
	}

	public function results_details_template()
	{
		$settings = (array) get_option( 'qmn-settings' );
		if (isset($settings['results_details_template']))
		{
			$template = htmlspecialchars_decode($settings['results_details_template'], ENT_QUOTES);
		}
		else
		{
			$template = "<h2>Quiz Results for %QUIZ_NAME%</h2>
			<p>Name Provided: %USER_NAME%</p>
			<p>Business Provided: %USER_BUSINESS%</p>
			<p>Phone Provided: %USER_PHONE%</p>
			<p>Email Provided: %USER_EMAIL%</p>
			<p>Score Received: %AMOUNT_CORRECT%/%TOTAL_QUESTIONS% or %CORRECT_SCORE%% or %POINT_SCORE% points</p>
			<h2>Answers Provided:</h2>
			<p>The user took %TIMER% to complete quiz.</p>
			<p>Comments entered were: %COMMENT_SECTION%</p>
			<p>The answers were as follows:</p>
			%QUESTIONS_ANSWERS%";
		}
		wp_editor( $template, 'results_template', array('textarea_name' => 'qmn-settings[results_details_template]') );
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
