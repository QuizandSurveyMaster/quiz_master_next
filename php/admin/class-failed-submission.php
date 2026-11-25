<?php
/**
 * Creates the failed submission page within the admin area
 *
 * @package QSM
 * @since 9.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'QmnFailedSubmissions' ) && class_exists( 'WP_List_Table' ) ) {
	/**
	 * This class display failed submission list table
	 *
	 * @since 9.0.2
	 */
	class QmnFailedSubmissions extends WP_List_Table {

		/**
		 * meta_key name which contain failed submission data
		 *
		 * @var object
		 * @since 9.0.2
		 */
		public $meta_key = '_qmn_log_result_insert_data';

		/**
		 * Variable to check if ip is enable
		 *
		 * @var object
		 * @since 9.0.2
		 */
		public $ip_enabled = false;

		/**
		 * table_data
		 *
		 * @var object
		 * @since 9.0.2
		 */
		private $table_data = array();


		/**
		 * Error log post ids
		 *
		 * @var object
		 * @since 9.0.2
		 */
		private $posts = array();

		/**
		 * Current Tab
		 *
		 * @var object
		 * @since 9.0.2
		 */
		private $current_tab = array();


		public function __construct() {
			parent::__construct(
				array(
					'plural'   => 'submissions',
					'singular' => 'submission',
					'ajax'     => true,
				)
			);
			$this->current_tab = ( empty( $_GET['tab'] ) || 'retrieve' === sanitize_key( $_GET['tab'] ) ) ? 'retrieve' : 'processed';

			// Get settings.
			$settings = (array) get_option( 'qmn-settings' );

			// ip_collection value 1 means it's disabled.
			if ( empty( $settings ) || ! isset( $settings['ip_collection'] ) || '1' != $settings['ip_collection'] ) {
				$this->ip_enabled = true;
			}
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 *  @since 9.0.2
		 *
		 *  @return void
		 */
		public function prepare_items() {
			// QMN Error log.
			$this->posts = get_posts(
				array(
					'post_type'      => 'qmn_log',
					'meta_key'       => $this->meta_key,
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'posts_per_page' => -1,
				)
			);

			$this->posts = empty( $this->posts ) ? array() : $this->posts;
			$per_page    = 20;
			if ( ! empty( $this->posts ) ) {
				$current_page       = intval( $this->get_pagenum() ) - 1;
				$post_start_postion = $per_page * $current_page;

				foreach ( $this->posts as $index => $postID ) {

					if ( $post_start_postion > $index || $index >= ( $post_start_postion + $per_page ) ) {
						continue;
					}

					$data = get_post_meta( $postID, $this->meta_key, true );
					if ( empty( $data ) ) {
						continue;
					}

					$data = maybe_unserialize( $data );
					if ( ! is_array( $data ) || ( 'processed' === $this->current_tab && empty( $data['processed'] ) ) || ( 'retrieve' === $this->current_tab && ! empty( $data['processed'] ) ) || empty( $data['qmn_array_for_variables'] ) ) {
						continue;
					}

					$data['qmn_array_for_variables']['post_id'] = $postID;
					$this->table_data[]                         = $data['qmn_array_for_variables'];
				}
			}

			// pagination.
			$this->set_pagination_args(
				array(
					'total_items' => count( $this->posts ),
					'per_page'    => $per_page,
				)
			);

			// table data.
			$this->items = $this->table_data;

			// table headers.
			$this->_column_headers = array(
				$this->get_columns(),
				$this->get_hidden_columns(),
				array(), // Sortable column
				'cb', // Primary column
			);
		}

		/**
		 * Gets a list of columns.
		 *
		 * @since 9.0.2
		 *
		 * @return array columns list
		 */
		public function get_columns() {
			$columns = array(
				'cb'         => '<input type="checkbox" />',
				'post_id'    => __( 'ID', 'quiz-master-next' ),
				'quiz_name'  => __( 'Quiz Name', 'quiz-master-next' ),
				'quiz_time'  => __( 'Time', 'quiz-master-next' ),
				'user_name'  => __( 'Name', 'quiz-master-next' ),
				'user_email' => __( 'Email', 'quiz-master-next' ),
			);

			if ( $this->ip_enabled ) {
				$columns['user_ip'] = __( 'IP Address', 'quiz-master-next' );
			}

			$columns['submission_action'] = __( 'Action', 'quiz-master-next' );

			return $columns;
		}

		/**
		 * Gets the list of views available on this table.
		 *
		 * @since 9.0.2
		 *
		 * @return array tabs link
		 */
		protected function get_views() {
			$views = array(
				'retrieve'  => array(
					'label' => __( 'Resubmit', 'quiz-master-next' ),
				),
				'processed' => array(
					'label' => __( 'Processed', 'quiz-master-next' ),
				),
			);

			$view_links = array();

			foreach ( $views as $view_id => $view ) {
				$view_links[ $view_id ] = '<a href="' . esc_url( admin_url( 'admin.php?page=qsm-quiz-failed-submission&tab=' . $view_id ) ) . '" class="' . ( ( $view_id === $this->current_tab ) ? 'current' : '' ) . '" >' . esc_html( $view['label'] ) . '</a>';
			}

			return $view_links;
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since 9.0.2
		 *
		 * @param object|array $submission The current item
		 *
		 * @return void
		 */
		public function single_row( $submission ) {
			echo '<tr id="qsm-submission-row-' . esc_attr( $submission['post_id'] ) . '" >';
			$this->single_row_columns( $submission );
			echo '</tr>';
		}

		/**
		 * Gets a list of hidden columns.
		 *
		 * @since 9.0.2
		 *
		 * @return array hidden column name
		 */
		public function get_hidden_columns() {
			return array(
				'post_id',
			);
		}

		/**
		 * Checkbox to select submissions.
		 *
		 * @since 9.0.2
		 *
		 * @return html input checkbox
		 */
		public function column_cb( $submission ) {
			$label = sprintf(
				/* translators: %d: failed submission ID */
				__( 'Select submission %d', 'quiz-master-next' ),
				$submission['post_id']
			);
			return sprintf(
				'<label class="screen-reader-text" for="qsm-fs-cb-%1$d">%2$s</label><input type="checkbox" id="qsm-fs-cb-%1$d" name="post_id[]" value="%1$d" /> ',
				absint( $submission['post_id'] ),
				esc_html( $label )
			);
		}

		/**
		 * Column value
		 *
		 * @since 9.0.2
		 *
		 * @return string specific column value
		 */
		public function column_default( $submission, $column_name ) {
			$column_value = '';
			switch ( $column_name ) {
				case 'post_id':
					$column_value = $submission['post_id'];
					break;
				case 'quiz_name':
					$column_value = $submission['quiz_name'];
					break;
				case 'quiz_time':
					$column_value = gmdate( 'd-m-Y', strtotime( $submission['time_taken'] ) );
					break;
				case 'user_name':
					$column_value = $submission['user_name'];
					break;
				case 'user_email':
					$column_value = $submission['user_email'];
					break;
				case 'user_ip':
					$column_value = $submission['user_ip'];
					break;
				case 'submission_action':
					$column_value = '<span id="action-link-' . esc_attr( $submission['post_id'] ) . '">';
					if ( 'processed' === $this->current_tab ) {
						$column_value .= QSM_Admin_UI::badge( __( 'Processed', 'quiz-master-next' ), 'success' );
					} else {
						$column_value .= '<a href="#"  post-id="' . esc_attr( $submission['post_id'] ) . '" class="qmn-retrieve-failed-submission-link qsm-ui-btn qsm-ui-btn-sm" >' . esc_html__( 'Resubmit', 'quiz-master-next' ) . '</a>';
					}
					$column_value .= '</span>';
					break;
				default:
					break;
			}

			return $column_value;
		}

		/**
		 * Table classes: the admin UI kit data table (D-01) on top of WP_List_Table's own.
		 *
		 * @since 11.3.0
		 *
		 * @return array
		 */
		protected function get_table_classes() {
			return array( 'widefat', 'qsm-ui-table', 'qsm-ui-fs-table', $this->_args['plural'] );
		}

		/**
		 * Message when the current view has no submissions.
		 *
		 * @since 11.3.0
		 *
		 * @return void
		 */
		public function no_items() {
			if ( 'processed' === $this->current_tab ) {
				esc_html_e( 'No processed submissions yet.', 'quiz-master-next' );
			} else {
				esc_html_e( 'No failed submissions. Every quiz submission was saved.', 'quiz-master-next' );
			}
		}

		/**
		 * Bulk action
		 *
		 * @since 9.0.2
		 *
		 * @return array actions
		 */
		public function get_bulk_actions() {
			return array(
				'retrieve' => __( 'Resubmit', 'quiz-master-next' ),
				'trash'    => __( 'Delete', 'quiz-master-next' ),
			);
		}

		/**
		 * Render page with this table
		 *
		 * @since 9.0.2
		 *
		 * @return HTML failed submission page
		 */
		public function render_list_table() {

			$this->prepare_items();
			?>
			<div class="qmn-failed-submission wrap qsm-ui qsm-ui-fs-page" id="qmn-failed-submission-conatiner" >
				<div class="qsm-ui-page-header qsm-ui-text">
					<div>
						<h1 class="qsm-ui-h1" id="result_details"><?php esc_html_e( 'Failed Submissions', 'quiz-master-next' ); ?></h1>
						<p class="qsm-ui-subtitle"><?php esc_html_e( 'Quiz submissions that could not be saved. Resubmit them to save them to the results.', 'quiz-master-next' ); ?></p>
					</div>
				</div>
				<hr class="wp-header-end">
				<nav class="qsm-ui-page-tabs-nav" aria-label="<?php esc_attr_e( 'Failed submissions', 'quiz-master-next' ); ?>">
					<div class="qsm-ui-tabs qsm-ui-page-tabs">
						<?php
						$views = array(
							'retrieve'  => __( 'Resubmit', 'quiz-master-next' ),
							'processed' => __( 'Processed', 'quiz-master-next' ),
						);
						foreach ( $views as $view_id => $label ) {
							echo '<a href="' . esc_url( admin_url( 'admin.php?page=qsm-quiz-failed-submission&tab=' . $view_id ) ) . '" class="qsm-ui-tab"' . ( $view_id === $this->current_tab ? ' aria-current="page"' : '' ) . '>' . esc_html( $label ) . '</a>';
						}
						?>
					</div>
				</nav>
				<div class="qmn-body">
					<?php // qsm-admin.js writes the result of an action here and switches notice-success / notice-error. ?>
					<div class="qsm-ui-fs-live" role="status" aria-live="polite">
						<div id="qmn-failed-submission-table-message" class="notice inline display-none-notice qsm-ui-fs-notice" >
							<button type="button" class="notice-dismiss"><span class="screen-reader-text"> <?php esc_html_e( 'Dismiss this notice.', 'quiz-master-next' ); ?> </span></button>
							<p class="notice-message" ></p>
						</div>
					</div>
					<div class="qsm-ui-card qsm-ui-fs-card">
						<div class="qsm-ui-card-body">
							<form method='post' id='failed-submission-action-form' class="qsm-ui-fs-form" action=''>
								<input type="hidden" name="qmnnonce" value="<?php echo esc_attr( wp_create_nonce( 'qmn_failed_submission' ) ); ?>" />
								<?php
									$this->display(); // render table
								?>
							</form>
						</div>
					</div>
				</div>
				<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-ui-popup" id="qsm-failed-submission-delete-popup" aria-hidden="true">
					<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
						<div class="qsm-popup__container qsm-ui" role="dialog" aria-modal="true" aria-labelledby="qsm-failed-submission-delete-title">
							<header class="qsm-popup__header qsm-ui-text">
								<div class="qsm-ui-confirm-head">
									<div class="qsm-ui-modal-icon"><?php echo QSM_Admin_UI::icon( 'trash' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?></div>
									<h2 class="qsm-popup__title" id="qsm-failed-submission-delete-title"><?php esc_html_e( 'Delete the selected submissions?', 'quiz-master-next' ); ?></h2>
								</div>
								<a class="qsm-popup__close" href="javascript:void(0)" aria-label="<?php esc_attr_e( 'Close', 'quiz-master-next' ); ?>" data-micromodal-close></a>
							</header>
							<main class="qsm-popup__content qsm-ui-text">
								<p><?php esc_html_e( 'The failed submissions will be removed and can no longer be resubmitted.', 'quiz-master-next' ); ?></p>
								<p class="qsm-ui-small"><?php esc_html_e( 'This cannot be undone.', 'quiz-master-next' ); ?></p>
							</main>
							<footer class="qsm-popup__footer">
								<button type="button" class="qsm-ui-btn qsm-ui-foot-left" data-micromodal-close><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
								<button type="button" class="qsm-ui-btn qsm-ui-btn-danger qsm-failed-submission-delete-confirm"><?php echo QSM_Admin_UI::icon( 'trash', 'qsm-ui-icon-sm' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI. ?><?php esc_html_e( 'Delete', 'quiz-master-next' ); ?></button>
							</footer>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
}
