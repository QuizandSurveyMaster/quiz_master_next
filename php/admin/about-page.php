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
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css' , __FILE__ ), array(), $version );
	wp_enqueue_script( 'qsm_admin_js', plugins_url( '../../js/admin.js' , __FILE__ ), array( 'jquery' ), $version );
	?>
	<style>
		div.qsm_icon_wrap {
			background: <?php echo 'url("' . plugins_url( '../../assets/icon-128x128.png', __FILE__ ) . '" )'; ?> no-repeat;
		}
	</style>
	<div class="wrap about-wrap">
		<h1><?php _e( 'Welcome To Quiz And Survey Master (Formerly Quiz Master Next)', 'quiz-master-next' ); ?></h1>
		<div class="about-text"><?php _e( 'Thank you for updating!', 'quiz-master-next' ); ?></div>
		<div class="qsm_icon_wrap"><?php echo $version; ?></div>
		<h2 class="nav-tab-wrapper">
			<a href="#" data-tab='1' class="nav-tab nav-tab-active qsm-tab">
				<?php _e( "What's New!", 'quiz-master-next' ); ?></a>
			<a href="#" data-tab='2' class="nav-tab qsm-tab">
				<?php _e( 'Changelog', 'quiz-master-next' ); ?></a>
			<a href="#" data-tab='3' class="nav-tab qsm-tab">
				<?php _e( 'People Who Make QSM Possible', 'quiz-master-next' ); ?></a>
		</h2>
		<div class="qsm-tab-content tab-1">
			<div class="feature">
				<h2 class="feature-headline">Welcome to QSM 5.3!</h2>
			</div>
			<div class="feature">
				<h2 class="feature-headline">New Progress Bar!</h2>
				<p class="feature-text">You can now enable a progress bar on quizzes and surveys that are using the new pages and questions editor from the 5.2.0 update!</p>
				<p class="feature-text">When editing your quiz or survey, go to the "Options" tab and set the "Show a progress bar?" option to "Yes".</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">Disable IP Address Collection</h2>
				<p class="feature-text">You can now disable the plugin from collecting IP addresses, if needed. To do so, go to the "Settings" page and check the "Disable collecting and storing IP addresses?" checkbox.</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">Disable Storing Results In The Database</h2>
				<p class="feature-text">For the quizzes and surveys that do not need to actually store any responses, you can now turn off the results being stored in the database. To do so, go to the "Options" tab when editing your quiz or survey and set the "Store the responses in the database?" option to "No".</p>
			</div>
		</div>
		<div class="qsm-tab-content tab-2" style="display: none;">
			<h2>Changelog</h2>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 51 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 50 ); ?>
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
						// translators: This is the 'title' attribute for GitHub contributors. This would add the GitHub user such as 'View fpcorso'.
						esc_html( sprintf( __( 'View %s', 'quiz-master-next' ), $contributor->login ) )
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
