<?php

function qsm_fetch_data_from_xml() {
    $file = esc_url('http://localhost/work/et/wp-dev/addons.xml');
    $response = wp_remote_post($file, array('sslverify' => false));
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return "<p>" . __('Something went wrong', 'quiz-master-next') . " : $error_message" . "</p>";
    }else{
        $body = wp_remote_retrieve_body($response);
        return $xml = simplexml_load_string($body);
    }    
}
