<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Question_Review_Fill_In_Blanks extends QSM_Question_Review {

	function __construct( $question_id = 0, $question_title_old = '', $answer_array = array() ) {
		parent::__construct( $question_id, $question_title_old, $answer_array );
	}
	public function get_question_text() {
		if ( strpos( $this->question_description, '%BLANK%' ) !== false || strpos( $this->question_description, '%blank%' ) !== false ) {
			return str_replace( array( '%BLANK%', '%blank%' ), array( '__________', '__________' ), do_shortcode( htmlspecialchars_decode( $this->question_description, ENT_QUOTES ) ) );
		} else {
			return false;
		}
	}

	public function set_user_answer() {
		if ( isset( $_POST[ 'question' . $this->question_id ] ) ) {
			$user_response = wp_unslash( $_POST[ 'question' . $this->question_id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $user_response as $user_answer_key => $user_answer_value ) {
				$user_answer_value                     = $this->sanitize_answer_from_post( $user_answer_value );
				$user_answer_value                     = $this->decode_response_from_text_field( $user_answer_value );
				$this->user_answer[ $user_answer_key ] = $user_answer_value;
			}
		}
		return $this->user_answer;
	}

	public function set_correct_answer() {
		foreach ( $this->answer_array as $answer_key => $answer_value ) {
			$this->correct_answer[ $answer_key ] = $this->sanitize_answer_from_db( $answer_value[0] );
		}
	}

	public function set_answer_status() {
		global $mlwQuizMasterNext;
		$match_answer = $mlwQuizMasterNext->pluginHelper->get_question_setting( $this->question_id, 'matchAnswer' );
		if ( 'sequence' === $match_answer ) {
			$this->process_sequentially();
		} else {
			$this->process_randomly();
		}
	}

	private function process_randomly() {
		$total_user_answers   = sizeof( $this->user_answer );
		$total_correct_answer = sizeof( $this->correct_answer );
		if ( $total_user_answers <= $total_correct_answer ) {
			foreach ( $this->user_answer as $user_answer ) {
				$answer_key = array_search( $this->prepare_for_string_matching( $user_answer ), array_map( array( $this, 'prepare_for_string_matching' ), $this->correct_answer ), true );
				if ( false !== $answer_key ) {
					$this->answer_status = 'correct';
					$this->points       += $this->answer_array[ $answer_key ][1];
				} else {
					$this->answer_status = 'incorrect';
				}
			}
		} else {
			foreach ( $this->correct_answer as $correct_answer ) {
				$answer_key = array_search( $this->prepare_for_string_matching( $correct_answer ),  array_map( array( $this, 'prepare_for_string_matching' ), $this->user_answer ), true );
				if ( false !== $answer_key ) {
					$this->answer_status = 'correct';
					$this->points       += $this->answer_array[ $answer_key ][1];
				} else {
					$this->answer_status = 'incorrect';
				}
			}
		}
	}

	private function process_sequentially() {
		$total_user_answers   = sizeof( $this->user_answer );
		$total_correct_answer = sizeof( $this->correct_answer );
		if ( $total_user_answers <= $total_correct_answer ) {
			foreach ( $this->user_answer as $user_answer_key => $user_answer ) {
				if ( $this->prepare_for_string_matching( $user_answer ) === $this->prepare_for_string_matching( $this->correct_answer[ $user_answer_key ] ) ) {
					$this->answer_status = 'correct';
					$this->points       += $this->answer_array[ $user_answer_key ][1];
				} else {
					$this->answer_status = 'incorrect';
				}
			}
		} else {
			foreach ( $this->correct_answer as $correct_answer_key => $correct_answer ) {
				if ( $this->prepare_for_string_matching( $correct_answer ) === $this->prepare_for_string_matching( $this->user_answer[ $correct_answer_key ] ) ) {
					$this->answer_status = 'correct';
					$this->points       += $this->answer_array[ $correct_answer_key ][1];
				} else {
					$this->answer_status = 'incorrect';
				}
			}
		}
	}
}
