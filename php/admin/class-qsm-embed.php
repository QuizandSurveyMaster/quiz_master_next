<?php
/**
 * QSM Quiz Embed URL Handler
 *
 * Registers the /qsm-embed/{quiz_id} rewrite rule and renders
 * the quiz bare (no theme header/footer) so it can be used
 * inside any <iframe> or embedded on external sites.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'QSM_Embed' ) ) {

	class QSM_Embed {

		const QUERY_VAR = 'qsm_embed_quiz_id';

		/**
		 * Holds the single running instance.
		 *
		 * @var QSM_Embed|null
		 */
		private static $instance = null;

		public function __construct() {
			add_action( 'init', array( $this, 'add_rewrite_rule' ) );
			add_filter( 'query_vars', array( $this, 'add_query_var' ) );
			add_action( 'template_redirect', array( $this, 'handle_embed_request' ) );
		}

		/**
		 * Bootstrap the class and store the single instance.
		 *
		 * @return self
		 */
		public static function init() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Register the rewrite rule:  qsm-embed/{quiz_id}  →  index.php?qsm_embed_quiz_id={quiz_id}
		 */
		public function add_rewrite_rule() {
			add_rewrite_rule(
				'^qsm-embed/([0-9]+)/?$',
				'index.php?' . self::QUERY_VAR . '=$matches[1]',
				'top'
			);

			// Flush once after activation via the flag set by on_activation().
			if ( get_option( 'qsm_embed_flush_needed' ) ) {
				delete_option( 'qsm_embed_flush_needed' );
				flush_rewrite_rules( false );
			}
		}

		/**
		 * Whitelist the custom query variable so WordPress passes it through.
		 */
		public function add_query_var( $vars ) {
			$vars[] = self::QUERY_VAR;
			return $vars;
		}

		/**
		 * When the embed URL is hit, render only the quiz and exit.
		 */
		public function handle_embed_request() {
			$quiz_id = get_query_var( self::QUERY_VAR );

			if ( empty( $quiz_id ) ) {
				return;
			}

			$quiz_id = absint( $quiz_id );

			global $wpdb;
			$quiz_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT quiz_id FROM {$wpdb->prefix}mlw_quizzes WHERE quiz_id = %d AND deleted = 0 LIMIT 1",
					$quiz_id
				)
			);

			if ( ! $quiz_exists ) {
				wp_die(
					esc_html__( 'Quiz not found.', 'quiz-master-next' ),
					esc_html__( 'Quiz Not Found', 'quiz-master-next' ),
					array( 'response' => 404 )
				);
			}

			header( 'Content-Security-Policy: frame-ancestors *' );
			header( 'X-Robots-Tag: noindex, nofollow' );

			$this->render_embed_page( $quiz_id );
			exit;
		}

		/**
		 * Output a complete, self-contained HTML page with only the quiz.
		 *
		 * @param int $quiz_id
		 */
		private function render_embed_page( $quiz_id ) {
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php esc_html_e( 'Quiz', 'quiz-master-next' ); ?></title>
<?php wp_head(); ?>
<style>
	html, body { margin: 0; padding: 0; background: transparent; }
	.qsm-embed-wrapper { width: 100%; box-sizing: border-box; padding: 16px; }
	#wpadminbar { display: none !important; }
</style>
</head>
<body class="qsm-embed-body">
<div class="qsm-embed-wrapper">
	<?php echo do_shortcode( '[qsm quiz=' . $quiz_id . ']' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
<?php
		}

		/**
		 * Generate the embed URL for a given quiz ID.
		 *
		 * @param  int    $quiz_id
		 * @return string
		 */
		public static function get_embed_url( $quiz_id ) {
			$quiz_id = absint( $quiz_id );
			if ( get_option( 'permalink_structure' ) ) {
				return trailingslashit( home_url() ) . 'qsm-embed/' . $quiz_id . '/';
			}
			return add_query_arg( self::QUERY_VAR, $quiz_id, home_url( '/' ) );
		}

		/**
		 * Flush rewrite rules on activation so the new rule is recognised
		 * without requiring a manual Settings → Permalinks save.
		 */
		public static function on_activation() {
			update_option( 'qsm_embed_flush_needed', true );
		}

		public static function on_deactivation() {
			flush_rewrite_rules();
		}
	}

	QSM_Embed::init();
}