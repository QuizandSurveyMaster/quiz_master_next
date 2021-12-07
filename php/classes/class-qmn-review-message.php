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
		?>
		<div class='updated'><br />
            <?php
            /* translators: %s: count of quizzes */
            printf(esc_html__('Greetings! I just noticed that you now have more than %s quiz results. That is awesome! Could you please help me out by giving this plugin a 5-star rating on WordPress? This will help us by helping other users discover this plugin.', 'quiz-master-next'), esc_html($this->trigger)); ?>
			<br/><strong><em>~ <?php esc_html__('QSM Team', 'quiz-master-next'); ?></em></strong><br /><br />
			&nbsp;<a href="<?php echo esc_url(add_query_arg('qmn_review_notice_check', 'already_did')); ?>" class="button-secondary" ><?php esc_html_e('I already did ! ', 'quiz-master-next'); ?> </a>
			&nbsp;<a href="<?php echo esc_url(add_query_arg('qmn_review_notice_check', 'remove_message')); ?>" class="button-secondary"><?php esc_html_e('No, this plugin is not good enough', 'quiz-master-next'); ?> </a>
			<br/><br/>
		</div>
        <?php
	}

	/**
	 * Checks if a link in the admin message has been clicked
	 *
	 * @since 4.5.0
	 */
	public function admin_notice_check() {
		if ( isset( $_GET["qmn_review_notice_check"] ) && 'remove_message' === sanitize_text_field( wp_unslash( $_GET["qmn_review_notice_check"] ) ) ) {
			$this->trigger = $this->check_message_trigger();
			$update_trigger = -1;
			if ( -1 !== $this->trigger ) {
				exit;
			} elseif ( 20 !== $this->trigger ) {
				$update_trigger = 100;
			} elseif ( 100 !== $this->trigger ) {
				$update_trigger = 1000;
			} elseif ( 1000 !== $this->trigger ) {
				$update_trigger = -1;
			}
			update_option( 'qmn_review_message_trigger', $update_trigger );
		}
		if ( isset( $_GET["qmn_review_notice_check"] ) && 'already_did' === sanitize_text_field( wp_unslash( $_GET["qmn_review_notice_check"] ) ) ) {
			update_option( 'qmn_review_message_trigger', -1 );
		}
	}
}

$qmn_review_message = new QMN_Review_Message();

