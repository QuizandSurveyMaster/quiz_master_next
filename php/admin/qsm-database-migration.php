<?php


function qsm_debug_output( $data, $exit = 0 ){
	echo '<pre>';
	echo print_r( $data, true );
	echo '</pre>';
	if( $exit ) {
		exit;
	}
}


function qsm_migration_callback() {
	global $mlwQuizMasterNext;
    // Enqueue required scripts and styles
    wp_enqueue_style('qsm-database-migration', QSM_PLUGIN_CSS_URL . '/qsm-database-migration.css', array(), $mlwQuizMasterNext->version);
    wp_enqueue_script('qsm-database-migration', QSM_PLUGIN_JS_URL . '/qsm-database-migration-script.js', array('jquery'), $mlwQuizMasterNext->version, true);

    // Localize script with translated strings
    wp_localize_script('qsm-database-migration', 'qsmMigrationData', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('qsm_migration_nonce'),
        'confirmMessage' => __('Are you sure you want to start the database migration? This process cannot be reversed.', 'quiz-master-next'),
        'startMessage' => __('Migration started...', 'quiz-master-next'),
        'processingMessage' => __('Migration in progress...', 'quiz-master-next'),
        'successMessage' => __('Migration completed successfully!', 'quiz-master-next'),
        'errorMessage' => __('An error occurred during migration.', 'quiz-master-next'),
        'warningMessage' => __('Before starting migration, please create a database backup.', 'quiz-master-next')
    ));
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Database Migration', 'quiz-master-next'); ?></h1>

        <div class="qsm-database-migration-wrapper">
            <form id="qsm-migration-form" class="qsm-migration-form">
                <div class="qsm-migration-warning">
                    <strong>⚠️ <?php echo esc_html__('Warning:', 'quiz-master-next'); ?></strong>
                    <?php echo esc_html__('Before starting the migration, please create a full database backup.', 'quiz-master-next'); ?>
                </div>

                <div class="qsm-migration-status"></div>

                <button type="submit" id="qsm-start-migration" class="qsm-migration-button button button-primary">
                    <?php echo esc_html__('Start Migration', 'quiz-master-next'); ?>
                </button>
            </form>
        </div>
    </div>
    <?php
}

Class QSM_Database_Migration {

    public $wpdb;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        add_action('wp_ajax_qsm_start_migration', array($this, 'qsm_start_migration_callback'));
    }

    function create_migration_tables() {
        
        $charset_collate = $this->wpdb->get_charset_collate();

        // Create wp_qsm_results_answers table
        $sql_results_answers = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}qsm_results_answers (
            id INT NOT NULL AUTO_INCREMENT,
            result_id INT NOT NULL,
            question_id INT NOT NULL,
            user_answer LONGTEXT,
            correct TINYINT(1),
            points FLOAT,
            created_at DATETIME,
            correct_answer LONGTEXT,
            PRIMARY KEY (id),
            KEY result_id (result_id),
            KEY question_id (question_id),
            KEY result_question (result_id, question_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_results_answers);
    }

    /**
     * AJAX callback for handling database migration
     */
    function qsm_start_migration_callback() {
        // Verify nonce
        if (!check_ajax_referer('qsm_migration_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'quiz-master-next')));
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform migrations.', 'quiz-master-next')));
        }

        try {

            // Recreate tables with updated structure
            $this->create_migration_tables();
            
            $old_results = $this->wpdb->get_results("SELECT * FROM {$this->wpdb->prefix}mlw_results WHERE deleted = 0 ORDER BY result_id DESC");
            
            foreach ($old_results as $result_value) {      
                $results_id = $result_value->result_id;
                $unserializedValue = maybe_unserialize($result_value->quiz_results);

                if (isset($unserializedValue[1]) && is_array($unserializedValue[1])) {
                    foreach ($unserializedValue[1] as $question_value) {
                        if (!isset($question_value['id'])) {
                            continue;
                        }
                        $data_to_insert = array(
                            'result_id' => $results_id,
                            'question_id' => $question_value['id'],
                            'user_answer' => isset($question_value[1]) ? maybe_serialize($question_value[1]) : '',
                            'correct' => isset($question_value['correct']) && 'correct' == $question_value['correct'] ? 1 : 0,
                            'points' => isset($question_value['points']) ? floatval($question_value['points']) : 0,
                            'correct_answer' => isset($question_value[2]) ? maybe_serialize($question_value[2]) : '',
                            'created_at' => current_time('mysql')
                        );

                        // qsm_debug_output( $data_to_insert , 0);

                        // Insert into the new simplified results_answers table
                        $this->wpdb->insert(
                            $this->wpdb->prefix . 'qsm_results_answers',
                            $data_to_insert
                        );
                    }
                }
            }
            // Send success response
            wp_send_json_success(array(
                'message' => __('Database migration completed successfully.', 'quiz-master-next')
            ));
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => sprintf(
                    __('Migration failed: %s', 'quiz-master-next'),
                    $e->getMessage()
                )
            ));
        }
    }
}
new QSM_Database_Migration();

// Add AJAX action for database migration
