<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* This function shows the about page. It also shows the changelog information.
*
* @return void
* @since 4.4.0
*/
function mlw_generate_about_page() {

	global $mlwQuizMasterNext;
	$version = $mlwQuizMasterNext->version;
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../css/qsm-admin.css' , __FILE__ ), array(), '5.0.0' );
	wp_enqueue_script( 'qsm_admin_js', plugins_url( '../js/admin.js' , __FILE__ ), array( 'jquery' ), '5.0.0' );
	?>
	<style>
		div.qsm_icon_wrap {
			background: <?php echo 'url("'.plugins_url( '../assets/icon-128x128.png' , __FILE__ ).'")'; ?> no-repeat;
		}
	</style>
	<div class="wrap about-wrap">
		<h1><?php _e('Welcome To Quiz And Survey Master (Formerly Quiz Master Next)', 'quiz-master-next'); ?></h1>
		<div class="about-text"><?php _e('Thank you for updating!', 'quiz-master-next'); ?></div>
		<div class="qsm_icon_wrap"><?php echo $version; ?></div>
		<h2 class="nav-tab-wrapper">
			<a href="#" data-tab='1' class="nav-tab nav-tab-active qsm-tab">
				<?php _e("What's New!", 'quiz-master-next'); ?></a>
			<a href="#" data-tab='2' class="nav-tab qsm-tab">
				<?php _e('Changelog', 'quiz-master-next'); ?></a>
			<a href="#" data-tab='3' class="nav-tab qsm-tab">
				<?php _e('People Who Make QSM Possible', 'quiz-master-next'); ?></a>
		</h2>
		<div class="qsm-tab-content tab-1">
			<div class="feature">
				<h2 class="feature-headline">Welcome to QSM 5.0!</h2>
				<p class="feature-text">There are many changes in version 5.0. From a contact fields system to new developer functions, there are a lot to see.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">New Contact Fields System</h2>
				<p class="feature-text">The biggest change is our new contact fields. Instead of having to edit some of the options on one tab, the text on another tab, and final settings on another tab, all of the contact field options are on the new "Contact" tab.</p>
				<p class="feature-text">Even better, you can now create new contact fields for anything you need! Need an "Age" field or an "Attendee ID" field? Now you can!</p>
				<p class="feature-text">You can now choose from three different contact field types: Small Open Answer, Email, or Checkbox.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">More Text Is Editable</h2>
				<p class="feature-text">You can now edit the text for the "Hint" as well as all 4 of the error messages that are used when validating a user's responses. You can edit these on the "Text" tab.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">Scrollable And Searchable Quizzes/Surveys</h2>
				<p class="feature-text">For the users who have many quizzes and surveys on their site, it has been difficult and frustrating to find a particular quiz/survey to edit it on the Quizzes page. Now, the quizzes/surveys can be searched using the new searchbox in the top-right! Also, there are no more pages of quizzes/surveys to look through as the quizzes/surveys load as you scroll down making editing much quicker and more efficient.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">Introduction Of Onboarding Process</h2>
				<p class="feature-text">New users of this plugin will now see a getting started video and documentation when first accessing the Quizzes/Surveys page.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">Certificate Is Now An Addon</h2>
				<p class="feature-text">As we stated 8 months ago, the certificate feature is no longer built into the plugin and is available as a free addon.</p>
			</div>
			<hr />
			<div class="feature">
				<h2 class="feature-headline">For Developers: New Settings System</h2>
				<p class="feature-text">The settings system has been completely rewritten and is now stored as serialized arrays that can be extended instead of hardcoded as columns in the database. You can now "register" settings with defaults and assign which tab they belong to. Documentation coming soon.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">For Developers: New Results Page Tabs</h2>
				<p class="feature-text">The "Results" page in the admin can now be extended with tabs. A great location for adding features that affect or analyze the results.</p>
			</div>
		</div>
		<div class="qsm-tab-content tab-2" style="display: none;">
			<h2>Changelog</h2>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 34 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 33 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 26 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 19 ); ?>
		</div>
		<div class="qsm-tab-content tab-3" style="display:none;">
			<h2>GitHub Contributors</h2>
			<?php
			$contributors = get_transient( 'qmn_contributors' );
			if ( false === $contributors ) {
				$response = wp_remote_get( 'https://api.github.com/repos/fpcorso/quiz_master_next/contributors', array( 'sslverify' => false ) );
				if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
					$contributors = array();
				} else {
					$contributors = json_decode( wp_remote_retrieve_body( $response ) );
				}
			}
			if ( is_array( $contributors ) & ! empty( $contributors ) ) {
				set_transient( 'qmn_contributors', $contributors, 3600 );
				$contributor_list = '<ul class="wp-people-group">';
				foreach ( $contributors as $contributor ) {
					$contributor_list .= '<li class="wp-person">';
					$contributor_list .= sprintf( '<a href="%s" title="%s">',
						esc_url( 'https://github.com/' . $contributor->login ),
						esc_html( sprintf( __( 'View %s', 'edd' ), $contributor->login ) )
					);
					$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
					$contributor_list .= '</a>';
					$contributor_list .= sprintf( '<a class="web" href="%s" target="_blank">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
					$contributor_list .= '</a>';
					$contributor_list .= '</li>';
				}
				$contributor_list .= '</ul>';
				echo $contributor_list;
			}
			?>
			<a href="https://github.com/fpcorso/quiz_master_next" target="_blank" class="button-primary">View GitHub Repo</a>
		</div>
	</div>
<?php
}
?>
