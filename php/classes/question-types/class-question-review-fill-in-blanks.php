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
		$decoded_answer = html_entity_decode( (string) $correct_answer, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$raw_parts      = explode( ',', $decoded_answer );
		$parts          = array();
		foreach ( $raw_parts as $raw_part ) {
			$sanitized = trim( sanitize_text_field( $raw_part ) );
			if ( '' !== $sanitized ) {
				$parts[] = $sanitized;
			}
		}
		if ( empty( $parts ) ) {
			$parts = array( trim( sanitize_text_field( $decoded_answer ) ) );
		}
		if ( 1 !== intval( $case_sensitive ) ) {
			$parts = array_map( array( $this, 'prepare_for_string_matching' ), $parts );
		}
		return $parts;
	}

	private function qsm_normalize_user_answer( $user_answer, $case_sensitive ) {
		$normalized = trim( sanitize_text_field( html_entity_decode( (string) $user_answer, ENT_QUOTES, get_bloginfo( 'charset' ) ) ) );
		if ( 1 === intval( $case_sensitive ) ) {
			return $normalized;
		}
		return $this->prepare_for_string_matching( $normalized );
	}

	private function qsm_answers_match( $user_answer, $correct_answer, $case_sensitive ) {
		$normalized_user = $this->qsm_normalize_user_answer( $user_answer, $case_sensitive );
		$acceptable      = $this->qsm_get_acceptable_answers( $correct_answer, $case_sensitive );
		if ( empty( $acceptable ) ) {
			return false;
		}
		return in_array( $normalized_user, $acceptable, true );
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
				if ( $this->is_sequential_match( $user_answer, $user_answer_key, $case_sensitive ) ) {
					++$user_correct_ans;
					$this->points       += $this->answer_array[ $user_answer_key ][1];
				}
			}
		} else {
			foreach ( $this->correct_answer as $correct_answer_key => $correct_answer ) {
				if ( $this->is_sequential_match( $this->user_answer[ $correct_answer_key ] ?? '', $correct_answer_key, $case_sensitive ) ) {
					++$user_correct_ans;
					$this->points       += $this->answer_array[ $correct_answer_key ][1];
				}
			}
		}
		if ( ( $this->correct_answer_logic && $total_user_answers === $user_correct_ans ) || ( ! $this->correct_answer_logic && 0 < $user_correct_ans ) ) {
			$this->answer_status = 'correct';
		}
	}

	private function is_sequential_match( $user_answer, $answer_key, $case_sensitive ) {
		$correct_answer = isset( $this->correct_answer[ $answer_key ] ) ? $this->correct_answer[ $answer_key ] : '';
		$possible_answers = $this->get_possible_answers_for_blank( $correct_answer );
		if ( empty( $possible_answers ) ) {
			return false;
		}
		foreach ( $possible_answers as $possible_answer ) {
			if ( 1 === intval( $case_sensitive ) ) {
				if ( $user_answer === $possible_answer ) {
					return true;
				}
			} else {
				if ( $this->prepare_for_string_matching( $user_answer ) === $this->prepare_for_string_matching( $possible_answer ) ) {
					return true;
				}
			}
		}
		return false;
	}

	public function get_possible_answers_for_blank( $answer ) {
		if ( ! is_string( $answer ) || '' === trim( $answer ) ) {
			return array();
		}
		$decoded_answer = html_entity_decode( $answer, ENT_QUOTES, get_bloginfo( 'charset' ) );
		$raw_answers    = explode( ',', $decoded_answer );
		$split_answers  = array_map(
			static function( $value ) {
				return trim( sanitize_text_field( $value ) );
			},
			$raw_answers
		);
		$split_answers = array_values(
			array_filter(
				$split_answers,
				static function ( $value ) {
					return '' !== $value;
				}
			)
		);
		return ! empty( $split_answers ) ? $split_answers : array( trim( sanitize_text_field( $decoded_answer ) ) );
	}
}
