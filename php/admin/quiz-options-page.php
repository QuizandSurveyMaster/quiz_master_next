<?php
/**
 * Creates the view for all tabs for editing the quiz.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function allows for the editing of quiz options.
 *
 * @return void
 * @since 4.4.0
 */
function qsm_generate_quiz_options() {

	// Checks if current user can.
	if ( ! current_user_can( 'moderate_comments' ) ) {
		return;
	}

	global $wpdb;
	global $mlwQuizMasterNext;

	$quiz_name = '';

	// Gets registered tabs for the options page and set current tab.
	$tab_array  = $mlwQuizMasterNext->pluginHelper->get_settings_tabs();
	$active_tab = strtolower( str_replace( ' ', '-', isset( $_GET[ 'tab' ] ) ? stripslashes( $_GET[ 'tab' ] ) : __( 'Questions', 'quiz-master-next' ) ) );

	// Prepares quiz.
	$quiz_id = isset( $_GET['quiz_id'] ) ? intval( $_GET['quiz_id'] ) : 0;
	if ( isset( $_GET['quiz_id'] ) ) {
		$quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
	}

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-dialog' );
	wp_enqueue_script( 'jquery-ui-button' );
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'jquery-effects-blind' );
	wp_enqueue_script( 'jquery-effects-explode' );

	wp_enqueue_script( 'qmn_admin_js', plugins_url( '../../js/admin.js', __FILE__ ), array( 'backbone', 'underscore', 'wp-util' ), $mlwQuizMasterNext->version, true );
	wp_enqueue_style( 'qsm_admin_style', plugins_url( '../../css/qsm-admin.css', __FILE__ ), array(), $mlwQuizMasterNext->version );
	wp_enqueue_style( 'qmn_jquery_redmond_theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css' );
	wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );
	?>
	<div class="wrap">
		<div class='mlw_quiz_options'>
			<h1><?php echo $quiz_name; ?></h1>
			<?php
			// Puts all output from tab into ob_get_contents below.
			ob_start();

			// If the quiz is set and not empty.
			if ( ! empty( $quiz_id ) ) {
				?>
				<nav class="nav-tab-wrapper">
					<?php
					// Cycles through registered tabs to create navigation.
					foreach ( $tab_array as $tab ) {
						$active_class = '';
						if ( $active_tab == $tab['slug'] ) {
							$active_class = 'nav-tab-active';
						}
						?>
						<a href="?page=mlw_quiz_options&quiz_id=<?php echo esc_attr( $quiz_id ); ?>&tab=<?php echo esc_attr( $tab['slug'] ); ?>" class="nav-tab <?php echo esc_attr( $active_class ); ?>"><?php echo esc_html( $tab['title'] ); ?></a>
						<?php
					}
					?>
				</nav>
				<div class="qsm_tab_content">
					<?php
					// Cycles through tabs looking for current tab to create tab's content.
					foreach ( $tab_array as $tab ) {
						if ( $active_tab == $tab['slug'] ) {
							call_user_func( $tab['function'] );
						}
					}
					?>
				</div>
				<?php
			} else {
				?>
				<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
					<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
					<strong><?php esc_html_e( 'Error!', 'quiz-master-next' ); ?></strong> <?php esc_html_e( 'Please go to the quizzes page and click on the Edit link from the quiz you wish to edit.', 'quiz-master-next' ); ?></p>
				</div>
				<?php
			}
			$mlw_output = ob_get_contents();
			ob_end_clean();

			// Shows alerts, ads, then tab content.
			?>
			<div class="qsm-alerts">
				<?php
				$mlwQuizMasterNext->alertManager->showAlerts();
				?>
			</div>
			<?php
			qsm_show_adverts();
			echo $mlw_output;
			?>
		</div>
	</div>

	<!-- Backbone Views -->

	<!-- View for Notices -->
	<script type="text/template" id="tmpl-notice">
		<div class="notice notice-large notice-{{data.type}}">
			<p>{{data.message}}</p>
		</div>
	</script>
<?php
}
?>
