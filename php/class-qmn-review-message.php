<?php

/**
 * Class that handles displaying of review notice
 * 
 * @since 4.5.0
 */
class QMN_Review_Message {
	
	public $trigger = -1;
	
	function __construct() {
		add_action( 'admin_init', array( $this, 'admin_notice_check' ) );
		$this->check_message_display();
	}
	
	public function check_message_display() {
		$this->trigger = $this->check_message_trigger();
		if ( $this->trigger !== -1 ) {
			$amount = $this->check_results_amount();
			if ( $amount > $this->trigger ) {
				add_action( 'admin_notices', array( $this, 'display_admin_message' ) );
			}
		}
	}
	
	public function check_message_trigger() {
		$trigger = get_option( 'qmn_review_message_trigger' );
		if ( empty($trigger) || is_null($trigger) ) {
			add_option('qmn_review_message_trigger', 10 );
			return 10;
		}
		return $trigger;
	}
	
	public function check_results_amount() {
		global $wpdb;
		$table_name = $wpdb->prefix."mlw_results";
		$amount = $wpdb->get_var( "SELECT COUNT(result_id) FROM $table_name WHERE deleted=0" );
		return $amount;
	}
	
	public function display_admin_message() {
		$already_url  = esc_url( add_query_arg( 'qmn_review_notice_check', 'already_did' ) );
		$nope_url  = esc_url( add_query_arg( 'qmn_review_notice_check', 'remove_message' ) );
		echo "<div class='updated'>";
		echo sprintf( __('Greetings! I just noticed that you now have more than %d quiz results. That is 
		awesome! Could you please help me out by giving this plugin a 5-star rating on WordPress? This 
		will help us by helping other users discover this plugin. %s', 'quiz_master_next'), 
			$this->trigger,
			'<br /><strong><em>~ Frank Corso</em></strong>'
		);
		echo '&nbsp;<a href="" class="button-primary">' . __( 'Yeah, you deserve it!', 'quiz-master-next' ) . '</a>';
		echo '&nbsp;<a href="' . esc_url( $already_url ) . '" class="button-secondary">' . __( 'I already did!', 'quiz-master-next' ) . '</a>';
  		echo '&nbsp;<a href="' . esc_url( $nope_url ) . '" class="button-secondary">' . __( 'No, this plugin is not good enough', 'quiz-master-next' ) . '</a>';
		echo "</div>";
	}
	
	public function admin_notice_check() {
		if ( isset( $_GET["qmn_review_notice_check"] ) && $_GET["qmn_review_notice_check"] == 'remove_message' ) {
			$this->trigger = $this->check_message_trigger();
			$update_trigger = -1;
			if ( $this->trigger === -1 ) {
				exit;
			} else if ( $this->trigger === 10 ) {
				$update_trigger = 100;
			} else if ( $this->trigger === 100 ) {
				$update_trigger = 1000;
			}
			update_option( 'qmn_review_message_trigger', $update_trigger );
		}
		if ( isset( $_GET["qmn_review_notice_check"] ) && $_GET["qmn_review_notice_check"] == 'already_did' ) {
			update_option( 'qmn_review_message_trigger', -1 );
		}
	}
}

$qmn_review_message = new QMN_Review_Message();
?>
