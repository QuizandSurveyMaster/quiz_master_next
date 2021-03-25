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
function qsm_add_author_column_in_db() {

	if( get_option('qsm_update_db_column', '') != '1' ) {

		global $wpdb;

		/*
		 * Array of table and its column mapping.
		 * Each array's item key refers to the table to be altered and its value refers 
		 * to the array of column and its definition to be added.
		 */
		$table_column_arr = array( 
			$wpdb->prefix . 'mlw_quizzes' => array( 'quiz_author_id' => 'INT NOT NULL' ),
			$wpdb->prefix . 'mlw_results' => array( 'unique_id'      => 'VARCHAR(255) NOT NULL' ),
		);

		foreach( $table_column_arr as $table => $column_def ) {
			foreach( $column_def  as $col_name => $col_def ) {
				$table_col_obj = $wpdb->get_results( $wpdb->prepare(
					'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $table, $col_name 
				) );

				if ( empty( $table_col_obj ) ) {
					$wpdb->query( 'ALTER TABLE ' . $table . ' ADD ' . $col_name . ' ' . $col_def );
				}
			}
		}

		update_option( 'qsm_update_db_column', '1' );

	}
        
        //Update result db        
        if( get_option('qsm_update_result_db_column', '') != '1' ){
            global $wpdb;
            $result_table_name = $wpdb->prefix . "mlw_results";
            $table_result_col_obj = $wpdb->get_results( $wpdb->prepare(
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'form_type' 
            ) );
            if ( empty( $table_result_col_obj ) ) {
                $wpdb->query("ALTER TABLE $result_table_name ADD form_type INT NOT NULL");
            }
            update_option('qsm_update_result_db_column', '1');
        }
        
        /**
         * Changed the system word to quiz_system in quiz table
         * @since 7.0.0
         */
        if( get_option('qsm_update_quiz_db_column', '') != '1' ){
            global $wpdb;
            $quiz_table_name = $wpdb->prefix . "mlw_quizzes";
            $table_quiz_col_obj = $wpdb->get_results( $wpdb->prepare(
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $quiz_table_name, 'system' 
            ) );
            if ( !empty( $table_quiz_col_obj ) ) {
                    $wpdb->query("ALTER TABLE $quiz_table_name CHANGE `system` `quiz_system` INT(11) NOT NULL;");
            }
            update_option('qsm_update_quiz_db_column', '1');
        }
        
        /**
         * Changed result table column data type
         * @since 7.0.1
         */
        if( get_option('qsm_update_result_db_column_datatype', '') != '1' ){
            global $wpdb;
            $result_table_name = $wpdb->prefix . "mlw_results";
            $table_quiz_result_obj = $wpdb->get_row( $wpdb->prepare(
                'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'quiz_results' 
            ), ARRAY_A );            
            if ( isset( $table_quiz_result_obj['DATA_TYPE'] ) && $table_quiz_result_obj['DATA_TYPE'] == 'text' ) {
                    $wpdb->query("ALTER TABLE $result_table_name CHANGE `quiz_results` `quiz_results` LONGTEXT;");
            }            
            update_option('qsm_update_result_db_column_datatype', '1');
        }
        
        /**
         * Add new column in question table
         * @since 7.0.3
         */
        if( get_option('qsm_add_new_column_question_table_table', '1') <= 3 ){            
            $total_count_val = get_option('qsm_add_new_column_question_table_table', '1');            
            global $wpdb;
            $question_table_name = $wpdb->prefix . "mlw_questions";
            $table_result_col_obj = $wpdb->get_results( $wpdb->prepare(
                'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $question_table_name, 'deleted_question_bank' 
            ) );
            if ( empty( $table_result_col_obj ) ) {
                $wpdb->query("ALTER TABLE $question_table_name ADD deleted_question_bank INT NOT NULL");
            }
            $inc_val = $total_count_val + 1;            
            update_option('qsm_add_new_column_question_table_table', $inc_val);
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
			$show_class .= 'qsm_show_question_type_' . trim( $show_value ) .' ';
		}
		$show_class .= ' qsm_hide_for_other';
	}
    $tooltip = '';
    $document_text = '';
    if( isset($single_option['tooltip']) && $single_option['tooltip'] != '' ){
        $tooltip .= '<span class="dashicons dashicons-editor-help qsm-tooltips-icon">';
        $tooltip .= '<span class="qsm-tooltips">' . $single_option['tooltip'] . '</span>';
        $tooltip .= '</span>';
    }
    if( isset($single_option['documentation_link']) && $single_option['documentation_link'] != '' ){
        $document_text .= '<a class="qsm-question-doc" href="'. $single_option['documentation_link'] .'" target="_blank" title="'. __('View Documentation', 'quiz-master-next') .'">';
        $document_text .= '<span class="dashicons dashicons-media-document"></span>';
        $document_text .= '</a>';
    }
    switch ($type){         
        case 'text':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>                    
                    <?php echo $document_text; ?>
                </label>                
                <input type="text" name="<?php echo $key; ?>" value="<?php echo isset($single_option['default']) ? $single_option['default'] : ''; ?>" id="<?php echo $key ?>" />
            </div>
            <?php
        break;

        case 'number':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>
                    <?php echo $document_text; ?>
                </label>
                <input type="number" name="<?php echo $key; ?>" value="<?php echo isset($single_option['default']) ? $single_option['default'] : ''; ?>" id="<?php echo $key ?>" />
            </div>
            <?php
        break;

        case 'select':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>
                    <?php echo $document_text; ?>
                </label>
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
                <label>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>
                    <?php echo $document_text; ?>
                </label>
                <textarea id="<?php echo $key ?>" name="<?php echo $key; ?>"><?php echo isset($single_option['default']) ? $single_option['default'] : ''; ?></textarea>
            </div>
            <?php
        break;

        case 'category':
            ?>
            <div id="category_area" class="qsm-row <?php echo $show_class; ?>">
                <label>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>
                    <?php echo $document_text; ?>
                </label>
                <div id="categories">
                    <a id="qsm-category-add-toggle" class="hide-if-no-js">
                        <?php _e( '+ Add New Category', 'quiz-master-next' ); ?>
                    </a>
                    <p id="qsm-category-add" style="display: none;">
                        <input type="radio" style="display: none;" name="category" class="category-radio" id="new_category_new" value="new_category"><label for="new_category_new"><input type='text' id='new_category' value='' placeholder="Add new category" /></label>
                    </p>
                </div>
            </div>
            <?php
        break;

        case 'multi_checkbox':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
                <label>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>
                    <?php echo $document_text; ?>
                </label>
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
        
        case 'single_checkbox':
            ?>
            <div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">                
                <label>
                    <?php
                    $parent_key = $key;
                    $default = isset($single_option['default']) ? $single_option['default'] : '';
                    if(isset($single_option['options']) && is_array($single_option['options'])){
                        foreach ($single_option['options'] as $key => $value) {
                            $selected = $key === $default ? 'checked' : '';
                            ?>
                            <input name="<?php echo $parent_key; ?>" id="<?php echo $parent_key ?>" type="checkbox" value="<?php echo $key; ?>" <?php echo $selected; ?> />
                        <?php
                        }
                    }
                    ?>
                    <?php echo isset($single_option['label']) ? $single_option['label'] : ''; ?>
                    <?php echo $tooltip; ?>
                    <?php echo $document_text; ?>
                </label>
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
                                'form_type' => array(
                                    'option_name' => 'Form Type',
                                    'value' => 0
                                ),
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 0
                                )
                            ),
                            'recommended_addon' => array(
                                array(
                                    'name' => 'Reporting And Analysis',
                                    'link' => 'https://quizandsurveymaster.com/downloads/results-analysis/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Reporting-And-Analysis.jpg',
                                    'attribute' => 'recommended'
                                ),
                                array(
                                    'name' => 'Export & Import',
                                    'link' => 'https://quizandsurveymaster.com/downloads/export-import/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Export-Import.jpg',
                                    'attribute' => 'recommended'
                                ),
                            )
                        ),
                        array(
                            'template_name' => __('Simple Quiz', 'quiz-master-next'),
                            'priority' => '2',
                            'template_img' => QSM_PLUGIN_URL . '/assets/simple-quiz.png',
                            'options' => array(
                                'form_type' => array(
                                    'option_name' => 'Form Type',
                                    'value' => 0
                                ),
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                     
                                'require_log_in' => array(
                                    'option_name' => 'Require User Login',
                                    'value' => 0
                                ),
                                'progress_bar' => array(
                                    'option_name' => 'Show progress bar',
                                    'value' => 0
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
                                    'name' => 'Recaptcha',
                                    'link' => 'https://quizandsurveymaster.com/downloads/recaptcha/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/reCaptcha.jpg',
                                    'attribute' => 'recommended'
                                ),
                            )
                        ),
                        array(
                            'template_name' => __('Time Based Quiz', 'quiz-master-next'),
                            'priority' => '3',
                            'template_img' => QSM_PLUGIN_URL . '/assets/time-based-quiz.png',
                            'options' => array(
                                'form_type' => array(
                                    'option_name' => 'Form Type',
                                    'value' => 0
                                ),
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                                
                                'timer_limit' => array(
                                    'option_name' => 'Time Limit (in Minute)',
                                    'value' => 0
                                )
                            ),
                            'recommended_addon' => array(
                                array(
                                    'name' => 'Save and Resume',
                                    'link' => 'https://quizandsurveymaster.com/downloads/save-and-resume/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Save-and-Resume.jpg',
                                    'attribute' => 'recommended'
                                ),
                                array(
                                    'name' => 'Advanced Timer',
                                    'link' => 'https://quizandsurveymaster.com/downloads/wordpress-quiz-timer-advanced/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/35.jpg',
                                    'attribute' => 'recommended'
                                ),
                            )
                        ),
                        array(
                            'template_name' => __('Survey', 'quiz-master-next'),
                            'priority' => '4',
                            'template_img' => QSM_PLUGIN_URL . '/assets/survey-quiz.png',
                            'options' => array(
                                'form_type' => array(
                                    'option_name' => 'Form Type',
                                    'value' => 1
                                ),
                                'system' => array(
                                    'option_name' => 'Graded System',
                                    'value' => 0
                                ),                                
                                'progress_bar' => array(
                                    'option_name' => 'Progress Bar',
                                    'value' => 0
                                )
                            ),
                            'recommended_addon' => array(
                                array(
                                    'name' => 'Daily Limit',
                                    'link' => 'https://quizandsurveymaster.com/downloads/daily-limit/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Daily-Limit.jpg',
                                    'attribute' => 'recommended'
                                ),
                                array(
                                    'name' => 'Google Sheet Connector',
                                    'link' => 'https://quizandsurveymaster.com/downloads/sync-with-google-sheets/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/03/first-1.jpg',
                                    'attribute' => 'recommended'
                                ),
                                array(
                                    'name' => 'Mailchimp Integration',
                                    'link' => 'https://quizandsurveymaster.com/downloads/mailchimp-integration/',
                                    'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/MailChimp-Integration.jpg',
                                    'attribute' => 'recommended'
                                ),
                            )
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
                                                    <div class="template-center-vertical">
                                                        <?php
                                                        if( isset($single_template['template_img']) ){ ?>
                                                            <img src="<?php echo $single_template['template_img']; ?>" title="<?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?>">
                                                        <?php                                                        
                                                        }
                                                        ?>
                                                    </div>
                                                    <h3><?php echo isset($single_template['template_name']) ? $single_template['template_name'] : ''; ?></h3>
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

/**
 * @since 7.0
 * @return array Template Variable
 */
function qsm_text_template_variable_list(){
    $variable_list = array(
        '%POINT_SCORE%' => __('Score for the quiz when using points', 'quiz-master-next'),
        '%MAXIMUM_POINTS%' => __('Maximum possible points one can score', 'quiz-master-next'),
        '%AVERAGE_POINT%' => __('The average amount of points user had per question', 'quiz-master-next'),
        '%AMOUNT_CORRECT%' => __('The number of correct answers the user had', 'quiz-master-next'),
        '%AMOUNT_INCORRECT%' => __('The number of incorrect answers the user had', 'quiz-master-next'),
        '%AMOUNT_ATTEMPTED%' => __('The number of questions are attempted', 'quiz-master-next'),
        '%TOTAL_QUESTIONS%' => __('The total number of questions in the quiz', 'quiz-master-next'),
        '%CORRECT_SCORE%' => __('Score for the quiz when using correct answers', 'quiz-master-next'),
        '%USER_NAME%' => __('The name the user entered before the quiz', 'quiz-master-next'),
		'%FULL_NAME%' => __('The full name of user with first name and last name', 'quiz-master-next'),
        '%USER_BUSINESS%' => __('The business the user entered before the quiz', 'quiz-master-next'),
        '%USER_PHONE%' => __('The phone number the user entered before the quiz', 'quiz-master-next'),
        '%USER_EMAIL%' => __('The email the user entered before the quiz', 'quiz-master-next'),
        '%QUIZ_NAME%' => __('The name of the quiz', 'quiz-master-next'),
        '%QUIZ_LINK%' => __('The link of the quiz', 'quiz-master-next'),
        '%QUESTIONS_ANSWERS%' => __('Shows the question, the answer the user provided, and the correct answer', 'quiz-master-next'),
        '%COMMENT_SECTION%' => __('The comments the user entered into comment box if enabled', 'quiz-master-next'),
        '%TIMER%' => __('The amount of time user spent on quiz in seconds', 'quiz-master-next'),
        '%TIMER_MINUTES%' => __('The amount of time user spent on quiz in minutes', 'quiz-master-next'),
        '%CATEGORY_POINTS_X%' => __('X: Category name - The amount of points a specific category earned.', 'quiz-master-next'),
        '%CATEGORY_SCORE_X%' => __('X: Category name - The score a specific category earned.', 'quiz-master-next'),
        '%CATEGORY_AVERAGE_POINTS%' => __('The average points from all categories.', 'quiz-master-next'),
        '%CATEGORY_AVERAGE_SCORE%' => __('The average score from all categories.', 'quiz-master-next'),
        '%QUESTION%' => __('The question that the user answered', 'quiz-master-next'),
        '%USER_ANSWER%' => __('The answer the user gave for the question', 'quiz-master-next'),
        '%USER_ANSWERS_DEFAULT%' => __('The answer the user gave for the question with default design', 'quiz-master-next'),
        '%CORRECT_ANSWER%' => __('The correct answer for the question', 'quiz-master-next'),
        '%USER_COMMENTS%' => __('The comments the user provided in the comment field for the question', 'quiz-master-next'),
        '%CORRECT_ANSWER_INFO%' => __('Reason why the correct answer is the correct answer', 'quiz-master-next'),
        '%CURRENT_DATE%' => __('The Current Date', 'quiz-master-next'),
        '%QUESTION_POINT_SCORE%' => __('Point Score of the question', 'quiz-master-next'),
        '%QUESTION_MAX_POINTS%' => __('Maximum points of the question', 'quiz-master-next'),
		'%FACEBOOK_SHARE%' => __('Displays button to share on Facebook.', 'quiz-master-next'),
		'%TWITTER_SHARE%' => __('Displays button to share on Twitter.', 'quiz-master-next'),
		'%RESULT_LINK%' => __('The link of the result page.', 'quiz-master-next'),
    );
    $variable_list = apply_filters('qsm_text_variable_list', $variable_list);
    return $variable_list;
}

add_action('admin_init', 'qsm_update_question_type_col_val');

/**
 * Replace `fill-in-the-blank` value in question_type_column for Fill 
 * In The Blank question types.
 *
 * @since version 6.4.12
 */
function qsm_update_question_type_col_val() {

	global $wpdb;
	global $mlwQuizMasterNext;

	if ( version_compare( $mlwQuizMasterNext->version, '6.4.12', '<' ) ) {
		if( get_option('qsm_upated_question_type_val') != '1' ) {
			$table_name  = $wpdb->prefix . 'mlw_questions';
			$status      = $wpdb->query(
				$wpdb->prepare( 
					"UPDATE " . $table_name . " SET `question_type_new` = REPLACE( `question_type_new`, 'fill-in-the-blank', %d )", 14 )
				);

			if( $status ) {
				update_option('qsm_upated_question_type_val', '1');
			}
		}
	}
}

/**
 * Check and create table if not present
 * 
 * @since 7.0.0
 */
function qsm_check_create_tables(){
    global $wpdb;
    $quiz_table_name = $wpdb->prefix . "mlw_quizzes";
    if( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_table_name'" ) != $quiz_table_name ) {
        QSM_Install::install();
    }
}
add_action('admin_init', 'qsm_check_create_tables');

/**
 * Redirect the admin old slug to new slug
 * 
 * @since 7.0.0
 */
function qsm_admin_page_access_func(){
    if( isset( $_GET['page'] ) && $_GET['page'] == 'quiz-master-next/mlw_quizmaster2.php'){
        wp_redirect( admin_url( 'admin.php?page=qsm_dashboard' ) );
        exit;
    }
}
add_action('admin_page_access_denied', 'qsm_admin_page_access_func');

/**
 * Display roadmap page
 * 
 * @since 7.1.11
 */
function qsm_generate_roadmap_page(){ ?>
    <div class="wrap">
        <style>
            iframe {
                height: 1350px;
            }
        </style>
        <iframe src="https://app.productstash.io/roadmaps/5f7b1a36636db50029f51d5c/public" height="900" width="100%" frameborder="0"></iframe>
        <script>
                var ps_config = {
                        productId : "d24ad9de-78c7-4835-a2a8-3f5ee0317f31"
                };
        </script>
        <script type="text/javascript" src="https://app.productstash.io/js/productstash-embed.js" defer="defer"></script>
    </div>
<?php
}