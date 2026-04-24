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
		global $mlwQuizMasterNext;
		$case_sensitive = $mlwQuizMasterNext->pluginHelper->get_question_setting( $this->question_id, 'case_sensitive' );
        $user_answer_value = $this->user_answer['input'];
        $correct_answers   = $this->correct_answer;
        if ( 12 === intval( $this->question_type ) ) {
            $correct_answers = array_map( array( $this, 'formatDateAnswer' ), $correct_answers );
        }
		if ( 1 === intval($case_sensitive ) ) {
			$answer_key = array_search( $user_answer_value, $correct_answers, true );
		}else {
			$answer_key = array_search( $this->prepare_for_string_matching( $user_answer_value ), array_map( array( $this, 'prepare_for_string_matching' ), $correct_answers ), true );
		}
        if ( false !== $answer_key ) {
            $this->answer_status = 'correct';
            $this->points       += floatval( $this->answer_array[ $answer_key ][1] );
        }
    }

    private function formatDateAnswer( $value ) {
        if ( is_string( $value ) ) {
            if ( preg_match( '#^([1-9]\d{3})[-/](\d{2})[-/](\d{2})$#', $value, $m ) ) {
                return $m[1] . '-' . $m[2] . '-' . $m[3];
            }
            if ( preg_match( '#^(\d{2})[-/](\d{2})[-/]([1-9]\d{3})$#', $value, $m ) ) {
                return $m[3] . '-' . $m[2] . '-' . $m[1];
            }
        }
        return $value;
    }
}
