<?php
/**
 * Converts our qsm shortcode to a Gutenberg block.
 *
 * @package QSM
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'QSMBlock' ) ) {

	class QSMBlock {

		// The instance of this class
		private static $instance = null;

		// Returns the instance of this class.
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Main Construct Function
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_block' ) );
			//Fires after block assets have been enqueued for the editing interface
			add_action( 'enqueue_block_editor_assets', array( $this, 'register_block_scripts' ) );

			add_action( 'rest_api_init', array( $this, 'register_editor_rest_routes' ) );

		}

		/**
		 * Register block.
		 */
		public function register_block() {
			if ( ! function_exists( 'register_block_type' ) ) {
				// Block editor is not available.
				return;
			}
			//register legacy block
			$this->qsm_block_register_legacy();
			// QSM Main Block
			register_block_type(
				__DIR__ . '/build',
				array(
					'render_callback' => array( $this, 'qsm_block_render' ),
				)
			);

			foreach ( array( 'page', 'question', 'answer-option' ) as $block_dir ) {
				register_block_type(
					__DIR__ . '/build/'.$block_dir,
					array(
						'render_callback' => array( $this, 'render_block_content' ),
					)
				);
			}

		}

		/**
		 * legacy block
		 */
		private function qsm_block_register_legacy() {
			global $wp_version, $mlwQuizMasterNext;
			$dependencies = array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-api-request' );
			if ( version_compare( $wp_version, '5.3', '>=' ) ) {
				$dependencies = array_merge( $dependencies, array( 'wp-block-editor', 'wp-server-side-render' ) );
			}

			// Register our block editor script.
			wp_register_script( 'qsm-quiz-block', plugins_url( 'block.js', __FILE__ ), $dependencies, $mlwQuizMasterNext->version, true );

			// Register our block, and explicitly define the attributes we accept.
			register_block_type( 'qsm/main-block', array(
				'attributes'      => array(
					'quiz'    => array(
						'type' => 'string',
					),
					'quiz_id' => array(
						'type'    => 'array',
						'default' => array(
							array(
								'label' => 'quiz name',
								'value' => '0',
							),
						),
					),
				),
				'supports'        => array(
					'inserter' => false, // Hide this block from the inserter.
				),
				'editor_script'   => 'qsm-quiz-block',
				'render_callback' => array( $this, 'qsm_block_render' ),
			) );
		}

		/**
		 * Get hierarchical qsm_category
		 */
		private function hierarchical_qsm_category( $cat = 0 ) {
			$category = [];
			$next = get_categories( array(
				'taxonomy'     => 'qsm_category',
				'hide_empty'   => false,
				'hierarchical' => true,
				'orderby'      => 'name',
				'order'        => 'ASC',
				'parent'       => $cat,
			) );

			if ( $next ) {
				foreach ( $next as $cat ) {
					$cat->name = $cat->cat_name;
					$cat->id = $cat->term_id;
					$cat->children = $this->hierarchical_qsm_category( $cat->term_id );
					$category[] = $cat;
				}
			}
		  	return $category;
		}

		/**
		 * Register scripts for block
		 */
		public function register_block_scripts() {
			global $globalQuizsetting, $mlwQuizMasterNext;
			if ( empty( $globalQuizsetting ) || empty( $mlwQuizMasterNext ) || ! function_exists( 'qsm_get_plugin_link' ) || ! function_exists( 'qsm_settings_to_create_quiz' ) ) {
				return;
			}

			$question_type = $mlwQuizMasterNext->pluginHelper->categorize_question_types();
			$question_types = array();
			$question_type_description = array(
				'13' => __( 'Displays a range between two given option. Please keep only two option here and remove others.', 'quiz-master-next' ),
			);
			if ( ! empty( $question_type ) && is_array( $question_type ) ) {
				//Question description
				$question_type_desc = $mlwQuizMasterNext->pluginHelper->description_array();
				if ( ! empty( $question_type_desc ) && is_array( $question_type_desc ) ) {
					foreach ( $question_type_desc as  $question_desc ) {
						if ( ! empty( $question_desc['question_type_id'] ) && ! empty( $question_desc['description'] ) ) {
							$question_type_description[ $question_desc['question_type_id'] ] = $question_desc['description'];
						}
					}
				}

				//Question types category wise
				foreach ( $question_type as $category => $qtypes ) {
					$question_types[] = array(
						'category' => $category,
						'types'    => array_values( $qtypes ),
					);
				}
			}

			wp_localize_script(
				'qsm-quiz-editor-script',
				'qsmBlockData',
				array(
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'quiz_settings_url'         => admin_url( 'admin.php?page=mlw_quiz_options' ),
					'save_pages_action'         => 'qsm_save_pages',
					'saveNonce'                 => wp_create_nonce( 'ajax-nonce-sandy-page' ), // save page
					'nonce'                     => wp_create_nonce( 'qsm_block_quiz' ),
					'qsm_new_quiz_nonce'        => wp_create_nonce( 'qsm_new_quiz' ), //create quiz
					'globalQuizsetting'         => array_merge(
						$globalQuizsetting,
						array(
							'enable_contact_form' => 0,
						)
					),
					'QSMQuizList'               => function_exists( 'qsm_get_quizzes_list' ) ? qsm_get_quizzes_list() : array(),
					'quizOptions'               => qsm_settings_to_create_quiz( true ),
					'question_type'             => array(
						'label'              => __( 'Question Type', 'quiz-master-next' ),
						'options'            => $question_types,
						'default'            => '0',
						'documentation_link' => esc_url( qsm_get_plugin_link( 'docs/about-quiz-survey-master/question-types/', 'quiz_editor', 'question_type', 'quizsurvey-question-type_doc' ) ),
					),
					'question_type_description' => $question_type_description,
					'is_pro_activated'          => class_exists( 'QSM_Advance_Question' ) ? '1' : '0',
					'upgrade_link'              => function_exists( 'qsm_get_plugin_link' ) ? qsm_get_plugin_link( 'pricing', 'qsm', 'upgrade-box', 'upgrade', 'qsm_plugin_upsell' ) : '',
					'answerEditor'              => array(
						'label'              => __( 'Answers Type', 'quiz-master-next' ),
						'options'            => array(
							array(
								'label' => __( 'Text Answers', 'quiz-master-next' ),
								'value' => 'text',
							),
							array(
								'label' => __( 'Rich Answers', 'quiz-master-next' ),
								'value' => 'rich',
							),
							array(
								'label' => __( 'Image Answers', 'quiz-master-next' ),
								'value' => 'image',
							),
						),
						'default'            => 'text',
						'documentation_link' => esc_url( qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'answer_type', 'answer_type_doc#Answer-Type' ) ),
					),
					'categoryList'              => get_terms(  array(
						'taxonomy'   => 'qsm_category',
						'hide_empty' => false,
					)
					),
					'hierarchicalCategoryList'  => $this->hierarchical_qsm_category(),
					'commentBox'                => array(
						'heading'            => __( 'Comment Box', 'quiz-master-next' ),
						'label'              => __( 'Field Type', 'quiz-master-next' ),
						'options'            => array(
							array(
								'label' => __( 'Small Text Field', 'quiz-master-next' ),
								'value' => '0',
							),
							array(
								'label' => __( 'Large Text Field', 'quiz-master-next' ),
								'value' => '2',
							),
							array(
								'label' => __( 'None', 'quiz-master-next' ),
								'value' => '1',
							),
						),
						'default'            => '1',
						'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'comment-box', 'quizsurvey-comment-box_doc' ),
					),
					'file_upload_limit'         => array(
						'heading' => __( 'File upload limit ( in MB )', 'quiz-master-next' ),
						'default' => '4',
					),
					'file_upload_type'          => array(
						'heading' => __( 'Allow File type', 'quiz-master-next' ),
						'options' => array(
							'text/plain'      => __( 'Text File', 'quiz-master-next' ),
							'image'           => __( 'Image', 'quiz-master-next' ),
							'application/pdf' => __( 'PDF File', 'quiz-master-next' ),
							'doc'             => __( 'Doc File', 'quiz-master-next' ),
							'excel'           => __( 'Excel File', 'quiz-master-next' ),
							'video/mp4'       => __( 'Video', 'quiz-master-next' ),
						),
						'default' => 'image,application/pdf',
					),
				)
			);
		}

		//nonce to save question
		private function get_rest_nonce( $quiz_id ) {
			$user_id = get_current_user_id();
			return wp_create_nonce( 'wp_rest_nonce_' . $quiz_id . '_' . $user_id );
		}

		/**
		 * The block renderer.
		 *
		 * This simply calls our main shortcode renderer.
		 *
		 * @param array $attributes The attributes that were set on the block.
		 */
		public function qsm_block_render( $attributes, $content, $block ) {
			global $qmnQuizManager, $mlwQuizMasterNext;
			$post_status = true;
			if ( ! empty( $attributes ) ) {
				if ( ! empty( $attributes['quizID'] ) ) {
					$attributes['quiz'] = intval( $attributes['quizID'] );
				}
				if ( ! empty( $mlwQuizMasterNext ) && ! $mlwQuizMasterNext->qsm_is_admin( 'edit_posts' ) && ! empty( $attributes['postID'] ) && function_exists('get_post_status') ) {
					$post_status = get_post_status( intval( $attributes['postID'] ) );
					$post_status = 'publish' === $post_status;
				}
			}
			return $post_status ? $qmnQuizManager->display_shortcode( $attributes ) : '';
		}

		/**
		 * Render block like page, question, answer-option
		 */
		public function render_block_content( $attributes, $content, $block ) {
			return $content;
		}

		/**
		 * Register REST APIs
		 */
		public function register_editor_rest_routes() {
			if ( ! function_exists( 'register_rest_route' ) ) {
				return;
			}

			//get quiz hierarchical category structure data
			register_rest_route(
				'quiz-survey-master/v1',
				'/quiz/hierarchical-category-list',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'hierarchical_category_list' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			//get quiz advance question type upgrade popup html
			register_rest_route(
				'quiz-survey-master/v1',
				'/quiz/advance-ques-type-upgrade-popup',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'advance_question_type_upgrade_popup' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			//get quiz structure data
			register_rest_route(
				'quiz-survey-master/v1',
				'/quiz/structure',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'qsm_quiz_structure_data' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			//create Quiz
			register_rest_route(
				'quiz-survey-master/v1',
				'/quiz/create_quiz',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_new_quiz_from_editor' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			//save Quiz
			register_rest_route(
				'quiz-survey-master/v1',
				'/quiz/save_quiz',
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_quiz' ),
					'permission_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);

			//save pages and question order inside page : qsm_ajax_save_pages()

		}

		/**
		 * REST API
		 * get hierarchical qsm category list
		 */
		public function hierarchical_category_list() {
			return array(
				'status' => 'success',
				'result' => $this->hierarchical_qsm_category(),
			);
		}

		/**
		 * REST API
		 * get hierarchical qsm category list
		 */
		public function advance_question_type_upgrade_popup() {
			$contents = '';
			if ( function_exists( 'qsm_advance_question_type_upgrade_popup' ) ) {
				ob_start();
				qsm_advance_question_type_upgrade_popup();
				$contents = ob_get_clean();
			}
			return array(
				'status' => 'success',
				'result' => $contents,
			);
		}


		//get post id from quiz id
		private function get_post_id_from_quiz_id( $quiz_id ) {
			$post_ids      = get_posts( array(
				'posts_per_page' => 1,
				'post_type'      => 'qsm_quiz',
				'post_status'    => array( 'publish', 'draft' ),
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'quiz_id',
						'value'   => $quiz_id,
						'compare' => '=',
					),
				),
			) );
			wp_reset_postdata();
			return ( empty( $post_ids ) || ! is_array( $post_ids ) ) ? 0 : $post_ids[0];
		}

		/**
		 * REST API
		 * Get quiz structural data i.e. details, pages, questions, attributes
		 *
		 * @param { integer } $_POST[quiz_id]
		 *
		 * @return { array } status and quiz details, pages, questions, attributes
		 *
		 */
		public function qsm_quiz_structure_data( WP_REST_Request $request ) {

			$result = array(
				'status' => 'error',
				'msg'    => __( 'User not found', 'quiz-master-next' ),
			);
			if ( ! is_user_logged_in() || ! function_exists( 'wp_get_current_user' ) || empty( wp_get_current_user() ) ) {
				return $result;
			}

			$quiz_id = isset( $request['quizID'] ) ? intval( $request['quizID'] ) : 0;

			if ( empty( $quiz_id ) && ! is_numeric( $quiz_id ) ) {
				$result['msg'] = __( 'Invalid quiz id', 'quiz-master-next' );
				return $result;
			}

			global $wpdb;


			$quiz_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE deleted = 0 AND quiz_id = %d ORDER BY quiz_id DESC", $quiz_id ), ARRAY_A );

			if ( ! empty( $quiz_data ) ) {
				//nonce to save question
				$quiz_data[0]['rest_nonce'] = $this->get_rest_nonce( $quiz_id );
				$quiz_data[0]['post_id'] = $this->get_post_id_from_quiz_id( intval( $quiz_id ) );
				$quiz_data[0]['post_status'] = get_post_status( intval( $quiz_data[0]['post_id'] ) );
				// Cycle through each quiz and retrieve all of quiz's questions.
				foreach ( $quiz_data as $key => $quiz ) {

					$question_data = QSM_Questions::load_questions_by_pages( $quiz['quiz_id'] );
					$quiz_data[ $key ]['questions'] = $question_data;

					//unserialize quiz_settings
					if ( ! empty( $quiz_data[ $key ]['quiz_settings'] ) ) {
						$quiz_data[ $key ]['quiz_settings'] = maybe_unserialize( $quiz_data[ $key ]['quiz_settings'] );
						//unserialize pages
						if ( ! empty( $quiz_data[ $key ]['quiz_settings']['qpages'] ) ) {
							$quiz_data[ $key ]['qpages'] = maybe_unserialize( $quiz_data[ $key ]['quiz_settings']['qpages'] );
							if ( ! empty( $quiz_data[ $key ]['quiz_settings']['pages'] ) ) {
								$quiz_data[ $key ]['pages'] = maybe_unserialize( $quiz_data[ $key ]['quiz_settings']['pages'] );
								//group question under individual pages
								if ( is_array( $quiz_data[ $key ]['qpages'] ) ) {
									foreach ( $quiz_data[ $key ]['qpages'] as $pageIndex => $page ) {
										if ( ! empty( $page['questions'] ) && ! empty( $quiz_data[ $key ]['pages'][ $pageIndex ] ) ) {
											$quiz_data[ $key ]['qpages'][ $pageIndex ]['question_arr'] = array();
											foreach ( $quiz_data[ $key ]['pages'][ $pageIndex ] as $qIndex => $q ) {
												$quiz_data[ $key ]['qpages'][ $pageIndex ]['question_arr'][] = $quiz_data[ $key ]['questions'][ $q ];
											}
										}
									}
								}
							}
						}
					}

					// checking if logic is updated to tables
					$logic_updated = get_option( 'logic_rules_quiz_' . $quiz['quiz_id'] );
					if ( $logic_updated ) {
						$query      = $wpdb->prepare( "SELECT logic FROM {$wpdb->prefix}mlw_logic where quiz_id = %d", $quiz['quiz_id'] );
						$logic_data = $wpdb->get_results( $query, ARRAY_N );
						$logics     = array();
						if ( ! empty( $logic_data ) ) {
							foreach ( $logic_data as $logic ) {
								$logics[] = maybe_unserialize( $logic[0] );
							}
							$serialized_logic           = maybe_serialize( $logics );
							$quiz_data[ $key ]['logic'] = $serialized_logic;
						}
					}

					// get featured image of quiz if available
					$qsm_featured_image = get_option( 'quiz_featured_image_' . $quiz['quiz_id'] );
					if ( $qsm_featured_image ) {
						$quiz_data[ $key ]['featured_image'] = $qsm_featured_image;
					}

					// get themes setting
					$query       = $wpdb->prepare( "SELECT A.theme, B.quiz_theme_settings, B.active_theme FROM {$wpdb->prefix}mlw_themes A, {$wpdb->prefix}mlw_quiz_theme_settings B where A.id = B.theme_id and B.quiz_id = %d", $quiz['quiz_id'] );
					$themes_data = $wpdb->get_results( $query, ARRAY_N );
					if ( ! empty( $themes_data ) ) {
						$themes = array();
						foreach ( $themes_data as $data ) {
							$themes[] = $data;
						}
						$serialized_themes           = maybe_serialize( $themes );
						$quiz_data[ $key ]['themes'] = $serialized_themes;
					}
				}
				return array(
					'status' => 'success',
					'result' => $quiz_data[0],
				);

			}else {
				$result['msg'] = __( 'Quiz not found!', 'quiz-master-next' );
				return $result;
			}
		}

		/**
		 * REST API
		 * Create quiz using quiz name and other options
		 *
		 *
		 * @return { integer } quizID quiz id of newly created quiz
		 *
		 */
		public function create_new_quiz_from_editor( WP_REST_Request $request ) {
			if ( empty( $_POST['quiz_name'] ) ) {
				return array(
					'status' => 'error',
					'msg'    => __( 'Missing Input', 'quiz-master-next' ),
					'post'   => $_POST,
				);
			}
			if ( function_exists( 'qsm_create_new_quiz_from_wizard' ) ) {
				//create Quiz
				qsm_create_new_quiz_from_wizard();
				global $mlwQuizMasterNext;
				//get created quiz id
				$quizID = $mlwQuizMasterNext->quizCreator->get_id();
				$quizPostID = $mlwQuizMasterNext->quizCreator->get_quiz_post_id();

				if ( empty( $quizID ) ) {
					return array(
						'status' => 'error',
						'msg'    => __( 'Failed to create quiz.', 'quiz-master-next' ),
					);
				}

				return array(
					'status'     => 'success',
					'quizID'     => $quizID,
					'quizPostID' => $quizPostID,
					'msg'        => __( 'Quiz created successfully.', 'quiz-master-next' ),
				);
			}

			return array(
				'status'   => 'error',
				'msg'      => __( 'Failed to create quiz. Function not found', 'quiz-master-next' ),
				'is_admin' => is_admin(),
			);
		}

		/**
		 * REST API
		 * Create quiz using quiz name and other options
		 *
		 *
		 * @return { integer } quizID quiz id of newly created quiz
		 *
		 */
		public function save_quiz( WP_REST_Request $request ) {
			//verify nonce
			if ( ! isset( $_POST['qsm_block_quiz_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['qsm_block_quiz_nonce'] ) ), 'qsm_block_quiz' ) || empty( $_POST['quizData'] ) ) {
				return array(
					'status' => 'error',
					'msg'    => __( 'Invalid or missing input.', 'quiz-master-next' ),
					'post'   => $_POST,
				);
			}

			if ( ! function_exists( 'qsm_rest_save_question' ) || ! function_exists( 'qsm_ajax_save_pages' ) ) {
				return array(
					'status' => 'error',
					'msg'    => __( 'can\'t save quiz', 'quiz-master-next' ),
				);
			}

			//quiz data
			$_POST['quizData'] = ! empty( $_POST['quizData'] ) ? json_decode( wp_unslash( $_POST['quizData']  ), true ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$quiz_id = ! empty( $_POST['quizData']['quiz_id'] ) ? intval( sanitize_key( wp_unslash( $_POST['quizData']['quiz_id'] ) ) ) : 0;
			$post_id = ! empty( $_POST['quizData']['post_id'] ) ? intval( sanitize_key( wp_unslash( $_POST['quizData']['post_id'] ) ) ) : 0;
			if ( empty( $quiz_id ) || empty( $post_id ) ) {
				return array(
					'status' => 'error',
					'msg'    => __( 'Missing quiz_id or post_id', 'quiz-master-next' ),
					'post'   => $_POST,
				);
			}

			global $mlwQuizMasterNext;
			//Save Questions
			if ( ! empty( $_POST['quizData']['questions'] ) ) {
				$request->set_param( 'rest_nonce', $this->get_rest_nonce( $quiz_id ) );

				foreach ( wp_unslash( $_POST['quizData']['questions'] ) as $question ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					foreach ( $question as $qkey => $qvalue ) {
						$request->set_param( $qkey, $qvalue );
					}
					qsm_rest_save_question( $request );
				}
			}

			//save quiz name
			if ( ! empty(  $_POST['quizData']['quiz'] ) && ! empty(  $_POST['quizData']['quiz']['quiz_name'] ) ) {
				$quiz_name = sanitize_key( wp_unslash( $_POST['quizData']['quiz']['quiz_name'] ) );
				if ( ! empty( $quiz_id ) && ! empty( $post_id ) && ! empty( $quiz_name ) ) {
					//update quiz name
					$mlwQuizMasterNext->quizCreator->edit_quiz_name( $quiz_id, $quiz_name, $post_id );
				}
			}

			//Update status
			if ( ! empty( $quiz_id ) && ! empty( $post_id ) ) {

				//Default post status
				$post_status = 'publish';

				//page status which conatin quiz
				if ( ! empty( $_POST['post_status'] ) ) {
					$post_status = sanitize_key( wp_unslash( $_POST['post_status'] ) );
				}

				//Update quiz status
				if ( 'publish' === $post_status ) {
					wp_update_post( array(
						'ID'          => $post_id,
						'post_status' => 'publish',
					) );
				}
			}

			//Save Pages
			if ( ! empty( $_POST['quizData']['pages'] ) ) {
				foreach ( wp_unslash( $_POST['quizData'] ) as $qkey => $qvalue ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$_POST[ $qkey ] = $qvalue;
				}
				qsm_ajax_save_pages();
			}

			return array(
				'status' => 'success',
				'msg'    => __( 'Quiz saved successfully', 'quiz-master-next' ),
			);

		}

	}

	QSMBlock::get_instance();
}

if ( ! function_exists( 'is_qsm_block_api_call' ) ) {
	function is_qsm_block_api_call( $postcheck = false ) {
		if ( empty( $postcheck ) ) {
			return ! empty( $_POST['qsm_block_api_call'] );
		}
		return ( ! empty( $_POST['qsm_block_api_call'] ) && ! empty( $_POST[ $postcheck ] )  );
	}
}
