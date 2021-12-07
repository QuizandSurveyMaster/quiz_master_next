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
	if ( ! current_user_can('moderate_comments') ) {
		return;
	}
	global $mlwQuizMasterNext;
	$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'quiz-and-survey-submissions';
	$tab_array = $mlwQuizMasterNext->pluginHelper->get_stats_tabs();
	?>
	<div class="wrap">
		<h2><?php esc_html_e('Quiz/Survey Statistics', 'quiz-master-next'); ?></h2>
		<?php qsm_show_adverts(); ?>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $tab_array as $tab ) {
				$active_class = '';
				if ( $active_tab === $tab['slug'] ) {
					$active_class = ' nav-tab-active';
				}
				echo '<a href="?page=qmn_stats&tab="' . esc_attr( $tab['slug'] ) . '" class="nav-tab' . esc_attr( $active_class ) . '">' . esc_html( $tab['title'] ) . '</a>';
			}
			?>
		</h2>
		<div>
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
add_action("plugins_loaded", 'qmn_stats_overview_tab');

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
	?>
	<style>
		.postbox:after {
			display:table;
			content:" ";
			clear:both;
		}
		.postbox {
			padding: 10px 1%;
		}
		.postbox h3 {
			padding: 0;
			margin: 0 0 20px;
		}
	</style>
	<div class="metabox-holder">
		<div class="postbox">
			<form action="" method="post">
				<select name="range">
					<option value="daily" <?php if ( "daily" === $range ) { echo 'selected="selected"'; } ?>><?php esc_html_e('Daily', 'quiz-master-next'); ?></option>
					<option value="weekly" <?php if ( "weekly" === $range ) { echo 'selected="selected"'; } ?>><?php esc_html_e('Weekly', 'quiz-master-next'); ?></option>
					<option value="monthly" <?php if ( "monthly" === $range ) { echo 'selected="selected"'; } ?>><?php esc_html_e('Monthly', 'quiz-master-next'); ?></option>
					<?php do_action('qmn_quiz_taken_stats_options'); ?>
				</select>
				<button type="submit" class="button"><?php esc_html_e('Filter', 'quiz-master-next'); ?></button>
			</form>
			<div>
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
