<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generates The Settings Page For The Plugin
 *
 * @since 4.1.0
 */
class QMNGlobalSettingsPage {

	/**
	  * Main Construct Function
	  *
	  * Call functions within class
	  *
	  * @since 4.1.0
	  * @uses QMNGlobalSettingsPage::load_dependencies() Loads required filed
	  * @uses QMNGlobalSettingsPage::add_hooks() Adds actions to hooks and filters
	  * @return void
	  */
	function __construct() {
		$this->add_hooks();
	}

	/**
	  * Add Hooks
	  *
	  * Adds functions to relavent hooks and filters
	  *
	  * @since 4.1.0
	  * @return void
	  */
	private function add_hooks() {
		add_action( "admin_init", array( $this, 'init' ) );
	}

	/**
	 * Prepares Settings Fields And Sections
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function init() {
		register_setting( 'qmn-settings-group', 'qmn-settings' );
		add_settings_section( 'qmn-global-section', __( 'Main Settings', 'quiz-master-next' ), array( $this, 'global_section' ), 'qmn_global_settings' );
		add_settings_field( 'usage-tracker', __( 'Allow Usage Tracking?', 'quiz-master-next' ), array( $this, 'usage_tracker_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'ip-collection', __( 'Disable collecting and storing IP addresses?', 'quiz-master-next' ), array( $this, 'ip_collection_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-search', __( 'Disable Quiz Posts From Being Searched?', 'quiz-master-next' ), array( $this, 'cpt_search_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-archive', __( 'Disable Quiz Archive?', 'quiz-master-next' ), array( $this, 'cpt_archive_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'cpt-slug', __( 'Quiz Url Slug', 'quiz-master-next' ), array( $this, 'cpt_slug_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'plural-name', __( 'Post Type Plural Name (Shown in various places such as on archive pages)', 'quiz-master-next' ), array( $this, 'plural_name_field' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'facebook-app-id', __( 'Facebook App Id', 'quiz-master-next' ), array( $this, 'facebook_app_id' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'from-name', __( 'From Name (The name emails come from)', 'quiz-master-next' ), array( $this, 'from_name' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'from-email', __( 'From Email (The email address that emails come from)', 'quiz-master-next' ), array( $this, 'from_email' ), 'qmn_global_settings', 'qmn-global-section' );
		add_settings_field( 'results-details', __( 'Template For Admin Results Details', 'quiz-master-next' ), array( $this, 'results_details_template' ), 'qmn_global_settings', 'qmn-global-section' );
	}

	/**
	 * Generates Section Text
	 *
	 * Generates the section text. If page has been saved, flush rewrite rules for updated post type slug
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function global_section() {                
		_e( 'These settings are applied to the entire plugin and all quizzes.', 'quiz-master-next' );
		if ( isset( $_GET["settings-updated"] ) && $_GET["settings-updated"] ) {
			flush_rewrite_rules( true );
                        echo '<div class="updated" style="padding: 10px;">';
			echo "<span style='color:red;'>" . __( ' Settings have been updated!', 'quiz-master-next' ) . "</span>";
                        echo '</div>';
		}                
	}

	/**
	 * Generates Setting Field For From Email
	 *
	 * @since 6.2.0
	 * @return void
	 */
	public function from_email() {
		$settings   = (array) get_option( 'qmn-settings' );
		$from_email = get_option( 'admin_email', '' );
		if ( isset( $settings['from_email'] ) ) {
			$from_email = $settings['from_email'];
		}
		?>                
		<input type='email' name='qmn-settings[from_email]' id='qmn-settings[from_email]' value='<?php echo esc_attr( $from_email ); ?>' />                
		<?php
	}

	/**
	 * Generates Setting Field For From Name
	 *
	 * @since 6.2.0
	 * @return void
	 */
	public function from_name() {
		$settings  = (array) get_option( 'qmn-settings' );
		$from_name = get_bloginfo( 'name' );
		if ( isset( $settings['from_name'] ) ) {
			$from_name = $settings['from_name'];
		}
		?>                
		<input type='text' name='qmn-settings[from_name]' id='qmn-settings[from_name]' value='<?php echo esc_attr( $from_name ); ?>' />                
		<?php
	}

