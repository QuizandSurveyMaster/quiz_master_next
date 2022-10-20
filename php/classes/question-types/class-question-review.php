<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class QSM_Question_Review {
	public $question_id          = 0;
	public $answer_array         = array();
	public $user_answer          = array();
	public $correct_answer       = array();
	public $answer_status        = 'incorrect';
	public $points               = 0;
	public $question_description = '';
	public $input_field          = '';
	public $form_type            = 0;
	public $grading_system       = 0;
	public $correct_answer_logic = 0;

	function __construct( $question_id = 0, $question_description = '', $answer_array = array() ) {
		global $mlwQuizMasterNext;
		$this->question_id          = $question_id;
		$this->answer_array         = $answer_array;
		$this->question_description = $question_description;
		$this->question_type        = QSM_Questions::load_question_data( $this->question_id, 'question_type_new' );
		$this->input_field          = $mlwQuizMasterNext->pluginHelper->question_types[ $this->question_type ]['input_field'];
		$quiz_options               = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
		$this->form_type            = intval( $quiz_options->form_type );
		$this->grading_system       = intval( $quiz_options->system );
		$this->correct_answer_logic = intval( $quiz_options->correct_answer_logic );
		$this->set_user_answer();
		$this->set_correct_answer();
		$this->set_answer_status();
	}

	public function sanitize_answer_from_post( $data ) {
		if ( 'text_area' === $this->input_field ) {
			return sanitize_textarea_field( wp_unslash( $data ) );
		} else {
			return sanitize_text_field( wp_unslash( $data ) );
		}
	}

	public function sanitize_answer_from_db( $data ) {
		if ( 'text_area' === $this->input_field ) {
			return trim( stripslashes( htmlspecialchars_decode( sanitize_textarea_field( $data ), ENT_QUOTES ) ) );
		} else {
			return trim( stripslashes( htmlspecialchars_decode( sanitize_text_field( $data ), ENT_QUOTES ) ) );
		}
	}

	public function decode_response_from_text_field( $data ) {
		return trim( htmlentities( $data ) );
	}


	public function prepare_for_string_matching( $data ) {
		if ( 'text_area' === $this->input_field ) {
			return mb_strtoupper( str_replace( ' ', '', preg_replace( '/\s\s+/', '', $data ) ) );
		} else {
			return mb_strtoupper( $data );
		}
	}

	abstract public function set_user_answer();

	public function set_correct_answer() {
		foreach ( $this->answer_array as $answer_key => $answer_value ) {
			if ( 1 === $this->grading_system ) {
				$this->correct_answer[ $answer_key ] = $this->sanitize_answer_from_db( $answer_value[0] );
			} elseif ( 1 === intval( $answer_value[2] ) ) {
				$this->correct_answer[ $answer_key ] = $this->sanitize_answer_from_db( $answer_value[0] );
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
