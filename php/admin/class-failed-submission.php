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
			return sprintf(
				'<input type="checkbox" name="post_id[]" value="%d" /> ',
				$submission['post_id']
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
						$column_value .= '<span class="dashicons dashicons-yes-alt"></span>';
					} else {
						$column_value .= '<a href="#"  post-id="' . esc_attr( $submission['post_id'] ) . '" class="qmn-retrieve-failed-submission-link" >' . __( 'Resubmit', 'quiz-master-next' ) . '</a>';
					}
					$column_value .= '</span>';
					break;
				default:
					break;
			}

			return $column_value;
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
			<!-- header. -->
			<div class="qmn-failed-submission wrap" id="qmn-failed-submission-conatiner" >
				<!-- heading. -->
				<h2 id="result_details" > <?php esc_html_e( 'Failed Submissions', 'quiz-master-next' ); ?> </h2>
				<!-- body -->
				<div class="qmn-body">
				<!-- Action response notice -->
				<div id="qmn-failed-submission-table-message" class="notice display-none-notice" >
				<button type="button" class="notice-dismiss"><span class="screen-reader-text"> <?php esc_html_e( 'Dismiss this notice.', 'quiz-master-next' ); ?> </span></button>
				<p class="notice-message" ></p>
				</div>
				<form method='post' id='failed-submission-action-form' action=''>
					<div class="submission-filter-wrapper" >
						<?php
							$this->views(); // render bulk action and pagination
						?>
					</div>
					<input type="hidden" name="qmnnonce" value="<?php echo esc_attr( wp_create_nonce( 'qmn_failed_submission' ) ); ?>" />
					<?php
						$this->display(); // render table
					?>
				</form>
				</div>
			</div>
			<?php
		}
	}
}
