<?php
/**
 * Abstract WP_Async_Request class.
 *
 * Uses https://github.com/A5hleyRich/wp-background-processing
 * updates in the background.
 *
 * @since 7.0
 */

defined('ABSPATH') || exit;

if ( ! class_exists('WP_Async_Request', false) ) {
    include_once dirname(QSM_SUBMENU) . '/php/classes/lib/wp-async-request.php';
}

if ( ! class_exists('WP_Background_Process', false) ) {
    include_once dirname(QSM_SUBMENU) . '/php/classes/lib/wp-background-process.php';
}

class QSM_Background_Request extends WP_Async_Request {

    /**
     * @var string
     */
    protected $action = 'qsm_email_request';

    /**
     * Handle
     *
     * Override this method to perform any actions required
     * during the async request.
     */
    protected function handle() {
        $message = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
        if ( 'send_emails' === $message ) {
            $qmn_array_for_variables = isset( $_POST['variables'] ) ? qsm_sanitize_rec_array( wp_unslash( $_POST['variables'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            try {
                $this->really_long_running_task();
                QSM_Emails::send_emails($qmn_array_for_variables);
            } catch ( Exception $e ) {
                if ( defined('WP_DEBUG') && WP_DEBUG ) {
                    trigger_error('Background email triggered fatal error for callback.', E_USER_WARNING); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
                }
            }
        }
    }

    /**
     * Really long running process
     *
     * @return int
     */
    public function really_long_running_task() {
        return sleep(5);
    }

}