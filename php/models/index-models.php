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

	/**
	 * Returns all data for this object.
	 *
	 * @since  8.0
	 * @return array
	 */
	public function get_data() {
		return array_merge( array( 'id' => $this->get_id() ), $this->data );
	}

	/**
	 * Sets a field for a setter method.
	 *
	 * @since  8.0
	 * @param  string $field Name of prop to set.
	 * @param  mixed  $value Value of the prop.
	 * @param  string $section The name of the section the setting is registered in
	 */
	protected function set_field_value( $field, $value, $section = '' ) {
		if ( ! empty( $section ) && array_key_exists( $section, $this->data ) ) {
			$section_data			 = maybe_unserialize( $this->data[$section] );
			$section_data[$field]	 = $value;

			$this->data[$section] = $section_data;
		} else {
			if ( array_key_exists( $field, $this->data ) ) {
				$this->data[$field] = $value;
			}
		}
	}

	/**
	 * Gets a field for a getter method.
	 *
	 * @since  8.0
	 * @param  string $field Name of field to get.
	 * @param  string $section The name of the section the setting is registered in
	 * @return mixed
	 */
	protected function get_field_value( $field, $section = '' ) {
		$value = null;

		if ( ! empty( $section ) && array_key_exists( $section, $this->data ) ) {
			$section_data = maybe_unserialize( $this->data[$section] );
			if ( array_key_exists( $field, $section_data ) ) {
				$value = $section_data[$field];
			}
		} else {
			if ( array_key_exists( $field, $this->data ) ) {
				$value = $this->data[$field];
			}
		}

		return $value;
	}

}
