<?php
/**
 * QSM New Rendering System Controller
 *
 * This is a separate implementation that doesn't interfere with the current system
 *
 * @package QSM
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QSM_New_Renderer class
 *
 * Main controller for the new template-based rendering system
 */
class QSM_New_Renderer {

	/**
	 * Instance of this class
	 *
	 * @var QSM_New_Renderer
	 */
	private static $instance = null;

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
	public $quiz_data;
	public static $qmn_quiz_options;

	/**
	 * Get singleton instance
	 *
	 * @return QSM_New_Renderer
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			// Prevent infinite loops during initialization
			static $initializing = false;
			if ( $initializing ) {
				return null;
			}
			
			$initializing = true;
			self::$instance = new self();
			$initializing = false;
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Always enqueue scripts when new rendering is enabled
		
		

		
		$qmn_global_settings    = (array) get_option( 'qmn-settings' );
		$enable_new_render 		= ! empty( $qmn_global_settings['enable_new_render'] ) ? esc_attr( $qmn_global_settings['enable_new_render'] ) : 0;
		if ( 1 === intval( $enable_new_render ) ) {
			add_shortcode( 'mlw_quizmaster', array( $this, 'render_quiz_shortcode' ) );
			add_shortcode( 'qsm', array( $this, 'render_quiz_shortcode' ) );
		}
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ),99999 );
		
		// Add admin option to enable new rendering
		add_filter( 'qsm_quiz_options', array( $this, 'add_new_rendering_option' ) );
		
		// Hook into main quiz container to add new CSS classes
		add_filter( 'qsm_quiz_container_classes', array( $this, 'add_quiz_container_classes' ), 10, 2 );
		
		// Include AJAX handler for lazy loading
		require_once QSM_PLUGIN_PATH . 'qsm-11/frontend/class-qsm-ajax-handler.php';		
	}
	

	/**
	 * Enqueue scripts and styles for new rendering system
	 */
	public function enqueue_scripts() {
		global $mlwQuizMasterNext,$qsm_quiz_ids;
		
		wp_enqueue_style( 
			'qmn_quiz_animation_style', 
			QSM_PLUGIN_CSS_URL . '/animate.css', 
			array(), 
			$mlwQuizMasterNext->version 
		);

		// Enqueue MicroModal script
		wp_enqueue_script( 
			'micromodal_script', 
			QSM_PLUGIN_JS_URL . '/micromodal.min.js', 
			array( 'jquery' ), 
			$mlwQuizMasterNext->version, 
			true 
		);
		
		wp_enqueue_script( 'jquery-ui-slider' );
		// Enqueue slider script
		wp_enqueue_script( 
			'slider', 
			QSM_PLUGIN_JS_URL . '/jquery.ui.slider-rtl.js', 
			array( 'jquery' ), 
			$mlwQuizMasterNext->version, 
			true 
		);

		// Enqueue slider CSS
		wp_enqueue_style( 
			'slider', 
			QSM_PLUGIN_CSS_URL . '/jquery.ui.slider-rtl.css', 
			array(), 
			$mlwQuizMasterNext->version 
		);

		// Enqueue common script
		wp_enqueue_script( 
			'qsm-common', 
			QSM_PLUGIN_JS_URL . '/qsm-common.js', 
			array( 'jquery' ), 
			$mlwQuizMasterNext->version, 
			true 
		);

		// Enqueue encryption script
		wp_enqueue_script( 
			'qsm_encryption', 
			QSM_PLUGIN_JS_URL . '/crypto-js.js', 
			array( 'jquery' ), 
			$mlwQuizMasterNext->version, 
			false 
		);

		// Enqueue navigation JavaScript
		wp_enqueue_script( 
			'qsm-quiz-navigation', 
			QSM_PLUGIN_URL . 'qsm-11/assets/js/qsm-quiz-navigation.js', 
			array( 'wp-util', 'underscore', 'jquery', 'backbone', 'jquery-ui-tooltip', 'qsm_encryption' ), 
			$mlwQuizMasterNext->version, 
			true 
		);
		
		
		// Enqueue progress bar JavaScript
		wp_enqueue_script( 
			'qsm-progressbar', 
			QSM_PLUGIN_URL . 'qsm-11/assets/js/qsm-progressbar.js', 
			array( 'jquery', 'qsm-quiz-navigation' ), 
			$mlwQuizMasterNext->version, 
			true 
		);
		
		// Enqueue timer JavaScript
		wp_enqueue_script( 
			'qsm-quiz-timer', 
			QSM_PLUGIN_URL . 'qsm-11/assets/js/qsm-timer.js', 
			array( 'jquery', 'qsm-quiz-navigation' ), 
			$mlwQuizMasterNext->version, 
			true 
		);
		
		// Enqueue required scripts
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'jquery-ui-tooltip' );

