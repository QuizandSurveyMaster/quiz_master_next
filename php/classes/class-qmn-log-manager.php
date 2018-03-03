<?php

/**
 * Log Manager For Quiz And Survey Master
 *
 * Used to log errors and events into a custom post type of qmn_log
 *
 * @since 4.5.0
 */
class QMN_Log_Manager
{

  /**
   *  Main constructor
   *
   * @since 4.5.0
   */
  function __construct()
  {
    // create the log post type
    add_action( 'init', array( $this, 'register_post_type' ) );
    // create types taxonomy and default types
    add_action( 'init', array( $this, 'register_taxonomy' ) );
  }

  /**
   * Returns an array of the available log types
   *
   * @return array The available log types
   * @since 4.5.0
   */
  private function log_types() {
	$terms = array( 'error', 'event' );

	// Filters the taxomony for the QSM logs
	return apply_filters( 'qmn_log_types', $terms );
  }

  /**
   * Registers the qmn_log custom post type
   *
   * @since 4.5.0
   */
  public function register_post_type() {
		/* logs post type */
		$log_args = array(
			'labels'          => array( 'name' => 'QSM Logs' ),
			'public'          => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'query_var'       => false,
			'rewrite'         => false,
			'capability_type' => 'post',
			'supports'        => array( 'title', 'editor' ),
			'can_export'      => false
		);

		// Registers QSM logs post type with filtered $args
		register_post_type( 'qmn_log', apply_filters( 'qmn_log_post_type_args', $log_args ) );
	}

  /**
   * Registers the qmn_log taxonomies which are the log types
   *
   * @since 4.5.0
   */
  public function register_taxonomy() {
		register_taxonomy( 'qmn_log_type', 'qmn_log', array( 'public' => defined( 'WP_DEBUG' ) && WP_DEBUG ) );
		$types = $this->log_types();
		foreach ( $types as $type ) {
			if( ! term_exists( $type, 'qmn_log_type' ) ) {
				wp_insert_term( $type, 'qmn_log_type' );
			}
		}
	}

  /**
   *  Checks to see if the type is one of the available types
   *
   *
   * @param $type string A type that needs to be check to see if available
   * @since 4.5.0
   * @return bool True if the type is available
   */
  private function valid_type( $type ) {
		return in_array( $type, $this->log_types() );
	}

  /**
   * Prepares the log to be inserted
   *
   * @since 4.5.0
   * @param $title string The title of the log
   * @param $message string The message of the log
   * @param $parent int The object ID associated with the log
   * @param $type string The type of the log
   * @return bool|int False if error else id of the newly inserted post
   */
  public function add( $title = '', $message = '', $parent = 0, $type = null ) {
		$log_data = array(
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type
		);
		return $this->insert_log( $log_data );
	}

  /**
   * Inserts the log into the posts
   *
   * @since 4.5.0
   * @param $log_data Array of data about the log including title, message, and type
   * @return bool|int False if error else id of the newly inserted post
   */
  public function insert_log( $log_data = array() ) {
		$defaults = array(
			'post_type'    => 'qmn_log',
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => false
		);
		$args = wp_parse_args( $log_data, $defaults );

		// Hook called before a QSM log is inserted
		do_action( 'wp_pre_insert_qmn_log' );

		// store the log entry
		$log_id = wp_insert_post( $args );
		// set the log type, if any
		if( $log_data['log_type'] && $this->valid_type( $log_data['log_type'] ) ) {
			wp_set_object_terms( $log_id, $log_data['log_type'], 'qmn_log_type', false );
		}
		// set log meta, if any
		if( $log_id && ! empty( $log_meta ) ) {
			foreach( (array) $log_meta as $key => $meta ) {
				update_post_meta( $log_id, '_qmn_log_' . sanitize_key( $key ), $meta );
			}
		}

		// Hook called after a QSM log is inserted
		do_action( 'wp_post_insert_qmn_log', $log_id );
		return $log_id;
	}

  /**
   * Retrieves the logs
   *
   * @since 4.5.0
   * @param $type string The type of log to return
   * @param $amount int The amount of logs to return
   * @return bool|array Returns an array of logs or false on error
   */
  public function get_logs( $type = false, $amount = 5 ) {
		$query_args = array(
			'post_parent'    => 0,
			'post_type'      => 'qmn_log',
			'posts_per_page' => intval($amount),
			'post_status'    => 'publish'
		);
		if( $type && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'qmn_log_type',
					'field'    => 'slug',
					'terms'    => $type
				)
			);
		}
		$logs = get_posts( $query_args );
		if( $logs )
			return $logs;
		// no logs found
		return false;
	}
}

?>
