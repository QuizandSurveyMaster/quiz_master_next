<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This function adds a widget to the dashboard in WordPress.
 *
 * @return void
 * @since 4.4.0
 */
function qmn_add_dashboard_widget() {
	if ( current_user_can( 'publish_posts' ) ) {
		wp_add_dashboard_widget(
			'qmn_snapshot_widget',
			__( 'Quiz And Survey Master Snapshot', 'quiz-master-next' ),
			'qmn_snapshot_dashboard_widget'
		);
	}
}

add_action( 'wp_dashboard_setup', 'qmn_add_dashboard_widget' );

/**
 * This function creates the actual widget that is added to the dashboard.
 *
 * This widget adds things like the most popular/least popular quiz. How many people have taken the quiz etc.
 *
 * @param type description
 * @return type description
 * @since 4.4.0
 */
function qmn_snapshot_dashboard_widget() {
	global $wpdb;
	$mlw_qmn_today_taken        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", gmdate( 'Y-m-d', current_time( 'timestamp' ) ), gmdate( 'Y-m-d', current_time( 'timestamp' ) ) ) );
	$mlw_last_week              = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 7, gmdate( 'Y' ) );
	$mlw_last_week              = gmdate( 'Y-m-d', $mlw_last_week );
	$mlw_qmn_last_weekday_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_last_week, $mlw_last_week ) );
	if ( 0 != intval( $mlw_qmn_last_weekday_taken ) ) {
		$mlw_qmn_analyze_today = round( ( ( $mlw_qmn_today_taken - $mlw_qmn_last_weekday_taken ) / $mlw_qmn_last_weekday_taken ) * 100, 2 );
	} else {
		$mlw_qmn_analyze_today = $mlw_qmn_today_taken * 100;
	}

	$mlw_this_week           = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 6, gmdate( 'Y' ) );
	$mlw_this_week           = gmdate( 'Y-m-d', $mlw_this_week );
	$mlw_qmn_this_week_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_this_week, gmdate( 'Y-m-d' ) ) );

	$mlw_last_week_start     = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 13, gmdate( 'Y' ) );
	$mlw_last_week_start     = gmdate( 'Y-m-d', $mlw_last_week_start );
	$mlw_last_week_end       = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 7, gmdate( 'Y' ) );
	$mlw_last_week_end       = gmdate( 'Y-m-d', $mlw_last_week_end );
	$mlw_qmn_last_week_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_last_week_start, $mlw_last_week_end ) );

	if ( 0 !== intval( $mlw_qmn_last_week_taken ) ) {
		$mlw_qmn_analyze_week = round( ( ( $mlw_qmn_this_week_taken - $mlw_qmn_last_week_taken ) / $mlw_qmn_last_week_taken ) * 100, 2 );
	} else {
		$mlw_qmn_analyze_week = $mlw_qmn_this_week_taken * 100;
	}

	$mlw_this_month           = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 29, gmdate( 'Y' ) );
	$mlw_this_month           = gmdate( 'Y-m-d', $mlw_this_month );
	$mlw_qmn_this_month_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_this_month, gmdate( 'Y-m-d' ) ) );

	$mlw_last_month_start     = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 59, gmdate( 'Y' ) );
	$mlw_last_month_start     = gmdate( 'Y-m-d', $mlw_last_month_start );
	$mlw_last_month_end       = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 30, gmdate( 'Y' ) );
	$mlw_last_month_end       = gmdate( 'Y-m-d', $mlw_last_month_end );
	$mlw_qmn_last_month_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_last_month_start, $mlw_last_month_end ) );

	if ( 0 != intval( $mlw_qmn_last_month_taken ) ) {
		$mlw_qmn_analyze_month = round( ( ( $mlw_qmn_this_month_taken - $mlw_qmn_last_month_taken ) / $mlw_qmn_last_month_taken ) * 100, 2 );
	} else {
		$mlw_qmn_analyze_month = $mlw_qmn_this_month_taken * 100;
	}

	$mlw_this_quater           = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 89, gmdate( 'Y' ) );
	$mlw_this_quater           = gmdate( 'Y-m-d', $mlw_this_quater );
	$mlw_qmn_this_quater_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_this_quater, gmdate( 'Y-m-d' ) ) );

	$mlw_last_quater_start     = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 179, gmdate( 'Y' ) );
	$mlw_last_quater_start     = gmdate( 'Y-m-d', $mlw_last_quater_start );
	$mlw_last_quater_end       = mktime( 0, 0, 0, gmdate( 'm' ), gmdate( 'd' ) - 90, gmdate( 'Y' ) );
	$mlw_last_quater_end       = gmdate( 'Y-m-d', $mlw_last_quater_end );
	$mlw_qmn_last_quater_taken = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE (time_taken_real BETWEEN '%1s 00:00:00' AND '%2s 23:59:59') AND deleted=0", $mlw_last_quater_start, $mlw_last_quater_end ) );

	if ( 0 != intval( $mlw_qmn_last_quater_taken ) ) {
		$mlw_qmn_analyze_quater = round( ( ( $mlw_qmn_this_quater_taken - $mlw_qmn_last_quater_taken ) / $mlw_qmn_last_quater_taken ) * 100, 2 );
	} else {
		$mlw_qmn_analyze_quater = $mlw_qmn_this_quater_taken * 100;
	}

	$mlw_stat_total_active_quiz = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_quizzes WHERE deleted=0 LIMIT 1" );
	$mlw_stat_total_questions   = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_questions WHERE deleted=0 LIMIT 1" );

	$mlw_stat_most_popular_quiz  = $wpdb->get_row( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE deleted=0 ORDER BY quiz_taken Desc LIMIT 1" );
	$mlw_stat_least_popular_quiz = $wpdb->get_row( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE deleted=0 ORDER BY quiz_taken ASC LIMIT 1" );
	?>
	<style>
		.qmn_dashboard_list
		{
			overflow: hidden;
			margin: 0;
		}
		.qmn_dashboard_list li:first-child
		{
			border-top: 0;
		}
		.qmn_full_width
		{
			width: 100%;
		}
		.qmn_half_width
		{
			width: 50%;
		}
		.qmn_dashboard_element
		{
			float: left;
			padding: 0;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
			margin: 0;
			border-top: 1px solid #ececec;
			color: #aaa;
		}
		.qmn_dashboard_inside
		{
			display: block;
			color: #aaa;
			padding: 9px 12px;
			-webkit-transition: all ease .5s;
			position: relative;
			font-size: 12px;
		}
		.qmn_dashboard_inside strong
		{
			font-size: 18px;
			line-height: 1.2em;
			font-weight: 400;
			display: block;
			color: #21759b;
		}
		.qmn_dashboard_graph
		{
			width: 25%;
			height: 10px;
			display: block;
			float: right;
			position: absolute;
			right: 0;
			top: 50%;
			margin-right: 12px;
			margin-top: -1.25em;
			font-size: 18px
		}
		.qmn_dashboard_graph img
		{
			width: 15px;
			height: 15px;
		}
	</style>
	<ul class="qmn_dashboard_list">
		<li class="qmn_dashboard_element qmn_full_width">
			<div class="qmn_dashboard_inside">
				<strong><?php echo esc_html( $mlw_qmn_today_taken ); ?></strong>
				<?php esc_html_e( 'quizzes taken today', 'quiz-master-next' ); ?>
				<span class="qmn_dashboard_graph">
					<?php
					echo esc_html( $mlw_qmn_analyze_today . '% ' );
					if ( $mlw_qmn_analyze_today >= 0 ) {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/green_triangle.png'/>";
					} else {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/red_triangle.png'/>";
					}
					?>
				</span>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_full_width">
			<div class="qmn_dashboard_inside">
				<strong><?php echo esc_html( $mlw_qmn_this_week_taken ); ?></strong>
				<?php esc_html_e( 'quizzes taken last 7 days', 'quiz-master-next' ); ?>
				<span class="qmn_dashboard_graph">
					<?php
					echo esc_html( $mlw_qmn_analyze_week . '% ' );
					if ( $mlw_qmn_analyze_week >= 0 ) {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/green_triangle.png'/>";
					} else {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/red_triangle.png'/>";
					}
					?>
				</span>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_full_width">
			<div class="qmn_dashboard_inside">
				<strong><?php echo esc_html( $mlw_qmn_this_month_taken ); ?></strong>
				<?php esc_html_e( 'quizzes taken last 30 days', 'quiz-master-next' ); ?>
				<span class="qmn_dashboard_graph">
					<?php
					echo esc_html( $mlw_qmn_analyze_month . '% ' );
					if ( $mlw_qmn_analyze_month >= 0 ) {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/green_triangle.png'/>";
					} else {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/red_triangle.png'/>";
					}
					?>
				</span>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_full_width">
			<div class="qmn_dashboard_inside">
				<strong><?php echo esc_html( $mlw_qmn_this_quater_taken ); ?></strong>
				<?php esc_html_e( 'quizzes taken last 120 days', 'quiz-master-next' ); ?>
				<span class="qmn_dashboard_graph">
					<?php
					echo esc_html( $mlw_qmn_analyze_quater . '% ' );
					if ( $mlw_qmn_analyze_quater >= 0 ) {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/green_triangle.png'/>";
					} else {
						echo "<img src='" . esc_url( plugin_dir_url( __FILE__ ) ) . "../images/red_triangle.png'/>";
					}
					?>
				</span>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_half_width">
			<div class="qmn_dashboard_inside">
				<strong><?php echo esc_html( $mlw_stat_total_active_quiz ); ?></strong>
				<?php esc_html_e( 'total active quizzes', 'quiz-master-next' ); ?>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_half_width">
			<div class="qmn_dashboard_inside">
				<strong><?php echo esc_html( $mlw_stat_total_questions ); ?></strong>
				<?php esc_html_e( 'total active questions', 'quiz-master-next' ); ?>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_half_width">
			<div class="qmn_dashboard_inside">
				<strong>
				<?php
				if ( ! is_null( $mlw_stat_most_popular_quiz ) ) {
					echo wp_kses_post( $mlw_stat_most_popular_quiz->quiz_name );
				}
				?>
				</strong>
				<?php esc_html_e( 'most popular quiz', 'quiz-master-next' ); ?>
			</div>
		</li>
		<li class="qmn_dashboard_element qmn_half_width">
			<div class="qmn_dashboard_inside">
				<strong>
				<?php
				if ( ! is_null( $mlw_stat_least_popular_quiz ) ) {
					echo wp_kses_post( $mlw_stat_least_popular_quiz->quiz_name );
				}
				?>
				</strong>
				<?php esc_html_e( 'least popular quiz', 'quiz-master-next' ); ?>
			</div>
		</li>
	</ul>
	<?php
}
?>
