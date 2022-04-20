<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles Legacy data
 *
 * @since 4.7.1
 */
class QSM_Legacy {

	/**
	 * Main Constructor
	 *
	 * @uses QSM_Legacy::add_hooks
	 * @since 4.7.1
	 */
	function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds the various class functions to hooks and filters
	 *
	 * @since 4.7.1
	 */
	public function add_hooks() {

		// Add wc-admin report tables to list of WooCommerce tables.
		add_filter( 'qsm_get_db_tables', array( __CLASS__, 'get_tables' ) );
	}

	/**
	 * Return a list of tables.
	 *
	 * @return array QSM tables.
	 */
	public static function get_tables( $tables ) {
		global $wpdb;
		return array_merge(
			$tables, array(
			"{$wpdb->prefix}mlw_quizzes",
			"{$wpdb->prefix}mlw_questions",
			"{$wpdb->prefix}mlw_question_terms",
			"{$wpdb->prefix}mlw_results",
			"{$wpdb->prefix}mlw_qm_audit_trail",
			"{$wpdb->prefix}mlw_themes",
			"{$wpdb->prefix}mlw_quiz_theme_settings",
			)
		);
	}

}

$QSM_Legacy = new QSM_Legacy();
