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
    $row = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$quiz_table_name' AND column_name = 'quiz_author_id'");
    if (empty($row)) {
        $wpdb->query("ALTER TABLE $quiz_table_name ADD quiz_author_id INT NOT NULL");
    }
//    $result_table = $wpdb->prefix . "mlw_results";
//    $get_column_schema = $wpdb->get_results("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$result_table' AND COLUMN_NAME = 'quiz_results'", ARRAY_A);
//    if($get_column_schema && isset($get_column_schema[0]) && $get_column_schema[0]['DATA_TYPE'] == 'text'){
//        $wpdb->query("ALTER TABLE wp_mlw_results ALTER COLUMN quiz_results MEDIUMTEXT(16M)");
//    }
}