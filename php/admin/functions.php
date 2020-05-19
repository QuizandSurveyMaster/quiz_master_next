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
    if( get_option('qsm_update_db_column', '') != '1' ){
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
        update_option('qsm_update_db_column', '1');
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