<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Quiz extends QSM_Model {

	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'quiz';

	/**
	 * Stores quiz data.
	 *
	 * @var array
	 */
	protected $data = array(
		'id'              => '',
		'quiz_id'         => '',
		'name'            => '',
		'system'          => '',
		'views'           => 0,
		'taken'           => 0,
		'author_id'       => 0,
		'deleted'         => 0,
		'updated'         => null,
		'created'         => null,
		'settings'        => array(),
		'legacy_settings' => array(),
	);

	/**
	 * Quiz constructor.
	 * @param int|QSM_Quiz|object $quiz Quiz to init.
	 */
	public function __construct( $quiz = 0 ) {
		parent::__construct( $quiz );
		if ( is_numeric( $quiz ) && $quiz > 0 ) {
			$this->set_id( $quiz );
		} elseif ( $quiz instanceof self ) {
			$this->set_id( absint( $quiz->get_id() ) );
		} elseif ( ! empty( $quiz->id ) ) {
			$this->set_id( absint( $quiz->id ) );
		} else {
			$this->set_object_read( true );
		}

		$this->data_store = QSM_Data_Store::load( $this->object_type );
		if ( $this->get_id() > 0 ) {
			$this->data_store->read( $this );
		}
	}

	/**
	 * Get quiz name.
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->get_field( 'name' );
	}

	/**
	 * Get quiz system.
	 *
	 * @return string
	 */
	public function get_system() {
		return $this->get_field( 'system' );
	}

	/**
	 * Get quiz views.
	 *
	 * @return string
	 */
	public function get_views() {
		return $this->get_field( 'views' );
	}

	/**
	 * Get quiz taken.
	 *
	 * @return string
	 */
	public function get_taken() {
		return $this->get_field( 'taken' );
	}

	/**
	 * Get quiz author_id.
	 *
	 * @return string
	 */
	public function get_author_id() {
		$author_id = $this->get_field( 'author_id' );
		if ( 0 == $author_id ) {
			$current_user    = wp_get_current_user();
			$author_id       = $current_user->ID;
		}
		return $author_id;
	}

	/**
	 * Get quiz created date.
	 *
	 * @return DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_created() {
		return $this->get_field( 'created' );
	}

	/**
	 * Get quiz modified date.
	 *
	 * @return DateTime|NULL object if the date is set or null if there is no date.
	 */
	public function get_date_updated() {
		return $this->get_field( 'updated' );
	}

	/**
	 * Check whether quiz is deleted or not
	 */
	public function is_deleted() {
		return $this->get_field( 'deleted' );
	}

	/**
	 * Get all quiz settings
	 * 
	 * @return array
	 */
	public function get_settings() {
		return $this->get_field( 'settings' );
	}

	/**
	 * Get all quiz settings for legacy support
	 * 
	 * @return array
	 */
	public function get_legacy_settings() {
		return $this->get_field( 'legacy_settings' );
	}

	/**
	 * Delete the product, set its ID to 0, and return result.
	 *
	 * @param  bool $force_delete Should the product be deleted permanently.
	 * @return bool result
	 */
	public function delete( $force_delete = false, $delete_questions = false ) {
		$deleted = parent::delete( $force_delete );
		if ( $deleted ) {
			/**
			 * Delete quiz questions
			 */
			if ( $delete_questions ) {
				$this->data_store->delete_questions( $this, $force_delete );
			}
		}
		return $deleted;
	}

}
