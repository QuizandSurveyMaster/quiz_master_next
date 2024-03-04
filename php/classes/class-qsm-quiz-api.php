<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class is a helper class to be used for extending the plugin
 *
 * This class contains many functions for extending the plugin
 *
 * @since 4.0.0
 */
class QSMQuizApi {
	
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_action( 'wp_ajax_regenerate_api_key', array( $this, 'regenerate_api_key' ) );
	}

	public function register_routes() {

		register_rest_route(
            'qsm',
            '/quiz(?:/(?P<quiz_id>\d+))?',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'qsm_get_quiz_info' ),
                'permission_callback' => '__return_true',
            )
        );

        // Register REST API route to get quiz result JSON by result_id
        register_rest_route(
            'qsm',
            '/quiz_result(?:/(?P<result_id>\d+))?',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'qsm_get_quiz_result_info' ),
                'permission_callback' => '__return_true',
            )
        );

        // Register REST API route to submit quiz results
        register_rest_route(
            'qsm',
            '/submitquiz/',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'qsm_api_quiz_submit' ),
                'permission_callback' => '__return_true',
            )
        );

        // Register REST API route to get quiz questions
        register_rest_route(
            'qsm',
            '/get_questions/',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'qsm_get_quiz_questions' ),
                'permission_callback' => '__return_true',
            )
        );
    }

	public function regenerate_api_key() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'regenerate_api_key_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}
		$api_key = bin2hex(random_bytes(16));
		$api_key = password_hash($api_key, PASSWORD_BCRYPT);
		wp_send_json_success( $api_key );
	}

	public function load_form_field() {
	
		if ( isset($_POST['qsm_api_form_nonce']) && wp_verify_nonce($_POST['qsm_api_form_nonce'], 'qsm_api_form') && isset($_POST['qsm_api_settings']) ) {
			$qsm_api_settings = maybe_serialize($_POST['qsm_api_settings']); 
			update_option('qsm_quiz_api_settings', $qsm_api_settings);
		}
		
		$qsm_api_settings_serialized = get_option('qsm_quiz_api_settings');

		if ( $qsm_api_settings_serialized ) {
			$qsm_api_settings = maybe_unserialize($qsm_api_settings_serialized);
		} else {
			
			$default_api_settings = array(
				'api_key'           => '',
				'get_questions'     => '',
				'get_quiz'          => '',
				'allow_submit_quiz' => '',
				'get_result'        => '',
			);

			$qsm_api_settings = $default_api_settings;
		}

		$qsm_api_settings['api_key'] = sanitize_text_field($qsm_api_settings['api_key']);
		$qsm_api_settings['get_questions'] = isset($qsm_api_settings['get_questions']) ? sanitize_text_field($qsm_api_settings['get_questions']) : '';
		$qsm_api_settings['get_quiz'] = isset($qsm_api_settings['get_quiz']) ? sanitize_text_field($qsm_api_settings['get_quiz']) : '';
		$qsm_api_settings['allow_submit_quiz'] = isset($qsm_api_settings['allow_submit_quiz']) ? sanitize_text_field($qsm_api_settings['allow_submit_quiz']) : '';
		$qsm_api_settings['get_result'] = isset($qsm_api_settings['get_result']) ? sanitize_text_field($qsm_api_settings['get_result']) : '';

		$qpi_script_inline = array(
			'confirmation_message' => __('Are you sure you want to regenerate the API Key? This will affect your settings when you save changes, and the old key will no longer work.', 'quiz-master-next'),
			'nonce'                => wp_create_nonce('regenerate_api_key_nonce'),
		);
		wp_localize_script( 'qsm_admin_js', 'qsm_api_object', $qpi_script_inline );
		?>
		<form action="" method="post" class="qsm-api-settings-form">
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><label for="qsm_api_key"><?php esc_html_e('API Key', 'quiz-master-next'); ?></label></th>
					<td><input type="text" id="qsm_api_key" class="regular-text" name="qsm_api_settings[api_key]" readonly value='<?php echo esc_attr($qsm_api_settings['api_key']); ?>' />
					<?php if ( "" != $qsm_api_settings['api_key'] ) { ?>
						<button class="button qsm-generate-api-key confirmation" ><?php esc_html_e('Regenerate Key', 'quiz-master-next'); ?></button>
					<?php } else { ?>
						<button class="button qsm-generate-api-key"><?php esc_html_e('Generate Key', 'quiz-master-next'); ?></button>
					<?php } ?>
				</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="api_key_options"><?php esc_html_e('API Key Options', 'quiz-master-next'); ?></label></th>
					<td>
						<div class="qsm-api-keys-options">
							<div class="qsm-api-keys-option">
								<input type="checkbox" id="get_questions" name="qsm_api_settings[get_questions]" value="1" <?php checked($qsm_api_settings['get_questions'], '1'); ?>>
								<label for="get_questions"><?php esc_html_e('Enable get questions', 'quiz-master-next'); ?></label>
							</div>
							<div class="qsm-api-keys-option">
								<input type="checkbox" id="get_quiz" name="qsm_api_settings[get_quiz]" value="1" <?php checked($qsm_api_settings['get_quiz'], '1'); ?>>
								<label for="get_quiz"><?php esc_html_e('Enable get quiz', 'quiz-master-next'); ?></label>
							</div>
							<div class="qsm-api-keys-option">
								<input type="checkbox" id="allow_submit_quiz" name="qsm_api_settings[allow_submit_quiz]" value="1" <?php checked($qsm_api_settings['allow_submit_quiz'], '1'); ?>>
								<label for="allow_submit_quiz"><?php esc_html_e('Allow Submit Quiz', 'quiz-master-next'); ?></label>
							</div>
							<div class="qsm-api-keys-option">
								<input type="checkbox" id="get_result" name="qsm_api_settings[get_result]" value="1" <?php checked($qsm_api_settings['get_result'], '1'); ?>>
								<label for="get_result"><?php esc_html_e('Get Result', 'quiz-master-next'); ?></label>
							</div>
						</div>
					</td>
				</tr>
			</table>
			<?php wp_nonce_field('qsm_api_form', 'qsm_api_form_nonce'); ?>
			<button type="submit" name="qsm_api_submit" class="button-primary"><?php esc_html_e('Save Changes', 'quiz-master-next'); ?></button>
		</form>
		<?php 
	}

	/**
	 * Verify API key settings before performing an action.
	 *
	 * @param string $api_key The API key parameter received in the request.
	 * @return array The response containing success status and message.
	 */
	protected function qsm_verify_api_key_settings( $api_key, $type ) {
		$response = array(
			'success' => false,
			'message' => '',
		);

		$qsm_api_settings_serialized = get_option('qsm_quiz_api_settings');

		if ( $qsm_api_settings_serialized ) {
			$qsm_api_settings = maybe_unserialize($qsm_api_settings_serialized);

			if ( ($api_key && "" != $api_key) && (isset($qsm_api_settings['api_key']) && ("" != $qsm_api_settings['api_key'] && $api_key == $qsm_api_settings['api_key'])) && (isset($qsm_api_settings[ $type ]) && "1" == $qsm_api_settings[ $type ]) ) {
				$response['success'] = true;
			} else {
				if ( ! isset($qsm_api_settings['api_key']) || "" == $qsm_api_settings['api_key'] ) {
					$response['message'] = __('The API key is not configured.', 'quiz-master-next');
				} elseif ( ! $api_key ) {
					$response['message'] = __('Please provide an API key.', 'quiz-master-next');
				} elseif ( $api_key != $qsm_api_settings['api_key'] ) {
					$response['message'] = __('The provided API key is invalid. Please verify and try again.', 'quiz-master-next');
				} elseif ( ! isset($qsm_api_settings[ $type ]) || "" == $qsm_api_settings[ $type ] ) {
					$response['message'] = __('Admin does not allow process your request, please contact administrator.', 'quiz-master-next');
				}
			}
		} else {
			$response['message'] = __('The API key settings are not configured.', 'quiz-master-next');
		}

		return $response;
	}

	public function qsm_get_quiz_result_info( WP_REST_Request $request ) {
		
		$api_key_param = $request->get_header('authorization');
		$verification = $this->qsm_verify_api_key_settings($api_key_param, 'get_result');
		if ( $verification['success'] ) {
			if ( $request->get_param('result_id') ) {
				global $wpdb;
				$results_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_results WHERE result_id = %d", $request->get_param('result_id') ) );
		
				if ( $results_data ) {
					$results_data->quiz_results = maybe_unserialize($results_data->quiz_results);
					$response = array(
						'success' => true,
						'data'    => $results_data,
					);
				} else {
					$response = array(
						'success' => false,
						'message' => __('Quiz result not found based on this result id.', 'quiz-master-next'),
					);
				}
			} else {
				global $wpdb;
				$limit = $request->get_param('limit');
				$quiz_id = $request->get_param('quizId');
				$name = $request->get_param('name');
				$email = $request->get_param('email');
				$from_date = $request->get_param('from_date');
				$order = $request->get_param('order');
				$s = $request->get_param('s');
		
				$query = "SELECT * FROM {$wpdb->prefix}mlw_results WHERE 1=1";
				$limit = empty($limit) ? 10 : $limit;
				$order = empty($order) ? 'ASC' : $order;
		
				if ( ! empty($quiz_id) ) {
					$query .= $wpdb->prepare(" AND quiz_id = %s", $quiz_id);
				}
		
				if ( ! empty($s) ) {
					$rsearch  = '%' . esc_sql( $wpdb->esc_like( $s ) ) . '%';
					$query .= $wpdb->prepare(" AND (name LIKE %s OR quiz_name LIKE %s OR email LIKE %s)", $rsearch, $rsearch, $rsearch);
				}
				
				if ( ! empty($name) ) {
					$query .= $wpdb->prepare(" AND name = %s", $name);
				}
				
				if ( ! empty($email) ) {
					$query .= $wpdb->prepare(" AND email = %s", $email);
				}
		
				if ( ! empty($from_date) ) {
					$query .= $wpdb->prepare( " AND time_taken_real >= %s", $from_date );
				}
		
				$results = $wpdb->get_results($query .= " ORDER BY result_id {$order} LIMIT {$limit}");
				
				if ( $results ) {
					$data = [];
					foreach ( $results as $key => $value ) {
						$value->quiz_results = maybe_unserialize($value->quiz_results);
						$data[] = $value;
					}
					$response = array(
						'count'   => count($data),
						'success' => true,
						'data'    => $data,
					);
				} else {
					$response = array(
						'success' => false,
						'message' => "",
					);
				}
		
				if ( ! $results ) {
					if ( ! $request->get_param('result_id') && ! $request->get_param('quizId') && empty($name) && empty($email) && ! $request->get_param('from_date') ) {
						$response['message'] = __('No quiz results available.found for the specified criteria.', 'quiz-master-next');
					} elseif ( ! $request->get_param('quizId') ) {
						$response['message'] = __('No search results found based on the quiz id.', 'quiz-master-next');
					} elseif ( ! empty($name) && empty($email) ) {
						$response['message'] = __('No search results found based on the provided name.', 'quiz-master-next');
					} elseif ( empty($name) && ! empty($email) ) {
						$response['message'] = __('No search results found based on the provided email.', 'quiz-master-next');
					} else {
						$response['message'] = __('No results found for the specified criteria', 'quiz-master-next');
					}
				}
			}
		} else {
			$response = array(
				'success' => false,
				'message' => $verification['message'],
			);
		}
		return rest_ensure_response($response);
	}
		
	public function qsm_get_quiz_info( WP_REST_Request $request ) {
		$api_key_param = $request->get_header('authorization');
		$verification = $this->qsm_verify_api_key_settings($api_key_param, 'get_quiz');
		if ( $verification['success'] ) {
			if ( $request->get_param('quizId') ) {
				global $mlwQuizMasterNext;
				$quiz_data = $mlwQuizMasterNext->pluginHelper->prepare_quiz($request->get_param('quizId'));
				if ( $quiz_data ) {
					$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
					$formated_array = $this->qsm_convert_to_api_format($qmn_quiz_options);
					$response = array(
						'success' => true,
						'data'    => $formated_array,
					);
				} else {
					$response = array(
						'success' => false,
						'message' => __('Quiz not found on given quiz id.', 'quiz-master-next'),
					);
				}
			} else {
				global $wpdb;
				$limit     = $request->get_param( 'limit' ) ? $request->get_param( 'limit' ) : 10;
				$quiz_name = $request->get_param('quiz_name');
				$from_date = $request->get_param('from_date');
				$query = "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE 1=1";

				if ( ! empty($quiz_name) ) {
					$qnsearch  = '%' . esc_sql( $wpdb->esc_like( $quiz_name ) ) . '%';
					$query .= $wpdb->prepare(" AND quiz_name LIKE %s", $qnsearch);
				}

				if ( ! empty($from_date) ) {
					$query .= $wpdb->prepare( " AND last_activity >= %s", $from_date );
				}

				$results = $wpdb->get_results($query .= " LIMIT {$limit}");
				if ( $results ) {

					$data = [];
					foreach ( $results as $key => $value ) {
						$formated_array = $this->qsm_convert_to_api_format($value);
						$data[] = $formated_array;
					}

					$response = array(
						'count'   => count($data),
						'success' => true,
						'data'    => $data,
					);
				} else {
					$response = array(
						'success' => false,
						'message' => "",
					);
				}

				if ( ! $results ) {
					if ( empty($quiz_name) && ! $request->get_param('from_date') ) {
						$response['message'] = __('No quiz results available.', 'quiz-master-next');
					}elseif ( empty($quiz_name) ) {
						$response['message'] = __('No search results found based on the provided quiz name.', 'quiz-master-next');
					}else {
						$response['message'] = __('No quizzes found for the specified criteria', 'quiz-master-next');
					}
				}
			}
		} else {
			$response = array(
				'success' => false,
				'message' => $verification['message'],
			);
		}
		return rest_ensure_response($response);
	}

	public function qsm_convert_to_api_format( $inputObject ) {

		$apiFormat = [];
	
		foreach ( $inputObject as $key => $value ) {
			if ( $key === 'message_after' || $key === 'user_email_template' || $key === 'quiz_settings' ) {
				$apiFormat[ $key ] = maybe_unserialize($value);
				if ( $key === 'quiz_settings' ) {
					$apiFormat[ $key ] = $this->qsm_unserialize_to_api_format($apiFormat[ $key ]);
				}
			} elseif ( is_array($value) || is_object($value) ) {
				$apiFormat[ $key ] = $this->qsm_convert_to_api_format($value); 
			} else {
				$apiFormat[ $key ] = $value;
			}
		}
	
		return $apiFormat;
	}
	
	public function qsm_unserialize_to_api_format( $data ) {
		$result = array();
	
		if ( is_serialized($data) ) {
			return maybe_unserialize($data);
		}
	
		foreach ( $data as $key => $value ) {
			if ( is_serialized($value) ) {
				$result[ $key ] = $this->qsm_unserialize_recursive_loop($value);
			} else {
				$result[ $key ] = $value;
			}
		}
	
		return $result;
	}
	
	public function qsm_unserialize_recursive_loop( $value ) {
		$unserializedValue = maybe_unserialize($value);
	
		if ( is_array($unserializedValue) ) {
			foreach ( $unserializedValue as $innerKey => $innerValue ) {
				$unserializedValue[ $innerKey ] = $this->qsm_unserialize_recursive_loop($innerValue);
			}
		}
	
		return $unserializedValue;
	}

	public function qsm_get_quiz_questions( WP_REST_Request $request ) {
		$api_key_param = $request->get_header('authorization');
		$verification = $this->qsm_verify_api_key_settings($api_key_param, 'get_questions');
		if ( $verification['success'] ) {
			if ( $request->get_param('question_id') ) {
				global $wpdb;
				$question_id = $request->get_param('question_id' );
				$results = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'mlw_questions WHERE question_id=%d', $question_id ) );
				if ( $results ) {
					$results->answer_array = maybe_unserialize( $results->answer_array );
					$results->question_settings = maybe_unserialize( $results->question_settings );
					$response = array(
						'success' => true,
						'data'    => $results,
					);
				} else {
					$response = array(
						'success' => false,
						'message' => __('No question found in this given question id.', 'quiz-master-next'),
					);
				}
			} else {
				$data = [];
				global $wpdb;
				$question_name = $request->get_param('question_name' );
				$quiz_id = $request->get_param('quizId' );
				$limit     = $request->get_param( 'limit' ) ? $request->get_param( 'limit' ) : 10;
		
				$query = "SELECT * FROM {$wpdb->prefix}mlw_questions WHERE 1=1";
		
				if ( ! empty($question_name) ) {
					$qnsearch  = '%' . esc_sql( $wpdb->esc_like( $question_name ) ) . '%';
					$query .= $wpdb->prepare(" AND question_name LIKE %s", $qnsearch);
				}
		
				if ( ! empty($quiz_id) ) {
					$query .= $wpdb->prepare( " AND quiz_id=%d", $quiz_id );
				}
		
				$results = $wpdb->get_results($query .= " LIMIT {$limit}");
		
				if ( $results ) {
		
					foreach ( $results as $key => $result ) {
						$result->answer_array = maybe_unserialize( $result->answer_array );
						$result->question_settings = maybe_unserialize( $result->question_settings );
						$data[] = $result;
					}
				
					$response = array(
						'count'   => count($data),
						'success' => true,
						'data'    => $data,
					);
				} else {
					$response = array(
						'success' => false,
						'message' => "",
					);
				}
		
				if ( ! $results ) {
					if ( ! $request->get_param('quizId') && ! $request->get_param('question_name') ) {
						$response['message'] = __('No quiz results available.', 'quiz-master-next');
					} elseif ( ! empty($quiz_id) && empty($question_name) ) {
						$response['message'] = __('No questions are available for the given quiz id.', 'quiz-master-next');
					} elseif ( empty($quiz_id) && ! empty($question_name) ) {
						$response['message'] = __('No question results found for the provided search.', 'quiz-master-next');
					} else {
						$response['message'] = __('No questions found for the specified criteria', 'quiz-master-next');
					}
				}
			}
		} else {
			$response = array(
				'success' => false,
				'message' => $verification['message'],
			);
		}
		return $response;
	}
	
	public function qsm_api_quiz_submit( $request ) {
		
		$qsm_api_settings_serialized = get_option('qsm_quiz_api_settings');
		$api_key = $request->get_header('authorization');
		if ( $qsm_api_settings_serialized ) {
			$qsm_api_settings = maybe_unserialize($qsm_api_settings_serialized); 
	
			if ( ($api_key && "" != $api_key) && (isset($qsm_api_settings['api_key']) && ("" != $qsm_api_settings['api_key'] && $api_key == $qsm_api_settings['api_key'])) && isset($qsm_api_settings['allow_submit_quiz']) && "1" == $qsm_api_settings['allow_submit_quiz'] ) {
				
				$quiz_id = ! empty( $_POST['qmn_quiz_id'] ) ? sanitize_text_field( wp_unslash( $_POST['qmn_quiz_id'] ) ) : 0 ;
			
				global $qmn_allowed_visit, $mlwQuizMasterNext, $wpdb, $qmnQuizManager;
				$qmn_allowed_visit = true;
				$qmnQuizManager = new QMNQuizManager();
				include_once plugin_dir_path( __FILE__ ) . 'class-qmn-background-process.php';
				$qmnQuizManager->qsm_background_email = new QSM_Background_Request();
				$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz_id );
				$options    = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
				$post_ids = get_posts(array(
					'post_type'   => 'qsm_quiz', // Replace with the post type you're working with
					'meta_key'    => 'quiz_id',
					'meta_value'  => intval( $quiz_id ),
					'fields'      => 'ids',
					'numberposts' => 1,
				));
			
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
				$post_status = false;
				
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
				$data      = array(
					'quiz_id'         => $options->quiz_id,
					'quiz_name'       => $options->quiz_name,
					'quiz_system'     => $options->system,
					'quiz_payment_id' => isset( $_POST['main_payment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['main_payment_id'] ) ) : '',
				);
				return rest_ensure_response($qmnQuizManager->submit_results( $options, $data ));
			} else {

				if ( ! isset($qsm_api_settings['api_key']) || "" == $qsm_api_settings['api_key'] ) {
					$message = __('The API key is not configured.', 'quiz-master-next');
				} elseif ( ! $api_key ) {
					$message = __('Please provide an API key.', 'quiz-master-next');
				} elseif ( $api_key != $qsm_api_settings['api_key'] ) {
					$message = __('The provided API key is invalid. Please verify and try again.', 'quiz-master-next');
				} elseif ( ! isset($qsm_api_settings['allow_submit_quiz']) || "" == $qsm_api_settings['allow_submit_quiz'] ) {
					$message = __('Admin does not allow process your request, please contact administrator.', 'quiz-master-next');
				}

				$response = array(
					'display'       => $message,
					'redirect'      => false,
					'result_status' => array(
						'save_response' => false,
					),
				);
			}
		} else {
			$response = array(
				'display'       => __('API settings are not configured.', 'quiz-master-next'),
				'redirect'      => false,
				'result_status' => array(
					'save_response' => false,
				),
			);
		}
		
		return rest_ensure_response($response);
	}

}
