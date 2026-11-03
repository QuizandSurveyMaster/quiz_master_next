<?php
/**
 * Where QSM's admin CSS and JS load.
 *
 * Core's handles (qsm_admin_js, qsm_admin_style, select2-js, micromodal_script, ...) are
 * REGISTERED on every admin page, so add-ons can keep using them as dependencies, with
 * wp_localize_script() or wp_add_inline_script(). They are only ENQUEUED on QSM screens
 * (see is_qsm_screen()); other admin pages (Posts, Plugins, the WordPress dashboard, ...)
 * no longer load QSM's admin bundle.
 *
 * Page-specific files stay in MLWQuizMasterNext::qsm_admin_scripts_style(), keyed by the
 * screen. Add-ons load their own files for one screen with the
 * `qsm_admin_enqueue_screen_assets` action, e.g.:
 *
 *     add_action( 'qsm_admin_enqueue_screen_assets', function ( $screen ) {
 *         if ( 'quiz-questions' === $screen ) {
 *             wp_enqueue_script( 'my-addon-questions', ..., array( 'qsm_admin_js' ), ... );
 *         }
 *     } );
 *
 * @package QSM
 * @since 11.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers QSM's admin assets everywhere and decides where they are enqueued.
 */
class QSM_Admin_Assets {

	/**
	 * Hidden QSM pages registered under options.php (not in the QSM menu).
	 *
	 * @var array
	 */
	private static $hidden_pages = array( 'mlw_quiz_options', 'qsm_quiz_result_details', 'qsm_ui_kit' );

	/**
	 * Builds the qsm_admin_messages array (MLWQuizMasterNext::qsm_admin_messages_data).
	 *
	 * @var callable|null
	 */
	private static $messages_callback = null;

	/**
	 * Whether qsm_admin_messages has been attached to qsm_admin_js.
	 *
	 * @var bool
	 */
	private static $messages_localized = false;

	/**
	 * Hooks the lazy localisation of qsm_admin_messages.
	 *
	 * @param callable $messages_callback Returns the qsm_admin_messages array.
	 * @return void
	 */
	public static function init( $messages_callback ) {
		self::$messages_callback = $messages_callback;
		// After every admin_enqueue_scripts callback (add-ons use priorities up to 9999), before any script prints.
		// Dependencies first, so the messages check sees them.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'add_compat_dependencies' ), PHP_INT_MAX );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'maybe_localize_messages' ), PHP_INT_MAX );
		// Fallback for scripts enqueued while the page renders. A header script that depends on qsm_admin_js
		// and is enqueued after admin_enqueue_scripts (admin_head) would print it without qsm_admin_messages;
		// no add-on does that.
		add_action( 'admin_print_footer_scripts', array( __CLASS__, 'maybe_localize_messages' ), 1 );
	}

	/**
	 * Core dependencies that released add-on scripts use without declaring them.
	 *
	 * These add-ons load their admin script outside QSM screens and relied on Core's admin
	 * bundle being on every admin page. Declaring the dependency here makes WordPress load the
	 * one Core script they need, without the whole bundle.
	 *
	 * @return void
	 */
	public static function add_compat_dependencies() {
		/**
		 * Script handle => Core handles it needs.
		 *
		 * @since 11.3.0
		 * @param array $map Handle => array of Core script handles.
		 */
		$map = apply_filters(
			'qsm_admin_assets_compat_dependencies',
			array(
				// Advanced Assessment: js/admin.js calls .multiselect() on ready; it loads on every
				// edit-tags.php screen and on any ?page= admin page.
				'qsm_advanced_assessment_admin_script' => array( 'qsm-jquery-multiselect-js' ),
			)
		);
		$scripts = wp_scripts();
		foreach ( (array) $map as $handle => $deps ) {
			if ( ! in_array( $handle, (array) $scripts->queue, true ) || ! isset( $scripts->registered[ $handle ] ) ) {
				continue;
			}
			$registered       = $scripts->registered[ $handle ];
			$registered->deps = array_values( array_unique( array_merge( (array) $registered->deps, (array) $deps ) ) );
		}
	}

