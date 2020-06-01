<?php

function qsm_fetch_data_from_xml() {
    $file = esc_url('https://quizandsurveymaster.com/addons.xml');
    $response = wp_remote_post($file, array('sslverify' => false));
    
    if (is_wp_error($response) || $response['response']['code'] === 404 ) {
        return "<p>" . __('Something went wrong', 'quiz-master-next') . "</p>";
    }else{
        $body = wp_remote_retrieve_body($response);
        return $xml = simplexml_load_string($body);
    }    
}

add_action('qmn_quiz_created','qsm_redirect_to_edit_page',10,1);
/**
 * @since 6.4.5
 * @param int $quiz_id Quiz id.
 */
function qsm_redirect_to_edit_page($quiz_id){
    $url = admin_url( 'admin.php?page=mlw_quiz_options&&quiz_id=' . $quiz_id ); ?>
    <script>
        window.location.href = '<?php echo $url; ?>';
    </script>
    <?php
}

add_action('admin_init','qsm_add_author_column_in_db');
/**
 * @since 6.4.6
 * Insert new column in quiz table
 */
function qsm_add_author_column_in_db(){
    global $wpdb;
    $quiz_table_name = $wpdb->prefix . "mlw_quizzes";
    $row = $wpdb->get_row("SELECT * FROM $quiz_table_name");
    if (!isset($row->quiz_author_id)) {
        $wpdb->query("ALTER TABLE $quiz_table_name ADD quiz_author_id INT NOT NULL");
    }
    $result_table_name = $wpdb->prefix . "mlw_results";
    $row = $wpdb->get_row("SELECT * FROM $result_table_name");
    if ( !isset($row->unique_id) ) {
        $wpdb->query("ALTER TABLE $result_table_name ADD unique_id varchar(255) NOT NULL");
    }

}

add_action('admin_init', 'qsm_change_the_post_type');
/**
 * @since version 6.4.8
 * Transfer all quiz post to new cpt 'qsm_quiz'
 */
function qsm_change_the_post_type(){
    if( get_option('qsm_change_the_post_type', '') != '1' ){
        $post_arr = array(
            'post_type'      => 'quiz',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash')
        );
        $my_query = new WP_Query( $post_arr );
        
        if ( $my_query->have_posts() ) {
            while ( $my_query->have_posts() ) {
                $my_query->the_post();

                $post_id = get_the_ID();
                $post_obj = get_post( $post_id );                
                if($post_obj->post_status == 'trash'){
                    $post_obj->post_status = 'draft';
                }
                $post_obj->post_type = 'qsm_quiz';
                wp_update_post( $post_obj ); 
            }
            wp_reset_postdata();
        }
        update_option('qsm_change_the_post_type', '1');
        flush_rewrite_rules();
    }
}

/**
 * @since  7.0.0
 * @param arr $single_option
 */
function qsm_display_question_option($key, $single_option){
    $type = isset($single_option['type']) ? $single_option['type'] : 'text';
	$show = isset($single_option['show']) ? explode(',', $single_option['show']) : array();
	$show_class = '';
	if($show){
		foreach($show as $show_value){
			$show_class .= 'qsm_show_question_type_' . $show_value .' ';
		}
		$show_class .= ' qsm_hide_for_other';
	}
	
    switch ($type){
        case 'text':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label><?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?></label>
                <input type="text" name="<?php echo $key; ?>" value="<?php echo isset($single_option['default']) ? $single_option['default'] : ''; ?>" id="<?php echo $key ?>" />
            </div>
            <?php
        break;

        case 'number':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label><?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?></label>
                <input type="number" name="<?php echo $key; ?>" value="<?php echo isset($single_option['default']) ? $single_option['default'] : ''; ?>" id="<?php echo $key ?>" />
            </div>
            <?php
        break;

        case 'select':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label><?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?></label>
                <select name="<?php echo $key; ?>" id="<?php echo $key ?>">
                    <?php
                    $default = isset($single_option['default']) ? $single_option['default'] : '';
                    if(isset($single_option['options']) && is_array($single_option['options'])){
                        foreach ($single_option['options'] as $key => $value) {
                            $selected = $key === $default ? 'selected = selected' : '';
                            ?>
                            <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                        <?php
                        }
                    }
                    ?>
                </select>
            </div>
            <?php
        break;

        case 'textarea':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label><?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?></label>
                <textarea id="<?php echo $key ?>" name="<?php echo $key; ?>"><?php echo isset($single_option['default']) ? $single_option['default'] : ''; ?></textarea>
            </div>
            <?php
        break;

        case 'category':
            ?>
            <div id="category_area" class="qsm-row <?php echo $show_class; ?>">
                <label><?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?></label>
                <div id="categories">
                    <input type="radio" name="category" class="category-radio" id="new_category_new" value="new_category"><label for="new_category_new">New: <input type='text' id='new_category' value='' /></label>
                </div>
            </div>
            <?php
        break;

        case 'multi_checkbox':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label><?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?></label>
                <?php
                $parent_key = $key;
                $default = isset($single_option['default']) ? $single_option['default'] : '';
                if(isset($single_option['options']) && is_array($single_option['options'])){
                    foreach ($single_option['options'] as $key => $value) {
                        $selected = $key === $default ? 'checked' : '';                        
                        ?>
                        <input name="<?php echo $parent_key; ?>[]" type="checkbox" value="<?php echo $key; ?>" <?php echo $selected; ?> /> <?php echo $value; ?><br/>
                    <?php
                    }
                }
                ?>
            </div>
            <?php
        break;

        default:
        //Do nothing
    }

}