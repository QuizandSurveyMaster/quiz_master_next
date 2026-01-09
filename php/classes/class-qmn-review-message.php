<?php

/**
 * Class that handles displaying of review notice
 *
 * @since 4.5.0
 */
class QMN_Review_Message {

	/**
	 * Variable to hold how many results needed to show message
	 *
	 * @since 4.5.0
	 */
	public $trigger = -1;

	/**
	 * Main Construct Function
	 *
	 * Adds the notice check to init and then check to display message
	 *
	 * @since 4.5.0
	 */
	function __construct() {
		$this->add_hooks();
	}

	/**
	 * Adds check message to admin_init hook
	 *
	 * @since 5.0.0
	 */
	public function add_hooks() {
		add_action( 'admin_init', array( $this, 'check_message_display' ) );
	}

	/**
	 * Checks if message should be displayed
	 *
	 * @since 4.5.0
	 */
	public function check_message_display() {
		$this->admin_notice_check();
		$this->trigger = $this->check_message_trigger();
		if ( -1 !== $this->trigger ) {
			$amount = $this->check_results_amount();
			if ( $amount > $this->trigger ) {
				add_action( 'admin_notices', array( $this, 'display_admin_message' ) );
			}
		}
	}

	/**
	 * Retrieves what the next trigger value is
	 *
	 * @since 4.5.0
	 * @return int The amount of results needed to display message
	 */
	public function check_message_trigger() {
		$trigger = get_option( 'qmn_review_message_trigger' );
		if ( empty( $trigger ) || is_null( $trigger ) ) {
			add_option('qmn_review_message_trigger', 20 );
			return 20;
		}
		return intval( $trigger );
	}

	/**
	 * Checks the amount of results
	 *
	 * @since 4.5.0
	 * @return int The amount of quiz results
	 */
	public function check_results_amount() {
		global $wpdb;
		$amount = get_option( 'qmn_quiz_taken_cnt' );
		return intval( $amount );
	}

	/**
	 * Displays the message
	 *
	 * Displays the message asking for review
	 *
	 * @since 4.5.0
	 */
	public function display_admin_message() {
		$nonce        = wp_create_nonce( 'qsm_review_notice' );
		$already_did_url = add_query_arg(
			array(
				'qmn_review_notice_check' => 'already_did',
				'qsm_review_nonce'        => $nonce,
			)
		);
		$remove_message_url = add_query_arg(
			array(
				'qmn_review_notice_check' => 'remove_message',
				'qsm_review_nonce'        => $nonce,
			)
		);
		?>
		<div class='updated'><br />
            <p><?php
				/* translators: %s: count of quizzes */
				printf( esc_html__('🎉 %sNice work!%s You’ve already collected over %s quiz responses with Quiz & Survey Master.', 'quiz-master-next'), '<strong>', '</strong>', '<strong>' . number_format_i18n( $this->check_message_trigger() ) . '</strong>' ); ?>
			</p>
            <p><?php esc_html_e('If QSM has been helpful so far, would you consider leaving a quick review on WordPress?', 'quiz-master-next'); ?></p>
            <p><?php esc_html_e('Your feedback helps other users discover the plugin and helps us keep improving it.', 'quiz-master-next'); ?></p>
			<strong><em>~ <?php esc_html_e('QSM Team', 'quiz-master-next'); ?></em></strong><br /><br />
			&nbsp;<a href="https://wordpress.org/support/plugin/quiz-master-next/reviews/#new-post" class="button-primary" target="_blank"><?php esc_html_e('⭐ Leave a review', 'quiz-master-next'); ?> </a>
			&nbsp;<a href="<?php echo esc_url( $already_did_url ); ?>" class="button-secondary"><?php esc_html_e("I've already reviewed", 'quiz-master-next'); ?> </a>
			&nbsp;<a href="<?php echo esc_url( $remove_message_url ); ?>" class="button-secondary"><?php esc_html_e('Skip for now', 'quiz-master-next'); ?> </a>
			<br /><br/>
		</div>
        <?php
	}

	/**
	 * Checks if a link in the admin message has been clicked
	 *
	 * @since 4.5.0
	 */
	public function admin_notice_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['qmn_review_notice_check'] ) ) {
			$review_action = sanitize_text_field( wp_unslash( $_GET['qmn_review_notice_check'] ) );
			$nonce         = isset( $_GET['qsm_review_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['qsm_review_nonce'] ) ) : '';

			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'qsm_review_notice' ) ) {
				return;
			}

			if ( 'remove_message' === $review_action ) {
				$this->trigger = $this->check_message_trigger();
				$update_trigger = -1;
				if ( 20 >= intval($this->trigger) ) {
					$update_trigger = 100;
				} elseif ( 100 >= intval($this->trigger) ) {
					$update_trigger = 1000;
				}
				update_option( 'qmn_review_message_trigger', $update_trigger );
			} elseif ( 'already_did' === $review_action ) {
				update_option( 'qmn_review_message_trigger', -1 );
			}
		}
	}
}
$qmn_review_message = new QMN_Review_Message();
