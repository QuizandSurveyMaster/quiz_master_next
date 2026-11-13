<?php
/**
 * Which admin UI (New UI kit or Legacy) each active QSM add-on runs with.
 *
 * Add-ons DECLARE their UI support with a filter, so the declaration is a no-op on a
 * Core that predates the New UI (an add-on must not call a Core function that may not exist):
 *
 *     add_filter( 'qsm_addon_ui_compatibility', function ( $addons ) {
 *         $addons['qsm-flashcards'] = array(            // plugin folder name
 *             'name'            => 'QSM - Flashcards',
 *             'version'         => '3.0.0',             // installed add-on version
 *             'new_ui'          => true,                // ships a New UI implementation
 *             'min_core'        => '9.0.0',             // basic compatibility (optional)
 *             'min_core_new_ui' => '11.3.0',            // first Core whose kit it was built for
 *         );
 *         return $addons;
 *     } );
 *
 * An add-on that does not declare is Legacy. To tell the customer which release of it
 * adds New UI support, Core keeps a map of plugin folder => first New UI release
 * (see known_new_ui_versions()). Version numbers alone never switch an add-on to the
 * New UI; only its own declaration does.
 *
 * On a Core older than the New UI this class does not exist. An add-on detects that with
 * `defined( 'QSM_ADMIN_UI_VERSION' )`, keeps its Legacy assets and reports the Core
 * update itself (one shared notice for all add-ons, see the add-on bootstrap in the PR).
 *
 * @package QSM
 * @since 11.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatibility Manager: resolves Core and add-on UI compatibility.
 */
class QSM_Addon_Compatibility {

	/**
	 * Resolved add-ons, keyed by plugin folder. Null until first resolved.
	 *
	 * @var array|null
	 */
	private static $addons = null;

	/**
	 * Installed Core version.
	 *
	 * @return string
	 */
	public static function core_version() {
		global $mlwQuizMasterNext;
		return isset( $mlwQuizMasterNext->version ) ? (string) $mlwQuizMasterNext->version : '';
	}

	/**
	 * First add-on release with New UI support, for add-ons that cannot declare it yet.
	 *
	 * Only list releases that are PUBLISHED: the notice tells customers to update to them.
	 *
	 * @return array Plugin folder => version.
	 */
	public static function known_new_ui_versions() {
		/**
		 * Plugin folder => first add-on version that supports the New UI.
		 *
		 * @since 11.3.0
		 * @param array $versions Map.
		 */
		return (array) apply_filters( 'qsm_addon_new_ui_min_versions', array() );
	}

	/**
	 * Every active QSM add-on with its resolved UI mode and compatibility issues.
	 *
	 * Each entry: name, version, declared (bool), ui ('new'|'legacy'),
	 * addon_update (add-on version required for the New UI, '' = none),
	 * core_update (Core version this add-on needs, '' = none).
	 *
	 * @return array Plugin folder => entry.
	 */
	public static function get_addons() {
		if ( null !== self::$addons ) {
			return self::$addons;
		}
		$core     = self::core_version();
		$declared = self::declared();
		$known    = self::known_new_ui_versions();
		$addons   = array();

		foreach ( self::active_addon_plugins() as $slug => $plugin ) {
			$info = isset( $declared[ $slug ] ) ? $declared[ $slug ] : array();
			$addons[ $slug ] = self::resolve( $slug, $plugin, $info, $core, $known );
		}
		// A declaration from an add-on not found in the plugin list (e.g. a mu-plugin) still counts.
		foreach ( $declared as $slug => $info ) {
			if ( ! isset( $addons[ $slug ] ) ) {
				$addons[ $slug ] = self::resolve( $slug, array(), $info, $core, $known );
			}
		}
		self::$addons = $addons;
		return $addons;
	}

	/**
	 * Whether an add-on should load its New UI assets and markup.
	 *
	 * @param string $slug Plugin folder of the add-on.
	 * @return bool
	 */
	public static function uses_new_ui( $slug ) {
		$addons = self::get_addons();
		return isset( $addons[ $slug ] ) && 'new' === $addons[ $slug ]['ui'];
	}

	/**
	 * Add-ons whose installed version has no New UI, but a published release does.
	 *
	 * @return array Plugin folder => entry.
	 */
	public static function addons_needing_update() {
		return array_filter(
			self::get_addons(),
			function ( $addon ) {
				return '' !== $addon['addon_update'];
			}
		);
	}

