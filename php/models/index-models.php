<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class QSM_Model {

	/**
	 * ID for this object.
	 *
	 * @var int
	 */
	protected $id			 = 0;

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type	 = '';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array
	 */
	protected $data			 = array();
	
	/**
	 * Core data changes for this object.
	 *
	 * @var array
	 */
	protected $changes = array();

	/**
	 * This is false until the object is read from the DB.
	 *
	 * @var bool
	 */
	protected $object_read	 = false;

	/**
	 * Contains a reference to the data store for this class.
	 *
	 * @var object
	 */
	protected $data_store;

	/**
	 * Default constructor.
	 *
	 * @param int|object|array $read ID to load from the DB (optional) or already queried data.
	 */
	public function __construct( $id = 0 ) {
		$this->default_data = $this->data;
	}

	/**
	 * Sets ID
	 */
	public function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Set all props to default values.
	 */
	public function set_defaults() {
		$this->data = $this->default_data;
		$this->changes = array();
		$this->set_object_read( false );
	}

	/**
	 * Set object read property.
	 *
	 * @param boolean $read Should read?.
	 */
	public function set_object_read( $read = true ) {
		$this->object_read = (bool) $read;
	}

	/**
	 * Get object read property.
	 *
	 * @return boolean
	 */
	public function get_object_read() {
		return (bool) $this->object_read;
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
	 * @return array
	 */
	public function get_data() {
		return array_merge( array( 'id' => $this->get_id() ), $this->data );
	}

	/**
	 * Gets a prop for a getter method.
	 *
	 * @param  string $prop Name of prop to get.
	 * @param  string $context What the value is for. Valid values are view and edit.
	 * @return mixed
	 */
	public function get_field( $field, $section = '', $default = null ) {
		$value = $default;
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
		return apply_filters( $this->get_hook_prefix() . $field, $value, $section, $this );
	}

	/**
	 * Sets a field for a setter method.
	 *
	 * @param  string $field Name of prop to set.
	 * @param  mixed  $value Value of the prop.
	 * @param  string $section The name of the section the setting is registered in
	 */
	public function set_field( $field, $value, $section = '' ) {
		if ( ! empty( $section ) && array_key_exists( $section, $this->data ) ) {
			$section_data			 = maybe_unserialize( $this->data[$section] );
			$section_data[$field]	 = $value;
			$this->data[$section]	 = $section_data;
		} else {
			if ( array_key_exists( $field, $this->data ) ) {
				$this->data[$field] = $value;
			}
		}
	}
	
	/**
	 * Return data changes only.
	 *
	 * @return array
	 */
	public function get_changes() {
		return $this->changes;
	}

	/**
	 * Merge changes with data and clear.
	 */
	public function apply_changes() {
		$this->data    = array_replace_recursive( $this->data, $this->changes ); // @codingStandardsIgnoreLine
		$this->changes = array();
	}
		
	/**
	 * Prefix for action and filter hooks on data.
	 *
	 * @return string
	 */
	protected function get_hook_prefix() {
		return 'qsm_' . $this->object_type . '_get_';
	}

	/**
	 * Delete an object, set the ID to 0, and return result.
	 *
	 * @param  bool $force_delete Should the quiz be deleted permanently.
	 * @return bool result
	 */
	public function delete( $force_delete = false ) {
		if ( $this->data_store ) {
			$this->data_store->delete( $this, array( 'force_delete' => $force_delete ) );
			$this->set_id( 0 );
			return true;
		}
		return false;
	}

	/**
	 * Save should create or update based on object existence.
	 *
	 * @return int
	 */
	public function save() {
		if ( ! $this->data_store ) {
			return $this->get_id();
		}

		/**
		 * Trigger action before saving to the DB. Allows you to adjust object props before save.
		 *
		 * @param $this The object being saved.
		 * @param $data_store THe data store persisting the data.
		 */
		do_action( 'qsm_before_' . $this->object_type . '_object_save', $this, $this->data_store );

		if ( $this->get_id() ) {
			$this->data_store->update( $this );
		} else {
			$this->data_store->create( $this );
		}

		/**
		 * Trigger action after saving to the DB.
		 *
		 * @param $this The object being saved.
		 * @param $data_store The data store persisting the data.
		 */
		do_action( 'qsm_after_' . $this->object_type . '_object_save', $this, $this->data_store );
		return $this->get_id();
	}

}
