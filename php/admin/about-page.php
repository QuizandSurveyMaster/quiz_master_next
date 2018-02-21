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
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../css/qsm-admin.css' , __FILE__ ), array(), '5.1.0' );
	wp_enqueue_script( 'qsm_admin_js', plugins_url( '../js/admin.js' , __FILE__ ), array( 'jquery' ), '5.1.0' );
	?>
	<style>
		div.qsm_icon_wrap {
			background: <?php echo 'url("' . plugins_url( '../assets/icon-128x128.png', __FILE__ ) . '" )'; ?> no-repeat;
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
				<h2 class="feature-headline">Welcome to QSM 5.1!</h2>
			</div>
			<div class="feature">
				<h2 class="feature-headline">New Shortcode For Quiz/Survey Link</h2>
				<p class="feature-text">There has always been a shortcode for displaying your quizzes and surveys. However, adding a link directly to the quiz or survey has been difficult.</p>
				<p class="feature-text">Before, if you place a direct link and the permalink changed, your link would break.</p>
				<p class="feature-text">Now, you can use the new "qsm_link" shortcode for creating links. The shortcode has been added to the "Quizzes/Surveys" page so it can easily be copied to be pasted anywhere on your site!</p>
			</div>
			<div class="feature">
				<h2 class="feature-headline">Dates Are Now Localized</h2>
				<p class="feature-text">You may have noticed that the dates in your results as well as variables such as %DATE_TAKEN% and %CURRENT_DATE% never followed the format you set your WordPress to.</p>
				<p class="feature-text">With this update, all dates will follow the format set on the "General" page of the "Settings" menu in your WordPress dashboard.</p>
			</div>
			<hr />
			<div class="feature">
				<h2 class="feature-headline">For Developers: New All Quizzes Function</h2>
				<p class="feature-text">There is a new function "get_quizzes" in the plugin helper API that returns all quizzes. With the functions parameters, you can get the quizzes ordered by a certain field and include deleted quizzes if needed.</p>
			</div>
		</div>
		<div class="qsm-tab-content tab-2" style="display: none;">
			<h2>Changelog</h2>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 47 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 45 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 44 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 43 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 41 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 40 ); ?>
			<?php QSM_Changelog_Generator::get_changelog_list( 'fpcorso/quiz_master_next', 25 ); ?>
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
