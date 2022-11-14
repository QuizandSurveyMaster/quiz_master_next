<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QSM_Question_Review_Text extends QSM_Question_Review {

	function __construct( $question_id = 0, $question_title_old = '', $answer_array = array() ) {
		parent::__construct( $question_id, $question_title_old, $answer_array );
	}

	public function set_user_answer() {
		if ( isset( $_POST[ 'question' . $this->question_id ] ) ) {
			$user_answer_key                       = 'input';
			$user_answer_value                     = $this->sanitize_answer_from_post( wp_unslash( $_POST[ 'question' . $this->question_id ] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$this->user_answer[ $user_answer_key ] = $user_answer_value;
		}
	}

    public function set_answer_status() {
        $user_answer_value = $this->user_answer['input'];
        $answer_key        = array_search( $this->prepare_for_string_matching( $user_answer_value ), array_map( array( $this, 'prepare_for_string_matching' ), $this->correct_answer ), true );
        if ( false !== $answer_key ) {
            $this->answer_status = 'correct';
            $this->points       += $this->answer_array[ $answer_key ][1];
        }
    }
}
