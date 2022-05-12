<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Question extends QSM_Model {
	
	/**
	 * This is the name of this object type.
	 *
	 * @var string
	 */
	protected $object_type = 'question';

	/**
	 * Stores question data.
	 *
	 * @var array
	 */
	public $data = array(
		'id'				 => '',
		'quiz_id'			 => '',
		'name'				 => '',
		'description'		 => '',
		'type'				 => 0,
		'order'				 => 0,
		'deleted'			 => 0,
		'deleted_from_bank'	 => 0,
		'updated'			 => null,
		'created'			 => null,
		'settings'			 => array(),
	);

	/**
	 * 
	 * @param int $id Question to init.
	 */
	public function __construct( $id = 0 ) {
		parent::__construct( $id );
	}

	public function prepare() {
		$question_id = $this->id;
		$quiz_id	 = $this->quiz_id;

		return false;
	}

	public static function add_question( $data = array() ) {


		return false;
	}

}
