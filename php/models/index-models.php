<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class QSM_Model {

	/**
	 * ID for this object.
	 *
	 * @since 8.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * This is the name of this object type.
	 *
	 * @since 8.0
	 * @var string
	 */
	protected $object_type = '';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 8.0
	 * @var array
	 */
	protected $data = array();

	public function __construct( $id = 0 ) {
		$this->set_id( $id );
	}

	/**
	 * Sets ID
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Gets the ID stored (for backwards compatibility)
	 */
	public function get_id() {
		if ( $this->id ) {
			return absint( $this->id );
		} else {
			return false;
		}
	}

}
