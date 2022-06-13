<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Adds the Text tab to the Quiz Settings page.
*
* @return void
* @since 4.4.0
*/
function qmn_settings_text_tab() {
	global $mlwQuizMasterNext;
	$mlwQuizMasterNext->pluginHelper->register_quiz_settings_tabs( __( 'Text', 'quiz-master-next' ), 'mlw_options_text_tab_content', 'text' );
}
add_action( "plugins_loaded", 'qmn_settings_text_tab', 5 );

/**
* Adds the Text tab content to the tab.
*
* @return void
* @since 4.4.0
* @since 7.0 changed the design
*/
function mlw_options_text_tab_content() {
	global $wpdb;
	global $mlwQuizMasterNext;
	//wp_enqueue_style( 'qmn_admin_style', QSM_PLUGIN_CSS_URL.'/qsm-admin.css' );
        $variable_list = qsm_text_template_variable_list();
	?>
        <div class="qsm-text-main-wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div class="qsm-question-text-tab" id="post-body-content" style="position: relative;">
                        <?php
                        $quiz_text_arr = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_text' );
                        $editor_text_arr = $text_text_arr = array();
                        if ( $quiz_text_arr ) {
                            foreach ( $quiz_text_arr as $key => $single_text_arr ) {
                                if ( 'editor' === $single_text_arr['type'] ) {
                                    $editor_text_arr[] = $single_text_arr;
                                }else {
                                    $text_text_arr[] = $single_text_arr;
                                }
                            }
                        }
                        ?>
                        <div class="qsm-text-header">
                            <div class="message-dropdown">
                                <div class="qsm-row">
                                    <label><?php esc_html_e('Select Message', 'quiz-master-next'); ?></label>
                                    <select id="qsm_question_text_message_id">
                                        <?php if ( $editor_text_arr ) {
                                        foreach ( $editor_text_arr as $key => $single_editor_arr ) {
                                            ?>
                                            <option value="<?php echo esc_attr( $single_editor_arr['id'] ); ?>"><?php echo esc_attr( $single_editor_arr['label'] ); ?></option>
                                        <?php
                                        }
                                            } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="save-text-changes">
                                <span class="spinner" style="float: none;"></span>
                                <button id="qsm_save_text_message" class="button button-primary"><?php esc_html_e('Save Text Message', 'quiz-master-next'); ?></button>
                            </div>
                        </div>
                        <div class="qsm-text-conent">
                            <div class="qsm-text-tab-message-loader" style="display: none;"><div class="qsm-spinner-loader"></div></div>
                            <?php
                            $value_answer = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', $editor_text_arr[0]['id'] );
                            wp_editor( htmlspecialchars_decode( $value_answer, ENT_QUOTES ), 'qsm_question_text_message', array( 'textarea_rows' => 10 ) );
                            ?>
                        </div>
                    </div>
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="qsm-text-header">
                            <h5>
								<?php esc_html_e('Allowed Variables', 'quiz-master-next'); ?>
								<span class="description"><?php esc_html_e('click to insert the variable', 'quiz-master-next'); ?></span>
							</h5>
                        </div>
                        <div class="qsm-text-conent qsm-text-allowed-variables">
                            <div class="qsm-text-tab-message-loader" style="display: none;"><div class="qsm-spinner-loader"></div></div>
                            <div class="qsm-text-variable-wrap">
                                <?php
                                $allowed_variables = isset( $editor_text_arr[0]['variables'] ) ? $editor_text_arr[0]['variables'] : array();
                                if ( $allowed_variables ) {
                                    foreach ( $allowed_variables as $variable ) { ?>
                                        <span class="qsm-text-template-span">
                                            <button class="button button-default"><?php echo wp_kses_post( $variable ); ?></button>
                                            <?php if ( isset( $variable_list[ $variable ] ) ) {
                                                ?>
                                                <span class="dashicons dashicons-editor-help qsm-tooltips-icon">
                                                    <span class="qsm-tooltips"><?php echo wp_kses_post( $variable_list[ $variable ] ); ?></span>
                                                </span>
                                            <?php } ?>
                                        </span>
                                    <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="qsm_text_customize_label"><?php esc_html_e('Customize Labels', 'quiz-master-next'); ?> <span class="dashicons dashicons-arrow-down-alt"></span></div>
        </div>
        <div class="qsm-text-label-wrapper">
            <h2><?php esc_html_e("Customize Labels", 'quiz-master-next'); ?></h2>
            <?php

            $mlwQuizMasterNext->pluginHelper->generate_settings_section( 'quiz_text', $text_text_arr );
            ?>
        </div>
        <?php
}

/**
 * Get the editor text string
 * @global object $mlwQuizMasterNext
 * @since 7.0
 */
function qsm_get_question_text_message(){
    global $mlwQuizMasterNext;
    $text_id = isset( $_POST['text_id'] ) ? sanitize_text_field( wp_unslash( $_POST['text_id'] ) ) : '';
    if ( '' === $text_id ) {
        echo wp_json_encode( array(
			'success' => false,
			'message' => __('Text id is missing.', 'quiz-master-next'),
		) );
        exit;
    } else {
        $settings = $mlwQuizMasterNext->pluginHelper->get_section_setting( 'quiz_text', $text_id );
        $quiz_text_arr = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_text' );
        $key = array_search( $text_id, array_column( $quiz_text_arr, 'id' ), true );
        $allowed_text = '';
        if ( isset( $quiz_text_arr[ $key ] ) ) {
            $variable_list = qsm_text_template_variable_list();
			/**
			 * Filter allowed variables for Text Tab options.
			 */
			$quiz_text_allowed_variables = apply_filters('qsm_text_allowed_variables', $quiz_text_arr[ $key ]['variables'], $key);
            foreach ( $quiz_text_allowed_variables as $variable ) {
                $allowed_text .= '<span class="qsm-text-template-span">';
                $allowed_text .= '<button class="button button-default">'. $variable .'</button>';
                if ( isset( $variable_list[ $variable ] ) ) {
                    $allowed_text .= '<span class="dashicons dashicons-editor-help qsm-tooltips-icon">';
                    $allowed_text .= '<span class="qsm-tooltips">'. $variable_list[ $variable ] . '</span>';
                    $allowed_text .= '</span>';
                }
                $allowed_text .= '</span>';
            }
        }
        $return = array(
            'text_message'          => $settings,
            'allowed_variable_text' => $allowed_text,
            'success'               => true,
        );
        echo wp_json_encode($return);
        exit;
    }
}
add_action( 'wp_ajax_qsm_get_question_text_message', 'qsm_get_question_text_message' );

/**
 * Update the text string in DB
 *
 * @since 7.0
 */
function qsm_update_text_message(){
    global $mlwQuizMasterNext;
    $quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
    $text_id = isset( $_POST['text_id'] ) ? sanitize_text_field( wp_unslash( $_POST['text_id'] ) ) : '';
    $message = isset( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
    $settings = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'quiz_text' );
    $settings[ $text_id ] = $message;
    $results = $mlwQuizMasterNext->pluginHelper->update_quiz_setting( 'quiz_text', $settings );
    if ( false !== $results ) {
		do_action( 'qsm_saved_text_message', $quiz_id, $text_id, $message );
        $results = array(
            'success' => true,
        );
    }else {
        $results = array(
            'success' => false,
            'message' => __( 'There has been an error in this action. Please share this with the developer', 'quiz-master-next' ),
        );
    }
    echo wp_json_encode($results);
    exit;
}
add_action( 'wp_ajax_qsm_update_text_message', 'qsm_update_text_message' );