		// Enqueue styles
		wp_enqueue_style( 
			'qsm-quiz-styles', 
			QSM_PLUGIN_URL . 'qsm-11/assets/css/qsm-quiz-style.css', 
			array(), 
			$mlwQuizMasterNext->version 
		);
	}

	/**
	 * Check if new rendering is enabled
	 *
	 * @return bool
	 */
	private function is_new_rendering_enabled() {
		$enabled = apply_filters( 'qsm_enable_new_rendering', false );
		
		// Also check for constant or GET parameter
		if ( defined( 'QSM_ENABLE_NEW_RENDERING' ) && QSM_ENABLE_NEW_RENDERING ) {
			$enabled = true;
		}
		
		if ( isset( $_GET['qsm_new_rendering'] ) && $_GET['qsm_new_rendering'] == '1' ) {
			$enabled = true;
		}
		
		return $enabled;
	}

	private function render_result_page() {
		global $wpdb;
		
		ob_start();
		
		$result_unique_id = sanitize_text_field( wp_unslash( $_GET['result_id'] ) );
		
		// Get result from database
		$result           = $wpdb->get_row( 
			$wpdb->prepare( 
				"SELECT `result_id`, `quiz_id` FROM {$wpdb->prefix}mlw_results WHERE unique_id = %s", 
				$result_unique_id 
			), 
			ARRAY_A 
		);
		
		if ( ! empty( $result ) && isset( $result['result_id'] ) ) {
			$disable_mathjax = isset( $this->quiz_options->disable_mathjax ) ? $this->quiz_options->disable_mathjax : '';
			
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
		return $return_display;
	}
	
	/**
	 * Render quiz using new system
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_quiz_shortcode( $atts ) {
		global $wpdb, $mlwQuizMasterNext;
		
		// Apply shortcode_atts and filter
		$shortcode_args = shortcode_atts( array(
			'quiz' => 0,
			'question_amount' => 0,
		), $atts );
		$shortcode_args = array_merge( $shortcode_args, $atts );

		$shortcode_args = apply_filters( 'qsm_shortcode_before', $shortcode_args, $atts );
		
		
		$quiz_id = intval( $shortcode_args['quiz'] );
		$question_amount = intval( $shortcode_args['question_amount'] );
		
		

		if ( ! $quiz_id ) {
			return '<p>Invalid quiz ID</p>';
		}
		


		wp_register_style( 'qmn_quiz_common_style', QSM_PLUGIN_URL . 'qsm-11/assets/css/qsm-common.css', array(), $mlwQuizMasterNext->version );
		wp_enqueue_style( 'qmn_quiz_common_style' );

		// Check if quiz is setup properly (matching legacy flow)
		$has_proper_quiz = $mlwQuizMasterNext->pluginHelper->has_proper_quiz( $quiz_id );
		if ( false === $has_proper_quiz['res'] ) {
			return $has_proper_quiz['message'];
		}
			// Get quiz post based on quiz id
		$args      = array(
			'posts_per_page' => 1,
			'post_type'      => 'qsm_quiz',
			'meta_query'     => array(
				array(
					'key'     => 'quiz_id',
					'value'   => $quiz_id,
					'compare' => '=',
				),
			),
		);
		$the_query = new WP_Query( $args );

		// The Loop
		$quiz_post_data = array(
			'post_status' => '',
			'post_id' => '',
			'post_permalink' => '',
			'edit_link' => '',
		);
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$quiz_post_data['post_status'] = get_post_status( get_the_ID() );
				$quiz_post_data['post_id'] = get_the_ID();
				$quiz_post_data['post_permalink'] = get_the_permalink( get_the_ID() );
				$quiz_post_data['edit_link'] = get_edit_post_link( get_the_ID() );
			}
			/* Restore original Post Data */
			wp_reset_postdata();
		}
		
		$qmn_quiz_options = $has_proper_quiz['qmn_quiz_options'];
		$qmn_quiz_options = apply_filters( 'qsm_quiz_option_before', $qmn_quiz_options );
		
		
		if ( isset( $_GET['result_id'] ) && '' !== $_GET['result_id'] ) {
			return $this->render_result_page();
		}
		
		ob_start();

		// Setup global variables for compatibility
		global $qmn_allowed_visit, $qmn_json_data, $mlw_qmn_quiz;
		$return_display = '';

		// Load theme functions if exists
		$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme_path( $quiz_id );
		$folder_name = QSM_THEME_PATH . $saved_quiz_theme . '/';
		if ( file_exists( $folder_name . 'functions.php' ) ) {
			include_once $folder_name . 'functions.php';
		}
		
		// Hook for enqueueing additional scripts/styles
		do_action( 'qsm_enqueue_script_style', $qmn_quiz_options );
		
		// Prepare quiz data array
		$qmn_array_for_variables = array(
			'quiz_id' => $qmn_quiz_options->quiz_id,
			'quiz_name' => $qmn_quiz_options->quiz_name,
			'quiz_system' => $qmn_quiz_options->system,
			'user_ip' => $this->get_user_ip(),
		);
		
		// Initialize qmn_quiz_data object
		$return_display .= '<script>
			if (window.qmn_quiz_data === undefined) {
				window.qmn_quiz_data = new Object();
			}
		</script>';
		
		// Apply filters before rendering
		$return_display = apply_filters( 'qmn_begin_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );
		$qmn_quiz_options = apply_filters( 'qsm_quiz_options_before', $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );
		
		// Check if we should show quiz
		if ( $qmn_allowed_visit && ! isset( $_POST['complete_quiz'] ) && ! empty( $qmn_quiz_options->quiz_name ) ) {
			// Prepare quiz data
			$quiz_data = array(
				'quiz_id' => $quiz_id,
				'quiz_name' => $qmn_quiz_options->quiz_name,
				'quiz_system' => $qmn_quiz_options->system,
				'user_ip' => $this->get_user_ip(),
				'quiz_post_data' => $quiz_post_data,
			);

			// Create renderer instance
			$renderer = new QSM_New_Pagination_Renderer( $qmn_quiz_options, $quiz_data, $shortcode_args );
			$auto_pagination_class = $qmn_quiz_options->pagination > 0 ? 'qsm_auto_pagination_enabled' : '';
			$randomness_order = $mlwQuizMasterNext->pluginHelper->qsm_get_randomization_modes( $qmn_quiz_options->randomness_order );
			$randomness_class = ! empty( $randomness_order ) ? 'random' : '';
			$container_classes = array( 
				'qsm-quiz-container', 
				'qmn_quiz_container', 
				'qsm-quiz-container-' . esc_attr( $quiz_data['quiz_id'] ), 
				'mlw_qmn_quiz', 
				$auto_pagination_class, 
				'quiz_theme_' . esc_attr( $saved_quiz_theme ), 
				$randomness_class, 
			);

			$container_classes = array_filter($container_classes);

			$container_attr = apply_filters(
				'qsm_quiz_container_attributes',
				array(
					'class' => $container_classes, // array, not string
					'data'  => array(
						'quiz-id' => esc_attr( $quiz_id ),
					),
				),
				$quiz_id,
				$quiz_data
			);
			?>
			<!-- // Render quiz container -->
			<div <?php echo $this->qsm_render_html_attributes( $container_attr ); ?>>

			
			<?php
			// Render quiz
			$renderer->render( $shortcode_args );
			?>
			</div>
			<?php
			// Apply end quiz filter
			echo apply_filters( 'qmn_end_quiz', '', $qmn_quiz_options, $quiz_data );
		} elseif ( isset( $_POST['complete_quiz'], $_POST['qmn_quiz_id'] ) && 'confirmation' == sanitize_text_field( wp_unslash( $_POST['complete_quiz'] ) ) && sanitize_text_field( wp_unslash( $_POST['qmn_quiz_id'] ) ) == $qmn_array_for_variables['quiz_id'] ) {
			// Display results - delegate to legacy system
			// This is handled by the main QMN quiz manager
		}
		
		$return_display .= ob_get_clean();
		
		// Apply end shortcode filter
		$return_display = apply_filters( 'qmn_end_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );
		
		return $return_display;
	}
	public function qsm_render_html_attributes( $atts ) {

        $output = array();

        foreach ( $atts as $key => $value ) {

            // CLASS ATTRIBUTES (ARRAY)
            if ( $key === 'class' ) {
                $value = array_filter( (array) $value ); // remove empty
                if ( ! empty( $value ) ) {
                    $output[] = 'class="' . esc_attr( implode( ' ', $value ) ) . '"';
                }
                continue;
            }

            // DATA ATTRIBUTES (ARRAY)
            if ( $key === 'data' ) {
                foreach ( (array) $value as $data_key => $data_value ) {
                    if ( $data_value === '' ) continue;
                    $output[] = 'data-' . esc_attr( $data_key ) . '="' . esc_attr( $data_value ) . '"';
                }
                continue;
            }

            // NORMAL ATTRIBUTE
            if ( $value !== '' ) {
                $output[] = esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
            }
        }

        return implode( ' ', $output );
    }
	
	public static function get_qmn_quiz_options() {
		return self::$qmn_quiz_options;
	}

	/**
	 * Add new rendering option to quiz settings
	 *
	 * @param array $options
	 * @return array
	 */
	public function add_new_rendering_option( $options ) {
		$options['enable_new_rendering'] = array(
			'label' => __( 'Enable New Template System', 'quiz-master-next' ),
			'type' => 'checkbox',
			'default' => 0,
			'help' => __( 'Use the new template-based rendering system for better customization', 'quiz-master-next' ),
		);
		return $options;
	}

	/**
	 * Add CSS classes to quiz container when using new rendering
	 *
	 * @param array $classes Existing classes
	 * @param int   $quiz_id Quiz ID
	 * @return array
	 */
	public function add_quiz_container_classes( $classes, $quiz_id ) {
		if ( $this->is_new_rendering_enabled() ) {
			$classes[] = 'qsm-new-rendering';
			$classes[] = 'qsm-new-quiz-container';
		}
		return $classes;
	}

	/**
	 * Get user IP address
	 *
	 * @return string
	 */
	private function get_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} else {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
	}
}

// Initialize the new renderer
QSM_New_Renderer::get_instance();