	/**
	 * Registers Core's shared admin handles. Safe to call more than once.
	 *
	 * @param string $version Plugin version (cache buster).
	 * @return void
	 */
	public static function register( $version ) {
		if ( ! wp_style_is( 'qsm_admin_style', 'registered' ) ) {
			wp_register_style( 'qsm_admin_style', QSM_PLUGIN_CSS_URL . '/qsm-admin.css', array(), $version );
		}
		if ( ! wp_style_is( 'qsm_admin_style_rtl', 'registered' ) ) {
			wp_register_style( 'qsm_admin_style_rtl', QSM_PLUGIN_CSS_URL . '/qsm-admin-rtl.css', array(), $version );
		}
		if ( ! wp_script_is( 'select2-js', 'registered' ) ) {
			wp_register_script( 'select2-js', QSM_PLUGIN_JS_URL . '/jquery.select2.min.js', array( 'jquery' ), $version, true );
		}
		if ( ! wp_style_is( 'select2-css', 'registered' ) ) {
			wp_register_style( 'select2-css', QSM_PLUGIN_CSS_URL . '/jquery.select2.min.css', array(), $version );
		}
		if ( ! wp_script_is( 'qsm-jquery-multiselect-js', 'registered' ) ) {
			wp_register_script( 'qsm-jquery-multiselect-js', QSM_PLUGIN_JS_URL . '/jquery.multiselect.min.js', array( 'jquery' ), $version, true );
		}
		if ( ! wp_style_is( 'jquer-multiselect-css', 'registered' ) ) {
			wp_register_style( 'jquer-multiselect-css', QSM_PLUGIN_CSS_URL . '/jquery.multiselect.min.css', array(), $version );
		}
		if ( ! wp_script_is( 'qsm_admin_js', 'registered' ) ) {
			/** 'wp-api-request' provides wpApiSettings. */
			wp_register_script( 'qsm_admin_js', QSM_PLUGIN_JS_URL . '/qsm-admin.js', array( 'jquery', 'backbone', 'underscore', 'wp-util', 'jquery-ui-sortable', 'jquery-touch-punch', 'qsm-jquery-multiselect-js', 'wp-api-request', 'select2-js' ), $version, true );
		}
		if ( ! wp_script_is( 'micromodal_script', 'registered' ) ) {
			wp_register_script( 'micromodal_script', QSM_PLUGIN_JS_URL . '/micromodal.min.js', array( 'jquery', 'qsm_admin_js' ), $version, true );
		}
		if ( ! wp_script_is( 'qsm_admin_notices_js', 'registered' ) ) {
			wp_register_script( 'qsm_admin_notices_js', QSM_PLUGIN_JS_URL . '/qsm-admin-notices.js', array( 'jquery' ), $version, true );
		}
	}

	/**
	 * Whether the current admin page is one of QSM's screens.
	 *
	 * @param string $hook The admin_enqueue_scripts hook suffix.
	 * @return bool
	 */
	public static function is_qsm_screen( $hook = '' ) {
		return '' !== self::screen( $hook );
	}

	/**
	 * Key of the current QSM screen, or '' when this isn't a QSM screen.
	 *
	 * Keys: dashboard, quizzes, quiz-{tab} (quiz-questions, quiz-options, ...), settings,
	 * results, result-details, stats, tools, question-bank, addons, about, categories,
	 * ui-kit, or the page slug for any other page in the QSM menu (add-on pages included).
	 *
	 * @param string $hook The admin_enqueue_scripts hook suffix.
	 * @return string
	 */
	public static function screen( $hook = '' ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only routing.
		$page   = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$key    = '';

		$known = array(
			'qsm_dashboard'           => 'dashboard',
			'qmn_global_settings'     => 'settings',
			'mlw_quiz_results'        => 'results',
			'qsm_quiz_result_details' => 'result-details',
			'qmn_stats'               => 'stats',
			'qsm_quiz_tools'          => 'tools',
			'qsm_question_bank'       => 'question-bank',
			'qmn_addons'              => 'addons',
			'qsm_quiz_about'          => 'about',
			'qsm_ui_kit'              => 'ui-kit',
		);
		if ( 'mlw_quiz_options' === $page ) {
			$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'questions';
			$key = 'quiz-' . $tab;
		} elseif ( isset( $known[ $page ] ) ) {
			$key = $known[ $page ];
		} elseif ( '' !== $page && ( in_array( $page, self::$hidden_pages, true ) || self::in_qsm_menu( $page ) || self::has_qsm_prefix( $page ) ) ) {
			// Includes add-on pages outside the QSM menu (their own top-level menus, hidden options.php pages):
			// they were given QSM's admin bundle before it was limited to QSM screens.
			$key = $page;
		} elseif ( $screen && 'qsm_quiz' === $screen->post_type ) {
			$key = 'quizzes';
		} elseif ( $screen && 'qsm_category' === $screen->taxonomy ) {
			$key = 'categories';
		} elseif ( $screen && self::has_qsm_prefix( (string) $screen->taxonomy ) ) {
			// Add-on taxonomies, e.g. qsm-answer-label (Advanced Assessment).
			$key = $screen->taxonomy;
		}
		// phpcs:enable

		/**
		 * Key of the current QSM admin screen ('' = not a QSM screen, QSM's admin bundle doesn't load).
		 *
		 * An add-on with its own top-level admin page can return a key here to load QSM's admin assets.
		 *
		 * @since 11.3.0
		 * @param string $key  Screen key.
		 * @param string $hook admin_enqueue_scripts hook suffix.
		 */
		return (string) apply_filters( 'qsm_admin_screen', $key, $hook );
	}

