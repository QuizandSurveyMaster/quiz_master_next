<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class handles creating form fields based on supplied arrays
 *
 * @since 5.0.0
 */
class QSM_Fields {

  /**
   * Cycles through the fields in the array and generates each field
   *
   * @since 5.0.0
   * @param array $fields The array that contains the data for all fields
   * @param array $settings The array that holds the settings for this section
   */
  public static function generate_section( $fields, $section ) {

    global $mlwQuizMasterNext;
    global $wpdb;

    // If nonce is correct, save settings
    if ( isset( $_POST["save_settings_nonce"] ) && wp_verify_nonce( $_POST['save_settings_nonce'], 'save_settings') ) {

      // Cycle through fields to retrieve all posted values
      $settings_array = array();
      foreach ( $fields as $field ) {

        // Sanitize the values based on type
        $sanitized_value = '';
        switch ( $field["type"] ) {
          case 'text':
            $sanitized_value = sanitize_text_field( stripslashes( $_POST[ $field["id"] ] ) );
            break;

          case 'radio':
          case 'date':
            $sanitized_value = sanitize_text_field( $_POST[ $field["id"] ] );
            break;

          case 'number':
            $sanitized_value = intval( $_POST[ $field["id"] ] );
            break;

          case 'editor':
            $sanitized_value = wp_kses_post( stripslashes( $_POST[ $field["id"] ] ) );
            break;

          default:
            $sanitized_value = sanitize_text_field( $_POST[ $field["id"] ] );
            break;
        }
        $settings_array[ $field["id"] ] = $sanitized_value;
      }

      // Update the settings and show alert based on outcome
      $results = $mlwQuizMasterNext->pluginHelper->update_quiz_setting( $section, $settings_array );
      if ( false !== $results ) {
  			$mlwQuizMasterNext->alertManager->newAlert( __( 'The settings has been updated successfully.', 'quiz-master-next' ), 'success' );
  			$mlwQuizMasterNext->audit_manager->new_audit( 'Settings Have Been Edited' );
  		} else {
  			$mlwQuizMasterNext->alertManager->newAlert( __( 'There was an error when updating the settings. Please try again.', 'quiz-master-next' ), 'error');
  		}
    }

    // Retrieve the settings for this section
    $settings = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( $section );

    ?>
    <form action="" method="post">
      <?php wp_nonce_field( 'save_settings','save_settings_nonce' ); ?>
      <button class="button-primary"><?php _e('Save Changes', 'quiz-master-next'); ?></button>
      <table class="form-table" style="width: 100%;">
        <?php

        // Cycles through each field
        foreach ( $fields as  $field ) {

          // Generate the field
          QSM_Fields::generate_field( $field, $settings[ $field["id"] ] );
        }
        ?>
      </table>
      <button class="button-primary"><?php _e('Save Changes', 'quiz-master-next'); ?></button>
    </form>
    <?php
  }

  /**
   * Prepares the field and calls the correct generate field function based on field's type
   *
   * @since 5.0.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   * @return bool False if the field is invalid, true if successful
   */
  public static function generate_field( $field, $value ) {

    // Load default
    $defaults = array(
      'id' => null,
      'label' => '',
      'type' => '',
      'options' => array(),
      'variables' => array()
    );
    $field = wp_parse_args( $field, $defaults );

    // If id is not valid, return false
    if ( is_null( $field["id"] ) || empty( $field["id"] ) ) {
      return false;
    }

    // If type is empty, assume text
    if ( empty( $field["type"] ) ) {
      $field["type"] = "text";
    }

    // Prepare function to call for field type
    $method = "generate_{$field["type"]}_field";
    QSM_Fields::$method( $field, $value );

    return true;
  }

  /**
   * Generates a text field
   *
   * @since 5.0.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   */
  public static function generate_text_field( $field, $value ) {
    ?>
    <tr valign="top">
      <th scope="row"><label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?></label></th>
      <td>
        <input type="text" id="<?php echo $field["id"]; ?>" name="<?php echo $field["id"]; ?>" value="<?php echo $value; ?>" />
      </td>
    </tr>
    <?php
  }

  /**
   * Generates a textarea field using the WP Editor
   *
   * @since 5.0.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   */
  public static function generate_editor_field( $field, $value ) {
    ?>
    <tr>
      <th scope="row">
        <label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?>
          <?php
          if ( is_array( $field["variables"] ) ) {
            ?>
            <br>
            <p><?php _e( "Allowed Variables:", 'quiz-master-next' ); ?></p>
            <?php
            foreach ( $field["variables"] as $variable ) {
              ?>
              <p style="margin: 2px 0">- <?php echo $variable; ?></p>
              <?php
            }
          }
          ?>
        </label>
      </th>
      <td>
        <?php wp_editor( htmlspecialchars_decode( $value, ENT_QUOTES ), $field["id"] ); ?>
      </td>
    </tr>
    <?php
  }

  /**
   * Generates a date field
   *
   * @since 5.0.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   */
  public static function generate_date_field( $field, $value ) {
    wp_enqueue_script( 'jquery-ui-datepicker' );
    ?>
    <script>
			jQuery(function() {
    			jQuery( "#<?php echo $field["id"]; ?>" ).datepicker();
			});
		</script>
    <tr valign="top">
      <th scope="row"><label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?></label></th>
      <td>
          <input type="text" id="<?php echo $field["id"]; ?>" name="<?php echo $field["id"]; ?>" value="<?php echo $value; ?>" />
      </td>
    </tr>
    <?php
  }

  /**
   * Generates a number field
   *
   * @since 5.0.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   */
  public static function generate_number_field( $field, $value ) {
    ?>
    <tr valign="top">
      <th scope="row"><label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?></label></th>
      <td>
          <input type="number" step="1" min="0" id="<?php echo $field["id"]; ?>" name="<?php echo $field["id"]; ?>" value="<?php echo $value; ?>" />
      </td>
    </tr>
    <?php
  }

  /**
   * Generates radio inputs
   *
   * @since 5.0.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   */
  public static function generate_radio_field( $field, $value ) {
    ?>
    <tr valign="top">
      <th scope="row"><label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?></label></th>
      <td>
        <?php   
        $green_class = count($field["options"]) > 2 ? 'green' : '';
        ?>
        <fieldset class="buttonset buttonset-hide <?php echo $green_class; ?>" data-hide='1'>
            <?php
              foreach ( $field["options"] as $option ) {
                ?>                
                <input type="radio" id="<?php echo $field["id"] . '-' . $option["value"]; ?>" name="<?php echo $field["id"]; ?>" <?php checked( $option["value"], $value ); ?> value="<?php echo $option["value"]; ?>" />
                <label for="<?php echo $field["id"] . '-' . $option["value"]; ?>"><?php echo $option["label"]; ?></label>
                <?php
              }
            ?>
        </fieldset>  
      </td>
    </tr>
    <?php
  }
}

?>
