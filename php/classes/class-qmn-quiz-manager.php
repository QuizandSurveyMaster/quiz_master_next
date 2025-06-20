<?php
/**
 * File for the QMNQuizManager class
 *
 * @package QSM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class generates the contents of the quiz shortcode
 *
 * @since 4.0.0
 */
class QMNQuizManager {


	/**
	 * $common_css
	 *
	 * @var   string
	 * @since 7.3.5
	 */
	public $common_css = QSM_PLUGIN_CSS_URL . '/common.css';
	/*
	* Default MathJax inline scripts.
	*/
	public static $default_MathJax_script = "MathJax = {
		tex: {
		  inlineMath: [['$','$'],['\\\\(','\\\\)']],
		  processEscapes: true
		},
		options: {
		  ignoreHtmlClass: 'tex2jax_ignore|editor-rich-text'
		}
	  };";
	public $mathjax_url                   = QSM_PLUGIN_JS_URL . '/mathjax/tex-mml-chtml.js';
	public $mathjax_version               = '3.2.0';

	/**
	 * Holds failed submission meta_key name
	 *
	 * @var object
	 * @since 9.0.2
	 */
	public $meta_key = '_qmn_log_result_insert_data';

	public $qsm_background_email;
	/**
	 * Main Construct Function
	 *
	 * Call functions within class
	 *
	 * @since  4.0.0
	 * @uses   QMNQuizManager::add_hooks() Adds actions to hooks and filters
	 * @return void
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Add Hooks
	 *
	 * Adds functions to relavent hooks and filters
	 *
	 * @since  4.0.0
	 * @return void
	 */
	public function add_hooks() {
		add_shortcode( 'mlw_quizmaster', array( $this, 'display_shortcode' ) );
		add_shortcode( 'qsm', array( $this, 'display_shortcode' ) );
		add_shortcode( 'qsm_result', array( $this, 'shortcode_display_result' ) );
		add_action( 'wp_ajax_qmn_process_quiz', array( $this, 'ajax_submit_results' ) );
		add_action( 'wp_ajax_nopriv_qmn_process_quiz', array( $this, 'ajax_submit_results' ) );
		add_action( 'wp_ajax_qsm_get_quiz_to_reload', array( $this, 'qsm_get_quiz_to_reload' ) );
		add_action( 'wp_ajax_nopriv_qsm_get_quiz_to_reload', array( $this, 'qsm_get_quiz_to_reload' ) );
		add_action( 'wp_ajax_nopriv_qsm_create_quiz_nonce', array( $this, 'qsm_create_quiz_nonce' ) );
		add_action( 'wp_ajax_qsm_create_quiz_nonce', array( $this, 'qsm_create_quiz_nonce' ) );

		// Exposrt audit trail
		add_action( 'wp_ajax_qsm_export_data', array( $this, 'qsm_export_data' ) );

		// Clear audit trail
		add_action( 'wp_ajax_qsm_clear_audit_data', array( $this, 'qsm_clear_audit_data' ) );

		// Upload file of file upload question type
		add_action( 'wp_ajax_qsm_upload_image_fd_question', array( $this, 'qsm_upload_image_fd_question' ) );
		add_action( 'wp_ajax_nopriv_qsm_upload_image_fd_question', array( $this, 'qsm_upload_image_fd_question' ) );

		// remove file of file upload question type
		add_action( 'wp_ajax_qsm_remove_file_fd_question', array( $this, 'qsm_remove_file_fd_question' ) );
		add_action( 'wp_ajax_nopriv_qsm_remove_file_fd_question', array( $this, 'qsm_remove_file_fd_question' ) );

		add_action( 'init', array( $this, 'qsm_process_background_email' ) );
		add_action('wp_ajax_nopriv_qsm_ajax_login', array( $this, 'qsm_ajax_login' ) );

		// Failed submission resubmit or trash
		add_action( 'wp_ajax_qsm_action_failed_submission_table', array( $this, 'process_action_failed_submission_table' ) );

		// Run failed ALTER TABLE query via ajax on notification button click
		add_action( 'wp_ajax_qsm_check_fix_db', array( $this, 'has_alter_table_issue_solved' ) );
	}

	/**
	 * Check if alter table issue has been solved by trying failed alter table query
	 *
	 * @since 9.0.2
	 *
	 * @return void
	 */
	public function has_alter_table_issue_solved() {
		if ( empty( $_POST['qmnnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qmnnonce'] ) ), 'qmn_check_db' ) || ! function_exists( 'is_admin' ) || ! is_admin() ) {
			wp_send_json_error(
				array(
					'status'  => 'error',
					'message' => __( 'Unauthorized!', 'quiz-master-next' ),
				)
			);
		} else {
			global $mlwQuizMasterNext, $wpdb;
			// Get failed alter table query list.
			$failed_queries = $mlwQuizMasterNext->get_failed_alter_table_queries();
			$query_index = ! empty( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : 0;
			if ( ! empty( $failed_queries ) && is_array( $failed_queries ) && isset( $failed_queries[ $query_index ] ) ) {
				$result = $mlwQuizMasterNext->wpdb_alter_table_query( $failed_queries[ $query_index ] );
				// exit loop if query failed to execute
				if ( false === $result ) {
					wp_send_json_error(
						array(
							'status'  => 'error',
							'message' => $wpdb->last_error,
						)
					);
				}else {
					if ( array_key_exists($query_index, $failed_queries) ) {
						unset($failed_queries[ $query_index ]);
					}
					update_option( 'qmn_failed_alter_table_queries', $failed_queries );
					wp_send_json_success(
						array(
							'status'  => 'success',
							'message' => __( 'Success! Database query executed successfully.', 'quiz-master-next' ),
						)
					);
				}
			}
		}
	}

	/**
	 * Process Bulk action for failed submission table
	 *
	 * @since 9.0.2
	 * @return void
	 */
    public function process_action_failed_submission_table() {

        if ( empty( $_POST['post_id'] ) || empty( $_POST['quiz_action'] ) || ! function_exists( 'is_admin' ) || ! is_admin() || empty( $_POST['qmnnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['qmnnonce'] ) ), 'qmn_failed_submission' ) ) {
            wp_send_json_error(
                array(
                    'status'  => 'error',
                    'message' => __( 'Missing or incorrect input', 'quiz-master-next' ),
                )
            );
        }

        $post_ids = is_array( $_POST['post_id'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['post_id'] ) ) : array( sanitize_key( wp_unslash( $_POST['post_id'] ) ) );
        $action   = wp_unslash( sanitize_key( $_POST['quiz_action'] ) );
        if ( ! empty( $post_ids ) ) {
            foreach ( $post_ids as $postID ) {

                $postID = intval( $postID );

                // Continue if postID not valid
                if ( 0 >= $postID ) {
                    continue;
                }

                $data = get_post_meta( $postID, $this->meta_key, true );

                if ( empty( $data ) ) {
                    wp_send_json_error(
                        array(
                            'status'  => 'error',
                            'message' => __( 'Details not found', 'quiz-master-next' ),
                            'data'    => $data,
                        )
                    );
                }

                $data = maybe_unserialize( $data );

                // Retrieve action.
                if ( 'retrieve' === $action ) {
                    $res = $this->add_quiz_results( $data, 'resubmit' );
                    if ( false !== $res ) {
                        $data['processed'] = 1;
                        // Mark submission processed.
                        update_post_meta( $postID, $this->meta_key, maybe_serialize( $data ) );

                        // return success message.
                        wp_send_json_success(
                            array(
                                'res'     => $res,
                                'status'  => 'success',
                                'message' => __( 'Quiz resubmitted successfully.', 'quiz-master-next' ),
                            )
                        );
                    } else {
                        // return error details.
                        global $wpdb;
                        wp_send_json_error(
                            array(
                                'status'  => 'error',
                                'message' => __( 'The system generated following error while resubmitting the result:', 'quiz-master-next' ) . $wpdb->last_error,
                            )
                        );
                    }
                } elseif ( 'trash' === $action ) {

                    // Change Error log post status to trash. Error log contain failed submission data as a post meta
                    wp_update_post(
                        array(
                            'ID'          => $postID,
                            'post_status' => 'trash',
                        )
                    );

                    // return success message.
                    wp_send_json_success(
                        array(
                            'status'  => 'success',
                            'message' => __( 'Quiz deleted successfully.', 'quiz-master-next' ),
                        )
                    );
                }
            }
        }

        wp_send_json_error(
            array(
                'status'  => 'error',
                'message' => __( 'Missing input', 'quiz-master-next' ),
            )
        );
    }

	/**
	 * @version 8.2.0
	 * ajax login function
	 */
	public function qsm_ajax_login() {
		$username = ! empty( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
		$password = ! empty( $_POST['password'] ) ? sanitize_text_field( wp_unslash( $_POST['password'] ) ) : '';

		$user = get_user_by('login', $username);

		if ( ! $user ) {
			$user = get_user_by('email', $username);
			if ( ! $user ) {
				wp_send_json_error( array( 'message' => __( 'User not found! Please try again.', 'quiz-master-next' ) ) );
			}
		}

		$user_id = $user->ID;

		// Check the password
		if ( ! wp_check_password( $password, $user->user_pass, $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Incorrect username or password! Please try again.', 'quiz-master-next' ) ) );
		}else {
			wp_send_json_success();
		}
	}

	/**
	 * @version 6.3.7
	 * Upload file to server
	 */
	public function qsm_upload_image_fd_question() {
		global $mlwQuizMasterNext;
		$question_id       = isset( $_POST['question_id'] ) ? sanitize_text_field( wp_unslash( $_POST['question_id'] ) ) : 0;
		$file_upload_type  = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'file_upload_type' );
		$file_upload_limit = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'file_upload_limit' );
		$mimes             = array();
		if ( $file_upload_type ) {
			$file_type_exp = explode( ',', $file_upload_type );
			foreach ( $file_type_exp as $value ) {
				$value = trim( $value );
				if ( 'image' === $value ) {
					$mimes[] = 'image/jpeg';
					$mimes[] = 'image/png';
					$mimes[] = 'image/x-icon';
					$mimes[] = 'image/gif';
					$mimes[] = 'image/webp';
				} elseif ( 'doc' === $value ) {
					$mimes[] = 'application/msword';
					$mimes[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
				} elseif ( 'excel' === $value ) {
					$mimes[] = 'application/excel, application/vnd.ms-excel, application/x-excel, application/x-msexcel';
					$mimes[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
					$mimes[] = 'text/csv';
				} elseif ( empty( $value ) ) {
					// don't add blank mime type
				} else {
					$mimes[] = $value;
				}
			}
			$mimes = apply_filters( 'qsm_file_upload_mime_type', $mimes );
		}

		$json = array();
		if ( ! isset( $_FILES['file'] ) ) {
			$json['type']    = 'error';
			$json['message'] = __( 'File is not uploaded!', 'quiz-master-next' );
			echo wp_json_encode( $json );
			exit;
		}
		$uploaded_file = $_FILES['file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$file_name     = isset( $_FILES['file']['name'] ) ? sanitize_file_name( wp_unslash( $uploaded_file['name'] ) ) : '';
		$validate_file = wp_check_filetype( $file_name );
		if ( isset( $validate_file['type'] ) && in_array( $validate_file['type'], $mimes, true ) ) {
			if ( isset( $_FILES['file']['size'] ) && $file_upload_limit > 0 && $_FILES['file']['size'] >= $file_upload_limit * 1024 * 1024 ) {
				$json['type']    = 'error';
				$json['message'] = __( 'File is too large. File must be less than ', 'quiz-master-next' ) . $file_upload_limit . ' MB';
				echo wp_json_encode( $json );
				exit;
			}

			$uploaded_file['name'] = 'qsmfileupload_' . uniqid() . '_' . str_replace( '-', '_', $file_name );
			$upload_overrides      = array(
				'test_form' => false,
			);
			$movefile              = wp_handle_upload( $uploaded_file, $upload_overrides );
			if ( $movefile && ! isset( $movefile['error'] ) ) {
				// Prepare an array of post data for the attachment.
				$attachment = array(
					'guid'           => $movefile['url'],
					'post_mime_type' => $movefile['type'],
					'post_title'     => preg_replace( '/\\.[^.]+$/', '', basename( $uploaded_file['name'] ) ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				);
				// Insert the attachment.
				$attach_id = wp_insert_attachment( $attachment, $movefile['file'], 0 );
				if ( $attach_id ) {
					include_once ABSPATH . 'wp-admin/includes/image.php';
					$attach_data = wp_generate_attachment_metadata( $attach_id, $movefile['file'] );
					wp_update_attachment_metadata( $attach_id, $attach_data );
					$json['type']      = 'success';
					$json['media_id']  = $attach_id;
					$json['wp_nonoce'] = wp_create_nonce( 'delete_atteched_file_' . $attach_id );
					$json['message']   = __( 'File uploaded successfully', 'quiz-master-next' );
					$json['file_url']  = $movefile['url'];
					$json['file_path'] = basename( $movefile['url'] );
					echo wp_json_encode( $json );
				} else {
					$json['type']    = 'error';
					$json['message'] = __( 'Upload failed!', 'quiz-master-next' );
					echo wp_json_encode( $json );
				}
			} else {
				$json['type']    = 'error';
				$json['message'] = $movefile['error'];
				echo wp_json_encode( $json );
			}
		} else {
			if ( ! empty ($file_upload_type) ) {
				$filestype = explode(',', $file_upload_type);
				foreach ( $filestype as $file ) {
					if ( strpos($file, '/') !== false ) {
						$filetypes = explode('/', $file);
						if ( ! empty($filetypes[0]) && 'application' == $filetypes[0] ) {
							$filetypes_allowed[] = 'pdf';
						} else {
						$filetypes_allowed[] = $filetypes[0];
						}
					}else {
						$filetypes_allowed[] = $file;
					}
				}
				if ( count($filetypes_allowed) > 1 ) {
					$files_allowed = implode(',', $filetypes_allowed);
				} else {
					$files_allowed = $filetypes_allowed[0]; // Just take the single element
				}
				$json['type']    = 'error';
				$json['message'] = __('File Upload Unsuccessful! (Please upload ', 'quiz-master-next') . $files_allowed . __(' file type)', 'quiz-master-next');
				echo wp_json_encode( $json );
			} else {
				$json['type']    = 'error';
				$json['message'] = __( 'File Upload Unsuccessful! (Please select file type)', 'quiz-master-next' );
				echo wp_json_encode( $json );
			}
		}
		exit;
	}

	/**
	 * @since 6.3.7
	 * Remove the uploaded image
	 */
	public function qsm_remove_file_fd_question() {
		$json          = array();
		$attachment_id = isset( $_POST['media_id'] ) ? intval( $_POST['media_id'] ) : '';
		if ( ! empty( $attachment_id ) && isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'delete_atteched_file_' . $attachment_id ) ) {
			$delete = wp_delete_attachment( $attachment_id, true );
			if ( $delete ) {
				$json['type']    = 'success';
				$json['message'] = __( 'File removed successfully', 'quiz-master-next' );
				echo wp_json_encode( $json );
				exit;
			}
		}

		$json['type']    = 'error';
		$json['message'] = __( 'File not removed', 'quiz-master-next' );
		echo wp_json_encode( $json );
		exit;
	}


	/**
	 * Export CSV file
	 */

	public function qsm_export_data() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'qsm_tools_' . get_current_user_id() ) ) {
			wp_send_json_error();
		}

		global $wpdb;
		$export_tool_data = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_qm_audit_trail" );
		// file creation
		$qsm_export_filename = 'export_' . gmdate( 'd-m-y' ) . '.csv';

		// Clean object
		ob_end_clean();

		// Open file
		$qsm_open_file = fopen( 'php://output', 'w' );
		fputcsv( $qsm_open_file, array( 'Trail ID', 'User', 'Action', 'Quiz Name', 'Form Data', 'Time' ) );

		// loop for insert data into CSV file
		foreach ( $export_tool_data as $export_data ) {
			$qsm_export_array = array(
				'trail_id'    => $export_data->trail_id,
				'action_user' => $export_data->action_user,
				'action'      => $export_data->action,
				'quiz_name'   => $export_data->quiz_name,
				'form_data'   => $export_data->form_data,
				'time'        => $export_data->time,
			);
			fputcsv( $qsm_open_file, $qsm_export_array );
		}

		// download csv file
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $qsm_export_filename );
		header( 'Content-Type: text/csv;' );
		exit;
	}

	public function qsm_clear_audit_data() {
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'qsm_tools_' . get_current_user_id() ) ) {
			wp_send_json_error();
		}

		global $wpdb;
		$table_audit = $wpdb->prefix . 'mlw_qm_audit_trail';
		$wpdb->query( "TRUNCATE TABLE $table_audit" );

		wp_send_json_success();
	}

	/**
	 * Generates Content For Quiz Shortcode
	 *
	 * Generates the content for the [mlw_quizmaster] shortcode
	 *
	 * @since  4.0.0
	 * @param  array $atts The attributes passed from the shortcode.
	 * @uses   QMNQuizManager:load_questions() Loads questions
	 * @uses   QMNQuizManager:create_answer_array() Prepares answers
	 * @uses   QMNQuizManager:display_quiz() Generates and prepares quiz page
	 * @uses   QMNQuizManager:display_results() Generates and prepares results page
	 * @return string The content for the shortcode
	 */
	public function display_shortcode( $atts ) {
		global $wpdb, $mlwQuizMasterNext;
		$shortcode_args  = shortcode_atts(
			array(
				'quiz'            => 0,
				'question_amount' => 0,
			),
			$atts
		);
		// Quiz ID.
		$quiz            = intval( $shortcode_args['quiz'] );
		$question_amount = intval( $shortcode_args['question_amount'] );

		// Check, if quiz is setup properly.
		$has_proper_quiz = $mlwQuizMasterNext->pluginHelper->has_proper_quiz( $quiz );
		if ( false === $has_proper_quiz['res'] ) {
			return $has_proper_quiz['message'];
		}

		$qmn_quiz_options = $has_proper_quiz['qmn_quiz_options'];
		$return_display = '';

		ob_start();
		if ( isset( $_GET['result_id'] ) && '' !== $_GET['result_id'] ) {
			$result_unique_id = sanitize_text_field( wp_unslash( $_GET['result_id'] ) );
			$result           = $wpdb->get_row( $wpdb->prepare( "SELECT `result_id`, `quiz_id` FROM {$wpdb->prefix}mlw_results WHERE unique_id = %s", $result_unique_id ), ARRAY_A );
			if ( ! empty( $result ) && isset( $result['result_id'] ) ) {

				wp_enqueue_style( 'qmn_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );
				wp_style_add_data( 'qmn_quiz_common_style', 'rtl', 'replace' );
				wp_enqueue_style( 'dashicons' );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-tooltip' );
				wp_enqueue_script( 'qsm_quiz', QSM_PLUGIN_JS_URL . '/qsm-quiz.js', array( 'wp-util', 'underscore', 'jquery', 'jquery-ui-tooltip' ), $mlwQuizMasterNext->version, false );
				wp_enqueue_script( 'qsm_common', QSM_PLUGIN_JS_URL . '/qsm-common.js', array(), $mlwQuizMasterNext->version, true );
				$disable_mathjax = isset( $qmn_quiz_options->disable_mathjax ) ? $qmn_quiz_options->disable_mathjax : '';
				if ( 1 != $disable_mathjax ) {
					wp_enqueue_script( 'math_jax', $this->mathjax_url, false, $this->mathjax_version, true );
					wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
				}

				$result_id      = $result['result_id'];
				$return_display = do_shortcode( '[qsm_result id="' . $result_id . '"]' );
				$return_display = str_replace( '%FB_RESULT_ID%', $result_unique_id, $return_display );
			} else {
				$return_display = esc_html__( 'Result id is wrong!', 'quiz-master-next' );
			}
			$return_display .= ob_get_clean();
		} else {
			global $qmn_allowed_visit, $qmn_json_data, $mlw_qmn_quiz;

			// Loads Quiz Template.
			wp_enqueue_style( 'qmn_quiz_animation_style', QSM_PLUGIN_CSS_URL . '/animate.css', array(), $mlwQuizMasterNext->version );
			wp_enqueue_style( 'qmn_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );
			wp_style_add_data( 'qmn_quiz_common_style', 'rtl', 'replace' );
			wp_enqueue_style( 'dashicons' );
			// The quiz_stye is misspelled because it has always been misspelled and fixing it would break many sites :(.
			if ( 'default' == $qmn_quiz_options->theme_selected ) {
				$return_display .= '<style type="text/css">' . preg_replace( '#<script(.*?)>(.*?)</script>#is', '', htmlspecialchars_decode( $qmn_quiz_options->quiz_stye, ENT_QUOTES) ) . '</style>';
				wp_enqueue_style( 'qmn_quiz_style', QSM_PLUGIN_CSS_URL . '/qmn_quiz.css', array(), $mlwQuizMasterNext->version );
				wp_style_add_data( 'qmn_quiz_style', 'rtl', 'replace' );
			} else {
				$registered_template = $mlwQuizMasterNext->pluginHelper->get_quiz_templates( $qmn_quiz_options->theme_selected );
				// Check direct file first, then check templates folder in plugin, then check templates file in theme.
				// If all fails, then load custom styling instead.
				if ( $registered_template && file_exists( ABSPATH . $registered_template['path'] ) ) {
					wp_enqueue_style( 'qmn_quiz_template', site_url( $registered_template['path'] ), array(), $mlwQuizMasterNext->version );
				} elseif ( $registered_template && file_exists( plugin_dir_path( __FILE__ ) . '../../templates/' . $registered_template['path'] ) ) {
					wp_enqueue_style( 'qmn_quiz_template', plugins_url( '../../templates/' . $registered_template['path'], __FILE__ ), array(), $mlwQuizMasterNext->version );
				} elseif ( $registered_template && file_exists( get_theme_file_path( '/templates/' . $registered_template['path'] ) ) ) {
					wp_enqueue_style( 'qmn_quiz_template', get_stylesheet_directory_uri() . '/templates/' . $registered_template['path'], array(), $mlwQuizMasterNext->version );
				}
				if ( ! empty( $qmn_quiz_options->quiz_stye ) ) {
					echo "<style type='text/css' id='qmn_quiz_template-css'>" . wp_kses_post( htmlspecialchars_decode( $qmn_quiz_options->quiz_stye ) ) . '</style>';
				}
			}
			$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme_path( $quiz );
			$folder_name      = QSM_THEME_PATH . $saved_quiz_theme . '/';
			if ( file_exists( $folder_name . 'functions.php' ) ) {
				include_once $folder_name . 'functions.php';
			}
			do_action( 'qsm_enqueue_script_style', $qmn_quiz_options );

			// Starts to prepare variable array for filters.
			$qmn_array_for_variables = array(
				'quiz_id'     => $qmn_quiz_options->quiz_id,
				'quiz_name'   => $qmn_quiz_options->quiz_name,
				'quiz_system' => $qmn_quiz_options->system,
				'user_ip'     => $this->get_user_ip(),
			);
			$return_display         .= '<script>
                            if (window.qmn_quiz_data === undefined) {
                                    window.qmn_quiz_data = new Object();
                            }
                    </script>';
			$qpages                  = array();
			$qpages_arr              = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array() );
			if ( ! empty( $qpages_arr ) ) {
				foreach ( $qpages_arr as $key => $qpage ) {
					unset( $qpage['questions'] );
					if ( isset( $qpage['id'] ) ) {
						$qpages[ $qpage['id'] ] = $qpage;
					}
				}
			}
			$correct_answer_text = sanitize_text_field( $qmn_quiz_options->quick_result_correct_answer_text );
			$correct_answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $correct_answer_text, "quiz_quick_result_correct_answer_text-{$qmn_array_for_variables['quiz_id']}" );
			$wrong_answer_text = sanitize_text_field( $qmn_quiz_options->quick_result_wrong_answer_text );
			$wrong_answer_text = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $wrong_answer_text, "quiz_quick_result_wrong_answer_text-{$qmn_array_for_variables['quiz_id']}" );
			$quiz_processing_message = isset( $qmn_quiz_options->quiz_processing_message ) ? $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->quiz_processing_message, "quiz_quiz_processing_message-{$qmn_array_for_variables['quiz_id']}" ) : '';
			$quiz_limit_choice = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->quiz_limit_choice, "quiz_quiz_limit_choice-{$qmn_array_for_variables['quiz_id']}" );
			$qmn_json_data = array(
				'quiz_id'                            => $qmn_array_for_variables['quiz_id'],
				'quiz_name'                          => $qmn_array_for_variables['quiz_name'],
				'disable_answer'                     => $qmn_quiz_options->disable_answer_onselect,
				'ajax_show_correct'                  => $qmn_quiz_options->ajax_show_correct,
				'progress_bar'                       => $qmn_quiz_options->progress_bar,
				'contact_info_location'              => $qmn_quiz_options->contact_info_location,
				'qpages'                             => $qpages,
				'skip_validation_time_expire'        => $qmn_quiz_options->skip_validation_time_expire,
				'timer_limit_val'                    => $qmn_quiz_options->timer_limit,
				'disable_scroll_next_previous_click' => $qmn_quiz_options->disable_scroll_next_previous_click,
				'disable_scroll_on_result'           => $qmn_quiz_options->disable_scroll_on_result,
				'disable_first_page'                 => $qmn_quiz_options->disable_first_page,
				'enable_result_after_timer_end'      => isset( $qmn_quiz_options->enable_result_after_timer_end ) ? $qmn_quiz_options->enable_result_after_timer_end : '',
				'enable_quick_result_mc'             => isset( $qmn_quiz_options->enable_quick_result_mc ) ? $qmn_quiz_options->enable_quick_result_mc : '',
				'end_quiz_if_wrong'                  => isset( $qmn_quiz_options->end_quiz_if_wrong ) ? $qmn_quiz_options->end_quiz_if_wrong : 0,
				'form_disable_autofill'              => isset( $qmn_quiz_options->form_disable_autofill ) ? $qmn_quiz_options->form_disable_autofill : '',
				'disable_mathjax'                    => isset( $qmn_quiz_options->disable_mathjax ) ? $qmn_quiz_options->disable_mathjax : '',
				'enable_quick_correct_answer_info'   => isset( $qmn_quiz_options->enable_quick_correct_answer_info ) ? $qmn_quiz_options->enable_quick_correct_answer_info : 0,
				'quick_result_correct_answer_text'   => $correct_answer_text,
				'quick_result_wrong_answer_text'     => $wrong_answer_text,
				'quiz_processing_message'            => $quiz_processing_message,
				'quiz_limit_choice'                  => $quiz_limit_choice,
				'not_allow_after_expired_time'       => $qmn_quiz_options->not_allow_after_expired_time,
				'scheduled_time_end'                 => strtotime( $qmn_quiz_options->scheduled_time_end ),
				'prevent_reload'                     => $qmn_quiz_options->prevent_reload,
			);

			$return_display = apply_filters( 'qmn_begin_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );
			$qmn_quiz_options = apply_filters( 'qsm_quiz_options_before', $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );

			// Checks if we should be showing quiz or results page.
			if ( $qmn_allowed_visit && ! isset( $_POST['complete_quiz'] ) && ! empty( $qmn_quiz_options->quiz_name ) ) {
				$return_display .= $this->display_quiz( $qmn_quiz_options, $qmn_array_for_variables, $question_amount, $shortcode_args );
			} elseif ( isset( $_POST['complete_quiz'], $_POST['qmn_quiz_id'] ) && 'confirmation' == sanitize_text_field( wp_unslash( $_POST['complete_quiz'] ) ) && sanitize_text_field( wp_unslash( $_POST['qmn_quiz_id'] ) ) == $qmn_array_for_variables['quiz_id'] ) {
				$return_display .= $this->display_results( $qmn_quiz_options, $qmn_array_for_variables );
			}

			$qmn_filtered_json = apply_filters( 'qmn_json_data', $qmn_json_data, $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );
			$qmn_settings_array = maybe_unserialize( $qmn_quiz_options->quiz_settings );
			$quiz_options = maybe_unserialize( $qmn_settings_array['quiz_options'] );
			$correct_answer_logic = ! empty( $quiz_options['correct_answer_logic'] ) ? $quiz_options['correct_answer_logic'] : '';
			$encryption['correct_answer_logic'] = $correct_answer_logic;
			$enc_questions = array();
			if ( ! empty( $qpages_arr ) ) {
				foreach ( $qpages_arr as $item ) {
					$enc_questions = array_merge($enc_questions, $item['questions']);
				}
			}
			$enc_questions = implode(',', $enc_questions);
			$question_array = $wpdb->get_results(
				"SELECT quiz_id, question_id, answer_array, question_answer_info, question_type_new, question_settings
				FROM {$wpdb->prefix}mlw_questions
				WHERE question_id IN ($enc_questions)", ARRAY_A);
			foreach ( $question_array as $key => $question ) {
				$encryption[ $question['question_id'] ]['question_type_new'] = $question['question_type_new'];
				$encryption[ $question['question_id'] ]['answer_array'] = maybe_unserialize( $question['answer_array'] );
				$encryption[ $question['question_id'] ]['settings'] = maybe_unserialize( $question['question_settings'] );
				$encryption[ $question['question_id'] ]['correct_info_text'] = isset( $question['question_answer_info'] ) ? html_entity_decode( $question['question_answer_info'] ) : '';
				$encryption[ $question['question_id'] ]['correct_info_text'] = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $encryption[ $question['question_id'] ]['correct_info_text'], "correctanswerinfo-{$question['question_id']}" );
			}
			if ( ( isset($qmn_json_data['end_quiz_if_wrong']) && 0 < $qmn_json_data['end_quiz_if_wrong'] ) || ( ! empty( $qmn_json_data['enable_quick_result_mc'] ) && 1 == $qmn_json_data['enable_quick_result_mc'] ) || ( ! empty( $qmn_json_data['enable_quick_correct_answer_info'] ) && 0 != $qmn_json_data['enable_quick_correct_answer_info'] ) || ( ! empty( $qmn_json_data['ajax_show_correct'] ) && 1 == $qmn_json_data['ajax_show_correct'] ) ) {
				$quiz_id = $qmn_json_data['quiz_id'];
				$qsm_inline_encrypt_js = '
				if (encryptionKey === undefined) {
                       var encryptionKey = {};
                }
                if (data === undefined) {
                       var data = {};
                }
                if (jsonString === undefined) {
                        var jsonString = {};
                }
                if (encryptedData === undefined) {
                      var encryptedData = {};
                }
				encryptionKey['.$quiz_id.'] = "'.hash('sha256',time().$quiz_id).'";

				data['.$quiz_id.'] = '.wp_json_encode($encryption).';
				jsonString['.$quiz_id.'] = JSON.stringify(data['.$quiz_id.']);
				encryptedData['.$quiz_id.'] = CryptoJS.AES.encrypt(jsonString['.$quiz_id.'], encryptionKey['.$quiz_id.']).toString();';
				wp_add_inline_script('qsm_encryption', $qsm_inline_encrypt_js, 'after');
			}

			$return_display .= '<script>window.qmn_quiz_data["' . $qmn_json_data['quiz_id'] . '"] = ' . wp_json_encode( $qmn_filtered_json ) . '
                    </script>';

			$return_display .= ob_get_clean();
			$return_display  = apply_filters( 'qmn_end_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );

		}
		return $return_display;
	}

	public function shortcode_display_result( $atts ) {

		$args = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts
		);

		$id = intval( $args['id'] );

		ob_start();
		if ( 0 === $id ) {
			$id = (int) isset( $_GET['result_id'] ) ? sanitize_text_field( wp_unslash( $_GET['result_id'] ) ) : 0;
		}
		if ( $id && is_numeric( $id ) ) {
			global $mlwQuizMasterNext;
			global $wpdb;
			$result_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $id ), ARRAY_A );
			if ( $result_data ) {
				wp_enqueue_style( 'qmn_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );
				wp_style_add_data( 'qmn_quiz_common_style', 'rtl', 'replace' );
				wp_enqueue_style( 'dashicons' );
				wp_enqueue_style( 'qsm_primary_css', plugins_url( '../../templates/qmn_primary.css', __FILE__ ), array(), $mlwQuizMasterNext->version );

				wp_enqueue_script( 'math_jax', $this->mathjax_url, false, $this->mathjax_version, true );
				wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
				$quiz_result   = maybe_unserialize( $result_data['quiz_results'] );
				$response_data = array(
					'quiz_id'                   => $result_data['quiz_id'],
					'quiz_name'                 => $result_data['quiz_name'],
					'quiz_system'               => $result_data['quiz_system'],
					'form_type'                 => $result_data['form_type'],
					'quiz_payment_id'           => '',
					'user_ip'                   => $result_data['user_ip'],
					'user_name'                 => $result_data['name'],
					'user_business'             => $result_data['business'],
					'user_email'                => $result_data['email'],
					'user_phone'                => $result_data['phone'],
					'user_id'                   => $result_data['user'],
					'timer'                     => 0,
					'time_taken'                => $result_data['time_taken'],
					'contact'                   => $quiz_result['contact'],
					'total_points'              => $result_data['point_score'],
					'total_score'               => $result_data['correct_score'],
					'total_correct'             => $result_data['correct'],
					'total_questions'           => $result_data['total'],
					'question_answers_array'    => $quiz_result[1],
					'comments'                  => '',
					'result_id'                 => $id,
					'total_possible_points'     => $quiz_result['total_possible_points'],
					'minimum_possible_points'   => $quiz_result['minimum_possible_points'],
					'total_attempted_questions' => $quiz_result['total_attempted_questions'],
				);
				$data          = QSM_Results_Pages::generate_pages( $response_data );
				return $data['display'];
			} else {
				esc_html_e( 'Invalid result id!', 'quiz-master-next' );
			}
		} else {
			esc_html_e( 'Invalid result id!', 'quiz-master-next' );
		}
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Loads Questions
	 *
	 * Retrieves the questions from the database
	 *
	 * @since      4.0.0
	 * @param      int   $quiz_id         The id for the quiz.
	 * @param      array $quiz_options    The database row for the quiz.
	 * @param      bool  $is_quiz_page    If the page being loaded is the quiz page or not.
	 * @param      int   $question_amount The amount of questions entered using the shortcode attribute.
	 * @return     array The questions for the quiz
	 * @deprecated 5.2.0 Use new class: QSM_Questions instead
	 */
	public function load_questions( $quiz_id, $quiz_options, $is_quiz_page, $question_amount = 0 ) {
		// Prepare variables.
		global $wpdb;
		global $mlwQuizMasterNext;
		$questions                = array();
		$order_by_sql             = 'ORDER BY question_order ASC';
		$limit_sql                = '';
		$big_array                = array();
		$exploded_arr             = array();
		$multiple_category_system = false;
		// check if multiple category is enabled.
		$enabled = get_option( 'qsm_multiple_category_enabled' );
		if ( $enabled && 'cancelled' !== $enabled ) {
			$multiple_category_system = true;
		}

		// Checks if the questions should be randomized.
		$cat_query = '';
		if ( 1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order ) {
			$order_by_sql = 'ORDER BY rand()';
			$categories   = isset( $quiz_options->randon_category ) ? $quiz_options->randon_category : '';
			if ( $categories && ! empty( $quiz_options->question_per_category ) ) {
				$exploded_arr = explode( ',', $quiz_options->randon_category );
				if ( ! $multiple_category_system ) {
					$cat_str   = "'" . implode( "', '", $exploded_arr ) . "'";
					$cat_query = " AND category IN ( $cat_str ) ";
				} else {
					$exploded_arr = array_map( 'intval', $exploded_arr );
				}
			}
		}

		// Check if we should load all questions or only a selcted amount.
		if ( $is_quiz_page && ( 0 != $quiz_options->question_from_total || 0 !== $question_amount ) ) {
			if ( 0 !== $question_amount ) {
				$limit_sql = " LIMIT $question_amount";
			} else {
				$limit_sql = ' LIMIT ' . intval( $quiz_options->question_from_total );
			}
		}

		// If using newer pages system from 5.2.
		$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
		// Get all question IDs needed.
		$total_pages           = is_countable($pages) ? count( $pages ) : 0;
		$category_question_ids = array();
		if ( $multiple_category_system && ! empty( $exploded_arr ) ) {
			$term_ids      = implode( ', ', $exploded_arr );
			$query         = $wpdb->prepare( "SELECT DISTINCT question_id FROM {$wpdb->prefix}mlw_question_terms WHERE quiz_id = %d AND term_id IN (%1s)", $quiz_id, $term_ids );
			$question_data = $wpdb->get_results( $query, ARRAY_N );
			foreach ( $question_data as $q_data ) {
				$category_question_ids[] = $q_data[0];
			}
		}
		if ( $total_pages > 0 ) {
			for ( $i = 0; $i < $total_pages; $i++ ) {
				foreach ( $pages[ $i ] as $question ) {
					if ( ! empty( $category_question_ids ) ) {
						if ( in_array( intval( $question ), array_map( 'intval', $category_question_ids ), true ) ) {
							$question_ids[] = intval( $question );
						}
					} else {
						$question_ids[] = intval( $question );
					}
				}
			}
			// check If we should load a specific number of question
			if ( ( '' == $quiz_options->limit_category_checkbox || 0 == $quiz_options->limit_category_checkbox ) && 0 != $quiz_options->question_per_category && $is_quiz_page ) {
				$categories   = QSM_Questions::get_quiz_categories( $quiz_id );
				$category_ids = ( isset( $categories['list'] ) ? array_keys( $categories['list'] ) : array() );
				$categories_tree = ( isset( $categories['tree'] ) ? $categories['tree'] : array() );

				if ( ! empty( $category_ids ) ) {
					$term_ids    = implode( ',', $category_ids );
					$question_id = implode( ',', $question_ids );
					$term_ids    = ( '' !== $quiz_options->randon_category ) ? $quiz_options->randon_category : $term_ids;
					$tq_ids = $wpdb->get_results(
						"SELECT DISTINCT qt.term_id, qt.question_id
						FROM {$wpdb->prefix}mlw_question_terms AS qt
						JOIN {$wpdb->prefix}mlw_questions AS q ON qt.question_id = q.question_id
						WHERE qt.question_id IN ($question_id)
							AND qt.term_id IN ($term_ids)
							AND qt.taxonomy = 'qsm_category'
							AND q.deleted = 0
						",
						ARRAY_A
					);
					$random = array();
					if ( ! empty( $tq_ids ) ) {
						$term_data = array();
						foreach ( $tq_ids as $key => $val ) {
							$term_data[ $val['term_id'] ][] = $val['question_id'];
						}
						if ( '' === $quiz_options->randon_category ) {
							foreach ( $categories_tree as $cat ) {
								if ( ! empty( $cat->children ) ) {
									unset( $term_data[ $cat->term_id ] );
								}
							}
						}
						foreach ( $term_data as $tv ) {
							if ( 1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order ) {
								shuffle( $tv );
							}
							$random = array_merge( $random, array_slice( array_unique( $tv ), 0, intval( $quiz_options->question_per_category ) ) );
						}
					}
					$question_ids = array_unique( $random );
				}
			} elseif ( 1 == $quiz_options->limit_category_checkbox && ! empty(maybe_unserialize($quiz_options->select_category_question)) && $is_quiz_page ) {
				$category_question_limit = maybe_unserialize($quiz_options->select_category_question);
				$categories   = QSM_Questions::get_quiz_categories( $quiz_id );
				$category_ids = ( isset( $categories['list'] ) ? array_keys( $categories['list'] ) : array() );
				if ( ! empty( $category_ids ) ) {
					$question_limit_sql = $category_question_limit['question_limit_key'];
					$tq_ids = array();
					foreach ( $category_question_limit['category_select_key'] as $key => $category ) {
						if ( empty( $category ) || empty( $category_question_limit['question_limit_key'][ $key ] ) ) {
							continue;
						}
						$limit = $category_question_limit['question_limit_key'][ $key ];
						$exclude_ids = 0;
						if ( ! empty( $tq_ids ) && ! empty( (array_column(array_merge(...array_map('array_merge', $tq_ids)),'question_id')) ) ) {
							$exclude_ids = implode(',', array_column(array_merge(...array_map('array_merge', $tq_ids)),'question_id') );
						}
						$category_order_sql = '';
						if ( 1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order ) {
							$category_order_sql = 'ORDER BY rand()';
						}
						$tq_ids[] = $wpdb->get_results(
							"SELECT DISTINCT q.`question_id`
							FROM `{$wpdb->prefix}mlw_questions` AS q
							JOIN `{$wpdb->prefix}mlw_question_terms` AS qt ON q.`question_id` = qt.`question_id`
							WHERE qt.`quiz_id` = $quiz_id
								AND qt.`term_id` = $category
								AND qt.`taxonomy` = 'qsm_category'
								AND qt.`question_id` NOT IN ($exclude_ids)
								AND q.`deleted` = 0
							".esc_sql( $category_order_sql )."
							LIMIT $limit",
							ARRAY_A
						);
					}
					$final_result = array_column(array_merge(...array_map('array_merge', $tq_ids)),'question_id');
					if ( 1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order ) {
						shuffle( $final_result );
					}
					$question_ids = $final_result;
				}
			}
			$question_ids = apply_filters( 'qsm_load_questions_ids', $question_ids, $quiz_id, $quiz_options );
			$question_sql = implode( ',', $question_ids );
			if ( 1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order ) {
				if ( isset( $_COOKIE[ 'question_ids_'.$quiz_id ] ) && empty( $quiz_options->question_per_category ) && empty( $quiz_options->limit_category_checkbox ) ) {
					$question_sql = sanitize_text_field( wp_unslash( $_COOKIE[ 'question_ids_'.$quiz_id ] ) );
					if ( ! preg_match("/^\d+(,\d+)*$/", $question_sql) ) {
						$question_sql = implode( ',', $question_ids );
					}
				}else {
					$question_ids = QMNPluginHelper::qsm_shuffle_assoc( $question_ids );
					$question_sql = implode( ',', $question_ids );
				}
				$order_by_sql = 'ORDER BY FIELD(question_id,'. esc_sql( $question_sql ) .')';
			}
			$query     = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE question_id IN (%1s) %2s %3s %4s", esc_sql( $question_sql ), esc_sql( $cat_query ), esc_sql( $order_by_sql ), esc_sql( $limit_sql ) );
			$questions = $wpdb->get_results( $query );
			$question_order = array();
			if ( ! empty($question_ids) ) {
				foreach ( $question_ids as $question_id_order ) {
					foreach ( $questions as $obj ) {
						if ( $obj->question_id == $question_id_order ) {
							$question_order[] = $obj;
							break;
						}
					}
				}
				$questions = $question_order;
			}
			// If we are not using randomization, we need to put the questions in the order of the new question editor.
			// If a user has saved the pages in the question editor but still uses the older pagination options
			// Then they will make it here. So, we need to order the questions based on the new editor.
			if ( 1 != $quiz_options->randomness_order && 2 != $quiz_options->randomness_order && 0 == $quiz_options->question_per_category && 0 == $quiz_options->limit_category_checkbox ) {
				$ordered_questions = array();

				foreach ( $questions as $question ) {
					$key = array_search( intval( $question->question_id ), $question_ids, true );
					if ( false !== $key ) {
						$ordered_questions[ $key ] = $question;
					}
				}
				ksort( $ordered_questions );
				$questions = $ordered_questions;

			}
		} else {
			$question_ids = apply_filters( 'qsm_load_questions_ids', array(), $quiz_id, $quiz_options );
			$question_sql = '';
			if ( ! empty( $question_ids ) ) {
				$qids         = implode( ', ', $question_ids );
				$question_sql = " AND question_id IN ($qids) ";
			}
			$questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE quiz_id=%d AND deleted=0 %1s %2s %3s", $quiz_id, $question_sql, $order_by_sql, $limit_sql ) );

		}
		if (
			in_array( intval( $quiz_options->randomness_order ), [ 1, 2 ], true) &&
			! empty($questions) &&
			is_array($questions) &&
			! isset($_COOKIE[ 'question_ids_' . $quiz_id ])
		) {
			$question_ids = array();
			foreach ( $questions as $question ) {
				$question_ids[] = $question->question_id;
			}

			$question_sql = implode(',', array_unique($question_ids)); // Prevent duplicates
			?>
			<script>
				const d = new Date();
				d.setTime(d.getTime() + (365 * 24 * 60 * 60 * 1000)); // Set cookie for 1 year
				let expires = "expires=" + d.toUTCString();
				document.cookie = "question_ids_<?php echo esc_js($quiz_id); ?>=" + "<?php echo esc_js($question_sql); ?>" + "; " + expires + "; path=/";
			</script>
			<?php
		}
		$questions = array_filter(
			$questions,
			function ( $question ) {
				$question_settings = maybe_unserialize( $question->question_settings );
				return ! isset( $question_settings['isPublished'] ) || 0 !== intval( $question_settings['isPublished'] );
			}
		);
		return apply_filters( 'qsm_load_questions_filter', $questions, $quiz_id, $quiz_options );
	}

	/**
	 * Prepares Answers
	 *
	 * Prepares or creates the answer array for the quiz
	 *
	 * @since      4.0.0
	 * @param      array $questions The questions for the quiz.
	 * @param      bool  $is_ajax   Pass true if this is an ajax call.
	 * @return     array The answers for the quiz
	 * @deprecated 5.2.0 Use new class: QSM_Questions instead
	 */
	public function create_answer_array( $questions, $is_ajax = false ) {

		// Load and prepare answer arrays.
		$mlw_qmn_answer_arrays = array();
		$question_list         = array();
		foreach ( $questions as $mlw_question_info ) {
			$question_list[ $mlw_question_info->question_id ] = get_object_vars( $mlw_question_info );
			$mlw_qmn_answer_array_each                        = maybe_unserialize( $mlw_question_info->answer_array );
			if ( is_array( $mlw_qmn_answer_array_each ) ) {
				$mlw_qmn_answer_arrays[ $mlw_question_info->question_id ]    = $mlw_qmn_answer_array_each;
				$question_list[ $mlw_question_info->question_id ]['answers'] = $mlw_qmn_answer_array_each;
			} else {
				$mlw_answer_array_correct = array( 0, 0, 0, 0, 0, 0 );
				$mlw_answer_array_correct[ $mlw_question_info->correct_answer - 1 ] = 1;
				$mlw_qmn_answer_arrays[ $mlw_question_info->question_id ]           = array(
					array( $mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0] ),
					array( $mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1] ),
					array( $mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2] ),
					array( $mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3] ),
					array( $mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4] ),
					array( $mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5] ),
				);
				$question_list[ $mlw_question_info->question_id ]['answers']        = $mlw_qmn_answer_arrays[ $mlw_question_info->question_id ];
			}
		}
		if ( ! $is_ajax ) {
			global $qmn_json_data;
			$qmn_json_data['question_list'] = $question_list;
		}

		return $mlw_qmn_answer_arrays;
	}

	/**
	 * Generates Content Quiz Page
	 *
	 * Generates the content for the quiz page part of the shortcode
	 *
	 * @since  4.0.0
	 * @param  array $options         The database row of the quiz.
	 * @param  array $quiz_data       The array of results for the quiz.
	 * @param  int   $question_amount The number of questions to load for quiz.
	 * @uses   QMNQuizManager:display_begin_section() Creates display for beginning section
	 * @uses   QMNQuizManager:display_questions() Creates display for questions
	 * @uses   QMNQuizManager:display_comment_section() Creates display for comment section
	 * @uses   QMNQuizManager:display_end_section() Creates display for end section
	 * @return string The content for the quiz page section
	 */
	public function display_quiz( $options, $quiz_data, $question_amount, $shortcode_args = array() ) {
		global $qmn_allowed_visit;
		global $mlwQuizMasterNext;
		echo apply_filters( 'qmn_begin_quiz', '', $options, $quiz_data );
		$options = apply_filters( 'qmn_begin_quiz_options', $options, $quiz_data );
		if ( ! $qmn_allowed_visit ) {
			return;
		}
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_style( 'jquery-redmond-theme', QSM_PLUGIN_CSS_URL . '/jquery-ui.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_style( 'qsm_quiz_common_style', $this->common_css, array(), $mlwQuizMasterNext->version );

		global $qmn_json_data;
		$qmn_json_data['error_messages'] = array(
			'email_error_text'     => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->email_error_text, "quiz_email_error_text-{$options->quiz_id}" ),
			'number_error_text'    => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->number_error_text, "quiz_number_error_text-{$options->quiz_id}" ),
			'incorrect_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->incorrect_error_text, "quiz_incorrect_error_text-{$options->quiz_id}" ),
			'empty_error_text'     => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->empty_error_text, "quiz_empty_error_text-{$options->quiz_id}" ),
			'url_error_text'       => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->url_error_text, "quiz_url_error_text-{$options->quiz_id}" ),
			'minlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->minlength_error_text, "quiz_minlength_error_text-{$options->quiz_id}" ),
			'maxlength_error_text' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->maxlength_error_text, "quiz_maxlength_error_text-{$options->quiz_id}" ),
			'recaptcha_error_text' => __( 'ReCaptcha is missing', 'quiz-master-next' ),
		);
		$qmn_json_data = apply_filters( 'qsm_json_error_message', $qmn_json_data ,$options);
		wp_enqueue_script( 'progress-bar', QSM_PLUGIN_JS_URL . '/progressbar.min.js', array(), '1.1.0', true );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-slider-rtl-js', QSM_PLUGIN_JS_URL . '/jquery.ui.slider-rtl.js', array(), $mlwQuizMasterNext->version, true );
		wp_enqueue_style( 'jquery-ui-slider-rtl-css', QSM_PLUGIN_CSS_URL . '/jquery.ui.slider-rtl.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_script( 'jquery-touch-punch' );
		wp_enqueue_script( 'qsm_model_js', QSM_PLUGIN_JS_URL . '/micromodal.min.js', array(), $mlwQuizMasterNext->version, false );
		wp_enqueue_script( 'qsm_quiz', QSM_PLUGIN_JS_URL . '/qsm-quiz.js', array( 'wp-util', 'underscore', 'jquery', 'backbone', 'jquery-ui-tooltip', 'progress-bar', 'qsm_encryption' ), $mlwQuizMasterNext->version, false );
		wp_enqueue_script( 'qsm_common', QSM_PLUGIN_JS_URL . '/qsm-common.js', array(), $mlwQuizMasterNext->version, true );
		wp_enqueue_script( 'qsm_encryption', QSM_PLUGIN_JS_URL . '/crypto-js.js', array( 'jquery' ), $mlwQuizMasterNext->version, false );
		wp_localize_script(
			'qsm_quiz',
			'qmn_ajax_object',
			array(
				'site_url'                  => site_url(),
				'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
				'multicheckbox_limit_reach' => $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->quiz_limit_choice, "quiz_quiz_limit_choice-{$options->quiz_id}" ),
				'out_of_text'               => esc_html__( ' out of ', 'quiz-master-next' ),
				'quiz_time_over'            => esc_html__( 'Quiz time is over.', 'quiz-master-next' ),
				'security'                  => wp_create_nonce( 'qsm_submit_quiz' ),
				'start_date'                => current_time( 'h:i:s A m/d/Y' ),
			)
		);

		$disable_mathjax = isset( $options->disable_mathjax ) ? $options->disable_mathjax : '';
		if ( 1 != $disable_mathjax ) {
			wp_enqueue_script( 'math_jax', $this->mathjax_url, array(), $this->mathjax_version, true );
			wp_add_inline_script( 'math_jax', self::$default_MathJax_script, 'before' );
		}
		global $qmn_total_questions, $qmn_all_questions_count;
		$qmn_total_questions = $qmn_all_questions_count = 0;
		global $mlw_qmn_section_count;
		$mlw_qmn_section_count = 0;
		$auto_pagination_class = $options->pagination > 0 ? 'qsm_auto_pagination_enabled' : '';
		// $saved_quiz_theme = $mlwQuizMasterNext->quiz_settings->get_setting('quiz_new_theme');
		$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme_path( $options->quiz_id );
		$randomness_class = 0 === intval( $options->randomness_order ) ? '' : 'random';
		?><div class='qsm-quiz-container qsm-quiz-container-<?php echo esc_attr($quiz_data['quiz_id']); ?> qmn_quiz_container mlw_qmn_quiz <?php echo esc_attr( $auto_pagination_class ); ?> quiz_theme_<?php echo esc_attr( $saved_quiz_theme . ' ' . $randomness_class ); ?> '>
		<?php
			if ( 'default' == $saved_quiz_theme ) {
				$featured_image       = get_option( "quiz_featured_image_$options->quiz_id" );
				$qsm_global_settings   = (array) get_option( 'qmn-settings' );
				$qsm_preloader_setting = isset( $qsm_global_settings['enable_preloader'] ) ? $qsm_global_settings['enable_preloader'] : '';

				if ( isset( $qsm_preloader_setting ) && $qsm_preloader_setting > 0 && ! empty( $featured_image ) ) {
					echo '<link rel="preload" href="' . esc_url( $featured_image ) . '" as="image">';
				}

				if ( "" != $featured_image ) {
					?>
					<img class="qsm-quiz-default-feature-image" src="<?php echo esc_url( $featured_image ); ?>" alt="<?php esc_attr_e( 'Featured Image', 'quiz-master-next' ); ?>" />
				<?php }
				?>
			<?php }
			echo apply_filters( 'qsm_display_before_form', '', $options, $quiz_data );
			$quiz_form_action = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			?>
			<form name="quizForm<?php echo esc_attr( $quiz_data['quiz_id'] ); ?>" id="quizForm<?php echo esc_attr( $quiz_data['quiz_id'] ); ?>" action="<?php echo esc_url( $quiz_form_action ); ?>" method="POST" class="qsm-quiz-form qmn_quiz_form mlw_quiz_form" novalidate enctype="multipart/form-data">
				<input type="hidden" name="qsm_hidden_questions" id="qsm_hidden_questions" value="">
				<input type="hidden" name="qsm_nonce" id="qsm_nonce_<?php echo esc_attr($quiz_data['quiz_id']); ?>" value="<?php echo esc_attr( wp_create_nonce( 'qsm_submit_quiz_' . intval( $quiz_data['quiz_id'] ) ) );?>">
				<input type="hidden" name="qsm_unique_key" id="qsm_unique_key_<?php echo esc_attr($quiz_data['quiz_id']); ?>" value="<?php echo esc_attr( uniqid() ); ?>">
				<div id="mlw_error_message" class="qsm-error-message qmn_error_message_section"></div>
				<span id="mlw_top_of_quiz"></span>
				<?php
				echo apply_filters( 'qmn_begin_quiz_form', '', $options, $quiz_data );
				// If deprecated pagination setting is not used, use new system...
				$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
				if ( 2 === intval( $options->randomness_order ) || 3 === intval( $options->randomness_order ) ) {
					global $quiz_answer_random_ids;
					$quiz_answer_random_ids = array();
				}
				if ( 0 == $options->question_from_total && 0 == $options->pagination && is_countable($pages) && 0 !== count( $pages ) ) {
					$this->display_pages( $options, $quiz_data );
				} else {
					// ... else, use older system.
					$questions = $this->load_questions( $quiz_data['quiz_id'], $options, true, $question_amount );
					$answers   = $this->create_answer_array( $questions );
					$this->display_begin_section( $options, $quiz_data );
					echo apply_filters( 'qmn_begin_quiz_questions', '', $options, $quiz_data );
					$this->display_questions( $options, $questions, $answers );
					echo apply_filters( 'qmn_before_comment_section', '', $options, $quiz_data );
					$this->display_comment_section( $options, $quiz_data );
					echo apply_filters( 'qmn_after_comment_section', '', $options, $quiz_data );
					$this->display_end_section( $options, $quiz_data );
				}
				echo apply_filters( 'qmn_before_error_message', '', $options, $quiz_data );
				?>
					<div id="mlw_error_message_bottom" class="qsm-error-message qmn_error_message_section"></div>
					<input type="hidden" name="qmn_all_questions_count" id="qmn_all_questions_count" value="<?php echo esc_attr( $qmn_all_questions_count ); ?>" />
					<input type="hidden" name="total_questions" id="total_questions" value="<?php echo esc_attr( $qmn_total_questions ); ?>" />
					<input type="hidden" name="timer" id="timer" value="0" />
					<input type="hidden" name="timer_ms" id="timer_ms" value="0"/>
					<input type="hidden" class="qmn_quiz_id" name="qmn_quiz_id" id="qmn_quiz_id" value="<?php echo esc_attr( $quiz_data['quiz_id'] ); ?>" />
					<input type='hidden' name='complete_quiz' value='confirmation' />
					<?php
					if ( 2 === intval( $options->randomness_order ) || 3 === intval( $options->randomness_order ) ) {
						?>
						<input type="hidden" name="quiz_answer_random_ids" id="quiz_answer_random_ids_<?php echo esc_attr( $quiz_data['quiz_id'] ); ?>" value="<?php echo esc_attr( maybe_serialize( $quiz_answer_random_ids ) ); ?>" />
						<?php
					}
					if ( isset( $_GET['payment_id'] ) && '' !== $_GET['payment_id'] ) {
						$payment_id = sanitize_text_field( wp_unslash( $_GET['payment_id'] ) );
						?>
						<input type="hidden" name="main_payment_id" value="<?php echo esc_attr( $payment_id ); ?>" />
						<?php
					}
					echo apply_filters( 'qmn_end_quiz_form', '', $options, $quiz_data );
					do_action( 'qsm_before_end_quiz_form', $options, $quiz_data, $shortcode_args );
					?>
				</form>
		</div>
		<?php
		echo apply_filters( 'qmn_end_quiz', '', $options, $quiz_data );
	}

	/**
	 * Creates the pages of content for the quiz/survey
	 *
	 * @since  5.2.0
	 * @param  array $options   The settings for the quiz.
	 * @param  array $quiz_data The array of quiz data.
	 * @return string The HTML for the pages
	 */
	public function display_pages( $options, $quiz_data ) {
		global $mlwQuizMasterNext, $wp_embed;
		global $qmn_json_data;
		$pages                  = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
		$qpages                 = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'qpages', array() );
		$questions              = QSM_Questions::load_questions_by_pages( $options->quiz_id );
		$question_list          = '';
		$contact_fields         = QSM_Contact_Manager::load_fields();
		$animation_effect       = isset( $options->quiz_animation ) && '' !== $options->quiz_animation ? ' animated ' . $options->quiz_animation : '';
		$enable_pagination_quiz = isset( $options->enable_pagination_quiz ) && 1 == $options->enable_pagination_quiz ? true : false;
		if ( ( 1 == $options->randomness_order || 2 == $options->randomness_order ) && is_array( $pages ) && empty( $options->question_per_category ) ) {
			$pages = QMNPluginHelper::qsm_shuffle_assoc( $pages );
			$question_list_array = array();
			foreach ( $pages as &$question_ids ) {
				shuffle( $question_ids );
				$question_list_array = array_merge($question_list_array, $question_ids);
			}
			$question_list_str = implode( ',', $question_list_array );
			?>
			<script>
				const d = new Date();
				d.setTime(d.getTime() + (365*24*60*60*1000));
				let expires = "expires="+ d.toUTCString();
				document.cookie = "question_ids_<?php echo esc_attr( $options->quiz_id ); ?> = <?php echo esc_attr( $question_list_str ) ?>; "+expires+"; path=/";
			</script>
			<?php
		}
		if ( 1 < count( $pages ) && 1 !== intval( $options->disable_first_page ) && ( ! empty( $options->message_before ) || ( 0 == $options->contact_info_location && $contact_fields ) ) ) {
			$qmn_json_data['first_page'] = true;
			$message_before              = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_before, ENT_QUOTES ), "quiz_message_before-{$options->quiz_id}" );
			$message_before              = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_before ), $quiz_data );
			?>
			<section class="qsm-page <?php echo esc_attr( $animation_effect ); ?>">
				<div class="quiz_section quiz_begin">
					<div class='qsm-before-message mlw_qmn_message_before'>
			<?php
			$editor_text = $wp_embed->run_shortcode( $message_before );
			$editor_text = preg_replace( '/\s*[\w\/:\.]*youtube.com\/watch\?v=([\w]+)([\w\*\-\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $editor_text );
			echo wp_kses_post( do_shortcode( $editor_text ) );
			?>
					</div>
			<?php
			if ( 0 == $options->contact_info_location ) {
				echo QSM_Contact_Manager::display_fields( $options );
			}
			do_action( 'qsm_after_begin_message', $options, $quiz_data );
			?>
				</div>
			</section>
			<?php
		}
		// If there is only one page.
		$pages = apply_filters( 'qsm_display_pages', $pages, $options->quiz_id, $options );
		if ( 1 == count( $pages ) ) {
			?>
			<section class="qsm-page <?php echo esc_attr( $animation_effect ); ?>">
			<?php
			if ( ! empty( $options->message_before ) || ( 0 == $options->contact_info_location && $contact_fields ) ) {
				$qmn_json_data['first_page'] = false;
				$message_before              = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_before, ENT_QUOTES ), "quiz_message_before-{$options->quiz_id}" );
				$message_before              = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_before ), $quiz_data );
				?>
					<div class="quiz_section quiz_begin">
						<div class='qsm-before-message mlw_qmn_message_before'>
				<?php
				$editor_text = $wp_embed->run_shortcode( $message_before );
				$editor_text = preg_replace( '/\s*[\w\/:\.]*youtube.com\/watch\?v=([\w]+)([\w\*\-\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $editor_text );
				echo wp_kses_post( do_shortcode( $editor_text ) );
				?>
						</div>
				<?php
				if ( 0 == $options->contact_info_location ) {
					echo QSM_Contact_Manager::display_fields( $options );
				}
				?>
					</div>
				<?php
			}
			do_action( 'qsm_after_welcome_page', $options, $quiz_data, 'single' );
			foreach ( $pages[0] as $question_id ) {
				$question_list .= $question_id . 'Q';
				$question       = $questions[ $question_id ];
				$category_class = '';
				if ( ! empty( $question['multicategories'] ) ) {
					foreach ( $question['multicategories'] as $cat ) {
						$category_class .= ' category-section-id-c' . esc_attr( $cat );
					}
				}
				?>
					<div class="quiz_section qsm-question-wrapper question-type-<?php echo esc_attr( $question['question_type_new'] ); ?> question-section-id-<?php echo esc_attr( $question_id ); ?> <?php echo esc_attr( $category_class ); ?>" data-qid="<?php echo esc_attr( $question_id ); ?>">
				<?php
				$mlwQuizMasterNext->pluginHelper->display_question( $question['question_type_new'], $question_id, $options );
				if ( 0 == $question['comments'] ) {
					?>
					<input type="text" class="qsm-question-comment qsm-question-comment-small mlw_qmn_question_comment" id="mlwComment<?php echo esc_attr( $question_id ); ?>" name="mlwComment<?php echo esc_attr( $question_id ); ?>" placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->comment_field_text, "quiz_comment_field_text-{$options->quiz_id}" ) ); ?>" onclick="qmnClearField(this)" />
					<?php
				}
				if ( 2 == $question['comments'] ) {
					?>
					<textarea class="qsm-question-comment qsm-question-comment-large mlw_qmn_question_comment" id="mlwComment<?php echo esc_attr( $question_id ); ?>" name="mlwComment<?php echo esc_attr( $question_id ); ?>" placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->comment_field_text, "quiz_comment_field_text-{$options->quiz_id}" ) ); ?>" onclick="qmnClearField(this)" ></textarea>
					<?php
				}
				// Checks if a hint is entered.
				if ( ! empty( $question['hints'] ) ) {
					$hint_data = wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $question['hints'], "hint-{$question_id}" ) );
					echo '<div class="qsm-hint qsm_hint mlw_qmn_hint_link qsm_tooltip" title="' . esc_attr( $hint_data ) . '">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->hint_text, "quiz_hint_text-{$options->quiz_id}" ) ) . '</div>';
				}
				?>
					</div>
				<?php
			}
			if ( 0 == $options->comment_section && "" !== $options->comment_section ) {
				$message_comments = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_comment, ENT_QUOTES ), "quiz_message_comment-{$options->quiz_id}" );
				?>
					<div class="quiz_section qsm-quiz-comment-section" style="display:none">
						<label for='mlwQuizComments' class='qsm-comments-label mlw_qmn_comment_section_text'><?php echo apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_comments ), $quiz_data ); ?></label>
						<textarea id='mlwQuizComments' name='mlwQuizComments' class='qsm-comments qmn_comment_section'></textarea>
					</div>
				<?php
			}
			if ( ! empty( $options->message_end_template ) || ( 1 == $options->contact_info_location && $contact_fields ) ) {
				$message_after = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_end_template, ENT_QUOTES ), "quiz_message_end_template-{$options->quiz_id}" );
				?>
					<div class="quiz_section">
						<div class='qsm-after-message mlw_qmn_message_end'>
							<?php
							$message_after = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_after ), $quiz_data );
							echo wp_kses_post( do_shortcode( $message_after ) );
							?>
						</div>
				<?php
				if ( 1 == $options->contact_info_location ) {
					echo QSM_Contact_Manager::display_fields( $options );
				}
				?>
					</div>
				<?php
			}
			?>
			</section>
			<?php
		} else {
			$total_pages_count = count( $pages );
			$pages_count       = 1;
			foreach ( $pages as $key => $page ) {
				$qpage        = ( isset( $qpages[ $key ] ) ? $qpages[ $key ] : array() );
				$qpage_id     = ( isset( $qpage['id'] ) ? $qpage['id'] : $key );
				$page_key     = ( isset( $qpage['pagekey'] ) ? $qpage['pagekey'] : $key );
				$hide_prevbtn = ( isset( $qpage['hide_prevbtn'] ) ? $qpage['hide_prevbtn'] : 0 );
				?>
				<section class="qsm-page qsm-question-page <?php echo esc_attr( $animation_effect ); ?> qsm-page-<?php echo esc_attr( $qpage_id ); ?>"
						data-pid="<?php echo esc_attr( $qpage_id ); ?>" data-qpid="<?php echo esc_attr( $pages_count ); ?>" data-prevbtn="<?php echo esc_attr( $hide_prevbtn ); ?>" style='display: none;'>
				<?php do_action( 'qsm_action_before_page', $qpage_id, $qpage ); ?>
				<?php
				foreach ( $page as $question_id ) {
					if ( ! isset( $questions[ $question_id ] ) ) {
						continue;
					}
					$question_list .= $question_id . 'Q';
					$question       = $questions[ $question_id ];
					$category_class = '';
					if ( ! empty( $question['multicategories'] ) ) {
						foreach ( $question['multicategories'] as $cat ) {
							$category_class .= ' category-section-id-c' . esc_attr( $cat );
						}
					}
					?>
						<div class='quiz_section qsm-question-wrapper question-type-<?php echo esc_attr( $question['question_type_new'] ); ?> question-section-id-<?php echo esc_attr( $question_id ); ?> <?php echo esc_attr( $category_class ); ?>' data-qid='<?php echo esc_attr( $question_id ); ?>'>
					<?php
					$mlwQuizMasterNext->pluginHelper->display_question( $question['question_type_new'], $question_id, $options );
					if ( 0 == $question['comments'] ) {
						?>
						<input type="text" class="qsm-question-comment qsm-question-comment-small mlw_qmn_question_comment" id="mlwComment<?php echo esc_attr( $question_id ); ?>" name="mlwComment<?php echo esc_attr( $question_id ); ?>" placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->comment_field_text, "quiz_comment_field_text-{$options->quiz_id}" ) ); ?>" onclick="qmnClearField(this)" />
						<?php
					}
					if ( 2 == $question['comments'] ) {
						?>
						<textarea class="qsm-question-comment qsm-question-comment-large mlw_qmn_question_comment" id="mlwComment<?php echo esc_attr( $question_id ); ?>" name="mlwComment<?php echo esc_attr( $question_id ); ?>" placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->comment_field_text, "quiz_comment_field_text-{$options->quiz_id}" ) ); ?>" onclick="qmnClearField(this)" ></textarea>
						<?php
					}
					// Checks if a hint is entered.
					if ( ! empty( $question['hints'] ) ) {
						$hint_data = wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $question['hints'], "hint-{$question_id}" ) );
						echo '<div class="qsm-hint qsm_hint mlw_qmn_hint_link qsm_tooltip" title="' . esc_attr( $hint_data ) . '">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->hint_text, "quiz_hint_text-{$options->quiz_id}" ) ) . '</div>';
					}
					?>
						</div>
					<?php
				}
				if ( $enable_pagination_quiz ) {
					?>
					<span class="pages_count">
					<?php
					$text_c = $pages_count . esc_html__( ' out of ', 'quiz-master-next' ) . $total_pages_count;
					echo apply_filters( 'qsm_total_pages_count', $text_c, $pages_count, $total_pages_count );
					?>
					</span>
				<?php } ?>
				</section>
				<?php
				$pages_count++;
			}
		}
		if ( count( $pages ) > 1 && 0 == $options->comment_section && "" !== $options->comment_section ) {
			$message_comments = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_comment, ENT_QUOTES ), "quiz_message_comment-{$options->quiz_id}" );
			?>
			<section class="qsm-page">
				<div class="quiz_section qsm-quiz-comment-section" style="display:none">
					<label for="mlwQuizComments" class="qsm-comments-label mlw_qmn_comment_section_text"><?php echo apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_comments ), $quiz_data ); ?></label>
					<textarea id="mlwQuizComments" name="mlwQuizComments" class="qsm-comments qmn_comment_section"></textarea>
				</div>
			</section>
			<?php
		}
		if ( count( $pages ) > 1 && ( ! empty( $options->message_end_template ) || ( 1 == $options->contact_info_location && $contact_fields ) ) ) {
			$message_after = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->message_end_template, ENT_QUOTES ), "quiz_message_end_template-{$options->quiz_id}" );
			?>
			<section class="qsm-page" style="display: none;">
				<div class="quiz_section">
					<div class='qsm-after-message mlw_qmn_message_end'>
						<?php
						$message_after = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_after ), $quiz_data );
						echo wp_kses_post( do_shortcode( $message_after ) );
						?>
					</div>
					<?php
					if ( 1 == $options->contact_info_location ) {
						echo QSM_Contact_Manager::display_fields( $options );
					}
					?>
				</div>
				<?php
				// Legacy code.
				do_action( 'mlw_qmn_end_quiz_section' );
				?>
			</section>
			<?php
		}
		do_action( 'qsm_after_all_section' );
			/**
		 * quiz display page templates
		 *
		 * @since 7.3.5
		 */
		$start_button_text = ! empty( $options->start_quiz_survey_text ) ? $options->start_quiz_survey_text : $options->next_button_text;
		$tmpl_pagination = '<div class="qsm-pagination qmn_pagination border margin-bottom">
			<a class="qsm-btn qsm-previous qmn_btn mlw_qmn_quiz_link mlw_previous" href="javascript:void(0)">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->previous_button_text, "quiz_previous_button_text-{$options->quiz_id}" ) ) . '</a>
			<span class="qmn_page_message"></span>
			<div class="qmn_page_counter_message"></div>
			<div class="qsm-progress-bar" style="display:none;"><div class="progressbar-text"></div></div>
			<a class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_start" href="javascript:void(0)">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $start_button_text, "quiz_next_button_text-{$options->quiz_id}" ) ) . '</a>
			<a class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_next" href="javascript:void(0)">' . esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->next_button_text, "quiz_next_button_text-{$options->quiz_id}" ) ) . '</a>
			<input type="submit" class="qsm-btn qsm-submit-btn qmn_btn" value="' . esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $options->submit_button_text, "quiz_submit_button_text-{$options->quiz_id}" ) ) . '" />
		</div>';
		qsm_add_inline_tmpl( 'qsm_quiz', 'tmpl-qsm-pagination-' . esc_attr( $options->quiz_id ), $tmpl_pagination );
		?>
		<input type="hidden" name="qmn_question_list" value="<?php echo esc_attr( $question_list ); ?>" />
		<?php

	}

	/**
	 * Creates Display For Beginning Section
	 *
	 * Generates the content for the beginning section of the quiz page
	 *
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options        The database row of the quiz.
	 * @param      array $qmn_array_for_variables The array of results for the quiz.
	 * @return     string The content for the beginning section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_begin_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		global $mlwQuizMasterNext, $qmn_json_data, $wp_embed;
		$contact_fields = QSM_Contact_Manager::load_fields();
		if ( 1 !== intval( $qmn_quiz_options->disable_first_page ) && ( ! empty( $qmn_quiz_options->message_before ) || ( 0 == $qmn_quiz_options->contact_info_location && $contact_fields ) ) ) {
			$qmn_json_data['first_page'] = true;
			global $mlw_qmn_section_count;
			$mlw_qmn_section_count += 1;
			$animation_effect       = isset( $qmn_quiz_options->quiz_animation ) && '' !== $qmn_quiz_options->quiz_animation ? ' animated ' . $qmn_quiz_options->quiz_animation : '';
			?>
			<div class="qsm-auto-page-row quiz_section quiz_begin <?php echo esc_attr( $animation_effect ); ?>">
				<?php
				$message_before = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $qmn_quiz_options->message_before, ENT_QUOTES ), "quiz_message_before-{$qmn_quiz_options->quiz_id}" );
				$message_before = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_before ), $qmn_array_for_variables );
				$editor_text    = $wp_embed->run_shortcode( $message_before );
				$editor_text    = preg_replace( '/\s*[\w\/:\.]*youtube.com\/watch\?v=([\w]+)([\w\*\-\?\&\;\%\=\.]*)/i', '<iframe width="420" height="315" src="//www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>', $editor_text );
				?>
				<div class='mlw_qmn_message_before'>
					<?php
					$allowed_html = wp_kses_allowed_html('post');
					$allowed_html['input'] = array(
						'type'  => array(),
						'name'  => array(),
						'value' => array(),
						'class' => array(), // Optional: Allow the class attribute
						'id'    => array(), // Optional: Allow the id attribute
					);
					echo wp_kses( do_shortcode( $editor_text ), $allowed_html );
					?>
				</div>
					<?php
					if ( 0 == $qmn_quiz_options->contact_info_location ) {
						echo QSM_Contact_Manager::display_fields( $qmn_quiz_options );
					}
					do_action( 'qsm_after_begin_message', $qmn_quiz_options, $qmn_array_for_variables );
					?>
				</div>
			<?php
		} else {
			$qmn_json_data['first_page'] = false;
		}
	}

	/**
	 * Creates Display For Questions
	 *
	 * Generates the content for the questions part of the quiz page
	 *
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options   The database row of the quiz.
	 * @param      array $qmn_quiz_questions The questions of the quiz.
	 * @param      array $qmn_quiz_answers   The answers of the quiz.
	 * @uses       QMNPluginHelper:display_question() Displays a question
	 * @return     string The content for the questions section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_questions( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers ) {
		global $mlwQuizMasterNext;
		global $qmn_total_questions;
		global $mlw_qmn_section_count;
		$question_id_list       = '';
		$animation_effect       = isset( $qmn_quiz_options->quiz_animation ) && '' !== $qmn_quiz_options->quiz_animation ? ' animated ' . $qmn_quiz_options->quiz_animation : '';
		$enable_pagination_quiz = isset( $qmn_quiz_options->enable_pagination_quiz ) && $qmn_quiz_options->enable_pagination_quiz ? $qmn_quiz_options->enable_pagination_quiz : 0;
		$pagination_option      = intval( $qmn_quiz_options->pagination );
		$total_pagination       = $total_pages_count  = 1;
		if ( $enable_pagination_quiz && $pagination_option ) {
			$total_pages_count = count( $qmn_quiz_questions );
			$total_pagination  = ceil( $total_pages_count / $pagination_option );
		}
		$pages_count         = 1;
		$current_page_number = 1;
		foreach ( $qmn_quiz_questions as $mlw_question ) {
			if ( 0 != $pagination_option ) {
				if ( 1 == $pagination_option || 1 == $pages_count % $pagination_option || 1 == $pages_count ) {
					?>
					<div class="qsm-auto-page-row qsm-question-page qsm-apc-<?php echo esc_attr( $current_page_number ); ?>" data-apid="<?php echo esc_attr($current_page_number); ?>" data-qpid="<?php echo esc_attr( $current_page_number ); ?>" style="display: none;">
					<?php
					$current_page_number++;
					echo apply_filters( 'qsm_auto_page_begin_pagination', '', ( $current_page_number - 1 ), $qmn_quiz_options, $qmn_quiz_questions );
				}
				echo apply_filters( 'qsm_auto_page_begin_row', '', ( $current_page_number - 1 ), $qmn_quiz_options, $qmn_quiz_questions );
			}
			$category_class      = '';
			$multicategories     = QSM_Questions::get_question_categories( $mlw_question->question_id );
			$question_categories = isset( $multicategories['category_tree'] ) && ! empty( $multicategories['category_tree'] ) ? array_keys( $multicategories['category_name'] ) : array();
			if ( ! empty( $question_categories ) ) {
				foreach ( $question_categories as $cat ) {
					$category_class .= ' category-section-id-c' . esc_attr( $cat );
				}
			}

			$question_id_list .= $mlw_question->question_id . 'Q';
			?>
			<div class="quiz_section qsm-question-wrapper question-type-<?php echo esc_attr( $mlw_question->question_type_new ); ?> <?php echo esc_attr( $animation_effect ); ?> question-section-id-<?php echo esc_attr( $mlw_question->question_id ); ?> slide<?php echo esc_attr( $mlw_qmn_section_count . ' ' . $category_class ); ?>">
				<?php
				$mlwQuizMasterNext->pluginHelper->display_question( $mlw_question->question_type_new, $mlw_question->question_id, $qmn_quiz_options );
				if ( 0 == $mlw_question->comments ) {
					?>
					<label class="qsm_accessibility_label" for="mlwComment<?php echo esc_attr( $mlw_question->question_id ); ?>"><?php echo esc_attr( "Comment" ); ?></label>
					<input type="text" class="mlw_qmn_question_comment" id="mlwComment<?php echo esc_attr( $mlw_question->question_id ); ?>" name="mlwComment<?php echo esc_attr( $mlw_question->question_id ); ?>" placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->comment_field_text, "quiz_comment_field_text-{$qmn_quiz_options->quiz_id}" ) ); ?>" onclick="qmnClearField(this)" /><br />
					<?php
				}
				if ( 2 == $mlw_question->comments ) {
					?>
					<label class="qsm_accessibility_label" for="mlwComment<?php echo esc_attr( $mlw_question->question_id ); ?>"><?php echo esc_attr( "Comment" ); ?></label>
					<textarea cols="70" rows="5" class="mlw_qmn_question_comment" id="mlwComment<?php echo esc_attr( $mlw_question->question_id ); ?>" name="mlwComment<?php echo esc_attr( $mlw_question->question_id ); ?>" placeholder="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->comment_field_text, "quiz_comment_field_text-{$qmn_quiz_options->quiz_id}" ) ); ?>" onclick="qmnClearField(this)"></textarea><br />
					<?php
				}
				// Checks if a hint is entered.
				if ( ! empty( $mlw_question->hints ) ) {
					$hint_data = wp_kses_post( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $mlw_question->hints, "hint-{$mlw_question->question_id}" ) );
					?>
					<div class="qsm-hint qsm_hint mlw_qmn_hint_link qsm_tooltip" title="<?php echo esc_attr( $hint_data );?>"><?php echo esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->hint_text, "quiz_hint_text-{$qmn_quiz_options->quiz_id}" ) ); ?></div><br /><br />
					<?php
				}
				?>
			</div><!-- .quiz_section -->
			<?php
			if ( 0 != $pagination_option ) {
				if ( 1 == $pagination_option || 0 == $pages_count % $pagination_option || count( $qmn_quiz_questions ) == $pages_count ) { // end of the row or last
					?>
					</div><!-- .qsm-auto-page-row -->
					<?php
				}
			}
			$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
			$pages_count++;
		}
		if ( $enable_pagination_quiz ) {
			?>
			<span class="pages_count" style="display: none;">
				<?php
				$text_c = esc_html__( '1 out of ', 'quiz-master-next' ) . $total_pagination;
				echo apply_filters( 'qsm_total_pages_count', $text_c, $pages_count, $total_pages_count );
				?>
			</span>
			<?php
		}
		?>
		<input type="hidden" name="qmn_question_list" value="<?php echo esc_attr( $question_id_list ); ?>" />
		<?php
	}

	/**
	 * Creates Display For Comment Section
	 *
	 * Generates the content for the comment section part of the quiz page
	 *
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options        The database row of the quiz.
	 * @param      array $qmn_array_for_variables The array of results for the quiz.
	 * @return     string The content for the comment section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_comment_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		global $mlwQuizMasterNext, $mlw_qmn_section_count;
		if ( 0 == $qmn_quiz_options->comment_section && "" !== $qmn_quiz_options->comment_section ) {
			$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
			?>
			<div class="quiz_section quiz_end qsm-auto-page-row qsm-quiz-comment-section slide <?php echo esc_attr( $mlw_qmn_section_count ); ?>" style="display:none">
			<?php
			$message_comments = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $qmn_quiz_options->message_comment, ENT_QUOTES ), "quiz_message_comment-{$qmn_quiz_options->quiz_id}" );
			$message_comments = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_comments ), $qmn_array_for_variables );
			?>
				<label for="mlwQuizComments" class="mlw_qmn_comment_section_text"><?php echo wp_kses_post( do_shortcode( $message_comments ) ); ?></label><br />
				<textarea cols="60" rows="10" id="mlwQuizComments" name="mlwQuizComments" class="qmn_comment_section"></textarea>
			</div>
			<?php
		}
	}

	/**
	 * Creates Display For End Section Of Quiz Page
	 *
	 * Generates the content for the end section of the quiz page
	 *
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options        The database row of the quiz.
	 * @param      array $qmn_array_for_variables The array of results for the quiz.
	 * @return     string The content for the end section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_end_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		global $mlwQuizMasterNext, $mlw_qmn_section_count;
		$section_display       = '';
		$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
		$pagination_option     = $qmn_quiz_options->pagination;

		do_action( 'mlw_qmn_end_quiz_section' );
		$qsm_d_none = 0 < intval( $pagination_option ) ? 'qsm-d-none' : '';
		if ( ! empty( $qmn_quiz_options->message_end_template ) || ( 1 === intval( $qmn_quiz_options->contact_info_location ) && ! empty( QSM_Contact_Manager::display_fields( $qmn_quiz_options ) ) ) ) {
			?>
			<br />
			<div class="qsm-auto-page-row quiz_section quiz_end <?php echo esc_attr( $qsm_d_none ); ?>">
				<?php
				// Legacy Code.
				if ( ! empty( $qmn_quiz_options->message_end_template ) ) {
					?>
					<span class='mlw_qmn_message_end'>
					<?php
						$message_end = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $qmn_quiz_options->message_end_template, ENT_QUOTES ), "quiz_message_end_template-{$qmn_quiz_options->quiz_id}" );
						$message_end = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message_end ), $qmn_array_for_variables );
						echo wp_kses_post( do_shortcode( $message_end ) );
					?>
					</span>
					<br /><br />
					<?php
				}
				if ( 1 === intval( $qmn_quiz_options->contact_info_location ) ) {
					echo QSM_Contact_Manager::display_fields( $qmn_quiz_options );
				}
				?>
				<?php if ( 0 === intval( $pagination_option ) ) : ?>
					<input type='submit' class='qsm-btn qsm-submit-btn qmn_btn' value="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->submit_button_text, "quiz_submit_button_text-{$qmn_quiz_options->quiz_id}" ) ); ?>" />
				<?php endif; ?>
			</div>
			<?php
		} else {
			?>
			<div class="qsm-auto-page-row quiz_section quiz_end empty_quiz_end <?php echo esc_attr( $qsm_d_none ); ?>" >
				<?php if ( ( ( ! empty( $qmn_quiz_options->randomness_order ) && 0 !== intval( $qmn_quiz_options->randomness_order ) ) || ( ! empty( $qmn_quiz_options->question_from_total ) && 0 !== intval( $qmn_quiz_options->question_from_total ) ) ) && ( empty( $qmn_quiz_options->pagination ) || 0 === intval( $qmn_quiz_options->pagination ) ) ) : ?>
					<input type="submit" class="qsm-btn qsm-submit-btn qmn_btn" value="<?php echo esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->submit_button_text, "quiz_submit_button_text-{$qmn_quiz_options->quiz_id}" ) ); ?>" />
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Generates Content Results Page
	 *
	 * Generates the content for the results page part of the shortcode
	 *
	 * @since  4.0.0
	 * @param  array $options The database row of the quiz.
	 * @param  array $data    The array of results for the quiz.
	 * @uses   QMNQuizManager:submit_results() Perform The Quiz/Survey Submission
	 * @return string The content for the results page section
	 */
	public function display_results( $options, $data ) {
		$quiz_id = ! empty( $_REQUEST['qmn_quiz_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['qmn_quiz_id'] ) ) : 0 ;
		if ( ! isset( $_REQUEST['qsm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['qsm_nonce'] ) ), 'qsm_submit_quiz_' . intval( $quiz_id ) ) ) {
			echo wp_json_encode(
				array(
					'display'       => __( 'Nonce Validation failed!', 'quiz-master-next' ),
					'redirect'      => false,
					'result_status' => array(
						'save_response' => false,
					),
				)
			);
			exit;
		}
		$result        = $this->submit_results( $options, $data );
		$results_array = $result;
		return $results_array['display'];
	}

	/**
	 * Validate Contact Fields
	 *
	 * Validates the contact fields in the request
	 *
	 * @since  10.0.3
	 * @param  array $contact_form The contact form fields
	 * @param  array $request      The request data
	 * @return bool               Whether the contact fields are valid
	 */
	public function qsm_validate_contact_fields( $contact_form, $request ) {
		$errors = [];

		if ( ! is_array( $contact_form ) ) {
			return;
		}

		foreach ( $contact_form as $index => $field ) {
			if ( 'true' === $field['enable'] ) {
				$contact_key = "contact_field_" . $index;
				$value = isset( $request[ $contact_key ] ) ? trim( $request[ $contact_key ] ) : '';

				if ( 'true' === $field['required'] && empty( $value ) ) {
					$errors[] = __( "Enter ", 'quiz-master-next' ) . $field['label'];
				}

				if ( ! empty( $field['minlength'] ) && strlen( $value ) < (int) $field['minlength'] ) {
					$errors[] = $field['label'] . __( " must be at least ", 'quiz-master-next' ) . $field['minlength'] . __( " characters long.", 'quiz-master-next' );
				}

				if ( ! empty( $field['maxlength'] ) && strlen( $value ) > (int) $field['maxlength'] ) {
					$errors[] = $field['label'] . __( " must be no more than ", 'quiz-master-next' ) . $field['maxlength'] . __( " characters long.", 'quiz-master-next' );
				}

				if ( 'email' === $field['type'] && ! empty( $value ) ) {
					if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
						$errors[] = __( "Email must be a valid e-mail.", 'quiz-master-next' );
					} else {
						$email_domain = substr( strrchr( $value, "@" ), 1 );

						if ( ! empty( $field['allowdomains'] ) ) {
							$allowed_domains = array_map( 'trim', explode( ',', $field['allowdomains'] ) );
							if ( ! in_array( $email_domain, $allowed_domains, true ) ) {
								$errors[] = __( "Email must be from an allowed domain (", 'quiz-master-next' ) . $field['allowdomains'] . ").";
							}
						}

						if ( ! empty( $field['blockdomains'] ) ) {
							$blocked_domains = array_map( 'trim', explode( ',', $field['blockdomains'] ) );
							if ( in_array( $email_domain, $blocked_domains, true ) ) {
								$errors[] = __( "Email cannot be from a blocked domain (", 'quiz-master-next' ) . $field['blockdomains'] . ").";
							}
						}
					}
				}
			}
		}
		return empty( $errors ) ? 1 : "<strong>" . __( 'There was an error with your submission:', 'quiz-master-next' ) . "</strong><ul style='left: -20px; position: relative;'><li>" . implode( "</li><li>", $errors ) . "</li></ul>";
	}

	/**
	 * Calls the results page from ajax
	 *
	 * @since  4.6.0
	 * @uses   QMNQuizManager:submit_results() Perform The Quiz/Survey Submission
	 * @return string The content for the results page section
	 */
	public function ajax_submit_results() {
		$quiz_id = ! empty( $_REQUEST['qmn_quiz_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['qmn_quiz_id'] ) ) : 0 ;
		if ( ! isset( $_REQUEST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ), 'qsm_submit_quiz_' . intval( $quiz_id ) ) ) {
			echo wp_json_encode(
				array(
					'display'       => apply_filters( 'qsm_nonce_failed_message', htmlspecialchars_decode( 'Nonce Validation failed!' ) ),
					'redirect'      => false,
					'result_status' => array(
						'save_response' => false,
					),
				)
			);
			exit;
		}

		global $qmn_allowed_visit, $mlwQuizMasterNext, $wpdb;

		$qmn_allowed_visit = true;
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
		$options    = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
		$post_ids = get_posts(array(
			'post_type'   => 'qsm_quiz', // Replace with the post type you're working with
			'meta_key'    => 'quiz_id',
			'meta_value'  => intval( $quiz_id ),
			'fields'      => 'ids',
			'numberposts' => 1,
		));
		$post_status = false;
		if ( ! empty( $post_ids[0] ) ) {
			$post_status = get_post_status( $post_ids[0] );
		}

		if ( is_null( $options ) || 1 == $options->deleted ) {
			echo wp_json_encode(
				array(
					'display'       => __( 'This quiz is no longer available.', 'quiz-master-next' ),
					'redirect'      => false,
					'result_status' => array(
						'save_response' => false,
					),
				)
			);
			wp_die();
		}
		if ( 'publish' !== $post_status ) {
			echo wp_json_encode(
				array(
					'display'       => __( 'This quiz is in draft mode and is not recording your responses. Please publish the quiz to start recording your responses.', 'quiz-master-next' ),
					'redirect'      => false,
					'result_status' => array(
						'save_response' => false,
					),
				)
			);
			wp_die();
		}

		$qsm_option = isset( $options->quiz_settings ) ? maybe_unserialize( $options->quiz_settings ) : array();
		$qsm_option = array_map( 'maybe_unserialize', $qsm_option );
		$dateStr    = $qsm_option['quiz_options']['scheduled_time_end'];
		$timezone   = isset( $_POST['currentuserTimeZone'] ) ? sanitize_text_field( wp_unslash( $_POST['currentuserTimeZone'] ) ) : '';
		$dtUtcDate  = strtotime( $dateStr . ' ' . $timezone );
		$enable_server_side_validation = isset( $qsm_option['quiz_options']['enable_server_side_validation'] ) ? $qsm_option['quiz_options']['enable_server_side_validation'] : 0;
		if ( 1 == $enable_server_side_validation ) {
			$missing_contact_fields = $this->qsm_validate_contact_fields( $qsm_option['contact_form'], $_REQUEST );
			if ( 1 !== $missing_contact_fields ) {
				echo wp_json_encode(
					array(
						'display'       => '<div class="qsm-result-page-warning">' . wp_kses_post( $missing_contact_fields ) . '</div>',
						'redirect'      => false,
						'result_status' => array(
							'save_response' => false,
						),
					)
				);
				wp_die();
			}
		}

		if ( isset($qsm_option['quiz_options']['not_allow_after_expired_time']) && '1' === $qsm_option['quiz_options']['not_allow_after_expired_time'] && isset( $_POST['currentuserTime'] ) && sanitize_text_field( wp_unslash( $_POST['currentuserTime'] ) ) > $dtUtcDate && ! empty($dateStr) ) {
			echo wp_json_encode(
				array(
					'display'       => __( 'Quiz Expired!', 'quiz-master-next' ),
					'redirect'      => false,
					'result_status' => array(
						'save_response' => false,
					),
				)
			);
			wp_die();
		}
		if ( 0 != $options->limit_total_entries ) {
			$mlw_qmn_entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(quiz_id) FROM {$wpdb->prefix}mlw_results WHERE deleted=0 AND quiz_id=%d", $options->quiz_id ) );
			if ( $mlw_qmn_entries_count >= $options->limit_total_entries ) {
				echo wp_json_encode(
					array(
						'display'       => $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->limit_total_entries_text, ENT_QUOTES ), "quiz_limit_total_entries_text-{$options->quiz_id}" ),
						'redirect'      => false,
						'result_status' => array(
							'save_response' => false,
						),
					)
				);
				wp_die();
			}
		}
		if ( 0 != $options->total_user_tries ) {

			// Prepares the variables
			$mlw_qmn_user_try_count = 0;

			// Checks if the user is logged in. If so, check by user id. If not, check by IP.
			if ( is_user_logged_in() ) {
				$current_user           = wp_get_current_user();
				$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE user=%d AND deleted=0 AND quiz_id=%d", $current_user->ID, $options->quiz_id ) );
			} else {
				$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE user_ip=%s AND deleted=0 AND quiz_id=%d", $this->get_user_ip(), $options->quiz_id ) );
			}

			// If user has already reached the limit for this quiz
			if ( $mlw_qmn_user_try_count >= $options->total_user_tries ) {
				echo wp_json_encode(
					array(
						'display'       => $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->total_user_tries_text, ENT_QUOTES ), "quiz_total_user_tries_text-{$options->quiz_id}" ),
						'redirect'      => false,
						'result_status' => array(
							'save_response' => false,
						),
					)
				);
				wp_die();
			}
		}
		$data      = array(
			'quiz_id'         => $options->quiz_id,
			'quiz_name'       => $options->quiz_name,
			'quiz_system'     => $options->system,
			'quiz_payment_id' => isset( $_POST['main_payment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['main_payment_id'] ) ) : '',
		);
		echo wp_json_encode( $this->submit_results( $options, $data ) );
		wp_die();
	}

	/**
	 * @version 6.3.2
	 * Show quiz on button click
	 */
	public function qsm_get_quiz_to_reload() {
		$quiz_id = isset( $_POST['quiz_id'] ) ? intval( $_POST['quiz_id'] ) : 0;
		echo do_shortcode( '[qsm quiz="' . $quiz_id . '"]' );
		exit;
	}

	/**
	 * Add quiz result
	 *
	 * @since  9.0.2
	 * @param  array $data required data ( i.e. qmn_array_for_variables, results_array, unique_id, http_referer, form_type )  for adding quiz result
	 *
	 * @return boolean results added or not
	 */
	public function add_quiz_results( $data, $action = '' ) {
		global $wpdb;
		if ( empty( $wpdb ) || empty( $data['qmn_array_for_variables'] ) || empty( $data['results_array'] ) || empty( $data['unique_id'] ) || ! isset( $data['http_referer'] ) || ! isset( $data['form_type'] ) ) {
			return false;
		}

		// Inserts the responses in the database.
		$table_name = $wpdb->prefix . 'mlw_results';

		// Temporarily suppress error reporting
		$wpdb->suppress_errors();

		try {
			if ( empty( $data['page_name'] ) ) {
				$data['page_name'] = url_to_postid( $data['http_referer'] ) ? get_the_title( url_to_postid( $data['http_referer'] ) ) : '';
			}
			// Check if unique_id already exists
			$existing_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT result_id FROM $table_name WHERE unique_id = %s LIMIT 1",
					$data['unique_id']
				)
			);

			$record = array(
				'quiz_id'         => $data['qmn_array_for_variables']['quiz_id'],
				'quiz_name'       => $data['qmn_array_for_variables']['quiz_name'],
				'quiz_system'     => $data['qmn_array_for_variables']['quiz_system'],
				'point_score'     => $data['qmn_array_for_variables']['total_points'],
				'correct_score'   => $data['qmn_array_for_variables']['total_score'],
				'correct'         => $data['qmn_array_for_variables']['total_correct'],
				'total'           => $data['qmn_array_for_variables']['total_questions'],
				'name'            => $data['qmn_array_for_variables']['user_name'],
				'business'        => $data['qmn_array_for_variables']['user_business'],
				'email'           => $data['qmn_array_for_variables']['user_email'],
				'phone'           => $data['qmn_array_for_variables']['user_phone'],
				'user'            => $data['qmn_array_for_variables']['user_id'],
				'user_ip'         => $data['qmn_array_for_variables']['user_ip'],
				'time_taken'      => $data['qmn_array_for_variables']['time_taken'],
				'time_taken_real' => gmdate( 'Y-m-d H:i:s', strtotime( $data['qmn_array_for_variables']['time_taken'] ) ),
				'quiz_results'    => maybe_serialize( $data['results_array'] ),
				'deleted'         => ( isset( $data['deleted'] ) && 1 === intval( $data['deleted'] ) ) ? 1 : 0,
				'unique_id'       => $data['unique_id'],
				'form_type'       => $data['form_type'],
				'page_url'        => $data['http_referer'],
				'page_name'       => sanitize_text_field( $data['page_name'] ),
			);

			$formats = array(
				'%d',  // quiz_id
				'%s',  // quiz_name
				'%d',  // quiz_system
				'%f',  // point_score
				'%d',  // correct_score
				'%d',  // correct
				'%d',  // total
				'%s',  // name
				'%s',  // business
				'%s',  // email
				'%s',  // phone
				'%d',  // user
				'%s',  // user_ip
				'%s',  // time_taken
				'%s',  // time_taken_real
				'%s',  // quiz_results
				'%d',  // deleted
				'%s',  // unique_id
				'%d',  // form_type
				'%s',  // page_url
				'%s',  // page_name
			);

			if ( 'resubmit' === $action && $existing_id ) {
				$new_unique_id = uniqid();
				$record['unique_id'] = $new_unique_id;
				$res = $wpdb->insert(
					$table_name,
					$record,
					$formats
				);
			} else {
				$res = $wpdb->insert(
					$table_name,
					$record,
					$formats
				);
			}
			if ( false === $res ) {
				// Throw exception
				throw new Exception( 'Database insert failed.' );
			}
			// If insert is successful, return response
			return $res;
		} catch ( Exception $e ) {
			return false;
		}

		return false;
	}

	/**
	 * Perform The Quiz/Survey Submission
	 *
	 * Prepares and save the results, prepares and send emails, prepare results page
	 *
	 * @since  4.6.0
	 * @param  array $qmn_quiz_options        The database row of the quiz.
	 * @param  array $qmn_array_for_variables The array of results for the quiz.
	 * @uses   QMNQuizManager:check_answers() Creates display for beginning section
	 * @uses   QMNQuizManager:check_comment_section() Creates display for questions
	 * @uses   QMNQuizManager:display_results_text() Creates display for end section
	 * @uses   QMNQuizManager:display_social() Creates display for comment section
	 * @uses   QMNQuizManager:send_user_email() Creates display for end section
	 * @uses   QMNQuizManager:send_admin_email() Creates display for end section
	 * @return string The content for the results page section
	 */
	public function submit_results( $qmn_quiz_options, $qmn_array_for_variables ) {
		global $wpdb, $qmn_allowed_visit, $mlwQuizMasterNext;
		$result_display = '';
		do_action( 'qsm_submit_results_before', $qmn_quiz_options, $qmn_array_for_variables );
		$qmn_array_for_variables['user_ip'] = $this->get_user_ip();

		$result_display = apply_filters( 'qmn_begin_results', $result_display, $qmn_quiz_options, $qmn_array_for_variables );
		if ( ! $qmn_allowed_visit ) {
			return $result_display;
		}
		// Add form type for new quiz system 7.0.0
		$qmn_array_for_variables['form_type'] = isset( $qmn_quiz_options->form_type ) ? $qmn_quiz_options->form_type : 0;
		// Gathers contact information.
		$qmn_array_for_variables['user_name']     = 'None';
		$qmn_array_for_variables['user_business'] = 'None';
		$qmn_array_for_variables['user_email']    = 'None';
		$qmn_array_for_variables['user_phone']    = 'None';
		$contact_responses                        = QSM_Contact_Manager::process_fields( $qmn_quiz_options );
		foreach ( $contact_responses as $field ) {
			if ( isset( $field['use'] ) ) {
				if ( 'name' === $field['use'] ) {
					$qmn_array_for_variables['user_name'] = $field['value'];
				}
				elseif ( 'comp' === $field['use'] ) {
					$qmn_array_for_variables['user_business'] = $field['value'];
				}
				elseif ( 'email' === $field['use'] ) {
					$qmn_array_for_variables['user_email'] = $field['value'];
				}
				elseif ( 'phone' === $field['use'] ) {
					$qmn_array_for_variables['user_phone'] = $field['value'];
				}
			}
		}

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			if ( 'None' === $qmn_array_for_variables['user_email'] ) {
				$qmn_array_for_variables['user_email'] = $current_user->user_email;
			}

			if ( 'None' === $qmn_array_for_variables['user_name'] ) {
				$qmn_array_for_variables['user_name'] = $current_user->display_name;
			}
		}

		$mlw_qmn_pagetime                      = isset( $_POST['pagetime'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['pagetime'] ) ) : array();
		$mlw_qmn_timer                         = isset( $_POST['timer'] ) ? intval( $_POST['timer'] ) : 0;
		$mlw_qmn_timer_ms                      = isset( $_POST['timer_ms'] ) ? intval( $_POST['timer_ms'] ) : 0;
		$mlw_quiz_start_date                   = isset( $_POST['quiz_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['quiz_start_date'] ) ) : '';
		$qmn_array_for_variables['user_id']    = get_current_user_id();
		$qmn_array_for_variables['timer']      = $mlw_qmn_timer;
		$qmn_array_for_variables['timer_ms']   = $mlw_qmn_timer_ms;
		$qmn_array_for_variables['time_taken'] = current_time( 'h:i:s A m/d/Y' );
		$qmn_array_for_variables['contact']    = $contact_responses;
		$qmn_array_for_variables['quiz_start_date']    = $mlw_quiz_start_date;
		$hidden_questions                      = array();
		if ( isset( $_POST['qsm_hidden_questions'] ) ) {
			$hidden_questions = sanitize_text_field( wp_unslash( $_POST['qsm_hidden_questions'] ) );
			$hidden_questions = json_decode( $hidden_questions, true );
		}
		$qmn_array_for_variables['hidden_questions'] = $hidden_questions;
		$qmn_array_for_variables                     = apply_filters( 'qsm_result_variables', $qmn_array_for_variables );
		$error_details = "";
		if ( ! isset( $_POST['mlw_code_captcha'] ) || ( isset( $_POST['mlw_code_captcha'], $_POST['mlw_user_captcha'] ) && sanitize_text_field( wp_unslash( $_POST['mlw_user_captcha'] ) ) == sanitize_text_field( wp_unslash( $_POST['mlw_code_captcha'] ) ) ) ) {
			$qsm_check_answers_return            = $this->check_answers( $qmn_quiz_options, $qmn_array_for_variables );
			$qmn_array_for_variables             = array_merge( $qmn_array_for_variables, $qsm_check_answers_return );
			$result_display                      = apply_filters( 'qmn_after_check_answers', $result_display, $qmn_quiz_options, $qmn_array_for_variables );
			$qmn_array_for_variables['comments'] = $this->check_comment_section( $qmn_quiz_options, $qmn_array_for_variables );
			$result_display                      = apply_filters( 'qmn_after_check_comments', $result_display, $qmn_quiz_options, $qmn_array_for_variables );
			$unique_id                           = ! empty( $_REQUEST['qsm_unique_key'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['qsm_unique_key'] ) ) : uniqid();
			$results_id                          = 0;
			// Creates our results array.
			$results_array = array(
				intval( $qmn_array_for_variables['timer'] ),
				$qmn_array_for_variables['question_answers_array'],
				htmlspecialchars( stripslashes( $qmn_array_for_variables['comments'] ), ENT_QUOTES ),
				'contact'  => $contact_responses,
				'timer_ms' => intval( $qmn_array_for_variables['timer_ms'] ),
				'pagetime' => $mlw_qmn_pagetime,
			);
			$results_array = apply_filters( 'qsm_results_array', $results_array, $qmn_array_for_variables );
			if ( isset( $results_array['parameters'] ) ) {
				$qmn_array_for_variables['parameters'] = $results_array['parameters'];
			}
			$results_array['hidden_questions']          = $qmn_array_for_variables['hidden_questions'];
			$results_array['total_possible_points']     = $qmn_array_for_variables['total_possible_points'];
			$results_array['total_attempted_questions'] = $qmn_array_for_variables['total_attempted_questions'];
			$results_array['minimum_possible_points']   = $qmn_array_for_variables['minimum_possible_points'];
			$results_array['quiz_start_date']           = $qmn_array_for_variables['quiz_start_date'];
			$qmn_array_for_variables = apply_filters( 'qsm_array_for_variables_before_db_query', $qmn_array_for_variables );
			// If the store responses in database option is set to Yes.
			if ( 1 === intval( $qmn_quiz_options->store_responses ) ) {
				// Inserts the responses in the database.
				$table_name = $wpdb->prefix . 'mlw_results';
				if ( isset( $_POST['update_result'] ) && ! empty( $_POST['update_result'] ) ) {
					$results_id     = sanitize_text_field( wp_unslash( $_POST['update_result'] ) );
					$results_update = $wpdb->update(
						$table_name,
						array(
							'point_score'     => $qmn_array_for_variables['total_points'],
							'correct_score'   => $qmn_array_for_variables['total_score'],
							'correct'         => $qmn_array_for_variables['total_correct'],
							'total'           => $qmn_array_for_variables['total_questions'],
							'user_ip'         => $qmn_array_for_variables['user_ip'],
							'time_taken'      => $qmn_array_for_variables['time_taken'],
							'time_taken_real' => gmdate( 'Y-m-d H:i:s', strtotime( $qmn_array_for_variables['time_taken'] ) ),
							'quiz_results'    => maybe_serialize( $results_array ),
						),
						array( 'result_id' => $results_id )
					);
					if ( false === $results_update ) {
						$error_details = $wpdb->last_error;
						$mlwQuizMasterNext->log_manager->add( 'Error 0001', $error_details . ' from ' . $wpdb->last_query, 0, 'error' );
					}
				} else {
					$http_referer   = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
					if ( 254 < strlen($http_referer) ) {
						$results_array['page_url'] = $http_referer;
						$http_referer = substr($http_referer, 0, 254);
					}
					$insert_data = array(
						'qmn_array_for_variables' => $qmn_array_for_variables,
						'results_array'           => $results_array,
						'unique_id'               => $unique_id,
						'form_type'               => isset( $qmn_quiz_options->form_type ) ? $qmn_quiz_options->form_type : 0,
						'http_referer'            => $http_referer,
					);
					$results_insert = $this->add_quiz_results( $insert_data );
					$results_id     = $wpdb->insert_id;
					if ( false === $results_insert ) {
						$quiz_submitted_data = qsm_printTableRows($qmn_array_for_variables);
						$error_details = $wpdb->last_error;
						$mlwQuizMasterNext->log_manager->add(
							__('Error 0001 submission failed - Quiz ID:', 'quiz-master-next') . $qmn_array_for_variables['quiz_id'],
							'<b>Quiz data:</b> ' . $quiz_submitted_data . ' <br/><b>Quiz answers:</b> ' . maybe_serialize( $results_array ) . '<br><b>Error:</b>' . $error_details . ' from ' . $wpdb->last_query,
							0,
							'error',
							array(
								'result_insert_data' => maybe_serialize( $insert_data ),
							)
						);
						$mlwQuizMasterNext->audit_manager->new_audit( 'Submit Quiz by ' . $qmn_array_for_variables['user_name'] .' - ' .$qmn_array_for_variables['user_ip'], $qmn_array_for_variables['quiz_id'], wp_json_encode( $qmn_array_for_variables ) );
						$result_display .= '<div class="qsm-result-page-warning">' . __( "Sorry, your submission was not successful. Please contact the website administrator.", "quiz-master-next" ) . '</div>';
					}
				}
			}
			$qmn_array_for_variables['response_saved']   = isset( $results_insert ) ? $results_insert : false;
			$qmn_array_for_variables['result_id']        = $results_id;
			$qmn_array_for_variables['result_unique_id'] = $unique_id;
			setcookie("question_ids_".$qmn_array_for_variables['quiz_id'], "", time() - 36000, "/");
			$qmn_array_for_variables = apply_filters( 'qsm_array_for_variables_after_db_query', $qmn_array_for_variables, $qmn_quiz_options );
			// Converts date to the preferred format
			$qmn_array_for_variables = $mlwQuizMasterNext->pluginHelper->convert_to_preferred_date_format( $qmn_array_for_variables );

			// Determines redirect/results page.
			$results_pages   = $this->display_results_text( $qmn_quiz_options, $qmn_array_for_variables );
			if ( 1 === intval( $qmn_quiz_options->store_responses ) && ! $qmn_array_for_variables['response_saved'] ) {
				$result_display .= '<div class="qsm-result-page-warning">' . __("Sorry, there's an issue in saving your responses. Please let the website admin know about it.", "quiz-master-next") . '</div>';
			}
			$result_display .= $results_pages['display'];
			$result_display  = apply_filters( 'qmn_after_results_text', $result_display, $qmn_quiz_options, $qmn_array_for_variables );

			$result_display .= $this->display_social( $qmn_quiz_options, $qmn_array_for_variables );
			$result_display  = apply_filters( 'qmn_after_social_media', $result_display, $qmn_quiz_options, $qmn_array_for_variables );
			$qmn_quiz_options  = apply_filters( 'qmn_retake_quiz_button_before', $qmn_quiz_options );
			if ( 1 == $qmn_quiz_options->enable_retake_quiz_button ) {
				$result_display .= '<form method="POST">';
				$result_display .= '<input type="hidden" value="' . $qmn_array_for_variables['quiz_id'] . '" name="qsm_retake_quiz_id" />';
				$result_display .= '<input type="submit" value="' . esc_attr( $mlwQuizMasterNext->pluginHelper->qsm_language_support( apply_filters( 'qsm_retake_quiz_text', $qmn_quiz_options->retake_quiz_button_text ), "quiz_retake_quiz_button_text-{$qmn_quiz_options->quiz_id}" ) ) . '" name="qsm_retake_button" class="qsm-btn qsm_retake_button qmn_btn" id="qsm_retake_button" />';
				$result_display .= '</form>';
			}

			/*
			* Update the option `qmn_quiz_taken_cnt` value by 1 each time
			* whenever the record inserted into the required table.
			*/
			if ( isset( $results_insert ) ) {
				$rec_inserted = intval( get_option( 'qmn_quiz_taken_cnt' ) );
				if ( 1000 > $rec_inserted ) {
					if ( ! $rec_inserted ) {
						update_option( 'qmn_quiz_taken_cnt', 1, true );
					} else {
						update_option( 'qmn_quiz_taken_cnt', ++$rec_inserted );
					}
				}
			}

			// Hook is fired after the responses are submitted. Passes responses, result ID, quiz settings, and response data.
			do_action( 'qsm_quiz_submitted', $results_array, $results_id, $qmn_quiz_options, $qmn_array_for_variables );

			$qmn_array_for_variables = apply_filters( 'qmn_filter_email_content', $qmn_array_for_variables, $results_id );

			$qmn_global_settings           = (array) get_option( 'qmn-settings' );
			$background_quiz_email_process = isset( $qmn_global_settings['background_quiz_email_process'] ) ? esc_attr( $qmn_global_settings['background_quiz_email_process'] ) : '1';
			if ( 1 === intval( $qmn_quiz_options->send_email ) ) {
				$qmn_array_for_variables['quiz_settings']   = isset( $qmn_quiz_options->quiz_settings ) ? maybe_unserialize( $qmn_quiz_options->quiz_settings ) : array();
				$qmn_array_for_variables['email_processed'] = 'yes';
				$transient_id = 'response_'.wp_rand(10000,99999);
				set_transient( $transient_id, maybe_serialize( $qmn_array_for_variables ), 6000 );
				if ( 1 == $background_quiz_email_process ) {
					// Send the emails in background.
					$this->qsm_background_email->data(
						array(
							'name'         => 'send_emails',
							'transient_id' => $transient_id,
						)
					)->dispatch();
				} else {
					// Sends the emails.
					QSM_Emails::send_emails( $transient_id );
				}
			}
			/**
			 * Filters for filtering the results text after emails are sent.
			 *
			 * @deprecated 6.2.0 There's no reason to use these over the actions
			 * in the QSM_Results_Pages class or the other filters in this function.
			 */
			$result_display = apply_filters( 'qmn_after_send_user_email', $result_display, $qmn_quiz_options, $qmn_array_for_variables );
			$result_display = apply_filters( 'qmn_after_send_admin_email', $result_display, $qmn_quiz_options, $qmn_array_for_variables );

			// Last time to filter the HTML results page.
			$result_display = apply_filters( 'qmn_end_results', $result_display, $qmn_quiz_options, $qmn_array_for_variables );

			// Legacy Code.
			do_action( 'mlw_qmn_load_results_page', $results_id, $qmn_quiz_options->quiz_settings );
		} else {
			$result_display .= apply_filters( 'qmn_captcha_varification_failed_msg', __( 'Captcha verification failed.', 'quiz-master-next' ), $qmn_quiz_options, $qmn_array_for_variables );
		}

		$result_display = str_replace( '%FB_RESULT_ID%', $unique_id, $result_display );

		// Prepares data to be sent back to front-end.
		$return_array = array(
			'quizExpired'   => false,
			'display'       => $result_display,
			'redirect'      => apply_filters( 'mlw_qmn_template_variable_results_page', $results_pages['redirect'], $qmn_array_for_variables ),
			'result_status' => array(
				'save_response' => $qmn_array_for_variables['response_saved'],
				'id'            => $qmn_array_for_variables['result_unique_id'],
				'error_details' => substr( $error_details, 0, 15 ),
			),
		);
		$return_array = apply_filters( 'qsm_submit_results_return_array', $return_array, $qmn_array_for_variables );
		return $return_array;
	}

	/**
	 * Scores User Answers
	 *
	 * Calculates the users scores for the quiz
	 *
	 * @since  4.0.0
	 * @param  array $options   The database row of the quiz
	 * @param  array $quiz_data The array of results for the quiz
	 * @uses   QMNPluginHelper:display_review() Scores the question
	 * @return array The results of the user's score
	 */
	public static function check_answers( $options, $quiz_data ) {
		global $mlwQuizMasterNext;
		$new_questions = array();
		// Load the pages and questions
		$pages     = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
		$questions = QSM_Questions::load_questions_by_pages( $options->quiz_id );
		if ( ( 1 == $options->randomness_order || 2 == $options->randomness_order ) && empty( $options->question_per_category ) && isset($_COOKIE[ 'question_ids_'.$options->quiz_id ]) ) {
			$question_sql = sanitize_text_field( wp_unslash( $_COOKIE[ 'question_ids_'.$options->quiz_id ] ) );
			$question_array = explode(",",$question_sql);
			foreach ( $question_array as $key ) {
				if ( isset( $questions[ $key ] ) ) {
					$new_questions[ $key ] = $questions[ $key ];
				}
			}
			$questions = $new_questions;
			$pages = array( $question_array );
		}
		// Retrieve data from submission
	    $total_questions = isset( $_POST['total_questions'] ) ? intval( $_POST['total_questions'] ) : 0;
		$question_list   = array();
		if ( isset( $_POST['qmn_question_list'] ) ) {
			$qmn_question_list = sanitize_text_field( wp_unslash( $_POST['qmn_question_list'] ) );
			$question_list     = explode( 'Q', $qmn_question_list );
		}

		// Prepare variables
		$points_earned           = 0;
		$total_correct           = 0;
		$total_score             = 0;
		$user_answer             = '';
		$correct_answer          = '';
		$correct_status          = 'incorrect';
		$answer_points           = 0;
		$question_data           = array();
		$hidden_questions        = array();
		$total_possible_points   = 0;
		$attempted_question      = 0;
		$minimum_possible_points = 0;
		// Question types to calculate result on
		$result_question_types = array(
			0, // Multiple Choice
			1, // Horizontal Multiple Choice
			2, // Drop Down
			4, // Multiple Response
			10, // Horizontal Multiple Response
			12, // Date
			3, // Small Open Answer
			5, // Large Open Answer
			7, // Number
			14, // Fill In The Blank
			13, // Polar.
		);

		// Advance Question types filter
		$result_question_types = apply_filters( 'qsm_result_question_types', $result_question_types );

		// If deprecated pagination setting is not used, use new system...
		if ( 0 == $options->question_from_total && 0 !== count( $pages ) ) {
			// Cycle through each page in quiz.
			foreach ( $pages as $page ) {
				// Cycle through each question on a page
				foreach ( $page as $page_question_id ) {
					// Cycle through each question that appeared to the user
					foreach ( $question_list as $question_id ) {
						// When the questions are the same...
						if ( $page_question_id == $question_id ) {
							global $mlwQuizMasterNext;
							$case_sensitive = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'case_sensitive' );
							$question          = $questions[ $page_question_id ];
							$question_type_new = $question['question_type_new'];
							// Ignore non points questions from result.
							$hidden_questions = is_array( $quiz_data['hidden_questions'] ) ? $quiz_data['hidden_questions'] : array();

							// Reset question-specific variables.
							$user_answer    = '';
							$correct_answer = '';
							$correct_status = 'incorrect';
							$answer_points  = 0;

							// Get maximum and minimum points for the quiz.
							if ( ! in_array( intval( $question_id ), $hidden_questions, true ) ) {
								$max_min_result           = self::qsm_max_min_points( $options, $question );
								$total_possible_points   += $max_min_result['max_point'];
								$minimum_possible_points += $max_min_result['min_point'];
							}

							// Send question to our grading function
							$results_array = $mlwQuizMasterNext->pluginHelper->display_review( $question['question_type_new'], $question['question_id'] );
							$results_array = apply_filters( 'qmn_results_array', $results_array, $question );
							// If question was graded correctly.
							if ( ! isset( $results_array['null_review'] ) ) {
								if ( in_array( intval( $question_type_new ), $result_question_types, true ) && ! in_array( intval( $question_id ), $hidden_questions, true ) ) {
									$points_earned += $results_array['points'] ? $results_array['points'] : 0;
									$answer_points += $results_array['points'] ? $results_array['points'] : 0;
								}

								// If the user's answer was correct
								if ( ! empty( $results_array['correct'] ) && 'correct' == $results_array['correct'] && in_array( intval( $question_type_new ), $result_question_types, true ) && ! in_array( intval( $question_id ), $hidden_questions, true ) ) {
									$total_correct += 1;
									$correct_status = 'correct';
								}
								$user_answer       = $results_array['user_text'];
								$correct_answer    = $results_array['correct_text'];
								$user_compare_text = isset( $results_array['user_compare_text'] ) ? $results_array['user_compare_text'] : '';

								if ( '' !== trim( $user_answer ) ) {
									$attempted_question++;
								}

								// If a comment was submitted
								if ( isset( $_POST[ 'mlwComment' . $question['question_id'] ] ) ) {
									$comment = htmlspecialchars( sanitize_textarea_field( wp_unslash( $_POST[ 'mlwComment' . $question['question_id'] ] ) ), ENT_QUOTES );
								} else {
									$comment = '';
								}

								// Get text for question
								$question_text = $question['question_name'];
								if ( isset( $results_array['question_text'] ) ) {
									$question_text = $results_array['question_text'];
								}

								$user_answer_array    = isset( $results_array['user_answer'] ) ? $results_array['user_answer'] : array();
								$correct_answer_array = isset( $results_array['correct_answer'] ) ? $results_array['correct_answer'] : array();

								// Save question data into new array in our array
								$question_data[] = apply_filters(
									'qmn_answer_array',
									array(
										$question_text,
										htmlspecialchars( $user_answer, ENT_QUOTES ),
										htmlspecialchars( $correct_answer, ENT_QUOTES ),
										$comment,
										'user_answer'     => $user_answer_array,
										'correct_answer'  => $correct_answer_array,
										'correct'         => $correct_status,
										'id'              => $question['question_id'],
										'points'          => $answer_points,
										'category'        => $question['category'],
										'multicategories' => $question['multicategories'],
										'question_type'   => $question['question_type_new'],
										'question_title'  => isset( $question['settings']['question_title'] ) ? $question['settings']['question_title'] : '',
										'user_compare_text' => $user_compare_text,
										'case_sensitive'  => $case_sensitive,
										'answer_limit_keys' => isset( $results_array['answer_limit_keys'] ) ? $results_array['answer_limit_keys'] : '',
									),
									$options,
									$quiz_data
								);
							}
							break;
						}
					}
				}
			}
		} else {
			// Cycle through each page in quiz.
			foreach ( $questions as $question ) {

				// Cycle through each question that appeared to the user.
				foreach ( $question_list as $question_id ) {

					// When the questions are the same...
					if ( $question['question_id'] == $question_id ) {
						global $mlwQuizMasterNext;
						$case_sensitive = $mlwQuizMasterNext->pluginHelper->get_question_setting( $question_id, 'case_sensitive' );
						// Reset question-specific variables.
						$user_answer    = '';
						$correct_answer = '';
						$correct_status = 'incorrect';
						$answer_points  = 0;

						// Get maximum and minimum points for the quiz.
						$max_min_result           = self::qsm_max_min_points( $options, $question );
						$total_possible_points   += $max_min_result['max_point'];
						$minimum_possible_points += $max_min_result['min_point'];

						// Send question to our grading function
						$results_array = $mlwQuizMasterNext->pluginHelper->display_review( $question['question_type_new'], $question['question_id'] );
						$results_array = apply_filters( 'qmn_results_array', $results_array, $question );
						// If question was graded correctly.
						if ( ! isset( $results_array['null_review'] ) ) {
							$points_earned += isset($results_array['points']) ? (float)$results_array['points'] : 0;
							$answer_points += isset($results_array['points']) ? (float)$results_array['points'] : 0;
							// If the user's answer was correct.
							if ( isset( $results_array['correct'] ) && ( 'correct' == $results_array['correct'] ) ) {
								$total_correct += 1;
								$correct_status = 'correct';
							}
							$user_answer       = $results_array['user_text'];
							$correct_answer    = $results_array['correct_text'];
							$user_compare_text = isset( $results_array['user_compare_text'] ) ? $results_array['user_compare_text'] : '';
							if ( '' !== trim( $user_answer ) ) {
								$attempted_question++;
							}
							// If a comment was submitted.
							if ( isset( $_POST[ 'mlwComment' . $question['question_id'] ] ) ) {
								$comment = htmlspecialchars( sanitize_textarea_field( wp_unslash( $_POST[ 'mlwComment' . $question['question_id'] ] ) ), ENT_QUOTES );
							} else {
								$comment = '';
							}

							// Get text for question
							$question_text = $question['question_name'];
							if ( isset( $results_array['question_text'] ) ) {
								$question_text = $results_array['question_text'];
							}
							$user_answer_array    = isset( $results_array['user_answer'] ) && is_array( $results_array['user_answer'] ) ? $results_array['user_answer'] : array();
							$correct_answer_array = isset( $results_array['correct_answer'] ) && is_array( $results_array['correct_answer'] ) ? $results_array['correct_answer'] : array();

							// Save question data into new array in our array
							$question_data[] = apply_filters(
								'qmn_answer_array',
								array(
									$mlwQuizMasterNext->pluginHelper->qsm_language_support( $question_text, "question-description-{$question_id}", "QSM Questions" ),
									htmlspecialchars( $user_answer, ENT_QUOTES ),
									$mlwQuizMasterNext->pluginHelper->qsm_language_support( $correct_answer, 'answer-' . $correct_answer, 'QSM Answers' ),
									$comment,
									'user_answer'       => $user_answer_array,
									'correct_answer'    => $correct_answer_array,
									'correct'           => $correct_status,
									'id'                => $question['question_id'],
									'points'            => $answer_points,
									'category'          => $question['category'],
									'multicategories'   => $question['multicategories'],
									'question_type'     => $question['question_type_new'],
									'question_title'    => isset( $question['settings']['question_title'] ) ? $mlwQuizMasterNext->pluginHelper->qsm_language_support( $question['settings']['question_title'], "Question-{$question_id}", "QSM Questions") : '',
									'user_compare_text' => $user_compare_text,
									'case_sensitive'    => $case_sensitive,
									'answer_limit_keys' => isset( $results_array['answer_limit_keys'] ) ? $results_array['answer_limit_keys'] : '',
								),
								$options,
								$quiz_data
							);
						}
						break;
					}
				}
			}
		}
		foreach ( $question_data as $questiontype ) {
			if ( 11 == $questiontype['question_type'] ) {
				$total_questions = $total_questions - 1;
			}
		}


		// Calculate Total Percent Score And Average Points Only If Total Questions Doesn't Equal Zero To Avoid Division By Zero Error
		if ( 0 !== $total_questions ) {
			$total_score = round( ( ( $total_correct / ( $total_questions - count( $hidden_questions ) ) ) * 100 ), 2 );
		} else {
			$total_score = 0;
		}

		// Return array to be merged with main user response array
		return apply_filters(
			'qsm_check_answers_results',
			array(
				'total_points'              => $points_earned,
				'total_score'               => $total_score,
				'total_correct'             => $total_correct,
				'total_questions'           => $total_questions,
				'question_answers_display'  => '', // Kept for backwards compatibility
				'question_answers_array'    => $question_data,
				'total_possible_points'     => $total_possible_points,
				'total_attempted_questions' => $attempted_question,
				'minimum_possible_points'   => $minimum_possible_points,
			),
			$options,
			$quiz_data
		);
	}

	/**
	 * Retrieves User's Comments
	 *
	 * Checks to see if the user left a comment and returns the comment
	 *
	 * @since  4.0.0
	 * @param  array $qmn_quiz_options        The database row of the quiz
	 * @param  array $qmn_array_for_variables The array of results for the quiz
	 * @return string The user's comments
	 */
	public function check_comment_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		$qmn_quiz_comments = '';
		if ( isset( $_POST['mlwQuizComments'] ) ) {
			$qmn_quiz_comments = sanitize_textarea_field( wp_unslash( $_POST['mlwQuizComments'] ) );
		}
		return apply_filters( 'qmn_returned_comments', $qmn_quiz_comments, $qmn_quiz_options, $qmn_array_for_variables );
	}

	/**
	 * computes maximum and minimum points for a quiz
	 *
	 * @since  7.3.5
	 * @param  array $options
	 * @param  array $question
	 * @return string $max_min_result
	 */
	public static function qsm_max_min_points( $options, $question ) {

		$max_value_array = array();
		$min_value_array = array();

		$valid_grading_system = ( 1 == $options->system || 3 == $options->system );
		$valid_answer_array   = ( isset( $question['answers'] ) && ! empty( $question['answers'] ) );

		$max_min_result = array(
			'max_point' => 0,
			'min_point' => 0,
		);

		if ( ! ( $valid_answer_array && $valid_grading_system ) ) {
			return $max_min_result;
		}

		foreach ( $question['answers'] as $single_answerk_key => $single_answer_arr ) {
			if ( isset( $single_answer_arr[1] ) ) {
				$single_answer_arr[1] = apply_filters( 'qsm_single_answer_arr', $single_answer_arr[1] );
				if ( floatval( $single_answer_arr[1] ) > 0 ) {
					array_push( $max_value_array, $single_answer_arr[1] );
				}
				if ( floatval( $single_answer_arr[1] ) < 0 ) {
					array_push( $min_value_array, $single_answer_arr[1] );
				}
			}
		}

		$question_type     = $question['question_type_new'];
		$question_required = ( 0 === maybe_unserialize( $question['question_settings'] )['required'] );
		$multi_response    = ( '4' === $question_type || '10' === $question_type || '14' === $question_type );

		return self::qsm_max_min_points_conditions( $max_value_array, $min_value_array, $question_required, $multi_response, $question );

	}
	/**
	 * evaluates conditions and returns maximum and minimum points for a quiz
	 *
	 * @since  7.3.5
	 * @param  array $max_value_array
	 * @param  array $min_value_array
	 * @param  array $question_required
	 * @param  array $multi_response
	 * @return string $max_min_result
	 */
	public static function qsm_max_min_points_conditions( $max_value_array, $min_value_array, $question_required, $multi_response, $question ) {
		$max_min_result = array(
			'max_point' => 0,
			'min_point' => 0,
		);
		if ( empty( $max_value_array ) && empty( $min_value_array ) ) {
			return $max_min_result;
		}
		if ( empty( $max_value_array ) && $question_required && $multi_response ) {
			$max_min_result['max_point'] = max( $min_value_array );
			$max_min_result['min_point'] = array_sum( $min_value_array );
		}
		if ( empty( $max_value_array ) && $question_required && ! $multi_response ) {
			$max_min_result['max_point'] = max( $min_value_array );
			$max_min_result['min_point'] = min( $min_value_array );
		}
		if ( empty( $max_value_array ) && ! $question_required && $multi_response ) {
			$max_min_result['max_point'] = 0;
			$max_min_result['min_point'] = array_sum( $min_value_array );
		}
		if ( empty( $max_value_array ) && ! $question_required && ! $multi_response ) {
			$max_min_result['max_point'] = 0;
			$max_min_result['min_point'] = min( $min_value_array );
		}
		if ( empty( $min_value_array ) && $question_required && $multi_response ) {
			$max_min_result['min_point'] = min( $max_value_array );
			$max_min_result['max_point'] = array_sum( $max_value_array );
		}
		if ( empty( $min_value_array ) && $question_required && ! $multi_response ) {
			$max_min_result['min_point'] = min( $max_value_array );
			$max_min_result['max_point'] = max( $max_value_array );
		}
		if ( empty( $min_value_array ) && ! $question_required && $multi_response ) {
			$max_min_result['min_point'] = 0;
			$max_min_result['max_point'] = array_sum( $max_value_array );
		}
		if ( empty( $min_value_array ) && ! $question_required && ! $multi_response ) {
			$max_min_result['min_point'] = 0;
			$max_min_result['max_point'] = max( $max_value_array );
		}
		if ( ! empty( $max_value_array ) && ! empty( $min_value_array ) && $multi_response ) {
			$max_min_result['max_point'] = array_sum( $max_value_array );
			$max_min_result['min_point'] = array_sum( $min_value_array );
		}
		if ( ! empty( $max_value_array ) && ! empty( $min_value_array ) && ! $multi_response ) {
			$max_min_result['max_point'] = max( $max_value_array );
			$max_min_result['min_point'] = min( $min_value_array );
		}
		return apply_filters( 'qsm_max_min_points_conditions_result', $max_min_result, $question_required, $question );
	}

	/**
	 * Displays Results Text
	 *
	 * @since      4.0.0
	 * @deprecated 6.1.0 Use the newer results page class instead.
	 * @param      array $options       The quiz settings.
	 * @param      array $response_data The array of results for the quiz.
	 * @return     string The contents for the results text
	 */
	public function display_results_text( $options, $response_data ) {
		return QSM_Results_Pages::generate_pages( $response_data );
	}

	/**
	 * Displays social media buttons
	 *
	 * @deprecated 6.1.0 Use the social media template variables instead.
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options        The database row of the quiz.
	 * @param      array $qmn_array_for_variables The array of results for the quiz.
	 * @return     string The content of the social media button section
	 */
	public function display_social( $qmn_quiz_options, $qmn_array_for_variables ) {
		$social_display = '';
		if ( 1 == $qmn_quiz_options->social_media ) {
			$settings        = (array) get_option( 'qmn-settings' );
			$facebook_app_id = '594986844960937';
			if ( isset( $settings['facebook_app_id'] ) ) {
				$facebook_app_id = $settings['facebook_app_id'];
			}

			// Loads Social Media Text.
			$qmn_social_media_text = maybe_unserialize( $qmn_quiz_options->social_media_text );
			if ( ! is_array( $qmn_social_media_text ) ) {
				$qmn_social_media_text = array(
					'twitter'  => $qmn_quiz_options->social_media_text,
					'facebook' => $qmn_quiz_options->social_media_text,
					'linkedin' => $qmn_quiz_options->social_media_text,
				);
			}
			$qmn_social_media_text['twitter']  = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text['twitter'], $qmn_array_for_variables );
			$qmn_social_media_text['facebook'] = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text['facebook'], $qmn_array_for_variables );
			$qmn_social_media_text['linkedin'] = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text['linkedin'], $qmn_array_for_variables );
			$social_display                   .= "<br /><a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('facebook', '" . esc_js( $qmn_social_media_text['facebook'] ) . "', '" . esc_js( $qmn_quiz_options->quiz_name ) . "', '" . esc_js( $facebook_app_id ) . "');\">Facebook</a><a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('twitter', '" . esc_js( $qmn_social_media_text['twitter'] ) . "', '" . esc_js( $qmn_quiz_options->quiz_name ) . "');\">Twitter</a><a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('linkedin', '" . esc_js( $qmn_social_media_text['linkedin'] ) . "', '" . esc_js( $qmn_quiz_options->quiz_name ) . "');\">Linkedin</a><br />";
		}
		return apply_filters( 'qmn_returned_social_buttons', $social_display, $qmn_quiz_options, $qmn_array_for_variables );
	}

	/**
	 * Send User Email
	 *
	 * Prepares the email to the user and then sends the email
	 *
	 * @deprecated 6.2.0 Use the newer QSM_Emails class instead.
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options        The database row of the quiz
	 * @param      array $qmn_array_for_variables The array of results for the quiz
	 */
	public function send_user_email( $qmn_quiz_options, $qmn_array_for_variables ) {
		add_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
		$mlw_message = '';

		// Check if this quiz has user emails turned on
		if ( 0 === intval( $qmn_quiz_options->send_user_email ) ) {

			// Make sure that the user filled in the email field
			if ( '' !== $qmn_array_for_variables['user_email'] ) {

				// Prepare from email and name
				$from_email_array = maybe_unserialize( $qmn_quiz_options->email_from_text );
				if ( ! isset( $from_email_array['from_email'] ) ) {
					$from_email_array = array(
						'from_name'  => $qmn_quiz_options->email_from_text,
						'from_email' => $qmn_quiz_options->admin_email,
						'reply_to'   => 1,
					);
				}

				if ( ! is_email( $from_email_array['from_email'] ) ) {
					if ( is_email( $qmn_quiz_options->admin_email ) ) {
						$from_email_array['from_email'] = $qmn_quiz_options->admin_email;
					} else {
						$from_email_array['from_email'] = get_option( 'admin_email ', 'test@example.com' );
					}
				}

				// Prepare email attachments
				$attachments = array();
				$attachments = apply_filters( 'qsm_user_email_attachments', $attachments, $qmn_array_for_variables );

				$mlw_user_email_array = maybe_unserialize( $qmn_quiz_options->user_email_template );
				if ( is_array( $mlw_user_email_array ) ) {
					// Cycle through emails
					foreach ( $mlw_user_email_array as $mlw_each ) {

						// Generate Email Subject
						if ( ! isset( $mlw_each[3] ) ) {
							$mlw_each[3] = 'Quiz Results For %QUIZ_NAME';
						}
						$mlw_each[3] = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_each[3], $qmn_array_for_variables );

						// Check to see if default
						if ( 0 == $mlw_each[0] && 0 == $mlw_each[1] ) {
							$mlw_message = htmlspecialchars_decode( $mlw_each[2], ENT_QUOTES );
							$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
							$mlw_message = str_replace( "\n", '<br>', $mlw_message );
							$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
							$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
							$mlw_headers = 'From: ' . $from_email_array['from_name'] . ' <' . $from_email_array['from_email'] . '>' . "\r\n";
							wp_mail( $qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments );
							break;
						} else {

							// Check to see if this quiz uses points and check if the points earned falls in the point range for this email
							if ( 1 == $qmn_quiz_options->system && $qmn_array_for_variables['total_points'] >= $mlw_each[0] && $qmn_array_for_variables['total_points'] <= $mlw_each[1] ) {
								$mlw_message = htmlspecialchars_decode( $mlw_each[2], ENT_QUOTES );
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
								$mlw_message = str_replace( "\n", '<br>', $mlw_message );
								$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
								$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
								$mlw_headers = 'From: ' . $from_email_array['from_name'] . ' <' . $from_email_array['from_email'] . '>' . "\r\n";
								wp_mail( $qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments );
								break;
							}

							// Check to see if score fall in correct range
							if ( 0 == $qmn_quiz_options->system && $qmn_array_for_variables['total_score'] >= $mlw_each[0] && $qmn_array_for_variables['total_score'] <= $mlw_each[1] ) {
								$mlw_message = htmlspecialchars_decode( $mlw_each[2], ENT_QUOTES );
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
								$mlw_message = str_replace( "\n", '<br>', $mlw_message );
								$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
								$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
								$mlw_headers = 'From: ' . $from_email_array['from_name'] . ' <' . $from_email_array['from_email'] . '>' . "\r\n";
								wp_mail( $qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments );
								break;
							}
						}
					}
				} else {
					// Uses older email system still which was before different emails were created.
					$mlw_message = htmlspecialchars_decode( $qmn_quiz_options->user_email_template, ENT_QUOTES );
					$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
					$mlw_message = str_replace( "\n", '<br>', $mlw_message );
					$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
					$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
					$mlw_headers = 'From: ' . $from_email_array['from_name'] . ' <' . $from_email_array['from_email'] . '>' . "\r\n";
					wp_mail( $qmn_array_for_variables['user_email'], 'Quiz Results For ' . $qmn_quiz_options->quiz_name, $mlw_message, $mlw_headers, $attachments );
				}
			}
		}
		remove_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
	}

	/**
	 * Send Admin Email
	 *
	 * Prepares the email to the admin and then sends the email
	 *
	 * @deprecated 6.2.0 Use the newer QSM_Emails class instead.
	 * @since      4.0.0
	 * @param      array $qmn_quiz_options        The database row of the quiz
	 * @param      arrar $qmn_array_for_variables The array of results for the quiz
	 */
	public function send_admin_email( $qmn_quiz_options, $qmn_array_for_variables ) {
		// Switch email type to HTML
		add_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );

		$mlw_message = '';
		if ( 0 === intval( $qmn_quiz_options->send_admin_email ) ) {
			if ( '' !== $qmn_quiz_options->admin_email ) {
				$from_email_array = maybe_unserialize( $qmn_quiz_options->email_from_text );
				if ( ! isset( $from_email_array['from_email'] ) ) {
					$from_email_array = array(
						'from_name'  => $qmn_quiz_options->email_from_text,
						'from_email' => $qmn_quiz_options->admin_email,
						'reply_to'   => 1,
					);
				}

				if ( ! is_email( $from_email_array['from_email'] ) ) {
					if ( is_email( $qmn_quiz_options->admin_email ) ) {
						$from_email_array['from_email'] = $qmn_quiz_options->admin_email;
					} else {
						$from_email_array['from_email'] = get_option( 'admin_email ', 'test@example.com' );
					}
				}

				$mlw_message           = '';
				$mlw_subject           = '';
				$mlw_admin_email_array = maybe_unserialize( $qmn_quiz_options->admin_email_template );
				if ( is_array( $mlw_admin_email_array ) ) {
					// Cycle through landing pages
					foreach ( $mlw_admin_email_array as $mlw_each ) {

						// Generate Email Subject
						if ( ! isset( $mlw_each['subject'] ) ) {
							$mlw_each['subject'] = 'Quiz Results For %QUIZ_NAME';
						}
						$mlw_each['subject'] = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_each['subject'], $qmn_array_for_variables );

						// Check to see if default
						if ( 0 == $mlw_each['begin_score'] && 0 == $mlw_each['end_score'] ) {
							$mlw_message = htmlspecialchars_decode( $mlw_each['message'], ENT_QUOTES );
							$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
							$mlw_message = str_replace( "\n", '<br>', $mlw_message );
							$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
							$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
							$mlw_subject = $mlw_each['subject'];
							break;
						} else {
							// Check to see if points fall in correct range
							if ( 1 == $qmn_quiz_options->system && $qmn_array_for_variables['total_points'] >= $mlw_each['begin_score'] && $qmn_array_for_variables['total_points'] <= $mlw_each['end_score'] ) {
								$mlw_message = htmlspecialchars_decode( $mlw_each['message'], ENT_QUOTES );
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
								$mlw_message = str_replace( "\n", '<br>', $mlw_message );
								$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
								$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
								$mlw_subject = $mlw_each['subject'];
								break;
							}

							// Check to see if score fall in correct range
							if ( 0 == $qmn_quiz_options->system && $qmn_array_for_variables['total_score'] >= $mlw_each['begin_score'] && $qmn_array_for_variables['total_score'] <= $mlw_each['end_score'] ) {
								$mlw_message = htmlspecialchars_decode( $mlw_each['message'], ENT_QUOTES );
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
								$mlw_message = str_replace( "\n", '<br>', $mlw_message );
								$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
								$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
								$mlw_subject = $mlw_each['subject'];
								break;
							}
						}
					}
				} else {
					$mlw_message = htmlspecialchars_decode( $qmn_quiz_options->admin_email_template, ENT_QUOTES );
					$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
					$mlw_message = str_replace( "\n", '<br>', $mlw_message );
					$mlw_message = str_replace( '<br/>', '<br>', $mlw_message );
					$mlw_message = str_replace( '<br />', '<br>', $mlw_message );
					$mlw_subject = 'Quiz Results For ' . $qmn_quiz_options->quiz_name;
				}
			}
			if ( get_option( 'mlw_advert_shows' ) == 'true' ) {
				$mlw_message .= '<br>This email was generated by the Quiz And Survey Master plugin';
			}
			$headers = array(
				'From: ' . $from_email_array['from_name'] . ' <' . $from_email_array['from_email'] . '>',
			);
			if ( 0 == $from_email_array['reply_to'] ) {
				$headers[] = 'Reply-To: ' . $qmn_array_for_variables['user_name'] . ' <' . $qmn_array_for_variables['user_email'] . '>';
			}
			$admin_emails = explode( ',', $qmn_quiz_options->admin_email );
			foreach ( $admin_emails as $admin_email ) {
				if ( is_email( $admin_email ) ) {
					wp_mail( $admin_email, $mlw_subject, $mlw_message, $headers );
				}
			}
		}

		// Remove HTML type for emails
		remove_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
	}

	/**
	 * Returns the quiz taker's IP if IP collection is enabled
	 *
	 * @since  5.3.0
	 * @return string The IP address or a phrase if not collected
	 */
	public function get_user_ip() {
		$ip            = __( 'Not collected', 'quiz-master-next' );
		$settings      = (array) get_option( 'qmn-settings' );
		$ip_collection = '0';
		if ( isset( $settings['ip_collection'] ) ) {
			$ip_collection = $settings['ip_collection'];
		}
		if ( '1' != $ip_collection ) {
			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ip = getenv( 'HTTP_CLIENT_IP' );
			} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
				$ip = getenv( 'HTTP_X_FORWARDED' );
			} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
				$ip = getenv( 'HTTP_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
				$ip = getenv( 'HTTP_FORWARDED' );
			} elseif ( getenv( 'REMOTE_ADDR' ) ) {
				$ip = getenv( 'REMOTE_ADDR' );
			} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			} else {
				$ip = __( 'Unknown', 'quiz-master-next' );
			}
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			return $ip;
		} else {
			return __( 'Invalid IP Address', 'quiz-master-next' );
		}
	}

	/**
	 * Determines whether a plugin is active.
	 *
	 * @since 6.4.11
	 *
	 * @param  string $plugin Path to the plugin file relative to the plugins directory.
	 * @return bool True, if in the active plugins list. False, not in the list.
	 */
	private function qsm_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || $this->qsm_plugin_active_for_network( $plugin );
	}

	/**
	 * Determines whether the plugin is active for the entire network.
	 *
	 * @since 6.4.11
	 *
	 * @param  string $plugin Path to the plugin file relative to the plugins directory.
	 * @return bool True if active for the network, otherwise false.
	 */
	private function qsm_plugin_active_for_network() {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		if ( isset( $plugins[ $plugin ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Include background process files
	 *
	 * @singce 7.0
	 */
	public function qsm_process_background_email() {
		include_once plugin_dir_path( __FILE__ ) . 'class-qmn-background-process.php';
		$this->qsm_background_email = new QSM_Background_Request();
	}
	/**
	 * Include background process files
	 *
	 * @singce 8.1.7
	 */
	public function qsm_create_quiz_nonce() {
		$quiz_id = ! empty( $_REQUEST['quiz_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['quiz_id'] ) ) : 0;
		wp_send_json_success( array(
			'nonce'      => wp_create_nonce( 'qsm_submit_quiz_' . $quiz_id ),
			'unique_key' => uniqid(),
		) );
	}
}

global $qmnQuizManager;
$qmnQuizManager = new QMNQuizManager();

add_filter( 'qmn_begin_shortcode', 'qmn_require_login_check', 10, 3 );

function qmn_require_login_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $mlwQuizMasterNext, $qmn_allowed_visit;
	if ( 1 == $qmn_quiz_options->require_log_in && ! is_user_logged_in() ) {
		$qmn_allowed_visit = false;
		$mlw_message = '';
		if ( isset( $qmn_quiz_options->require_log_in_text ) && '' !== $qmn_quiz_options->require_log_in_text ) {
			$mlw_message = htmlspecialchars_decode( $qmn_quiz_options->require_log_in_text, ENT_QUOTES );
		}
		$mlw_message = $mlwQuizMasterNext->pluginHelper->qsm_language_support( $mlw_message, "quiz_require_log_in_text-{$qmn_quiz_options->quiz_id}" );
		$mlw_message = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $mlw_message ), $qmn_array_for_variables );
		$mlw_message = str_replace( "\n", '<br>', $mlw_message );
		// $display .= do_shortcode($mlw_message);
		$display .= do_shortcode( $mlw_message );
		$display .= wp_login_form( array(
			'echo'    => false,
			'form_id' => 'qsm-login-form',
		) );
		wp_enqueue_script( 'qsm_common', QSM_PLUGIN_JS_URL . '/qsm-common.js', array( 'wp-util', 'underscore', 'jquery', 'jquery-ui-tooltip' ), $mlwQuizMasterNext->version, true );
		wp_localize_script(
			'qsm_common',
			'qmn_common_ajax_object',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
	}
	return $display;
}

add_filter( 'qmn_begin_shortcode', 'qsm_scheduled_timeframe_check', 99, 3 );

/**
 * @since 7.0.0 Added the condition for start time ( end time blank ) and end time ( start time blank ).
 *
 * @global boolean $qmn_allowed_visit
 * @param  HTML   $display
 * @param  Object $options
 * @param  Array  $variable_data
 * @return HTML This function check the time frame of quiz.
 */
function qsm_scheduled_timeframe_check( $display, $options, $variable_data ) {
	global $mlwQuizMasterNext, $qmn_allowed_visit;

	$checked_pass = false;
	// Checks if the start and end dates have data
	if ( ! empty( $options->scheduled_time_start ) && ! empty( $options->scheduled_time_end ) ) {
		$start = strtotime( $options->scheduled_time_start );
		$end   = strtotime( $options->scheduled_time_end );
		if ( strpos( $options->scheduled_time_end, ':' ) === false || strpos( $options->scheduled_time_end, '00:00' ) !== false ) {
			$end = strtotime( $options->scheduled_time_end ) + 86399;
		}

		$current_time = strtotime( current_time( 'm/d/Y H:i' ) );
		// Checks if the current timestamp is outside of scheduled timeframe
		if ( $current_time < $start || $current_time > $end ) {
			$checked_pass = true;
		}
	}
	if ( ! empty( $options->scheduled_time_start ) && empty( $options->scheduled_time_end ) ) {
		$start            = new DateTime( $options->scheduled_time_start );
		$current_datetime = new DateTime( current_time( 'm/d/Y H:i' ) );
		if ( $current_datetime < $start ) {
			$checked_pass = true;
		}
	}
	if ( empty( $options->scheduled_time_start ) && ! empty( $options->scheduled_time_end ) ) {
		$end              = new DateTime( $options->scheduled_time_end );
		$current_datetime = new DateTime( current_time( 'm/d/Y H:i' ) );
		if ( $current_datetime > $end ) {
			$checked_pass = true;
		}
	}
	if ( true == $checked_pass ) {
		$qmn_allowed_visit   = false;
		$message             = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $options->scheduled_timeframe_text, ENT_QUOTES ), "quiz_scheduled_timeframe_text-{$options->quiz_id}" );
		$message             = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $message ), $variable_data );
		$display             .= str_replace( "\n", '<br>', $message );
	}
	return $display;
}

