<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* The class contains all of the functions for the leaderboard widget.
*
* @return void
* @since 4.4.0
*/
class Mlw_Qmn_Leaderboard_Widget extends WP_Widget {

   	// constructor
    function __construct() {
        parent::__construct(false, $name = __('Quiz And Survey Master Leaderboard Widget', 'quiz-master-next'));
    }

    // widget form creation
    function form($instance) {
	    // Check values
		if( $instance) {
	     	$title = esc_attr($instance['title']);
	     	$quiz_id = esc_attr($instance['quiz_id']);
		} else {
			$title = '';
			$quiz_id = '';
		}
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'quiz-master-next'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('quiz_id'); ?>"><?php _e('Quiz ID', 'quiz-master-next'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('quiz_id'); ?>" name="<?php echo $this->get_field_name('quiz_id'); ?>" type="number" step="1" min="1" value="<?php echo $quiz_id; ?>" />
		</p>
		<?php
	}

    // widget update
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
      	// Fields
      	$instance['title'] = strip_tags($new_instance['title']);
      	$instance['quiz_id'] = strip_tags($new_instance['quiz_id']);
     	return $instance;
    }

    // widget display
    function widget($args, $instance) {
        extract( $args );
   		// these are the widget options
   		$title = apply_filters('widget_title', $instance['title']);
   		$quiz_id = $instance['quiz_id'];
    	echo $before_widget;
   		// Display the widget
   		echo '<div class="widget-text wp_widget_plugin_box">';
   		// Check if title is set
   		if ( $title ) {
      		echo $before_title . $title . $after_title;
   		}
   		$mlw_quiz_id = intval( $quiz_id );
		$mlw_quiz_leaderboard_display = "";


		global $wpdb;
		$mlw_quiz_options = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_quizzes WHERE quiz_id=%d AND deleted='0'", $mlw_quiz_id ) );
		foreach($mlw_quiz_options as $mlw_eaches) {
			$mlw_quiz_options = $mlw_eaches;
			break;
		}
		$sql = "SELECT * FROM " . $wpdb->prefix . "mlw_results WHERE quiz_id=%d AND deleted='0'";
		if ($mlw_quiz_options->system == 0)
		{
			$sql .= " ORDER BY correct_score DESC";
		}
		if ($mlw_quiz_options->system == 1)
		{
			$sql .= " ORDER BY point_score DESC";
		}
		$sql .= " LIMIT 10";
		$mlw_result_data = $wpdb->get_results( $wpdb->prepare( $sql, $mlw_quiz_id ) );

		$mlw_quiz_leaderboard_display = $mlw_quiz_options->leaderboard_template;
		$mlw_quiz_leaderboard_display = str_replace( "%QUIZ_NAME%" , $mlw_quiz_options->quiz_name, $mlw_quiz_leaderboard_display);

		$leader_count = 0;
		foreach($mlw_result_data as $mlw_eaches) {
			$leader_count++;
			if ($leader_count == 1) {$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
			if ($leader_count == 2) {$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
			if ($leader_count == 3) {$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
			if ($leader_count == 4) {$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
			if ($leader_count == 5) {$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_NAME%" , $mlw_eaches->name, $mlw_quiz_leaderboard_display);}
			if ($mlw_quiz_options->system == 0)
			{
				if ($leader_count == 1) {$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 2) {$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 3) {$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 4) {$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 5) {$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_SCORE%" , $mlw_eaches->correct_score."%", $mlw_quiz_leaderboard_display);}
			}
			if ($mlw_quiz_options->system == 1)
			{
				if ($leader_count == 1) {$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 2) {$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 3) {$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 4) {$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
				if ($leader_count == 5) {$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_SCORE%" , $mlw_eaches->point_score." Points", $mlw_quiz_leaderboard_display);}
			}
		}
		$mlw_quiz_leaderboard_display = str_replace( "%QUIZ_NAME%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_NAME%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%FIRST_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%SECOND_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%THIRD_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%FOURTH_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);
		$mlw_quiz_leaderboard_display = str_replace( "%FIFTH_PLACE_SCORE%" , " ", $mlw_quiz_leaderboard_display);

		echo $mlw_quiz_leaderboard_display;
   		echo '</div>';
   		echo $after_widget;
    }
}
?>
