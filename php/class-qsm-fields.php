<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class handles creating form fields based on supplied arrays
 *
 * @since 4.8.0
 */
class QSM_Fields {

  /**
   * 
   */
  public function generate_section( $fields, $settings ) {
    foreach ( $fields as  $field ) {
      if ( isset( $settings[ $field["id"] ] ) ) {
        $field["value"] = $settings[ $field["id"] ];
      } else {
        $field["value"] = $field["default"];
      }
      $this->generate_field( $field );
    }
  }

  /**
   * Prepares the field and calls the correct generate field function based on field's type
   *
   * @since 4.8.0
   * @param array $field The array that contains the data for the input field
   * @return bool False if the field is invalid, true if successful
   */
  public function generate_field( $field ) {

    // Load default
    $defaults = array(
      'id' => null,
      'label' => '',
      'type' => '',
      'options' => array(
        ''
      ),
      'value' => ''
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
    $this->$method( $field );

    return true;
  }

  /**
   * Generates a text field
   *
   * @since 4.8.0
   * @param array $field The array that contains the data for the input field
   */
  public function generate_text_field( $field ) {
    ?>
    <tr valign="top">
      <th scope="row"><label for="<?php echo $field["id"]; ?>"><?php echo $field["label"]; ?></label></th>
      <td>
          <input type="text" id="<?php echo $field["id"]; ?>" name="<?php echo $field["id"]; ?>" value='<?php echo $field["value"]; ?>' />
      </td>
    </tr>
    <?php
  }
}

?>