	/**
	 * Generates Setting Field For App Id
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function facebook_app_id() {
		$settings = (array) get_option( 'qmn-settings' );
		$facebook_app_id = '483815031724529';
		if (isset($settings['facebook_app_id']))
		{
			$facebook_app_id = esc_attr( $settings['facebook_app_id'] );
		}                
		echo "<input type='text' name='qmn-settings[facebook_app_id]' id='qmn-settings[facebook_app_id]' value='$facebook_app_id' />";                
	}

	/**
	 * Generates Setting Field For Post Slug
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function cpt_slug_field() {
		$settings = (array) get_option( 'qmn-settings' );
		$cpt_slug = 'quiz';
		if ( isset( $settings['cpt_slug'] ) ) {
			$cpt_slug = esc_attr( $settings['cpt_slug'] );
		}                
		echo "<input type='text' name='qmn-settings[cpt_slug]' id='qmn-settings[cpt_slug]' value='$cpt_slug' />";                
	}

	/**
	 * Generates Setting Field For Plural name
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public function plural_name_field() {
		$settings = (array) get_option( 'qmn-settings' );
		$plural_name = __( 'Quizzes & Surveys', 'quiz-master-next' );
		if ( isset( $settings['plural_name'] ) ) {
			$plural_name = esc_attr( $settings['plural_name'] );
		}                
		echo "<input type='text' name='qmn-settings[plural_name]' id='qmn-settings[plural_name]' value='$plural_name' />";                
	}

	/**
	 * Generates Setting Field For Exclude Search
	 *
	 * @since 4.1.0
	 * @return void
	 */
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
                echo '<label class="switch">';
		echo "<input type='checkbox' name='qmn-settings[cpt_search]' id='qmn-settings[cpt_search]' value='1'$checked />";
                echo '<span class="slider round"></span></label>';
	}

	/**
	 * Generates Setting Field For Post Archive
	 *
	 * @since 4.1.0
	 * @return void
	 */
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
                echo '<label class="switch">';
		echo "<input type='checkbox' name='qmn-settings[cpt_archive]' id='qmn-settings[cpt_archive]' value='1'$checked />";
                echo '<span class="slider round"></span></label>';
	}

	/**
	 * Generates Setting Field For Results Details Template
	 *
	 * @since 4.1.0
	 * @return void
	 */
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
			<p>%CONTACT_ALL%</p>
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

	/**
	 * Generates Setting Field For Usage Tracker Authorization
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public function usage_tracker_field()
	{
		$settings = (array) get_option( 'qmn-settings' );
		$tracking_allowed = '0';
		if (isset($settings['tracking_allowed']))
		{
			$tracking_allowed = esc_attr( $settings['tracking_allowed'] );
		}
		$checked = '';
		if ($tracking_allowed == '2')
		{
			$checked = " checked='checked'";
		}                
                echo '<label class="switch">';
		echo "<input type='checkbox' name='qmn-settings[tracking_allowed]' id='qmn-settings[tracking_allowed]' value='2'$checked /><span class='slider round'></span>";		
                echo '</label>';
                echo "<span class='global-sub-text' for='qmn-settings[tracking_allowed]'>" . __( "Allow Quiz And Survey Master to anonymously track this plugin's usage and help us make this plugin better.", 'quiz-master-next' ) . "</span>";
	}

	/**
	 * Generates Setting Field For IP Collection
	 *
	 * @since 5.3.0
	 * @return void
	 */
	public function ip_collection_field() {
		$settings = (array) get_option( 'qmn-settings' );
		$ip_collection = '0';
		if ( isset( $settings['ip_collection'] ) ) {
			$ip_collection = esc_attr( $settings['ip_collection'] );
		}
		$checked = '';
		if ( '1' == $ip_collection ) {
			$checked = " checked='checked'";
		}
                echo '<label class="switch">';
		echo "<input type='checkbox' name='qmn-settings[ip_collection]' id='qmn-settings[ip_collection]' value='1'$checked />";
                echo '<span class="slider round"></span></label>';
	}

	/**
	 * Generates Settings Page
	 *
	 * @since 4.1.0
	 * @return void
	 */
	public static function display_page() {
                global $mlwQuizMasterNext;
                wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
		?>
		<div class="wrap">
                    <h2><?php _e( 'Global Settings', 'quiz-master-next' ); ?></h2>
                    <form action="options.php" method="POST" class="qsm_global_settings">
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
