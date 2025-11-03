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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		add_shortcode( 'mlw_quizmaster', array( $this, 'render_quiz_shortcode' ) );
		add_shortcode( 'qsm', array( $this, 'render_quiz_shortcode' ) );
		
		// Add admin option to enable new rendering
		add_filter( 'qsm_quiz_options', array( $this, 'add_new_rendering_option' ) );
		
		// Hook into main quiz container to add new CSS classes
		add_filter( 'qsm_quiz_container_classes', array( $this, 'add_quiz_container_classes' ), 10, 2 );
	}

	/**
	 * Enqueue scripts and styles for new rendering system
	 */
	public function enqueue_scripts() {
		global $mlwQuizMasterNext;
		// Enqueue MicroModal script
		wp_enqueue_script( 
			'micromodal_script', 
			QSM_PLUGIN_JS_URL . '/micromodal.min.js', 
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
			array( 'jquery' ), 
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
		$shortcode_args = apply_filters( 'qsm_shortcode_before', $shortcode_args, $atts );
		
		$quiz_id = intval( $shortcode_args['quiz'] );
		$question_amount = intval( $shortcode_args['question_amount'] );
		
		if ( ! $quiz_id ) {
			return '<p>Invalid quiz ID</p>';
		}

		// Check if quiz is setup properly (matching legacy flow)
		$has_proper_quiz = $mlwQuizMasterNext->pluginHelper->has_proper_quiz( $quiz_id );
		if ( false === $has_proper_quiz['res'] ) {
			return $has_proper_quiz['message'];
		}
		
		$qmn_quiz_options = $has_proper_quiz['qmn_quiz_options'];
		$qmn_quiz_options = apply_filters( 'qsm_quiz_option_before', $qmn_quiz_options );

		// Setup global variables for compatibility
		global $qmn_allowed_visit, $qmn_json_data, $mlw_qmn_quiz;
		$return_display = '';
		
		ob_start();
		
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
			);

			// Create renderer instance
			$renderer = new QSM_New_Pagination_Renderer( $qmn_quiz_options, $quiz_data );
			$auto_pagination_class = $qmn_quiz_options->pagination > 0 ? 'qsm_auto_pagination_enabled' : '';
			$randomness_order = $mlwQuizMasterNext->pluginHelper->qsm_get_randomization_modes( $qmn_quiz_options->randomness_order );
			$randomness_class = ! empty( $randomness_order ) ? 'random' : '';
			?>
			<!-- // Render quiz container -->
			<div class="qsm-quiz-container qmn_quiz_container qsm-quiz-container-<?php echo esc_attr( $quiz_data['quiz_id'] ); ?> mlw_qmn_quiz <?php echo esc_attr( $auto_pagination_class ); ?> quiz_theme_<?php echo esc_attr( $saved_quiz_theme ); ?> <?php echo esc_attr( $randomness_class ); ?>" data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>">
			
			<?php
			// Render quiz
			$renderer->render();
			?>
			</div>
			<?php
		} elseif ( isset( $_POST['complete_quiz'], $_POST['qmn_quiz_id'] ) && 'confirmation' == sanitize_text_field( wp_unslash( $_POST['complete_quiz'] ) ) && sanitize_text_field( wp_unslash( $_POST['qmn_quiz_id'] ) ) == $qmn_array_for_variables['quiz_id'] ) {
			// Display results - delegate to legacy system
			// This is handled by the main QMN quiz manager
		}
		
		$return_display .= ob_get_clean();
		
		// Apply end shortcode filter
		$return_display = apply_filters( 'qmn_end_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables, $shortcode_args );
		
		return $return_display;
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