	/**
	 * Whether a slug uses one of the prefixes QSM and its add-ons name their pages with.
	 *
	 * @param string $slug Page slug or taxonomy.
	 * @return bool
	 */
	private static function has_qsm_prefix( $slug ) {
		return (bool) preg_match( '/^(qsm|qmn|mlw_)/', $slug );
	}

	/**
	 * Whether a page slug is in the QSM admin menu (Core's and add-ons' submenu pages).
	 *
	 * @param string $page Page slug.
	 * @return bool
	 */
	private static function in_qsm_menu( $page ) {
		global $submenu;
		if ( empty( $submenu['qsm_dashboard'] ) || ! is_array( $submenu['qsm_dashboard'] ) ) {
			return false;
		}
		foreach ( $submenu['qsm_dashboard'] as $item ) {
			if ( isset( $item[2] ) && $page === $item[2] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Assets QSM needs on any admin page: the notice script, only when that notice prints.
	 *
	 * Keep the condition in step with MLWQuizMasterNext::qsm_admin_notices().
	 *
	 * @return void
	 */
	public static function enqueue_global() {
		if ( is_user_logged_in() && current_user_can( 'manage_options' ) && ! get_option( 'qsm_multiple_category_enabled' ) ) {
			wp_enqueue_script( 'qsm_admin_notices_js' );
			wp_localize_script(
				'qsm_admin_notices_js',
				'qsmAdminNotices',
				array(
					'error'             => __( 'Error', 'quiz-master-next' ),
					'try_again'         => __( 'Please try again', 'quiz-master-next' ),
					'updating_db'       => __( 'Updating database', 'quiz-master-next' ),
					'update_db_success' => __( 'Database updated successfully.', 'quiz-master-next' ),
				)
			);
		}
	}

	/**
	 * Attaches qsm_admin_messages to qsm_admin_js once, if qsm_admin_js will print on this page.
	 *
	 * Building the messages runs queries (quizzes, template variables), so it only runs when
	 * qsm_admin_js is enqueued or needed by an enqueued script (an add-on dependency).
	 *
	 * @return void
	 */
	public static function maybe_localize_messages() {
		if ( self::$messages_localized || ! is_callable( self::$messages_callback ) || ! self::script_needed( 'qsm_admin_js' ) ) {
			return;
		}
		self::$messages_localized = true;
		wp_localize_script( 'qsm_admin_js', 'qsm_admin_messages', call_user_func( self::$messages_callback ) );
	}

	/**
	 * Whether a registered script will print: it's enqueued, or an enqueued script depends on it.
	 *
	 * @param string $handle Script handle.
	 * @return bool
	 */
	private static function script_needed( $handle ) {
		$scripts = wp_scripts();
		$stack   = (array) $scripts->queue;
		$seen    = array();
		while ( $stack ) {
			$current = array_pop( $stack );
			if ( isset( $seen[ $current ] ) ) {
				continue;
			}
			$seen[ $current ] = true;
			if ( $handle === $current ) {
				return true;
			}
			if ( isset( $scripts->registered[ $current ] ) ) {
				$stack = array_merge( $stack, (array) $scripts->registered[ $current ]->deps );
			}
		}
		return false;
	}
}
