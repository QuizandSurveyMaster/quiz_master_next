<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class handles creating form fields based on supplied arrays
 *
 * @since 4.8.0
 */
class QSM_Fields {

  /**
   * Cycles through the fields in the array and generates each field
   *
   * @since 4.8.0
   * @param array $fields The array that contains the data for all fields
   * @param array $settings The array that holds the settings for this section
   */
  public function generate_section( $fields, $settings ) {

    // Cycles through each field
    foreach ( $fields as  $field ) {

      // Generate the field
      $this->generate_field( $field, $settings[ $field["id"] );
    }
  }

  /**
   * Prepares the field and calls the correct generate field function based on field's type
   *
   * @since 4.8.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   * @return bool False if the field is invalid, true if successful
   */
  public function generate_field( $field, $value ) {

    // Load default
    $defaults = array(
      'id' => null,
      'label' => '',
      'type' => '',
      'options' => array(
        ''
      )
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
    $this->$method( $field, $value );

    return true;
  }

  /**
   * Generates a text field
   *
   * @since 4.8.0
   * @param array $field The array that contains the data for the input field
   * @param mixed $value The current value of the setting
   */
  public function generate_text_field( $field, $value ) {
    ?>
    <tr valign="top">
      <th scope="row"><label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?></label></th>
      <td>
          <input type="text" id="<?php echo $field["id"]; ?>" name="<?php echo $field["id"]; ?>" value="<?php echo $value; ?>" />
      </td>
    </tr>
    <?php
  }
}

?>
