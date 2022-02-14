<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Question_Review_Choice extends QSM_Question_Review {

	function __construct( $question_id = 0, $question_title_old = '', $answer_array = array() ) {
		parent::__construct( $question_id, $question_title_old, $answer_array );
	}

	public function set_user_answer() {
		if ( isset( $_POST[ 'question' . $this->question_id ] ) ) {
			$user_response = wp_unslash( $_POST[ 'question' . $this->question_id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if ( is_array( $user_response ) ) {
				foreach ( $user_response as $user_response_single ) {
					$user_answer_key                       = intval( $this->sanitize_answer_from_post( $user_response_single ) );
					$user_answer_value                     = $this->sanitize_answer_from_db( $this->answer_array[ $user_answer_key ][0] );
					$this->user_answer[ $user_answer_key ] = $user_answer_value;
				}
			} elseif ( '' !== $user_response ) {
				$user_answer_key                       = intval( $this->sanitize_answer_from_post( $user_response ) );
				$user_answer_value                     = $this->sanitize_answer_from_db( $this->answer_array[ $user_answer_key ][0] );
				$this->user_answer[ $user_answer_key ] = $user_answer_value;
			}
		}
	}

	public function set_answer_status() {
		$user_correct_ans  = 0;
		$total_correct_ans = 0;
		$is_user_attempted = false;
		foreach ( $this->user_answer as $user_answer_key => $user_answer_value ) {
			if ( in_array( $user_answer_key, array_keys( $this->correct_answer ), true ) ) {
				$user_correct_ans += 1;
				$is_user_attempted = true;
			} else {
				$user_correct_ans = -1;
			}
			$this->points            += $this->answer_array[ $user_answer_key ][1];
			$check_correct_answer_key = $this->answer_array[ $user_answer_key ][2];

			if ( 1 == $check_correct_answer_key ) {
				$total_correct_ans++;
			}
		}
		if ( $user_correct_ans == $total_correct_ans && $is_user_attempted ) {
			$this->answer_status = 'correct';
		}
	}
}