add_filter( 'qmn_begin_shortcode', 'qmn_total_user_tries_check', 10, 3 );

/**
 * Checks if user has already reach the user limit of the quiz
 *
 * @since  5.0.0
 * @param  string $display                 The HTML displayed for the quiz
 * @param  array  $qmn_quiz_options        The settings for the quiz
 * @param  array  $qmn_array_for_variables The array of data by the quiz
 * @return string The altered HTML display for the quiz
 */
function qmn_total_user_tries_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $mlwQuizMasterNext, $qmn_allowed_visit;
	if ( 0 != $qmn_quiz_options->total_user_tries ) {

		// Prepares the variables
		global $wpdb;
		$mlw_qmn_user_try_count = 0;

		// Checks if the user is logged in. If so, check by user id. If not, check by IP.
		if ( is_user_logged_in() ) {
			$current_user           = wp_get_current_user();
			$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE user=%d AND deleted=0 AND quiz_id=%d", $current_user->ID, $qmn_array_for_variables['quiz_id'] ) );
		} else {
			$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}mlw_results WHERE user_ip=%s AND deleted=0 AND quiz_id=%d", $qmn_array_for_variables['user_ip'], $qmn_array_for_variables['quiz_id'] ) );
		}
		$mlw_qmn_user_try_count = apply_filters( 'qsm_total_user_tries_check_before', $mlw_qmn_user_try_count, $qmn_quiz_options, $qmn_array_for_variables );
		// If user has already reached the limit for this quiz
		if ( $mlw_qmn_user_try_count >= $qmn_quiz_options->total_user_tries ) {

			// Stops the quiz and prepares entered text
			$qmn_allowed_visit = false;
			$mlw_message       = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $qmn_quiz_options->total_user_tries_text, ENT_QUOTES ), "quiz_total_user_tries_text-{$qmn_quiz_options->quiz_id}" );
			$mlw_message       = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $mlw_message ), $qmn_array_for_variables );
			$display          .= $mlw_message;
		}
	}
	return $display;
}

