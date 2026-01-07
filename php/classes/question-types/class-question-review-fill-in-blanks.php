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
				$this->user_answer[ $user_answer_key ] = $user_answer_value;
			}
		}
		return $this->user_answer;
	}

	public function set_correct_answer() {
		global $mlwQuizMasterNext;
		foreach ( $this->answer_array as $answer_key => $answer_value ) {
			$this->correct_answer[ $answer_key ] = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $this->sanitize_answer_from_db( $answer_value[0] ), "answer-{$this->question_id}-{$answer_key}", "QSM Answers" );
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

	private function qsm_get_acceptable_answers( $correct_answer, $case_sensitive ) {
		$correct_answer  = (string) $correct_answer;
		$case_sensitive  = intval( $case_sensitive );
		$parts           = array_map( 'trim', explode( ',', $correct_answer ) );
		$parts           = array_values( array_filter( $parts, function( $v ) {
			return '' !== $v;
		} ) );

		if ( empty( $parts ) ) {
			return array( '' );
		}

		if ( 1 !== $case_sensitive ) {
			$parts = array_map( array( $this, 'prepare_for_string_matching' ), $parts );
		}

		return $parts;
	}

	private function qsm_answers_match( $user_answer, $correct_answer, $case_sensitive ) {
		$user_answer    = (string) $user_answer;
		$case_sensitive = intval( $case_sensitive );

		if ( 1 !== $case_sensitive ) {
			$user_answer = $this->prepare_for_string_matching( $user_answer );
		}

		return in_array( $user_answer, $this->qsm_get_acceptable_answers( $correct_answer, $case_sensitive ), true );
	}

	private function qsm_find_matching_key_in_correct_answers( $user_answer, $correct_answers, $case_sensitive ) {
		foreach ( $correct_answers as $key => $correct_answer ) {
			if ( $this->qsm_answers_match( $user_answer, $correct_answer, $case_sensitive ) ) {
				return $key;
			}
		}
		return false;
	}

	private function process_randomly() {
		global $mlwQuizMasterNext;
		$user_correct_ans = 0;
		$total_user_answers   = sizeof( $this->user_answer );
		$total_correct_answer = sizeof( $this->correct_answer );
		$case_sensitive = $mlwQuizMasterNext->pluginHelper->get_question_setting( $this->question_id, 'case_sensitive' );
		if ( $total_user_answers <= $total_correct_answer ) {
			foreach ( $this->user_answer as $user_answer ) {
				$answer_key = $this->qsm_find_matching_key_in_correct_answers( $user_answer, $this->correct_answer, $case_sensitive );
				if ( false !== $answer_key ) {
					++$user_correct_ans;
					$this->points       += $this->answer_array[ $answer_key ][1];
				}
			}
		} else {
			foreach ( $this->correct_answer as $correct_answer ) {
				$answer_key = false;
				foreach ( $this->user_answer as $user_key => $user_answer ) {
					if ( $this->qsm_answers_match( $user_answer, $correct_answer, $case_sensitive ) ) {
						$answer_key = $user_key;
						break;
					}
				}
				if ( false !== $answer_key ) {
					++$user_correct_ans;
					$this->points       += $this->answer_array[ $answer_key ][1];
				}
			}
		}
		if ( ( $this->correct_answer_logic && $total_user_answers === $user_correct_ans ) || ( ! $this->correct_answer_logic && 0 < $user_correct_ans ) ) {
			$this->answer_status = 'correct';
		}
	}

	private function process_sequentially() {
		global $mlwQuizMasterNext;
		$user_correct_ans = 0;
		$total_user_answers   = sizeof( $this->user_answer );
		$total_correct_answer = sizeof( $this->correct_answer );
		$case_sensitive = $mlwQuizMasterNext->pluginHelper->get_question_setting( $this->question_id, 'case_sensitive' );
		if ( $total_user_answers <= $total_correct_answer ) {
			foreach ( $this->user_answer as $user_answer_key => $user_answer ) {
				if ( isset( $this->correct_answer[ $user_answer_key ] ) && $this->qsm_answers_match( $user_answer, $this->correct_answer[ $user_answer_key ], $case_sensitive ) ) {
					++$user_correct_ans;
					$this->points       += $this->answer_array[ $user_answer_key ][1];
				}
			}
		} else {
			foreach ( $this->correct_answer as $correct_answer_key => $correct_answer ) {
				if ( isset( $this->user_answer[ $correct_answer_key ] ) && $this->qsm_answers_match( $this->user_answer[ $correct_answer_key ], $correct_answer, $case_sensitive ) ) {
					++$user_correct_ans;
					$this->points       += $this->answer_array[ $correct_answer_key ][1];
				}
			}
		}
		if ( ( $this->correct_answer_logic && $total_user_answers === $user_correct_ans ) || ( ! $this->correct_answer_logic && 0 < $user_correct_ans ) ) {
			$this->answer_status = 'correct';
		}
	}
}
