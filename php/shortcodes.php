<?php
/**
 * Displays a link to a quiz using ID. Used [qsm_link id=1]Click Here[/qsm_link]
 *
 * @since 5.1.0
 * @param array $atts Attributes from add_shortcode function
 * @param string $content The text to be used for the link
 * @return string The HTML the shortcode will be replaced with
 */
function qsm_quiz_link_shortcode( $atts, $content = '' ) {
    extract(shortcode_atts(array(
        'id' => 0,
        'class' => '',
        'target' => ''
    ), $atts));
    $id = intval( $id );

    // Find the permalink by finding the post with the meta_key 'quiz_id' of supplied quiz
    $permalink = '';
	$my_query = new WP_Query( array( 'post_type' => 'quiz', 'meta_key' => 'quiz_id', 'meta_value' => $id, 'posts_per_page' => 1, 'post_status' => 'publish' ) );
	if ( $my_query->have_posts() ) {
	  while ( $my_query->have_posts() ) {
		$my_query->the_post();
		$permalink = get_permalink();
	  }
	}
    wp_reset_postdata();
    
    // Craft the target attribute if one is passed to shortcode
    $target_html = '';
    if ( ! empty( $target ) ) {
        $target_html = "target='" . esc_attr( $target ) . "'";
    }
    return "<a href='" . esc_url( $permalink ) . "' class='" . esc_attr( $class ) . "' $target_html>" . esc_html( $content ) . "</a>"; 
}
add_shortcode( 'qsm_link', 'qsm_quiz_link_shortcode' );

/**
 * Displays a list of most recently created quizes [qsm_recent_quizzes]
 * @param - attrs - array of shortcode attributes - no_of_quizzes, include_future_quizzes
 * @param - no_of_quizzes - Number of most recent quizzes to be displayed (default 10)
 * @param - include_future_quizzes - Whether to display future scheduled quizzes or not - yes/no (default yes)
 * @since 5.1.0
 * @return string - list of quizzes
 * Shortcode call - [qsm_recent_quizzes no_of_quizzes=5 include_future_quizzes='no' ]
 */

function qsm_display_recent_quizzes($attrs) {

    $no_of_quizzes = isset($attrs['no_of_quizzes'])? $attrs['no_of_quizzes']:10;
    $include_future_quizzes = isset($attrs['include_future_quizzes'])? $attrs['include_future_quizzes']: true;
    global $wpdb;
    wp_enqueue_style('quizzes-list', plugins_url('../css/quizzes-list.css', __FILE__));

    $quiz_tbl = $wpdb->prefix.'mlw_quizzes';
    
    $query = "SELECT quiz_id, quiz_name, quiz_settings FROM $quiz_tbl WHERE deleted=0 ORDER BY  quiz_id DESC";
    $quizzes = $wpdb->get_results($query);
    $result = '<div class="outer-con">';
    $i = 0;
    foreach($quizzes as $quiz) {
        if($i < $no_of_quizzes) {
            $setting = unserialize($quiz->quiz_settings);
            $options = unserialize($setting['quiz_options']);
            
            $start_date = $options['scheduled_time_start'];
            $end_date = $options['scheduled_time_end'];
            $today = date('m/d/Y');
            if($end_date!='' && $end_date < $today)
                continue;
            else if($include_future_quizzes == 'no' && $start_date > $today) 
                continue;
            else {
                $title = $quiz->quiz_name;
                $id = $quiz->quiz_id;
                $url = do_shortcode("[qsm_link id='$id'] Take Quiz [/qsm_link]");
                $result .= "<div class='ind-quiz'>
                                <div class='quiz-heading'>
                                    {$title} 
                                </div>
                                <div class='quiz-url'>
                                    {$url}
                                </div>
                            </div>";
                $result .= "<div class='clear'></div>";
                $i++;
            }
        }
    }
    if($i == 0)
        $result .= "No quiz found";
    $result .= "</div>";
    return $result;

}
add_shortcode('qsm_recent_quizzes', 'qsm_display_recent_quizzes');