<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class QSM_Question_Review {
	private $question_id          = 0;
	private $answer_array         = array();
	private $user_answer          = array();
	private $correct_answer       = array();
	private $answer_status        = 'incorrect';
	private $points               = 0;
	private $question_description = '';
	private $input_field          = '';

	function __construct( $question_id = 0, $question_description = '', $answer_array = array() ) {
		global $mlwQuizMasterNext;
		$this->question_id          = $question_id;
		$this->answer_array         = $answer_array;
		$this->question_description = $question_description;
		$this->question_type        = QSM_Questions::load_question_data( $this->question_id, 'question_type_new' );
		$this->input_field          = $mlwQuizMasterNext->pluginHelper->question_types[ $this->question_type ]['input_field'];
		$this->set_user_answer();
		$this->set_correct_answer();
		$this->set_answer_status();
	}

	public function sanitize_answer_from_post( $data, $type = 'text' ) {
		if ( 'text_area' === $type ) {
			return sanitize_textarea_field( wp_unslash( $data ) );
		} else {
			return sanitize_text_field( wp_unslash( $data ) );
		}
	}

	public function sanitize_answer_from_db( $data, $type = 'text' ) {
		if ( 'text_area' === $type ) {
			return trim( stripslashes( htmlspecialchars_decode( sanitize_textarea_field( $data ), ENT_QUOTES ) ) );
		} else {
			return trim( stripslashes( htmlspecialchars_decode( sanitize_text_field( $data ), ENT_QUOTES ) ) );
		}
	}

	public function decode_response_from_text_field( $data ) {
		return trim( preg_replace( '/\s\s+/', ' ', str_replace( "\n", ' ', htmlspecialchars_decode( $data, ENT_QUOTES ) ) ) );
	}


	public function prepare_for_string_matching( $data ) {
		return mb_strtoupper( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $data ) ) );
	}

	abstract public function set_user_answer();

	public function set_correct_answer() {
		foreach ( $this->answer_array as $answer_key => $answer_value ) {
			if ( 1 === intval( $answer_value[2] ) ) {
				$this->correct_answer[ $answer_key ] = $this->sanitize_answer_from_db( $answer_value[0], $this->input_field );
			}
		}
	}

	abstract public function set_answer_status();

	public function get_user_answer() {
		return $this->user_answer;
	}
	public function get_correct_answer() {
		return $this->correct_answer;
	}
	public function get_answer_status() {
		return $this->answer_status;
	}
	public function get_points() {
		return $this->points;
	}
}