add_filter( 'qmn_begin_quiz', 'qmn_total_tries_check', 20, 3 );

function qmn_total_tries_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $mlwQuizMasterNext, $qmn_allowed_visit;
	if ( 0 != $qmn_quiz_options->limit_total_entries ) {
		global $wpdb;
		$mlw_qmn_entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(quiz_id) FROM {$wpdb->prefix}mlw_results WHERE deleted=0 AND quiz_id=%d", $qmn_array_for_variables['quiz_id'] ) );
		if ( $mlw_qmn_entries_count >= $qmn_quiz_options->limit_total_entries ) {
			$qmn_allowed_visit = false;
			$mlw_message       = $mlwQuizMasterNext->pluginHelper->qsm_language_support( htmlspecialchars_decode( $qmn_quiz_options->limit_total_entries_text, ENT_QUOTES ), "quiz_limit_total_entries_text-{$qmn_quiz_options->quiz_id}" );
			$mlw_message       = apply_filters( 'mlw_qmn_template_variable_quiz_page', wpautop( $mlw_message ), $qmn_array_for_variables );
			$display          .= $mlw_message;
		}
	}
	return $display;
}

add_filter( 'qmn_begin_quiz', 'qmn_pagination_check', 10, 3 );

function qmn_pagination_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	if ( 0 != $qmn_quiz_options->pagination ) {
		global $wpdb, $mlwQuizMasterNext, $qmn_json_data;
		$total_questions = 0;
		if ( 0 != $qmn_quiz_options->question_from_total ) {
			$total_questions = $qmn_quiz_options->question_from_total;
		} else {
			$questions       = QSM_Questions::load_questions_by_pages( $qmn_quiz_options->quiz_id );
			$total_questions = count( $questions );
		}

		$default_texts = QMNPluginHelper::get_default_texts();
		$quiz_btn_display_text = $default_texts['next_button_text']; // For old quizes set default here
		$quiz_btn_submit_text = $default_texts['submit_button_text']; // For old quizes set default here

		if ( isset($qmn_quiz_options->start_quiz_survey_text) && "" != $qmn_quiz_options->start_quiz_survey_text ) {
			$quiz_btn_display_text = $qmn_quiz_options->start_quiz_survey_text; // For old quizes set default here
		}
		$qmn_json_data['pagination'] = array(
			'amount'                 => $qmn_quiz_options->pagination,
			'section_comments'       => $qmn_quiz_options->comment_section,
			'total_questions'        => $total_questions,
			'previous_text'          => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->previous_button_text, "quiz_previous_button_text-{$qmn_quiz_options->quiz_id}" ) ),
			'next_text'              => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $qmn_quiz_options->next_button_text, "quiz_next_button_text-{$qmn_quiz_options->quiz_id}" ) ),
			'start_quiz_survey_text' => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support( $quiz_btn_display_text, "quiz_start_quiz_text-{$qmn_quiz_options->quiz_id}" ) ),
			'submit_quiz_text'       => esc_html( $mlwQuizMasterNext->pluginHelper->qsm_language_support($qmn_quiz_options->submit_button_text, "quiz_submit_button_text-{$qmn_quiz_options->quiz_id}" ) ),
		);
	}
	return $display;
}

