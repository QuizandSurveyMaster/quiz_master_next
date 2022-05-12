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
		'id'        => '',
		'quiz_id'   => '',
		'name'      => '',
		'system'    => '',
		'views'     => 0,
		'taken'     => 0,
		'author_id' => 0,
		'deleted'   => 0,
		'updated'   => null,
		'created'   => null,
		'settings'  => array(),
	);

	/**
	 * 
	 * @param int $id Quiz to init.
	 */
	public function __construct( $id = 0 ) {
		parent::__construct( $id );
		$this->set_id( $id );
		$this->set_object_type();
		$this->data = $this->prepare_data();
	}

	public function prepare_data() {
		global $wpdb, $mlwQuizMasterNext;
		
		$quiz_id = $this->id;
		
		

		$quiz_data = '';

		return false;
	}

	public static function add_quiz( $data = array() ) {

		$quiz_data = array(
			'name'      => '',
			'system'    => '',
			'views'     => 0,
			'taken'     => 0,
			'author_id' => 0,
			'deleted'   => 0,
			'updated'   => '',
			'created'   => '',
		);

		return false;
	}

}
