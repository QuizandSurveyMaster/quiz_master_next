<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Result extends QSM_Model {
	
	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'result';
	
	/**
	 * 
	 * @param int|QSM_Result $id Result to init.
	 */
	public function __construct( $id = 0 ) {
		parent::__construct( $id );
	}

	/**
	 * Set name of this object type
	 */
	public function set_object_type() {
		$this->object_type = 'result';
	}

}
