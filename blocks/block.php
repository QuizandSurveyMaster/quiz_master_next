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
				'editor_script'   => 'qsm-quiz-block',
				'render_callback' => array( $this, 'qsm_block_render' ),
			) );
		}

		/**
		 * Register scripts for block
		 */
		public function register_block_scripts() {
			global $globalQuizsetting, $mlwQuizMasterNext;
			if ( empty( $globalQuizsetting ) || empty( $mlwQuizMasterNext ) || ! function_exists( 'qsm_get_plugin_link' ) ) {
				return;
			}
			
			$quiz_options = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_options' );
			$quiz_options_id_details = array(
				'enable_contact_form' => array(
					'label' => __( 'Enable Contact Form', 'quiz-master-next' ),
					'help'  => __( 'Display a contact form before quiz', 'quiz-master-next' ),
				),
			);
			if ( ! empty( $quiz_options ) && is_array( $quiz_options ) ) {
				foreach ( $quiz_options as  $quiz ) {
					if ( ! empty( $quiz ) && ! empty( $quiz['id'] ) ) {
						$quiz_options_id_details[ $quiz['id'] ] = $quiz;
					}
				}
			}

			$question_type = $mlwQuizMasterNext->pluginHelper->categorize_question_types();
			$question_types = array();
			if ( ! empty( $question_type ) && is_array( $question_type ) ) {
				foreach ($question_type as $category => $qtypes ) {
					$question_types[] = array(
						'category' => $category,
						'types' => array_values( $qtypes )
					);
				}
			}
			
			wp_localize_script(
				'qsm-quiz-editor-script',
				'qsmBlockData',
				array(
					'ajax_url'            => admin_url( 'admin-ajax.php' ),
					'nonce'               => wp_create_nonce( 'qsm_nlock_quiz' ),
					'globalQuizsetting'   => array_merge(
						$globalQuizsetting,
						array(
							'enable_contact_form' => 0
						)
					),
					'QSMQuizList' => function_exists( 'qsm_get_quizzes_list' ) ? qsm_get_quizzes_list(): array(),
					'quizOptions' => $quiz_options_id_details,
					'question_type' => array(
						'label'              => __( 'Question Type', 'quiz-master-next' ),
						'options'            => $question_types,
						'default'            => '0',
						'documentation_link' =>  esc_url( qsm_get_plugin_link( 'docs/about-quiz-survey-master/question-types/', 'quiz_editor', 'question_type', 'quizsurvey-question-type_doc' ) ),
					),
					'answerEditor' =>  array(
						'label'              => __( 'Answers Type', 'quiz-master-next' ),
						'options'            => array(
							array( 'label' => __( 'Text Answers', 'quiz-master-next' ), 'value' => 'text' ),
							array( 'label' => __( 'Rich Answers', 'quiz-master-next' ), 'value' => 'rich' ),
							array( 'label' => __( 'Image Answers', 'quiz-master-next' ), 'value' => 'image' ),
						),
						'default'            => 'text',
						'documentation_link' => esc_url( qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'answer_type', 'answer_type_doc#Answer-Type' ) ),
					),
					'categoryList' => get_terms(  array(
							'taxonomy'             => 'qsm_category',
							'hide_empty' => false
						)
					),
					'commentBox'         => array(
						'heading'  => __( 'Comment Box', 'quiz-master-next' ),
						'label'    => __( 'Field Type', 'quiz-master-next' ),
						'options'            => array(
							array( 'label' => __( 'Small Text Field', 'quiz-master-next' ), 'value' => '0' ),
							array( 'label' => __( 'Large Text Field', 'quiz-master-next' ), 'value' => '2' ),
							array( 'label' => __( 'None', 'quiz-master-next' ), 'value' => '1' ),
						),
						'default'  => '1',
						'documentation_link' => qsm_get_plugin_link( 'docs/creating-quizzes-and-surveys/adding-and-editing-questions/', 'quiz_editor', 'comment-box', 'quizsurvey-comment-box_doc' ),
					)
				)
			);
		}

		/**
		 * The block renderer.
		 *
		 * This simply calls our main shortcode renderer.
		 *
		 * @param array $attributes The attributes that were set on the block.
		 */
		public function qsm_block_render( $attributes, $content, $block ) {
			global $qmnQuizManager;
			return $qmnQuizManager->display_shortcode( $attributes );
		}

		public function render_block_content( $attributes, $content, $block ) {
			return $content;
		}
	}

	QSMBlock::get_instance();
}