add_filter( 'qmn_begin_quiz_form', 'qmn_timer_check', 15, 3 );

function qmn_timer_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $qmn_allowed_visit;
	global $qmn_json_data;
	if ( $qmn_allowed_visit && 0 != $qmn_quiz_options->timer_limit ) {
		$qmn_json_data['timer_limit'] = $qmn_quiz_options->timer_limit;
		$display                     .= '<div id="mlw_qmn_timer" class="mlw_qmn_timer"></div>';
	}
	return $display;
}

add_filter( 'qmn_begin_quiz', 'qmn_update_views', 10, 3 );

function qmn_update_views( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $wpdb;
	$mlw_views  = $qmn_quiz_options->quiz_views;
	$mlw_views += 1;
	$results    = $wpdb->update(
		$wpdb->prefix . 'mlw_quizzes',
		array(
			'quiz_views' => $mlw_views,
		),
		array( 'quiz_id' => $qmn_array_for_variables['quiz_id'] ),
		array(
			'%d',
		),
		array( '%d' )
	);
	return $display;
}

add_filter( 'qmn_begin_results', 'qmn_update_taken', 10, 3 );

function qmn_update_taken( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $wpdb;
	$mlw_taken  = $qmn_quiz_options->quiz_taken;
	$mlw_taken += 1;
	$results    = $wpdb->update(
		$wpdb->prefix . 'mlw_quizzes',
		array(
			'quiz_taken' => $mlw_taken,
		),
		array( 'quiz_id' => $qmn_array_for_variables['quiz_id'] ),
		array(
			'%d',
		),
		array( '%d' )
	);
	return $display;
}

// This function helps set the email type to HTML

function mlw_qmn_set_html_content_type() {
	return 'text/html';
}

function qsm_time_in_milliseconds() {
	return round( microtime( true ) * 1000 );
}
add_filter(
	'wp_video_extensions',
	function ( $exts ) {
		$exts[] = 'mov';
		$exts[] = 'avi';
		$exts[] = 'wmv';
		return $exts;
	}
);

// Print table rows
function qsm_printTableRows( $array, $prefix = '' ) {
	$table = "<table>";
    foreach ( $array as $key => $value ) {
        $table .= '<tr>';
        if ( is_array($value) ) {
            $table .= '<td>' . $prefix . $key . '</td>';
            $table .= '<td></td>';
            $table .= '</tr>';
            qsm_printTableRows($value, $prefix . $key . ' - ');
        } else {
            $table .= '<td>' . $prefix . $key . '</td>';
            $table .= '<td>' . $value . '</td>';
            $table .= '</tr>';
        }
    }
	$table .= '</table>';
	return $table;
}
