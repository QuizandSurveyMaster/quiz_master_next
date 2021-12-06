<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * This class handles the audit trail of the plugin
 */
class QSM_Audit {
<<<<<<< HEAD

  /**
   * Adds new audit to Audit Trail table
   *
   * @since 4.7.1
   * @param string $action The action that is to be saved into the audit trail
   * @return bool Returns true if successfull and false if fails
   */
  public function new_audit( $action, $quiz_id, $settings_array_json_convert, $user = null ) {
    global $wpdb;
    // Sanitizes action just in case 3rd party uses this funtion
    $action = sanitize_text_field( $action );

    $settings_array_json_convert = sanitize_text_field( $settings_array_json_convert );
    // Retrieves current user's data
    if ( is_null( $user ) ) {
      $current_user = wp_get_current_user();
    } else {
      $current_user = $user;
    }

    // Returns if the current user is not valid
    if ( ! ( $current_user instanceof WP_User ) ) {
      return false;
    }

    global $wpdb;

    $quiz_name = $wpdb->get_var( $wpdb->prepare( "SELECT quiz_name FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id=%d LIMIT 1", $quiz_id ) );

    $quiz_name = esc_attr( $quiz_name );

    // Inserts new audit into table
    $inserted = $wpdb->insert(
      $wpdb->prefix . "mlw_qm_audit_trail",
      array(
        'action_user' => $current_user->display_name,
        'action' => $action,
        'quiz_name' => $quiz_name,
        'form_data' => $settings_array_json_convert,
        'time' => date("h:i:s A m/d/Y")
      ),
      array(
        '%s',
        '%s',
        '%s',
        '%s'
      )
    );

    // If the insert returns false, then return false
    if ( false === $inserted ) {
      return false;
    }

    return true;
  }
=======
    /**
     * Adds new audit to Audit Trail table
     *
     * @since 4.7.1
     * @param string $action The action that is to be saved into the audit trail
     * @return bool Returns true if successfull and false if fails
     */
    public function new_audit( $action, $user = null ) {
        // Sanitizes action just in case 3rd party uses this funtion
        $action = sanitize_text_field( $action );

        // Retrieves current user's data
		if ( is_null( $user ) ) {
			$current_user = wp_get_current_user();
		} else {
			$current_user = $user;
		}

		// Returns if the current user is not valid
		if ( ! ( $current_user instanceof WP_User ) ) {
			return false;
		}

        global $wpdb;

		// Inserts new audit into table
		$inserted = $wpdb->insert(
			$wpdb->prefix . "mlw_qm_audit_trail",
			array(
				'action_user' => $current_user->display_name,
				'action'      => $action,
				'time'        => gmdate("h:i:s A m/d/Y"),
			),
			array(
				'%s',
				'%s',
				'%s',
			)
		);

		// If the insert returns false, then return false
		if ( false === $inserted ) {
			return false;
		}

        return true;
    }
>>>>>>> origin/dev
}

