<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 *
 */
class QSM_Contact_Manager {

  private static $fields = array();

  function __construct() {
    # code...
  }

  public static function display_fields( $options ) {
    $return = '';

    // Prepare name and email values from profile if logged in
    $name = '';
    $email = '';
    if ( is_user_logged_in() ) {
      $current_user = wp_get_current_user();
      $name = $current_user->display_name;
      $email = $current_user->user_email;
    }

    // If logged in user should see fields
    if ( 1 == $options->loggedin_user_contact ) {
      $return = '<div style="display:none;">';
    }

    // Loads fields
    $fields = self::load_fields();

    // If fields are empty, check for backwards compatibility
    if ( ( empty( $fields ) || ! is_array( $fields ) ) && ( 2 !== $options->user_name || 2 !== $options->user_comp || 2 !== $options->user_email || 2 !== $options->user_phone ) ) {

      // Check for name field
      if ( 2 !== $options->user_name ) {
        $class = '';
        if ( 1 === $options->user_name && 1 !== $options->loggedin_user_contact ) {
          $class = 'mlwRequiredText qsm_required_text';
        }
        $return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->name_field_text, ENT_QUOTES ) . "</span>";
        $return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserName' value='$name' />";
      }

      // Check for comp field
      if ( 2 !== $options->user_comp ) {
        $class = '';
        if ( 1 === $options->user_comp && 1 !== $options->loggedin_user_contact ) {
          $class = 'mlwRequiredText qsm_required_text';
        }
        $return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->business_field_text, ENT_QUOTES ) . "</span>";
        $return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserComp' value='' />";
      }

      // Check for email field
      if ( 2 !== $options->user_email ) {
        $class = '';
        if ( 1 === $options->user_email && 1 !== $options->loggedin_user_contact ) {
          $class = 'mlwRequiredText qsm_required_text';
        }
        $return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->email_field_text, ENT_QUOTES ) . "</span>";
        $return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserEmail' value='$email' />";
      }

      // Check for phone field
      if ( 2 !== $options->user_phone ) {
        $class = '';
        if ( 1 === $options->user_phone && 1 !== $options->loggedin_user_contact ) {
          $class = 'mlwRequiredText qsm_required_text';
        }
        $return .= "<span class='mlw_qmn_question qsm_question'>" . htmlspecialchars_decode( $options->phone_field_text, ENT_QUOTES ) . "</span>";
        $return .= "<input type='text' class='$class' x-webkit-speech name='mlwUserPhone' value='' />";
      }
    } elseif ( ! empty( $fields ) && is_array( $fields ) ) {
      for ( $i=0; $i < count( $fields ); $i++ ) {
        $class = '';
        $return .= "<span class='mlw_qmn_question qsm_question'>{$fields[ $i ]['label']}</span>";
        switch ( $fields[ $i ]['type'] ) {
          case 'text':
            if ( ( "true" === $fields[ $i ]["required"] || true === $fields[ $i ]["required"] ) && 1 !== $options->loggedin_user_contact ) {
              $class = 'mlwRequiredText qsm_required_text';
            }
            $return .= "<input type='text' class='$class' x-webkit-speech name='contact_field_$i' value='' />";
            break;

          case 'checkbox':
            if ( ( "true" === $fields[ $i ]["required"] || true === $fields[ $i ]["required"] ) && 1 !== $options->loggedin_user_contact ) {
              $class = 'mlwRequiredAccept qsm_required_accept';
            }
            $return .= "<input type='checkbox' class='$class' x-webkit-speech name='contact_field_$i' value='checked' />";
            break;

          default:
            break;
        }
      }
    }

    // If logged in user should see fields
    if ( 1 == $options->loggedin_user_contact ) {
      $return = '</div>';
    }

    // Return contact field HTML
  	return $return;
  }

  public static function process_fields() {

    $responses = array();

    // Loads fields
    $fields = self::load_fields();

    // If fields are empty, check for backwards compatibility
    if ( ( empty( $fields ) || ! is_array( $fields ) ) && ( 2 !== $options->user_name || 2 !== $options->user_comp || 2 !== $options->user_email || 2 !== $options->user_phone ) ) {
      $responses[] = array(
        'label' => 'Name',
        'value' => isset( $_POST["mlwUserName"] ) ? sanitize_text_field( $_POST["mlwUserName"] ) : 'None',
        'use-for' => 'name'
      );
      $responses[] = array(
        'label' => 'Business',
        'value' => isset( $_POST["mlwUserComp"] ) ? sanitize_text_field( $_POST["mlwUserComp"] ) : 'None',
        'use-for' => 'comp'
      );
      $responses[] = array(
        'label' => 'Email',
        'value' => isset( $_POST["mlwUserEmail"] ) ? sanitize_text_field( $_POST["mlwUserEmail"] ) : 'None',
        'use-for' => 'email'
      );
      $responses[] = array(
        'label' => 'Phone',
        'value' => isset( $_POST["mlwUserPhone"] ) ? sanitize_text_field( $_POST["mlwUserPhone"] ) : 'None',
        'use-for' => 'phone'
      );
    } elseif ( ! empty( $fields ) && is_array( $fields ) ) {
      for ( $i = 0; $i < count( $fields ); $i++ ) {
        $fieldArray = array(
          'label' => $fields[ $i ]['label'],
          'value' => isset( $_POST["contact_field_$i"] ) ? sanitize_text_field( $_POST["contact_field_$i"] ) : 'None'
        );
        if ( isset( $fields[ $i ]['use-for'] ) ) {
          $fieldArray['use-for'] = $fields[ $i ]['use-for'];
        }
        $responses[] = $fieldArray;
      }
    }

    // For backwards compatibility, use the 'use-for' fields for setting $_POST values of older version of contact fields
    foreach ( $responses as $field ) {
      if ( isset( $field['use-for'] ) ) {
        if ( 'name' === $field['use-for'] ) {
          $_POST["mlwUserName"] = $field["value"];
        }
        if ( 'comp' === $field['use-for'] ) {
          $_POST["mlwUserComp"] = $field["value"];
        }
        if ( 'email' === $field['use-for'] ) {
          $_POST["mlwUserEmail"] = $field["value"];
        }
        if ( 'phone' === $field['use-for'] ) {
          $_POST["mlwUserPhone"] = $field["value"];
        }
      }
    }

    return $responses;
  }

  public static function load_fields() {
    global $mlwQuizMasterNext;
    return maybe_unserialize( $mlwQuizMasterNext->pluginHelper->get_quiz_setting( "contact_form" ) );
  }

  public static function save_fields( $quiz_id, $fields ) {
    if ( self::load_fields() === $fields ) {
      return true;
    }
    global $mlwQuizMasterNext;
    $mlwQuizMasterNext->quizCreator->set_id( intval( $quiz_id ) );
    $mlwQuizMasterNext->quiz_settings->prepare_quiz( intval( $quiz_id ) );
    return $mlwQuizMasterNext->pluginHelper->update_quiz_setting( "contact_form", serialize( $fields ) );
  }
}
?>
