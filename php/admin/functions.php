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

/**
 * @since  6.4.6
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