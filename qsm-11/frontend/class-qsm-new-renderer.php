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
		// Enqueue MicroModal script
		wp_enqueue_script( 
			'micromodal_script', 
			QSM_PLUGIN_JS_URL . '/micromodal.min.js', 
			array( 'jquery' ), 
			'11.0.0', 
			true 
		);

		// Enqueue navigation JavaScript
		wp_enqueue_script( 
			'qsm-new-navigation', 
			QSM_PLUGIN_URL . 'qsm-11/assets/js/qsm-quiz-navigation.js', 
			array( 'jquery' ), 
			'1.0.0', 
			true 
		);
		
		// Enqueue progress bar JavaScript
		wp_enqueue_script( 
			'qsm-new-progressbar', 
			QSM_PLUGIN_URL . 'qsm-11/assets/js/qsm-progressbar.js', 
			array( 'jquery', 'qsm-new-navigation' ), 
			'1.0.0', 
			true 
		);
		
		// Enqueue timer JavaScript
		wp_enqueue_script( 
			'qsm-new-timer', 
			QSM_PLUGIN_URL . 'qsm-11/assets/js/qsm-timer.js', 
			array( 'jquery', 'qsm-new-navigation' ), 
			'1.0.0', 
			true 
		);
		
		// Enqueue styles
		wp_enqueue_style( 
			'qsm-new-styles', 
			QSM_PLUGIN_URL . 'qsm-11/assets/css/qsm-new-rendering.css', 
			array(), 
			'1.0.0' 
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
		// Debug: Check if this shortcode is being called
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'QSM New Renderer: Shortcode called with attributes: ' . print_r( $atts, true ) );
		}
		
		$atts = shortcode_atts( array(
			'quiz' => 0,
			'question_amount' => 0,
		), $atts );

		$quiz_id = intval( $atts['quiz'] );
		
		if ( ! $quiz_id ) {
			return '<p>Invalid quiz ID</p>';
		}

		// Get quiz options
		global $wpdb, $mlwQuizMasterNext;
		$quiz_options = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d", $quiz_id ) );
		
		if ( ! $quiz_options ) {
			return '<p>Quiz not found</p>';
		}

		// Prepare quiz data
		$quiz_data = array(
			'quiz_id' => $quiz_id,
			'quiz_name' => $quiz_options->quiz_name,
			'user_ip' => $this->get_user_ip(),
		);

		// Create renderer instance
		$renderer = new QSM_New_Pagination_Renderer( $quiz_options, $quiz_data );
		$auto_pagination_class = $quiz_options->pagination > 0 ? 'qsm_auto_pagination_enabled' : '';
		// $saved_quiz_theme = $mlwQuizMasterNext->quiz_settings->get_setting('quiz_new_theme');
		$saved_quiz_theme = $mlwQuizMasterNext->theme_settings->get_active_quiz_theme_path( $quiz_id );
		$randomness_class = 0 === intval( $quiz_options->randomness_order ) ? '' : 'random';
		ob_start();
		?>
		<div class='qsm-quiz-container qsm-quiz-container-<?php echo esc_attr($quiz_data['quiz_id']); ?> qmn_quiz_container mlw_qmn_quiz <?php echo esc_attr( $auto_pagination_class ); ?> quiz_theme_<?php echo esc_attr( $saved_quiz_theme . ' ' . $randomness_class ); ?> ' data-quiz-id="<?php echo esc_attr( $quiz_id ); ?>">
			<?php echo $renderer->render(); ?>
		</div>
		<?php
		$output = ob_get_clean();
		
		$quiz_settings = (object)maybe_unserialize($quiz_options->quiz_settings);
		$parameters = (object)array_merge( (array)$quiz_options, (array)maybe_unserialize($quiz_settings->quiz_options) );
		$output .= apply_filters( 'qmn_end_shortcode', '', $parameters, [], $atts );
		
		return $output;
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
