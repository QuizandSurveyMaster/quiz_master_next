<?php
/**
 * QSM Database Migration - Result-level logging (Option A)
 *
 * Replace or include this file in your plugin admin area.
 * This code exposes two AJAX endpoints:
 * - qsm_start_migration           -> initialize migration, create tables, return totals
 * - qsm_process_migration_batch   -> process a single batch (offset, limit)
 *
 * Behavior:
 * - Migration log table (one row per result_id) prevents duplicate migration
 * - Each result_id's question inserts are done atomically with a transaction:
 * - success -> log success
 * - failure -> rollback result and log error; continue with other results
 *
 * Make sure constants like QSM_PLUGIN_CSS_URL and QSM_PLUGIN_JS_URL exist in your plugin.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Debug helper
 */
function qsm_debug_output( $data, $exit = 0 ){
    echo '<pre>';
    echo print_r( $data, true );
    echo '</pre>';
    if ( $exit ) {
        exit;
    }
}

/**
 * Admin page UI and script enqueue + localization
 */
function qsm_migration_callback() {
    global $mlwQuizMasterNext;

    // Enqueue required scripts and styles; always enqueue to ensure localized data is available.
    wp_enqueue_style(
        'qsm-database-migration',
        defined('QSM_PLUGIN_CSS_URL') ? QSM_PLUGIN_CSS_URL . '/qsm-database-migration.css' : '',
        array(),
        isset($mlwQuizMasterNext->version) ? $mlwQuizMasterNext->version : false
    );

    wp_enqueue_script(
        'qsm-database-migration',
        defined('QSM_PLUGIN_JS_URL') ? QSM_PLUGIN_JS_URL . '/qsm-database-migration-script.js' : '',
        array('jquery'),
        isset($mlwQuizMasterNext->version) ? $mlwQuizMasterNext->version : false,
        true
    );

    // Localize script with translated strings & AJAX URL + nonce
    wp_localize_script('qsm-database-migration', 'qsmMigrationData', array(
        'ajax_url'           => admin_url('admin-ajax.php'),
        'nonce'              => wp_create_nonce('qsm_migration_nonce'),
        'confirmMessage'     => __('Are you sure you want to start the database migration? This process cannot be reversed.', 'quiz-master-next'),
        'startMessage'       => __('Migration started...', 'quiz-master-next'),
        'processingMessage'  => __('Migration in progress...', 'quiz-master-next'),
        'successMessage'     => __('Migration completed successfully!', 'quiz-master-next'),
        'errorMessage'       => __('An error occurred during migration.', 'quiz-master-next'),
        'warningMessage'     => __('Before starting migration, please create a database backup.', 'quiz-master-next'),
        // Labels for UI details
        'labelTotalRecords'  => __('Total Results to Migrate:', 'quiz-master-next'),
        'labelProcessed'     => __('Results Processed:', 'quiz-master-next'),
        'labelInserted'      => __('Total Results Migrated:', 'quiz-master-next'),
        'labelFailed'        => __('Total Results Failed:', 'quiz-master-next'),
        'labelErrorNote'     => __('Migration stopped due to an error. Check browser console and server logs for details.', 'quiz-master-next'),
    ));
    delete_option( 'qsm_migration_results_processed');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__('Database Migration', 'quiz-master-next'); ?></h1>

        <div class="qsm-database-migration-wrapper">
            <?php if ( get_option( 'qsm_migration_results_processed', false ) ) : ?>
                <div class="qsm-migration-complete">
                    <?php echo esc_html__( 'All results have already been migrated. No further action is required.', 'quiz-master-next' ); ?>
                </div>
            <?php else : ?>
                <form id="qsm-migration-form" class="qsm-migration-form">
                    <div class="qsm-migration-warning">
                        <strong>⚠️ <?php echo esc_html__('Warning:', 'quiz-master-next'); ?></strong>
                        <?php echo esc_html__('Before starting the migration, please create a full database backup.', 'quiz-master-next'); ?>
                    </div>

                    <div class="qsm-migration-progress-bar">
                        <div class="qsm-migration-progress" style="width: 0%;"></div>
                    </div>

                    <div class="qsm-migration-status"></div>
                    <div class="qsm-migration-details"></div>

                    <button type="submit" id="qsm-start-migration" class="qsm-migration-button button button-primary">
                        <?php echo esc_html__('Start Migration', 'quiz-master-next'); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

/**
 * Migration class
 */
class QSM_Database_Migration {

    public $wpdb;
    const BATCH_SIZE = 100; // You can change this value as needed

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;

        // Register AJAX endpoints
        add_action('wp_ajax_qsm_start_migration', array($this, 'qsm_initial_migration_start_callback'));
        add_action('wp_ajax_qsm_process_migration_batch', array($this, 'qsm_process_migration_batch_callback'));
    }
    function create_migration_tables() {
        
        $charset_collate = $this->wpdb->get_charset_collate();

        // Ensure answers table (use InnoDB for transactions)
        $answers_table = $this->wpdb->prefix . 'qsm_results_questions';
        $sql_results_answers = "CREATE TABLE IF NOT EXISTS `{$answers_table}` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `result_id` BIGINT(20) UNSIGNED NOT NULL,
            `quiz_id` BIGINT(20) UNSIGNED NOT NULL,
            `question_id` BIGINT(20) UNSIGNED NOT NULL,

            `question_title` TEXT,
            `question_description` LONGTEXT,
            `question_comment` TEXT,

            `question_type` VARCHAR(50),
            `answer_type` VARCHAR(50) DEFAULT 'text',  

            `correct_answer` TEXT,
            `user_answer` TEXT,

            `user_answer_comma` TEXT,
            `correct_answer_comma` TEXT,

            `points` FLOAT DEFAULT 0,
            `correct` TINYINT(1) DEFAULT 0,

            `category` TEXT,
            `multicategories` TEXT,

            `other_settings` TEXT,

            PRIMARY KEY (`id`),
            KEY `result_id` (`result_id`),
            KEY `question_id` (`question_id`),
            KEY `quiz_id` (`quiz_id`),
            KEY `result_question` (`result_id`, `question_id`)
        ) ENGINE=InnoDB {$charset_collate};";


        // Ensure results meta table
        $results_meta_table = $this->wpdb->prefix . 'qsm_results_meta';
        $sql_results_meta = "CREATE TABLE IF NOT EXISTS `{$results_meta_table}` (
            `meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `result_id` BIGINT(20) UNSIGNED NOT NULL,
            `meta_key` varchar(191) NOT NULL,
            `meta_value` longtext,
            PRIMARY KEY (`meta_id`),
            KEY `result_id` (`result_id`),
            KEY `meta_key` (`meta_key`)
        ) ENGINE=InnoDB {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Use dbDelta to be safe for updates
        dbDelta($sql_results_answers);
        dbDelta($sql_results_meta);

        // Add any missing indexes (safe checks)
        $this->maybe_add_index($answers_table, 'idx_qra_result_id', "CREATE INDEX idx_qra_result_id ON {$answers_table} (result_id)");
        $this->maybe_add_index($answers_table, 'idx_qra_result_correct', "CREATE INDEX idx_qra_result_correct ON {$answers_table} (result_id, correct)");

        // Add useful indexes on mlw_results if missing
        $mlw_results = $this->wpdb->prefix . 'mlw_results';
        $this->maybe_add_index($mlw_results, 'idx_mrw_quiz_id', "CREATE INDEX idx_mrw_quiz_id ON {$mlw_results} (quiz_id)");
        $this->maybe_add_index($mlw_results, 'idx_mrw_time_taken', "CREATE INDEX idx_mrw_time_taken ON {$mlw_results} (time_taken_real)");
        $this->maybe_add_index($mlw_results, 'idx_mrw_user', "CREATE INDEX idx_mrw_user ON {$mlw_results} (`user`)");
    }

    /**
     * Helper to add an index only if it doesn't exist.
     */
    private function maybe_add_index($table, $index_name, $sql) {
        $exists = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(1) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = %s AND index_name = %s",
                $table, $index_name
            )
        );

        if ( ! $exists ) {
            $this->wpdb->query($sql);
        }
    }

    /**
     * AJAX callback to initiate migration: create tables & return totals
     */
    public function qsm_initial_migration_start_callback() {
        // Verify nonce
        if (! check_ajax_referer('qsm_migration_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => __('Security check failed.', 'quiz-master-next')));
        }

        // Capability check
        if (! current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform migrations.', 'quiz-master-next')));
        }
        
        // Ensure tables exist and indexes applied
        $this->create_migration_tables();
        
        // --- Calculate Total Records to Migrate ---
        $mlw_results_table       = $this->wpdb->prefix . 'mlw_results';
        $results_meta_table_name = $this->wpdb->prefix . 'qsm_results_meta';
        
        // Count total results (the target count)
        $total_records = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$mlw_results_table}" );

        // Count how many results have been *logged* (migrated or failed)
        $logged_records = (int) $this->wpdb->get_var(
            "SELECT COUNT(DISTINCT r.result_id)
            FROM {$mlw_results_table} r
            INNER JOIN {$results_meta_table_name} m
                ON m.result_id = r.result_id
                AND m.meta_key = 'result_meta'"
        );
        
        // Number of records already processed (logged)
        $processed_count = $logged_records;

        // If all records are logged and there are no failed IDs, it's complete.
        $stored_failed_ids = get_option('qsm_migration_results_failed_ids', array());
        if ( $processed_count === $total_records && empty($stored_failed_ids) ) {
            // update_option( 'qsm_migration_results_processed', true ); // The check status handles this.
            wp_send_json_success(array(
                'message'        => __('Migration has already been completed. No further action is required.', 'quiz-master-next'),
                'already_done'   => true,
                'total_records'  => $total_records, // Send original total for progress bar
                'processed_count'=> $processed_count,
                'batch_size'     => self::BATCH_SIZE,
            ));
        }

        try {

            wp_send_json_success(array(
                'message' => __('Migration initiated. Starting batch processing.', 'quiz-master-next'),
                'total_records' => $total_records,
                'processed_count' => $processed_count, // Send back how many are already logged
                'batch_size' => self::BATCH_SIZE
            ));

        } catch (Exception $e) {
            wp_send_json_error(array('message' => __('Error during migration initialization: ', 'quiz-master-next') . $e->getMessage()));
        }
    }

    /**
     * AJAX callback to process a single batch of results.
     * Expects POST parameter: offset (int) - this is the running count of processed records for UI only.
     */
    public function qsm_process_migration_batch_callback() {
        // Verify nonce
        if ( ! check_ajax_referer( 'qsm_migration_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed.', 'quiz-master-next' ) ) );
        }

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform migrations.', 'quiz-master-next' ) ) );
        }

        $current_processed_count = isset( $_POST['current_processed_count'] ) ? intval( $_POST['current_processed_count'] ) : 0;
        $process_failed_only     = ! empty( $_POST['process_failed_only'] );
        $batch_size              = self::BATCH_SIZE;

        $mlw_results_table       = $this->wpdb->prefix . 'mlw_results';
        $answers_table           = $this->wpdb->prefix . 'qsm_results_questions';
        $results_meta_table_name = $this->wpdb->prefix . 'qsm_results_meta';
        
        $results_processed   = 0;
        $inserted_count      = 0;

        try {

            // Re-check status before processing
            if ( $this->qsm_check_migration_status() ) {
                wp_send_json_success(
                    array(
                        'message'           => __( 'Migration has already been completed. No further action is required.', 'quiz-master-next' ),
                        'results_processed' => 0,
                        'migrated_results'  => 0,
                        'failed_results'    => 0,
                        'next_offset'       => $current_processed_count,
                        'completed'         => true,
                        'success_ids'       => array(),
                        'failed_ids'        => array(),
                    )
                );
            }

            // --------------------------------------------------
            // 1. Fetch a batch of results to migrate
            // --------------------------------------------------
            $failed_ids = get_option( 'qsm_migration_results_failed_ids', array() );
            $failed_ids = array_map( 'intval', (array) $failed_ids );
            
            $query = "SELECT r.* FROM {$mlw_results_table} r 
            LEFT JOIN {$results_meta_table_name} m
              ON m.result_id = r.result_id
              AND m.meta_key = 'result_meta'
              WHERE m.meta_id IS NULL";

            $params = array();

            if ( $process_failed_only ) {
                // Failed-only mode: process only the IDs currently marked as failed.
                if ( empty( $failed_ids ) ) {
                    // Nothing to process in this mode.
                    wp_send_json_success(
                        array(
                            'message'           => __( 'No failed results to reprocess.', 'quiz-master-next' ),
                            'results_processed' => 0,
                            'migrated_results'  => 0,
                            'failed_results'    => 0,
                            'next_offset'       => $current_processed_count,
                            'completed'         => true, // Signal completion for this mode
                            'success_ids'       => array(),
                            'failed_ids'        => array(),
                        )
                    );
                }

                // In failed-only mode, we are only processing records in $failed_ids
                $placeholders = implode( ',', array_fill( 0, count( $failed_ids ), '%d' ) );
                $query       = "SELECT r.* FROM {$mlw_results_table} r WHERE r.result_id IN ($placeholders)";
                $params      = array_merge( $params, $failed_ids );
            } else {
                // Normal mode: avoid repeatedly retrying known failed IDs (if they are still pending)
                if ( ! empty( $failed_ids ) ) {
                    $placeholders = implode( ',', array_fill( 0, count( $failed_ids ), '%d' ) );
                    $query       .= " AND r.result_id NOT IN ($placeholders)";
                    $params       = array_merge( $params, $failed_ids );
                }
            }

            // NOTE: Do NOT use OFFSET here. Always take the next BATCH_SIZE pending rows.
            $query  .= " ORDER BY r.result_id ASC LIMIT %d";
            $params[] = $batch_size;

            // Build prepared SQL safely
            if ( ! empty( $params ) ) {
                $prepare_args = array_merge( array( $query ), $params );
                $prepared_sql = call_user_func_array( array( $this->wpdb, 'prepare' ), $prepare_args );
            } else {
                $prepared_sql = $query;
            }

            $results = $this->wpdb->get_results( $prepared_sql );

            if ( empty( $results ) ) {
                // Nothing left to process in this batch (either normal or failed-only mode)
                $is_completed = $this->qsm_check_migration_status();
                
                wp_send_json_success(
                    array(
                        'message'           => __( 'No more results to process in this batch.', 'quiz-master-next' ),
                        'results_processed' => 0,
                        'inserted_count'    => 0,
                        'completed'         => (bool) $is_completed,
                        'migrated_results'  => 0,
                        'failed_results'    => 0,
                        'next_offset'       => $current_processed_count, // Offset remains the same
                    )
                );
            }

            // Track IDs processed in this batch
            $batch_success_ids = array();
            $batch_failed_ids  = array();

            // --------------------------------------------------
            // 2. Loop through results and migrate each one
            // --------------------------------------------------
            foreach ( $results as $row ) {
                $row_stats = $this->qsm_do_process_result_row( $row );

                if ( isset( $row_stats['results_processed'] ) ) {
                    $results_processed += (int) $row_stats['results_processed'];
                }
                if ( isset( $row_stats['inserted_count'] ) ) {
                    $inserted_count += (int) $row_stats['inserted_count'];
                }
                if ( ! empty( $row_stats['success_ids'] ) && is_array( $row_stats['success_ids'] ) ) {
                    $batch_success_ids = array_merge( $batch_success_ids, $row_stats['success_ids'] );
                }
                if ( ! empty( $row_stats['failed_ids'] ) && is_array( $row_stats['failed_ids'] ) ) {
                    $batch_failed_ids = array_merge( $batch_failed_ids, $row_stats['failed_ids'] );
                }
            }

            $stored_failed_ids  = get_option( 'qsm_migration_results_failed_ids', array() );

            // 3. Update the stored failed IDs list (remove success, add new fails)
            if ( ! empty( $batch_success_ids ) ) {
                $stored_failed_ids = array_diff(
                    array_map( 'intval', (array) $stored_failed_ids ),
                    array_map( 'intval', (array) $batch_success_ids )
                );
            }

            // Newly failed IDs are appended to the failed list
            if ( ! empty( $batch_failed_ids ) ) {
                $stored_failed_ids  = array_unique(
                    array_map(
                        'intval',
                        array_merge( (array) $stored_failed_ids, $batch_failed_ids )
                    )
                );
            }

            update_option( 'qsm_migration_results_failed_ids', $stored_failed_ids );

            // 4. Calculate status for UI
            $total_results = (int) $this->wpdb->get_var(
                "SELECT COUNT(*) FROM {$mlw_results_table}"
            );

            // Count how many results have been *logged* (migrated or failed)
            $logged_records = (int) $this->wpdb->get_var(
                "SELECT COUNT(DISTINCT r.result_id)
                FROM {$mlw_results_table} r
                INNER JOIN {$results_meta_table_name} m
                    ON m.result_id = r.result_id
                    AND m.meta_key = 'result_meta'"
            );
            
            $next_offset = $logged_records;

            $failed_total = ! empty( $stored_failed_ids ) ? count( (array) $stored_failed_ids ) : 0;

            $total_success_count = max( 0, $logged_records - $failed_total );
            $total_failed_count  = $failed_total;
            
            $completed = $this->qsm_check_migration_status();

            wp_send_json_success(
                array(
                    'message'                 => __( 'Batch processed.', 'quiz-master-next' ),
                    'results_processed'       => (int) $results_processed,
                    'migrated_results'        => (int) $total_success_count,
                    'failed_results'          => (int) $total_failed_count,
                    'next_offset'             => $next_offset, // This is the total number of logged records
                    'completed'               => (bool) $completed,
                    'success_ids'             => $batch_success_ids,
                    'failed_ids'              => $batch_failed_ids,
                )
            );
        } catch ( Exception $e ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Error during migration batch: ', 'quiz-master-next' ) . $e->getMessage(),
                )
            );
        }
    }

    public function qsm_check_migration_status(){
        $results_table_name      = $this->wpdb->prefix . 'mlw_results';
        $results_meta_table_name = $this->wpdb->prefix . 'qsm_results_meta';
        
        $total_results = (int) $this->wpdb->get_var( "SELECT COUNT(*) FROM {$results_table_name}" );

        // Count how many results have been *logged* (migrated or failed)
        $logged_records = (int) $this->wpdb->get_var(
            "SELECT COUNT(DISTINCT r.result_id)
            FROM {$results_table_name} r
            INNER JOIN {$results_meta_table_name} m
                ON m.result_id = r.result_id
                AND m.meta_key = 'result_meta'"
        );
        
        $stored_failed_ids = get_option( 'qsm_migration_results_failed_ids', array() );

        // Migration is completed if ALL original results have a 'result_meta' entry AND there are no failed IDs left.
        $completed = ( $logged_records === $total_results && empty( $stored_failed_ids ) );
        
        if ( $completed ) {
            update_option( 'qsm_migration_results_processed', true );
        }
        return $completed;
    }

    
    function qsm_do_process_result_row( $row ) {
        global $mlwQuizMasterNext;

        $stats = array(
            'success_ids'       => array(),
            'failed_ids'        => array(),
            'results_processed' => 0,
            'inserted_count'    => 0
        );

        if ( empty( $row ) || ! is_object( $row ) ) {
            return $stats;
        }

        $stats['results_processed']++;

        $result_id = isset( $row->result_id ) ? intval( $row->result_id ) : 0;

        if ( ! $result_id ) {
            $stats['failed_ids'][] = 0;
            return $stats;
        }

        $answers_table           = $this->wpdb->prefix . 'qsm_results_questions';
        $results_meta_table_name = $this->wpdb->prefix . 'qsm_results_meta';

        // ----------------------------------------------
        // Parse quiz_results and insert per-question answers
        // ----------------------------------------------
        $unserializedResults = maybe_unserialize( $row->quiz_results );

        // *************** TRANSACTION START ***************
        $this->wpdb->query( 'START TRANSACTION' );
        $transaction_failed = false;

        if ( ! is_array( $unserializedResults ) || ! isset( $unserializedResults[1] ) || ! is_array( $unserializedResults[1] ) ) {
            // No questions found; treat as migrated without inserts
            // Insert result_meta to mark as processed.
            // Continue below to insert result_meta to log processing.
        } else {

            $results_meta_table_data     = array();
            $results_meta_table_ans_label = '';
            
            foreach ( $unserializedResults as $result_meta_key => $result_meta_value ) {
                if ( 1 == $result_meta_key ) {
                    // results question loop (answers table) – collect all rows then bulk insert
                    $per_result_failed = 0;
                    $answer_rows       = array();

                    foreach ( $result_meta_value as $question_key => $question_value ) {
                        if ( ! is_array( $question_value ) || ! isset( $question_value['id'] ) ) {
                            continue;
                        }

                        // incorrect = 0, correct = 1, unanswered = 2
                        $correcIncorrectUnanswered = 0;

                        if ( 'correct' == $question_value['correct'] || ( isset( $question_value[0]['correct'] ) && 'correct' == $question_value[0]['correct'] ) ) {
                            $correcIncorrectUnanswered = 1;
                        } else {

                            if ( empty( $question_value['user_answer'] ) ) {

                                if ( '13' == $question_value['question_type'] && 'incorrect' == $question_value['correct'] && ( 0 == $question_value[1] || ! empty( $question_value[1] ) ) ) {
                                    $correcIncorrectUnanswered = 0;
                                } else {
                                    if ( '13' != $question_value['question_type'] && 'incorrect' == $question_value['correct'] ) {
                                        if ( '7' == $question_value['question_type'] && ! empty( $question_value[1] ) && $question_value[1] != $question_value[2] ) {
                                            $correcIncorrectUnanswered = 0;
                                        } else {
                                            $correcIncorrectUnanswered = 2;
                                        }
                                    } else {
                                        if ( empty( $question_value[1] ) ) {
                                            $correcIncorrectUnanswered = 2;
                                        }
                                    }
                                }
                            } elseif ( 'incorrect' == $question_value['correct'] ) {
                                $ans_loop    = 0;
                                $is_unanswer = 0;
                                if ( in_array( $question_value['question_type'], array( '14', '12', '3', '5' ), true ) ) {
                                    foreach ( $question_value['user_answer'] as $ans_key => $ans_value ) {
                                        if ( '' == $ans_value ) {
                                            $is_unanswer++;
                                        }
                                        $ans_loop++;
                                    }
                                }
                                if ( 0 != $is_unanswer && $ans_loop == $is_unanswer ) {
                                    $correcIncorrectUnanswered = 2;
                                } else {
                                    $correcIncorrectUnanswered = 0;
                                }

                                if ( isset( $question_value['question_type'] ) && 4 != $question_value['question_type'] && isset( $question_value[1] ) && isset( $question_value[2] ) && $question_value[1] == $question_value[2] ) {
                                    // Advanced question types conditions here
                                    if ( ( '17' == $question_value['question_type'] || '16' == $question_value['question_type'] ) && empty( $question_value['correct_answer'] ) ) {
                                        if ( '16' == $question_value['question_type'] ) {
                                            if ( empty( $question_value['user_answer'] ) ) {
                                                $correcIncorrectUnanswered = 2;
                                            }
                                        }
                                        if ( '17' == $question_value['question_type'] ) {
                                            if ( empty( $question_value['user_answer'] ) ) {
                                                $correcIncorrectUnanswered = 2;
                                            }
                                        }
                                    } else {
                                        $correcIncorrectUnanswered = 1;
                                    }

                                }
                            }
                        }

                        // Normalize user_answer and correct_answer fields for storage
                        $user_answer_to_store    = isset( $question_value['user_answer'] ) ? $question_value['user_answer'] : array();
                        $correct_answer_to_store = isset( $question_value['correct_answer'] ) ? $question_value['correct_answer'] : array();

                        // Map fields (use fallbacks for numeric keys)
                        $question_description = isset( $question_value[0] ) ? $question_value[0] : '';

                        $user_answer_comma = isset( $question_value[1] ) ? $question_value[1] : '';

                        $correct_answer_comma = isset( $question_value[2] ) ? $question_value[2] : '';

                        $question_comment = isset( $question_value[3] ) ? $question_value[3] : '';

                        $question_title = isset( $question_value['question_title'] ) ? $question_value['question_title'] : ( isset( $question_value['question'] ) ? (string) $question_value['question'] : '' );

                        $question_type = isset( $question_value['question_type'] ) ? $question_value['question_type'] : '';

                        // Determine answer_type using heuristic
                        $answerEditor       = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_value['id'], 'answerEditor' );
                        $answer_type = '' != $answerEditor ? $answerEditor : 'text';
                        
                        $points   = isset( $question_value['points'] ) ? $question_value['points'] : 0;
                        $correct  = intval( $correcIncorrectUnanswered );

                        $category = isset( $question_value['category'] ) ? $question_value['category'] : '';

                        $multicategories = isset( $question_value['multicategories'] ) ? $question_value['multicategories'] : array();

                        // Ensure values passed to $wpdb->prepare are scalars; serialize any arrays.
                        if ( is_array( $category ) ) {
                            $category = maybe_serialize( $category );
                        }

                        if ( is_array( $multicategories ) ) {
                            $multicategories = maybe_serialize( $multicategories );
                        }

                        // other_settings from question fields (user_compare_text, case_sensitive, answer_limit_keys)
                        $other_settings_arr = array();
                        if ( isset( $question_value['user_compare_text'] ) ) {
                            $other_settings_arr['user_compare_text'] = $question_value['user_compare_text'];
                        }
                        if ( isset( $question_value['case_sensitive'] ) ) {
                            $other_settings_arr['case_sensitive'] = $question_value['case_sensitive'];
                        }
                        if ( isset( $question_value['answer_limit_keys'] ) ) {
                            $other_settings_arr['answer_limit_keys'] = $question_value['answer_limit_keys'];
                        }
                        $other_settings_serialized = maybe_serialize( $other_settings_arr );

                        // Prepare values for insert (order must match placeholders below)
                        $quiz_id = isset( $row->quiz_id ) ? intval( $row->quiz_id ) : 0;

                        $answer_rows[] = array(
                            intval( $result_id ),                       // result_id %d
                            intval( $quiz_id ),                         // quiz_id %d
                            intval( $question_value['id'] ),            // question_id %d
                            $question_title,                            // question_title %s
                            $question_description,                      // question_description %s (LONGTEXT)
                            $question_comment,                          // question_comment %s
                            $question_type,                             // question_type %s
                            $answer_type,                               // answer_type %s
                            maybe_serialize( $correct_answer_to_store ),// correct_answer %s
                            maybe_serialize( $user_answer_to_store ),   // user_answer %s
                            $user_answer_comma,                         // user_answer_comma %s
                            $correct_answer_comma,                      // correct_answer_comma %s
                            floatval( $points ),                        // points %f
                            intval( $correct ),                         // correct %d
                            $category,                                  // category %s
                            $multicategories,                           // multicategories %s
                            $other_settings_serialized                  // other_settings %s
                        );
                    }

                    if ( ! empty( $answer_rows ) ) {
                        $placeholders = array();
                        $params       = array();

                        foreach ( $answer_rows as $values ) {
                            // match the order from $answer_rows above
                            $placeholders[] = '( %d, %d, %d, %s, %s, %s, %s, %s, %s, %s, %s, %s, %f, %d, %s, %s, %s )';
                            $params         = array_merge( $params, $values );
                        }

                        $sql = "INSERT INTO {$answers_table}
                            ( result_id, quiz_id, question_id, question_title, question_description, question_comment,
                            question_type, answer_type, correct_answer, user_answer, user_answer_comma, correct_answer_comma,
                            points, correct, category, multicategories, other_settings )
                            VALUES " . implode( ', ', $placeholders );

                        // prepare & execute
                        $prepared = $this->wpdb->prepare( $sql, $params );
                        $inserted = $this->wpdb->query( $prepared );

                        if ( $inserted === false || $inserted === 0 ) {
                            // Question insert failed: rollback for this result
                            $transaction_failed = true;
                            $this->wpdb->query( 'ROLLBACK' );
                            break; // Exit the loop over result_meta_key (questions)
                        } else {
                            $stats['inserted_count']++;
                        }
                    }

                } else {
                    // results meta loop (non-question data)
                    if ( 0 == $result_meta_key ) {
                        $result_meta_key = 'total_seconds';
                    } elseif ( 2 == $result_meta_key ) {
                        $result_meta_key = 'quiz_comments';
                    }
                    if ( 'answer_label_points' == $result_meta_key ) {
                        if ( '' != $result_meta_value ) {
                            $results_meta_table_ans_label = $result_meta_value; // serialized value
                        }
                    } else {
                        $results_meta_table_data[ $result_meta_key ] = $result_meta_value;
                    }
                }
            }
        } // End question results processing

        if ( $transaction_failed ) {
             // Rollback was already called inside the loop
             $stats['failed_ids'][] = $result_id;
             return $stats;
        }

        // --- Insert result_meta to mark as processed ---

        $results_meta_table_data['total_questions'] = isset( $row->total ) ? $row->total : 0;
        $results_table_meta                       = array(
            'result_meta' => maybe_serialize( $results_meta_table_data ),
        );
        if ( ! empty( $results_meta_table_ans_label ) ) {
            $results_table_meta['answer_label_points'] = $results_meta_table_ans_label;  // already serialized
        }

        // Bulk insert all meta rows for this result in a single query
        if ( ! empty( $results_table_meta ) ) {
            $meta_rows          = array();
            $meta_placeholders  = array();
            $meta_params        = array();

            foreach ( $results_table_meta as $meta_key => $meta_value ) {
                $meta_rows[]         = array( $result_id, $meta_key, $meta_value );
                $meta_placeholders[] = '( %d, %s, %s )';
            }

            foreach ( $meta_rows as $row_values ) {
                $meta_params = array_merge( $meta_params, $row_values );
            }

            $meta_sql = "INSERT INTO {$results_meta_table_name}
                (result_id, meta_key, meta_value)
                VALUES " . implode( ', ', $meta_placeholders );

            $prepared_meta = $this->wpdb->prepare( $meta_sql, $meta_params );
            $meta_inserted = $this->wpdb->query( $prepared_meta );
            
            if ( $meta_inserted === false || $meta_inserted === 0 ) {
                $transaction_failed = true;
                $this->wpdb->query( 'ROLLBACK' );
            }
        }
        
        // *************** TRANSACTION END ***************
        if ( $transaction_failed ) {
            $stats['failed_ids'][] = $result_id;
        } else {
            $this->wpdb->query( 'COMMIT' );
            $stats['success_ids'][] = $result_id;
        }

        return $stats;
    }

}

// Initialize migration class
new QSM_Database_Migration();