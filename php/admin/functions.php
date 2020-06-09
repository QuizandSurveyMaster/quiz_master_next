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

/**
 * @since 7.0
 * New quiz popup
 */
function qsm_create_new_quiz_wizard(){ ?>
    <div class="qsm-popup qsm-popup-slide" id="model-wizard" aria-hidden="true">
        <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
            <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
                <header class="qsm-popup__header">
                    <h2 class="qsm-popup__title" id="modal-2-title"><?php _e('Create New Quiz Or Survey', 'quiz-master-next'); ?></h2>
                    <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                </header>
                <main class="qsm-popup__content" id="modal-2-content">
                    <?php
                    $qsm_quiz_templates = array(
                        array(
                            'template_name' => __('Start from scratch', 'quiz-master-next'),
                            'priority' => '1',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                )
                            ),
                            'recommended_addon' => array(
                                array(
                                    'name' => 'Payment Integration',
                                    'link' => 'https://quizandsurveymaster.com/downloads/paypal-and-stripe-payment-integration/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Paypal-and-Stripe-Payment-Integration-300x170.jpg',
                                    'attribute' => 'recommended'
                                ),
                                array(
                                    'name' => 'Google Sheet',
                                    'link' => 'https://quizandsurveymaster.com/downloads/sync-with-google-sheets/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/03/first-1-300x170.jpg',
                                    'attribute' => 'required'
                                ),
                            )
                        ),
                        array(
                            'template_name' => __('Sample Template', 'quiz-master-next'),
                            'priority' => '2',
                            'template_img' => 'http://localhost/work/et/qsm/wp-content/uploads/2020/05/sample-quiz.png',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                ),
                                'enable_result_after_timer_end' => array(
                                    'option_name' => 'Force Submit',
                                    'value' => 0
                                )
                            ),
                        ),
                        array(
                            'template_name' => __('Customer Feedback', 'quiz-master-next'),
                            'priority' => '3',
                            'template_img' => 'http://localhost/work/et/qsm/wp-content/uploads/2020/05/sample-quiz.png',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                ),
                                'enable_result_after_timer_end' => array(
                                    'option_name' => 'Force Submit',
                                    'value' => 0
                                ),
                                'progress_bar' => array(
                                    'option_name' => 'Progress Bar',
                                    'value' => 0
                                )
                            ),
                        ),
                        array(
                            'template_name' => __('Event Planning', 'quiz-master-next'),
                            'priority' => '4',
                            'template_img' => 'http://localhost/work/et/qsm/wp-content/uploads/2020/05/sample-quiz.png',
                            'options' => array(
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 1
                                ),
                                'enable_result_after_timer_end' => array(
                                    'option_name' => 'Force Submit',
                                    'value' => 0
                                ),
                                'progress_bar' => array(
                                    'option_name' => 'Progress Bar',
                                    'value' => 0
                                )
                            ),
                        ),
                    );
                    $qsm_quiz_templates = apply_filters('qsm_wizard_quiz_templates', $qsm_quiz_templates);
                    $keys = array_column($qsm_quiz_templates, 'priority');
                    array_multisort($keys, SORT_ASC, $qsm_quiz_templates);
                    ?>
                    <form action="" method="post" id="new-quiz-form">
                        <div class="qsm-wizard-template-section">
                            <?php wp_nonce_field('qsm_new_quiz', 'qsm_new_quiz_nonce'); ?>
                            <input type="text" class="quiz_name" name="quiz_name" value="" placeholder="<?php _e('Enter quiz name here', 'quiz-master-next'); ?>" required/>
                            <div class="template-inner-wrap">
                                <h6><?php _e('Select Template', 'quiz-master-next'); ?></h6>
                                <div class="template-list">
                                    <?php 
                                    if( $qsm_quiz_templates ){
                                        foreach ($qsm_quiz_templates as $key => $single_template) {
                                            if(isset($single_template['priority']) && $single_template['priority'] == 1){ ?>
                                                <div class="template-list-inner" data-settings='<?php echo isset($single_template['options']) ? json_encode($single_template['options']) : ''; ?>' data-addons='<?php echo isset($single_template['recommended_addon']) ? json_encode($single_template['recommended_addon']) : ''; ?>'>
                                                    <div class="template-center-vertical">
                                                        <span class="dashicons dashicons-plus-alt2"></span>
                                                        <p class="start_scratch"><?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?></p>
                                                    </div>                                        
                                                </div>
                                            <?php                                
                                            }else{ ?>
                                                <div class="template-list-inner inner-json" data-settings='<?php echo isset($single_template['options']) ? json_encode($single_template['options']) : ''; ?>' data-addons='<?php echo isset($single_template['recommended_addon']) ? json_encode($single_template['recommended_addon']) : ''; ?>'>
                                                    <h3><?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?></h3>
                                                    <div class="template-center-vertical">
                                                        <?php
                                                        if( isset($single_template['template_img']) ){ ?>
                                                            <img src="<?php echo $single_template['template_img']; ?>" title="<?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?>">
                                                        <?php                                                        
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php                                            
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="qsm-wizard-setting-section">
                            <div id="accordion">
                                <h3><?php _e('Quiz Settings', 'quiz-master-next'); ?></h3>
                                <div id="quiz_settings_wrapper"></div>
                                <h3><?php _e('Recommended Addons', 'quiz-master-next'); ?></h3>
                                <div id="recomm_addons_wrapper"></div>
                              </div>
                        </div>
                    </form>
                </main>
                <footer class="qsm-popup__footer">
                    <button id="create-quiz-button" class="button button-primary"><?php _e('Create Quiz', 'quiz-master-next'); ?></button>
                    <button class="button" data-micromodal-close aria-label="Close this dialog window"><?php _e('Cancel', 'quiz-master-next'); ?></button>
                </footer>
            </div>
        </div>
    </div>
<?php
}