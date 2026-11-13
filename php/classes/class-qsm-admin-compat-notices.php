<?php
/**
 * Consolidated add-on compatibility notices.
 *
 * At most two notices, each listing every affected add-on: one for add-ons that need an
 * update to support the New UI, one for add-ons that need a newer QSM Core. They print on
 * QSM screens only, once per page, and disappear as soon as the versions line up (nothing
 * is stored; they are recomputed from QSM_Addon_Compatibility on every load).
 *
 * @package QSM
 * @since 11.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Notice Manager for add-on compatibility.
 */
class QSM_Admin_Compat_Notices {

	/**
	 * Whether the notices already printed on this page.
	 *
	 * @var bool
	 */
	private static $printed = false;

	/**
	 * Hooks the notices.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'render' ) );
	}

	/**
	 * Prints the consolidated notices on QSM screens.
	 *
	 * @return void
	 */
	public static function render() {
		if ( self::$printed || ! current_user_can( 'update_plugins' ) || ! QSM_Admin_Assets::is_qsm_screen() ) {
			return;
		}
		self::$printed = true;

		$addon_updates = QSM_Addon_Compatibility::addons_needing_update();
		if ( $addon_updates ) {
			$items = array();
			foreach ( $addon_updates as $addon ) {
				/* translators: 1: add-on name, 2: version number */
				$items[] = sprintf( __( '%1$s – Required version: %2$s', 'quiz-master-next' ), $addon['name'], $addon['addon_update'] );
			}
			self::notice(
				__( 'QSM Add-on Update Required', 'quiz-master-next' ),
				array(
					__( 'Some of your installed QSM Add-ons are using older versions that do not support the new QSM UI.', 'quiz-master-next' ),
					__( 'Please update the following Add-ons to access the New UI:', 'quiz-master-next' ),
				),
				$items
			);
		}

		$core_updates = QSM_Addon_Compatibility::addons_needing_core_update();
		if ( $core_updates ) {
			$required = '';
			$items    = array();
			foreach ( $core_updates as $addon ) {
				$items[] = $addon['name'];
				if ( '' === $required || version_compare( qsm_normalize_version( $required ), qsm_normalize_version( $addon['core_update'] ), '<' ) ) {
					$required = $addon['core_update'];
				}
			}
			self::notice(
				__( 'QSM Core Update Required', 'quiz-master-next' ),
				array(
					__( 'Some of your installed QSM Add-ons require a newer version of QSM Core to work properly.', 'quiz-master-next' ),
					/* translators: %s: QSM version number */
					sprintf( __( 'Please update QSM Core to version %s or later to ensure compatibility with the following Add-ons:', 'quiz-master-next' ), $required ),
				),
				$items,
				__( 'Please update QSM Core to access the latest features and compatibility improvements.', 'quiz-master-next' )
			);
		}
	}

	/**
	 * Prints one standard WordPress warning notice.
	 *
	 * @param string $title  Heading.
	 * @param array  $lines  Paragraphs before the list.
	 * @param array  $items  List items (plain text).
	 * @param string $footer Paragraph after the list.
	 * @return void
	 */
	private static function notice( $title, $lines, $items, $footer = '' ) {
		?>
		<div class="notice notice-warning qsm-compat-notice">
			<p><strong><?php echo esc_html( $title ); ?></strong></p>
			<?php foreach ( $lines as $line ) : ?>
				<p><?php echo esc_html( $line ); ?></p>
			<?php endforeach; ?>
			<ul style="list-style:disc;margin-left:2em;">
				<?php foreach ( $items as $item ) : ?>
					<li><?php echo esc_html( $item ); ?></li>
				<?php endforeach; ?>
			</ul>
			<?php if ( '' !== $footer ) : ?>
				<p><?php echo esc_html( $footer ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}
}