	/**
	 * Add-ons that need a newer Core than the one installed.
	 *
	 * @return array Plugin folder => entry.
	 */
	public static function addons_needing_core_update() {
		return array_filter(
			self::get_addons(),
			function ( $addon ) {
				return '' !== $addon['core_update'];
			}
		);
	}

	/**
	 * Clears the resolved cache (after plugin activation/deactivation in the same request, tests).
	 *
	 * @return void
	 */
	public static function reset() {
		self::$addons = null;
	}

	/**
	 * Resolves one add-on.
	 *
	 * @param string $slug   Plugin folder.
	 * @param array  $plugin Plugin header data (Name, Version) or empty.
	 * @param array  $info   Declaration or empty.
	 * @param string $core   Core version.
	 * @param array  $known  Plugin folder => first New UI release.
	 * @return array
	 */
	private static function resolve( $slug, $plugin, $info, $core, $known ) {
		$name    = ! empty( $info['name'] ) ? $info['name'] : ( ! empty( $plugin['Name'] ) ? $plugin['Name'] : $slug );
		$version = ! empty( $info['version'] ) ? $info['version'] : ( ! empty( $plugin['Version'] ) ? $plugin['Version'] : '' );
		$entry   = array(
			'name'         => (string) $name,
			'version'      => (string) $version,
			'declared'     => ! empty( $info ),
			'ui'           => 'legacy',
			'addon_update' => '',
			'core_update'  => '',
		);

		// Basic compatibility, independent of the UI.
		$min_core = isset( $info['min_core'] ) ? (string) $info['min_core'] : '';
		if ( '' !== $min_core && self::lower( $core, $min_core ) ) {
			$entry['core_update'] = $min_core;
		}

		if ( ! empty( $info['new_ui'] ) ) {
			$min_core_ui = isset( $info['min_core_new_ui'] ) ? (string) $info['min_core_new_ui'] : '';
			if ( '' === $min_core_ui || ! self::lower( $core, $min_core_ui ) ) {
				$entry['ui'] = 'new';
			} elseif ( '' === $entry['core_update'] || self::lower( $entry['core_update'], $min_core_ui ) ) {
				// Built for a newer kit than this Core has: keep its Legacy UI, ask for a Core update.
				$entry['core_update'] = $min_core_ui;
			}
		} else {
			$required = '';
			if ( ! empty( $info['min_addon_new_ui'] ) ) {
				$required = (string) $info['min_addon_new_ui'];
			} elseif ( isset( $known[ $slug ] ) ) {
				$required = (string) $known[ $slug ];
			}
			if ( '' !== $required && ( '' === $entry['version'] || self::lower( $entry['version'], $required ) ) ) {
				$entry['addon_update'] = $required;
			}
		}
		return $entry;
	}

	/**
	 * Declarations from the `qsm_addon_ui_compatibility` filter, sanitised.
	 *
	 * @return array Plugin folder => declaration.
	 */
	private static function declared() {
		/**
		 * Add-on UI declarations, keyed by plugin folder.
		 *
		 * @since 11.3.0
		 * @param array $addons Plugin folder => array( name, version, new_ui, min_core, min_core_new_ui, min_addon_new_ui ).
		 */
		$raw = apply_filters( 'qsm_addon_ui_compatibility', array() );
		$out = array();
		foreach ( (array) $raw as $slug => $info ) {
			if ( is_string( $slug ) && '' !== $slug && is_array( $info ) ) {
				$out[ sanitize_key( $slug ) ] = $info;
			}
		}
		return $out;
	}

	/**
	 * Active QSM add-on plugins (folder starts with "qsm-"), site and network.
	 *
	 * @return array Plugin folder => header data.
	 */
	private static function active_addon_plugins() {
		$active = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$active = array_merge( $active, array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) );
		}
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$installed = get_plugins();
		$addons    = array();
		foreach ( $active as $file ) {
			$slug = dirname( (string) $file );
			if ( '.' === $slug || 0 !== strpos( $slug, 'qsm-' ) ) {
				continue;
			}
			$addons[ $slug ] = isset( $installed[ $file ] ) ? $installed[ $file ] : array();
		}
		return $addons;
	}

	/**
	 * Whether version $a is lower than $b ("2.1" equals "2.1.0").
	 *
	 * @param string $a Version.
	 * @param string $b Version.
	 * @return bool
	 */
	private static function lower( $a, $b ) {
		if ( function_exists( 'qsm_normalize_version' ) ) {
			$a = qsm_normalize_version( $a );
			$b = qsm_normalize_version( $b );
		}
		return version_compare( $a, $b, '<' );
	}
}
