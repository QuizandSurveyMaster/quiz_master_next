<?php
/**
 * This file creates the Stats page
 *
 * @since 4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Generates the HTML for the Stats page
 *
 * Retrieves the HTML for the tab of Stats page from the plugin helper
 *
 * @since 4.3.0
 * @return void
 */
function qmn_generate_stats_page() {
	if ( ! current_user_can('delete_others_qsm_quizzes') ) {
		return;
	}
	global $mlwQuizMasterNext;
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'quiz-and-survey-submissions';
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_stats_tabs();
	?>
	<div class="wrap qsm-ui qsm-ui-stats-page">
		<div class="qsm-ui-page-header qsm-ui-text">
			<div>
				<h1 class="qsm-ui-h1"><?php esc_html_e( 'Quiz/Survey Statistics', 'quiz-master-next' ); ?></h1>
				<p class="qsm-ui-subtitle"><?php esc_html_e( 'How many quizzes and surveys were submitted over time.', 'quiz-master-next' ); ?></p>
			</div>
		</div>
		<hr class="wp-header-end">
		<?php qsm_show_adverts(); ?>
		<?php if ( count( $tab_array ) > 1 ) { ?>
			<nav class="qsm-ui-page-tabs-nav" aria-label="<?php esc_attr_e( 'Statistics', 'quiz-master-next' ); ?>">
				<div class="qsm-ui-tabs qsm-ui-page-tabs">
					<?php
					foreach ( $tab_array as $tab ) {
						$url = add_query_arg(
							array(
								'page' => 'qmn_stats',
								'tab'  => $tab['slug'],
							),
							admin_url( 'admin.php' )
						);
						echo '<a href="' . esc_url( $url ) . '" class="qsm-ui-tab"' . ( $active_tab === $tab['slug'] ? ' aria-current="page"' : '' ) . '>' . esc_html( $tab['title'] ) . '</a>';
					}
					?>
				</div>
			</nav>
		<?php } ?>
		<div class="qsm-ui-stats-content">
		<?php
			foreach ( $tab_array as $tab ) {
				if ( $active_tab === $tab['slug'] ) {
					call_user_func($tab['function']);
				}
			}
		?>
		</div>
	</div>
	<?php
}

/**
 * Adds Overview Tab To Stats Page
 *
 * @since 4.3.0
 * @return void
 */
function qmn_stats_overview_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_stats_settings_tab(__("Quiz And Survey Submissions", 'quiz-master-next'), "qmn_stats_overview_content");
}
add_action("init", 'qmn_stats_overview_tab');

/**
 * Generates HTML For Overview Tab
 *
 * @since 4.3.0
 * @return void
 */
function qmn_stats_overview_content() {
	$range = "daily";
	if ( isset($_POST["range"]) && '' !== $_POST["range"] ) {
		$range = sanitize_text_field( wp_unslash( $_POST["range"] ) );
	}
	$data = qmn_load_stats($range, 7);
	$labels = array();
	$value = array();
	foreach ( $data as $stat ) {
		array_push($labels,"");
		array_push($value,intval($stat));
	}

	$qsm_admin_stats = array(
		'labels' => $labels,
		'value'  => $value,
	);
	wp_localize_script( 'qsm_admin_js', 'qsm_admin_stats', $qsm_admin_stats);
	$ranges = array(
		'daily'   => __( 'Daily', 'quiz-master-next' ),
		'weekly'  => __( 'Weekly', 'quiz-master-next' ),
		'monthly' => __( 'Monthly', 'quiz-master-next' ),
	);
	?>
	<div class="qsm-ui-card qsm-ui-stats-card">
		<div class="qsm-ui-card-head qsm-ui-text">
			<div>
				<h3 class="qsm-ui-h3"><?php esc_html_e( 'Quiz And Survey Submissions', 'quiz-master-next' ); ?></h3>
				<p><?php esc_html_e( 'Submissions for the last 7 periods.', 'quiz-master-next' ); ?></p>
			</div>
			<form action="" method="post" class="qsm-ui-row qsm-ui-stats-filter">
				<label class="qsm-ui-sr" for="qsm-stats-range"><?php esc_html_e( 'Range', 'quiz-master-next' ); ?></label>
				<select name="range" id="qsm-stats-range" class="qsm-ui-select qsm-ui-input-sm">
					<?php foreach ( $ranges as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $range, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
					<?php do_action('qmn_quiz_taken_stats_options'); ?>
				</select>
				<button type="submit" class="qsm-ui-btn qsm-ui-btn-sm"><?php esc_html_e('Filter', 'quiz-master-next'); ?></button>
			</form>
		</div>
		<div class="qsm-ui-card-body">
			<div class="qsm-ui-stats-chart">
				<canvas id="graph_canvas"></canvas>
			</div>
		</div>
	</div>
	<?php
}


/**
 * Loads Stats From mlw_results
 *
 * Creates array of stats from counting the amount of rows in mlw_results according to the $type.
 *
 * @since 4.3.0
 * @param $type string The type of stat report
 * @param $amount int The amount of stats to pull
 * @return array The array of stats
 */
function qmn_load_stats( $type, $amount = 0 ) {
	$stats = array();
	switch ( $type ) {
		case 'daily':
			global $wpdb;
			for ( $i = 0; $i < $amount; $i++ ) {
				$stat_date = gmdate("Y-m-d", mktime(0, 0, 0, gmdate("m")  , gmdate("d") - $i, gmdate("Y")));
				$retrieved_stats = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $stat_date, $stat_date ) );
				array_unshift($stats, $retrieved_stats);
			}
			break;

		case 'weekly':
				global $wpdb;
				for ( $i = 0; $i < $amount; $i++ ) {
					$stat_date = gmdate("Y-m-d", mktime(0, 0, 0, gmdate("m")  , gmdate("d") - (6 + ($i * 7)), gmdate("Y")));
					$stat_end_date = gmdate("Y-m-d", mktime(0, 0, 0, gmdate("m")  , gmdate("d") - ($i * 7), gmdate("Y")));
					$retrieved_stats = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $stat_date, $stat_end_date ) );
					array_unshift($stats, $retrieved_stats);
				}
            break;

		case 'monthly':
			global $wpdb;
			for ( $i = 0; $i < $amount; $i++ ) {
				$stat_date = gmdate("Y-m-d", mktime(0, 0, 0, gmdate("m") - $i, 1, gmdate("Y")));
				$stat_end_date = gmdate("Y-m-t", mktime(0, 0, 0, gmdate("m") - $i, gmdate("d"), gmdate("Y")));
				$retrieved_stats = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $stat_date, $stat_end_date ) );
				array_unshift($stats, $retrieved_stats);
			}
			break;

		default:
			# code...
			break;
	}
	$stats = apply_filters('qmn_quiz_taken_stats_load_stats', $stats, $type, $amount);
	return $stats;
}
?>
