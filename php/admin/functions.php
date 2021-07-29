<?php
$themes_data = array();

function qsm_fetch_data_from_xml() {
	$file     = esc_url( 'https://quizandsurveymaster.com/addons.xml' );
	$response = wp_remote_post( $file, array( 'sslverify' => false ) );

	if ( is_wp_error( $response ) || $response['response']['code'] === 404 ) {
		return '<p>' . __( 'Something went wrong', 'quiz-master-next' ) . '</p>';
	} else {
		$body       = wp_remote_retrieve_body( $response );
		return $xml = simplexml_load_string( $body );
	}
}

add_action( 'qmn_quiz_created', 'qsm_redirect_to_edit_page', 10, 1 );
/**
 * @since 6.4.5
 * @param int $quiz_id Quiz id.
 */
function qsm_redirect_to_edit_page( $quiz_id ) {
	link_featured_image( $quiz_id );
	$url = admin_url( 'admin.php?page=mlw_quiz_options&&quiz_id=' . $quiz_id );?>
<script>
window.location.href = '<?php echo $url; ?>';
</script>
<?php
}

/**
 * Links quiz featured image if exists
 *
 * @param int $quiz_id
 * @return void
 */
function link_featured_image( $quiz_id ) {
	$url = trim( $_POST['quiz_featured_image'] );
	if ( ! empty( $url ) ) {
		update_option( "quiz_featured_image_$quiz_id", $url );
	}
}

add_action( 'admin_init', 'qsm_add_author_column_in_db' );
/**
 * @since 6.4.6
 * Insert new column in quiz table
 */
function qsm_add_author_column_in_db() {
	if ( get_option( 'qsm_update_db_column', '' ) != '1' ) {

		global $wpdb;

		/*
		 * Array of table and its column mapping.
		 * Each array's item key refers to the table to be altered and its value refers
		 * to the array of column and its definition to be added.
		 */
		$table_column_arr = array(
			$wpdb->prefix . 'mlw_quizzes' => array( 'quiz_author_id' => 'INT NOT NULL' ),
			$wpdb->prefix . 'mlw_results' => array( 'unique_id' => 'VARCHAR(255) NOT NULL' ),
		);

		foreach ( $table_column_arr as $table => $column_def ) {
			foreach ( $column_def as $col_name => $col_def ) {
				$table_col_obj = $wpdb->get_results(
					$wpdb->prepare(
						'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
						$wpdb->dbname,
						$table,
						$col_name
					)
				);

				if ( empty( $table_col_obj ) ) {
					$wpdb->query( 'ALTER TABLE ' . $table . ' ADD ' . $col_name . ' ' . $col_def );
				}
			}
		}

		update_option( 'qsm_update_db_column', '1' );

	}

	// Update result db
	if ( get_option( 'qsm_update_result_db_column', '' ) != '1' ) {
		global $wpdb;
		$result_table_name    = $wpdb->prefix . 'mlw_results';
		$table_result_col_obj = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
				$wpdb->dbname,
				$result_table_name,
				'form_type'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			$wpdb->query( "ALTER TABLE $result_table_name ADD form_type INT NOT NULL" );
		}
		update_option( 'qsm_update_result_db_column', '1' );
	}

	/**
	 * Changed the system word to quiz_system in quiz table
	 *
	 * @since 7.0.0
	 */
	if ( get_option( 'qsm_update_quiz_db_column', '' ) != '1' ) {
		global $wpdb;
		$quiz_table_name    = $wpdb->prefix . 'mlw_quizzes';
		$table_quiz_col_obj = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
				$wpdb->dbname,
				$quiz_table_name,
				'system'
			)
		);
		if ( ! empty( $table_quiz_col_obj ) ) {
			$wpdb->query( "ALTER TABLE $quiz_table_name CHANGE `system` `quiz_system` INT(11) NOT NULL;" );
		}
		update_option( 'qsm_update_quiz_db_column', '1' );
	}

	/**
	 * Changed result table column data type
	 *
	 * @since 7.0.1
	 */
	if ( get_option( 'qsm_update_result_db_column_datatype', '' ) != '1' ) {
		global $wpdb;
		$result_table_name     = $wpdb->prefix . 'mlw_results';
		$table_quiz_result_obj = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
				$wpdb->dbname,
				$result_table_name,
				'quiz_results'
			),
			ARRAY_A
		);
		if ( isset( $table_quiz_result_obj['DATA_TYPE'] ) && $table_quiz_result_obj['DATA_TYPE'] == 'text' ) {
			$wpdb->query( "ALTER TABLE $result_table_name CHANGE `quiz_results` `quiz_results` LONGTEXT;" );
		}
		update_option( 'qsm_update_result_db_column_datatype', '1' );
	}

	/**
	 * Add new column in question table
	 *
	 * @since 7.0.3
	 */
	if ( get_option( 'qsm_add_new_column_question_table_table', '1' ) <= 3 ) {
		$total_count_val = get_option( 'qsm_add_new_column_question_table_table', '1' );
		global $wpdb;
		$question_table_name  = $wpdb->prefix . 'mlw_questions';
		$table_result_col_obj = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ',
				$wpdb->dbname,
				$question_table_name,
				'deleted_question_bank'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			$wpdb->query( "ALTER TABLE $question_table_name ADD deleted_question_bank INT NOT NULL" );
		}
		$inc_val = $total_count_val + 1;
		update_option( 'qsm_add_new_column_question_table_table', $inc_val );
	}
}

add_action( 'admin_init', 'qsm_change_the_post_type' );
/**
 * @since version 6.4.8
 * Transfer all quiz post to new cpt 'qsm_quiz'
 */
function qsm_change_the_post_type() {
	if ( get_option( 'qsm_change_the_post_type', '' ) != '1' ) {
		$post_arr = array(
			'post_type'      => 'quiz',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' ),
		);
		$my_query = new WP_Query( $post_arr );

		if ( $my_query->have_posts() ) {
			while ( $my_query->have_posts() ) {
				$my_query->the_post();

				$post_id  = get_the_ID();
				$post_obj = get_post( $post_id );
				if ( $post_obj->post_status == 'trash' ) {
					$post_obj->post_status = 'draft';
				}
				$post_obj->post_type = 'qsm_quiz';
				wp_update_post( $post_obj );
			}
			wp_reset_postdata();
		}
		update_option( 'qsm_change_the_post_type', '1' );
		flush_rewrite_rules();
	}
}

/**
 * @since  7.0.0
 * @param arr $single_option
 */
function qsm_display_question_option( $key, $single_option ) {
	$type       = isset( $single_option['type'] ) ? $single_option['type'] : 'text';
	$show       = isset( $single_option['show'] ) ? explode( ',', $single_option['show'] ) : array();
	$show_class = '';
	if ( $show ) {
		foreach ( $show as $show_value ) {
			$show_class .= 'qsm_show_question_type_' . trim( $show_value ) . ' ';
		}
		$show_class .= ' qsm_hide_for_other';
	}
	$tooltip       = '';
	$document_text = '';
	if ( isset( $single_option['tooltip'] ) && $single_option['tooltip'] != '' ) {
		$tooltip .= '<span class="dashicons dashicons-editor-help qsm-tooltips-icon">';
		$tooltip .= '<span class="qsm-tooltips">' . $single_option['tooltip'] . '</span>';
		$tooltip .= '</span>';
	}
	if ( isset( $single_option['documentation_link'] ) && $single_option['documentation_link'] != '' ) {
		$document_text .= '<a class="qsm-question-doc" href="' . $single_option['documentation_link'] . '" target="_blank" title="' . __( 'View Documentation', 'quiz-master-next' ) . '">';
		$document_text .= '<span class="dashicons dashicons-media-document"></span>';
		$document_text .= '</a>';
	}
	switch ( $type ) {
		case 'text':
			?>
<div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<input type="text" name="<?php echo $key; ?>"
		value="<?php echo isset( $single_option['default'] ) ? $single_option['default'] : ''; ?>"
		id="<?php echo $key; ?>" />
</div>
<?php
			break;

		case 'number':
			?>
<div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<input type="number" name="<?php echo $key; ?>"
		value="<?php echo isset( $single_option['default'] ) ? $single_option['default'] : ''; ?>"
		id="<?php echo $key; ?>" />
</div>
<?php
			break;

		case 'select':
			?>
<div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<select name="<?php echo $key; ?>" id="<?php echo $key; ?>">
		<?php
			$default = isset( $single_option['default'] ) ? $single_option['default'] : '';
			if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
				foreach ( $single_option['options'] as $key => $value ) {
					$selected = $key === $default ? 'selected = selected' : '';
					?>
		<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
		<?php
				}
			}
			?>
	</select>
</div>
<?php
			break;

		case 'textarea':
			?>
<div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<textarea id="<?php echo $key; ?>"
		name="<?php echo $key; ?>"><?php echo isset( $single_option['default'] ) ? $single_option['default'] : ''; ?></textarea>
</div>
<?php
			break;

		case 'category':
			?>
<div id="category_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<div id="categories">
		<a id="qsm-category-add-toggle" class="hide-if-no-js">
			<?php _e( '+ Add New Category', 'quiz-master-next' ); ?>
		</a>
		<p id="qsm-category-add" style="display: none;">
			<input type="radio" style="display: none;" name="category" class="category-radio" id="new_category_new"
				value="new_category"><label for="new_category_new"><input type='text' id='new_category' value=''
					placeholder="Add new category" /></label>
		</p>
	</div>
</div>
<?php
			break;

		case 'multi_category':
			?>
<div id="multi_category_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<div id="multi_categories_wrapper" class="categorydiv">
		<input type='text' class='qsm-category-filter' placeholder=' <?php _e( ' Search', 'quiz-master-next' ); ?> '>
		<ul id=" multicategories_checklist" class="qsm_category_checklist categorychecklist form-no-clear">
			<?php
						wp_terms_checklist(
							0,
							array(
								'taxonomy'             => 'qsm_category',
								'descendants_and_self' => 0,
								'selected_cats'        => false,
								'echo'                 => true,
							)
						);
			?>
		</ul>
		<a href='#' class='button button-primary add-multiple-category'>
			<?php _e( '+ Add New Category ', 'quiz-master-next' ); ?>
		</a>
	</div>
</div>
<?php
			break;

		case 'multi_checkbox':
			?>
<div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
	<?php
			$parent_key = $key;
			$default    = isset( $single_option['default'] ) ? $single_option['default'] : '';
			if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
				foreach ( $single_option['options'] as $key => $value ) {
					$selected = $key === $default ? 'checked' : '';
					?>
	<input name="<?php echo $parent_key; ?>[]" type="checkbox" value="<?php echo $key; ?>" <?php echo $selected; ?> />
	<?php echo $value; ?><br />
	<?php
				}
			}
			?>
</div>
<?php
			break;

		case 'single_checkbox':
			?>
<div id="<?php echo $key; ?>_area" class="qsm-row <?php echo $show_class; ?>">
	<label>
		<?php
			$parent_key = $key;
			$default    = isset( $single_option['default'] ) ? $single_option['default'] : '';
			if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
				foreach ( $single_option['options'] as $key => $value ) {
					$selected = $key === $default ? 'checked' : '';
					?>
		<input name="<?php echo $parent_key; ?>" id="<?php echo $parent_key; ?>" type="checkbox"
			value="<?php echo $key; ?>" <?php echo $selected; ?> />
		<?php
				}
			}
			?>
		<?php echo isset( $single_option['label'] ) ? $single_option['label'] : ''; ?>
		<?php echo $tooltip; ?>
		<?php echo $document_text; ?>
	</label>
</div>
<?php
			break;

		default:
			// Do nothing
	}

}

/**
 * @since 7.0
 * New quiz popup
 */
function qsm_create_new_quiz_wizard() {
	 global $mlwQuizMasterNext;
	 global $themes_data;
	 qsm_fetch_theme_data();
	?>
<div class="qsm-popup qsm-popup-slide" id="model-wizard" aria-hidden="true">
	<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
		<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
			<header class="qsm-popup__header">
				<h2 class="qsm-popup__title" id="modal-2-title">
					<?php _e( 'Create New Quiz Or Survey', 'quiz-master-next' ); ?></h2>
				<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
			</header>
			<form action="" method="post" id="new-quiz-form">
				<?php wp_nonce_field( 'qsm_new_quiz', 'qsm_new_quiz_nonce' ); ?>
				<main class="qsm-popup__content" id="modal-2-content">
					<div class="qsm-wizard-menu">
						<div class="qsm-logo"><img alt="" src="<?php echo QSM_PLUGIN_URL; ?>/assets/icon-128x128.png">
						</div>
						<div class="qsm-wizard-wrap active" data-show="select_themes">
							<span
								class="qsm-wizard-step-text"><?php echo _e( 'Select theme', 'quiz-master-next' ); ?></span>
						</div>
						<div class="qsm-wizard-wrap" data-show="quiz_settings">
							<span
								class="qsm-wizard-step-text"><?php echo _e( 'Quiz Settings', 'quiz-master-next' ); ?></span>
						</div>
						<div class="qsm-wizard-wrap" data-show="addons_list">
							<span class="qsm-wizard-step-text"><?php echo _e( 'Addons', 'quiz-master-next' ); ?></span>
						</div>
						<span class="qsm-wizard-step-text-optional">
							<?php echo _e( '(Optional)', 'quiz-master-next' ); ?>
						</span>
					</div>
					<ul style="display: none;" class="qsm-new_menu_tab_items">
						<li class="qsm-new_menu_tab_li active" data-show="quiz_settings">
							<a href="#">
								<div class="nav-item-label">
									<span class="nav-item-label-icon dashicons dashicons-admin-generic "></span>
									<div class="nav-item-label-content">
										<h4><?php _e( 'Quiz Setting', 'quiz-master-next' ); ?></h4>
										<span><?php _e( 'Fill quiz settings as per preferences', 'quiz-master-next' ); ?></span>
									</div>
								</div>
							</a>
						</li>
						<li class="qsm-new_menu_tab_li" data-show="select_themes">
							<a href="#">
								<div class="nav-item-label">
									<span class="nav-item-label-icon dashicons dashicons-layout"></span>
									<div class="nav-item-label-content">
										<h4><?php _e( 'Select Themes', 'quiz-master-next' ); ?></h4>
										<span><?php _e( 'Use pre-made theme to speed up the things.', 'quiz-master-next' ); ?></span>
									</div>
								</div>
							</a>
						</li>
						<li class="qsm-new_menu_tab_li" data-show="addons_list">
							<a href="#">
								<div class="nav-item-label">
									<span class="nav-item-label-icon dashicons dashicons-welcome-add-page"></span>
									<div class="nav-item-label-content">
										<h4><?php _e( 'Addons', 'quiz-master-next' ); ?></h4>
										<span><?php _e( 'Use 40+ addons to customize the quiz.', 'quiz-master-next' ); ?></span>
									</div>
								</div>
							</a>
						</li>
					</ul>
					<div id="quiz_settings" class="qsm-new-menu-elements" style="display: none;">
						<div class="input-group">
							<label for="quiz_name"><?php _e( 'Quiz Name', 'quiz-master-next' ); ?>
								<span
									class="qsm-opt-desc"><?php _e( 'Enter a name for this Quiz.', 'quiz-master-next' ); ?></span>
							</label>
							<input type="text" class="quiz_name" name="quiz_name" value="" required="">
						</div>
						<div class="input-group featured_image">
							<label for="quiz_name"><?php _e( 'Quiz Featured Image', 'quiz-master-next' ); ?>
								<span class="qsm-opt-desc">
									<?php _e( 'Enter an external URL or Choose from Media Library.', 'quiz-master-next' ); ?>
									<?php _e( 'Can be changed further from style tab', 'quiz-master-next' ); ?>
								</span>
							</label>
							<span id="qsm_span">
								<input type="text" class="quiz_featured_image" name="quiz_featured_image" value="">
								<a id="set_featured_image"
									class="button "><?php _e( 'Set Featured Image', 'quiz-master-next' ); ?></a>
							</span>
						</div>
						<?php
						$all_settings            = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_options' );
						$quiz_setting_option     = array(
							'form_type'              => array(
								'option_name' => __( 'Form Type', 'quiz-master-next' ),
								'value'       => 0,
							),
							'system'                 => array(
								'option_name' => __( 'Grading System', 'quiz-master-next' ),
								'value'       => 0,
							),
							'pagination'             => array(
								'option_name' => __( 'Questions Per Page', 'quiz-master-next' ),
								'value'       => 0,
							),
							'progress_bar'           => array(
								'option_name' => __( 'Show Progress Bar', 'quiz-master-next' ),
								'value'       => 0,
							),
							'timer_limit'            => array(
								'option_name' => __( 'Time Limit (in Minute)', 'quiz-master-next' ),
								'value'       => 0,
							),
							'enable_pagination_quiz' => array(
								'option_name' => __( 'Show current page number', 'quiz-master-next' ),
								'value'       => 0,
							),
							'require_log_in'         => array(
								'option_name' => __( 'Require User Login', 'quiz-master-next' ),
								'value'       => 0,
							),
							'disable_scroll_next_previous_click' => array(
								'option_name' => __( 'Disable scroll on next and previous button click?', 'quiz-master-next' ),
								'value'       => 0,
							),
						);
							$quiz_setting_option = apply_filters( 'qsm_quiz_wizard_settings_option', $quiz_setting_option );
						if ( $quiz_setting_option ) {
							foreach ( $quiz_setting_option as $key => $single_setting ) {
								$key              = array_search( $key, array_column( $all_settings, 'id' ) );
								$field            = $all_settings[ $key ];
								$field['label']   = $single_setting['option_name'];
								$field['default'] = $single_setting['value'];
								echo '<div class="input-group">';
								QSM_Fields::generate_field( $field, $single_setting['value'] );
								echo '</div>';
							}
						} else {
							_e( 'No settings found!', 'quiz-master-next' );
						}
						?>
					</div>
					<div id="select_themes" class="qsm-new-menu-elements">
						<div class="theme-browser rendered">
							<div class="themes wp-clearfix">
								<ul class="theme-sub-menu">
									<li class="active"><a data-show="downloaded_theme"
											href="#"><?php _e( 'Themes', 'quiz-master-next' ); ?></a></li>
									<?php if ( ! empty( $themes_data ) ) { ?>
									<li><a data-show="browse_themes"
											href="#"><?php _e( 'Explore Marketplace', 'quiz-master-next' ); ?></a></li>
									<?php } ?>
								</ul>
								<div class="theme-wrap" id="downloaded_theme">
									<?php
										qsm_get_installed_theme( 0, 'wizard_theme_list' );
										qsm_get_default_wizard_themes();
									?>
								</div>
								<div class="theme-wrap" id="browse_themes" style="display: none;">
									<?php qsm_get_market_themes(); ?>
								</div>
							</div>
						</div>
					</div>
					<div id="addons_list" class="qsm-new-menu-elements" style="display: none;">
						<div class="qsm-addon-setting-wrap">
							<div id="qsm_add_addons" class="qsm-primary-acnhor">
								<div class="qsm-quiz-page-addon qsm-addon-page-list">
									<?php
										$popular_addons = qsm_get_widget_data( 'popular_products' );
									if ( empty( $popular_addons ) ) {
										$qsm_admin_dd   = qsm_fetch_data_from_script();
										$popular_addons = isset( $qsm_admin_dd['popular_products'] ) ? $qsm_admin_dd['popular_products'] : array();
									}
									?>
									<div class="qsm_popular_addons" id="qsm_popular_addons">
										<div class="popuar-addon-ul">
											<?php
											if ( $popular_addons ) {
												foreach ( $popular_addons as $key => $single_arr ) {
													?>
											<div>
												<a href="<?php echo $single_arr['link']; ?>?utm_source=wizard&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
													target="_blank" rel="noopener">
													<span class="addon-itd-wrap">
														<img alt="" src="<?php echo $single_arr['img']; ?>" />
													</span>
													<span class="addon-price">
														<a class="addon-get-link"
															href="<?php echo $single_arr['link']; ?>?utm_source=wizard&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
															target="_blank" rel="noopener">
															<?php
															_e( 'Buy now', 'quiz-master-next' );
															echo ' : $ ';
															echo array_values( $single_arr['price'] )[0];
															?>
														</a>
													</span>
												</a>
											</div>
											<?php
												}
											}
											?>
										</div>
									</div>
								</div>
							</div>
							<div class="qsm-addon-list-right">
								<span><?php _e( '40+ addons available', 'quiz-master-next' ); ?></span>
								<a style="text-decoration: none; font-size: 15px;" rel="noopener"
									href="http://quizandsurveymaster.com/addons/?utm_source=wizard&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
									target="_blank"><?php _e( 'Browse All Addons', 'quiz-master-next' ); ?></a>
							</div>
						</div>
				</main>
			</form>
			<footer class="qsm-popup__footer">
				<button id="prev-theme-button"
					class="button qsm-wizard-borderless"><?php _e( 'Back', 'quiz-master-next' ); ?></button>
				<button id="prev-quiz-button"
					class="button qsm-wizard-borderless"></span><?php _e( 'Back', 'quiz-master-next' ); ?></button>
				<button class="button qsm-wizard-borderless" data-micromodal-close
					aria-label="Close this dialog window"><?php _e( 'Cancel', 'quiz-master-next' ); ?></button>
				<button id="next-quiz-button"
					class="button button-primary"><?php _e( 'Next', 'quiz-master-next' ); ?></button>
				<button id="choose-addons-button"
					class="button button-primary"><?php _e( 'Next', 'quiz-master-next' ); ?></button>
				<button id="create-quiz-button"
					class="button button-primary"><?php _e( 'Create Quiz', 'quiz-master-next' ); ?></button>
			</footer>
		</div>
	</div>
</div>
<?php
}

/**
 * @since 7.0
 * @return array Template Variable
 */
function qsm_text_template_variable_list() {
	$variable_list = array(
		'%POINT_SCORE%'             => __( 'Score for the quiz when using points', 'quiz-master-next' ),
		'%MAXIMUM_POINTS%'          => __( 'Maximum possible points one can score', 'quiz-master-next' ),
		'%AVERAGE_POINT%'           => __( 'The average amount of points user had per question', 'quiz-master-next' ),
		'%AMOUNT_CORRECT%'          => __( 'The number of correct answers the user had', 'quiz-master-next' ),
		'%AMOUNT_INCORRECT%'        => __( 'The number of incorrect answers the user had', 'quiz-master-next' ),
		'%AMOUNT_ATTEMPTED%'        => __( 'The number of questions are attempted', 'quiz-master-next' ),
		'%TOTAL_QUESTIONS%'         => __( 'The total number of questions in the quiz', 'quiz-master-next' ),
		'%CORRECT_SCORE%'           => __( 'Score for the quiz when using correct answers', 'quiz-master-next' ),
		'%USER_NAME%'               => __( 'The name the user entered before the quiz', 'quiz-master-next' ),
		'%FULL_NAME%'               => __( 'The full name of user with first name and last name', 'quiz-master-next' ),
		'%USER_BUSINESS%'           => __( 'The business the user entered before the quiz', 'quiz-master-next' ),
		'%USER_PHONE%'              => __( 'The phone number the user entered before the quiz', 'quiz-master-next' ),
		'%USER_EMAIL%'              => __( 'The email the user entered before the quiz', 'quiz-master-next' ),
		'%QUIZ_NAME%'               => __( 'The name of the quiz', 'quiz-master-next' ),
		'%QUIZ_LINK%'               => __( 'The link of the quiz', 'quiz-master-next' ),
		'%QUESTIONS_ANSWERS%'       => __( 'Shows the question, the answer the user provided, and the correct answer', 'quiz-master-next' ),
		'%COMMENT_SECTION%'         => __( 'The comments the user entered into comment box if enabled', 'quiz-master-next' ),
		'%TIMER%'                   => __( 'The amount of time user spent on quiz in seconds', 'quiz-master-next' ),
		'%TIMER_MINUTES%'           => __( 'The amount of time user spent on quiz in minutes i.e. If total time is 3 minutes 38 seconds. This will output 3', 'quiz-master-next' ),
		'%TIMER_SECONDS%'           => __( 'The left over seconds user spent on quiz. i.e. If total time is 3 minutes 38 seconds. This will output 38', 'quiz-master-next' ),
		'%CATEGORY_POINTS_X%'       => __( 'X: Category name - The amount of points a specific category earned.', 'quiz-master-next' ),
		'%CATEGORY_SCORE_X%'        => __( 'X: Category name - The score a specific category earned.', 'quiz-master-next' ),
		'%CATEGORY_AVERAGE_POINTS%' => __( 'The average points from all categories.', 'quiz-master-next' ),
		'%CATEGORY_AVERAGE_SCORE%'  => __( 'The average score from all categories.', 'quiz-master-next' ),
		'%QUESTION%'                => __( 'The question that the user answered', 'quiz-master-next' ),
		'%USER_ANSWER%'             => __( 'The answer the user gave for the question', 'quiz-master-next' ),
		'%USER_ANSWERS_DEFAULT%'    => __( 'The answer the user gave for the question with default design', 'quiz-master-next' ),
		'%CORRECT_ANSWER%'          => __( 'The correct answer for the question', 'quiz-master-next' ),
		'%USER_COMMENTS%'           => __( 'The comments the user provided in the comment field for the question', 'quiz-master-next' ),
		'%CORRECT_ANSWER_INFO%'     => __( 'Reason why the correct answer is the correct answer', 'quiz-master-next' ),
		'%CURRENT_DATE%'            => __( 'The Current Date', 'quiz-master-next' ),
		'%QUESTION_POINT_SCORE%'    => __( 'Point Score of the question', 'quiz-master-next' ),
		'%QUESTION_MAX_POINTS%'     => __( 'Maximum points of the question', 'quiz-master-next' ),
		'%FACEBOOK_SHARE%'          => __( 'Displays button to share on Facebook.', 'quiz-master-next' ),
		'%TWITTER_SHARE%'           => __( 'Displays button to share on Twitter.', 'quiz-master-next' ),
		'%RESULT_LINK%'             => __( 'The link of the result page.', 'quiz-master-next' ),
	);
	$variable_list = apply_filters( 'qsm_text_variable_list', $variable_list );
	return $variable_list;
}

add_action( 'admin_init', 'qsm_update_question_type_col_val' );

/**
 * Replace `fill-in-the-blank` value in question_type_column for Fill
 * In The Blank question types.
 *
 * @since version 6.4.12
 */
function qsm_update_question_type_col_val() {
	global $wpdb;
	global $mlwQuizMasterNext;

	if ( version_compare( $mlwQuizMasterNext->version, '6.4.12', '<' ) ) {
		if ( get_option( 'qsm_upated_question_type_val' ) != '1' ) {
			$table_name = $wpdb->prefix . 'mlw_questions';
			$status     = $wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . $table_name . " SET `question_type_new` = REPLACE( `question_type_new`, 'fill-in-the-blank', %d )",
					14
				)
			);

			if ( $status ) {
				update_option( 'qsm_upated_question_type_val', '1' );
			}
		}
	}
}

/**
 * Check and create table if not present
 *
 * @since 7.0.0
 */
function qsm_check_create_tables() {
	global $wpdb;
	$install         = false;

	$quiz_table_name = $wpdb->prefix . 'mlw_quizzes';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_table_name'" ) != $quiz_table_name ) {
		$install = true;
	}

	$quiz_theme_table_name = $wpdb->prefix . 'mlw_themes';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_theme_table_name'" ) != $quiz_theme_table_name ) {
		$install = true;
	}

	$question_terms_table_name = $wpdb->prefix . 'mlw_question_terms';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$question_terms_table_name'" ) != $question_terms_table_name ) {
		$install = true;
	}

	if ( $install ) {
		QSM_Install::install();
	}

}
add_action( 'admin_init', 'qsm_check_create_tables' );

/**
 * Redirect the admin old slug to new slug
 *
 * @since 7.0.0
 */
function qsm_admin_page_access_func() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'quiz-master-next/mlw_quizmaster2.php' ) {
		wp_redirect( admin_url( 'admin.php?page=qsm_dashboard' ) );
		exit;
	}
}
add_action( 'admin_page_access_denied', 'qsm_admin_page_access_func' );

function qsm_fetch_theme_data() {
	global $themes_data;
	$themes_data = qsm_get_widget_data( 'themes' );
	if ( empty( $themes_data ) ) {
		$qsm_admin_dd = qsm_fetch_data_from_script();
		$themes_data  = isset( $qsm_admin_dd['themes'] ) ? $qsm_admin_dd['themes'] : array();
	}
}

function qsm_get_installed_theme( $saved_quiz_theme, $wizard_theme_list = '' ) {
	global $mlwQuizMasterNext;
	$active_themes = $mlwQuizMasterNext->theme_settings->get_active_themes();
	$folder_name   = QSM_THEME_PATH;
	$folder_slug   = QSM_THEME_SLUG;
	$theme_folders = array();
	if ( ! empty( $active_themes ) ) {
		foreach ( $active_themes as $dir ) {
			$theme_dir = $folder_name . $dir['theme'];
			if ( is_dir( $theme_dir ) ) {
				$theme_folders[] = $dir;
			}
		}
	}
	?>
<div class="theme-wrapper theme 
	<?php
	if ( $saved_quiz_theme == '' || $saved_quiz_theme == 0 ) {
		echo 'active';
	}
	?>
	">
	<input style="display: none" type="radio" name="quiz_theme_id" value="0"
		<?php checked( $saved_quiz_theme, '0', true ); ?>>
	<div class="theme-screenshot" id="qsm-theme-screenshot">
		<img alt="" src="<?php echo QSM_PLUGIN_URL . '/assets/screenshot-default-theme.png'; ?>">
		<div class="downloaded-theme-button">
			<span class="button button-primary"><?php _e( 'Select', 'quiz-master-next' ); ?></span>
		</div>
	</div>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo __( 'Default Theme', 'quiz-master-next' ); ?></h2>
		<div class="theme-actions">
			<?php if ( $saved_quiz_theme != 'default' ) { ?>
			<button class="button qsm-activate-theme"><?php _e( 'Activate', 'quiz-master-next' ); ?></button>
			<?php } ?>
		</div>
	</div>
</div>
<?php do_action( 'qsm_add_after_default_theme' ); ?>
<?php
	if ( $theme_folders ) {
		foreach ( $theme_folders as $key => $theme ) {
			$theme_name = $theme['theme'];
			$theme_id   = $theme['id'];
			?>
<div class="theme-wrapper theme 
			<?php
			if ( $saved_quiz_theme == $theme_id ) {
				echo 'active';
			}
			?>
			">
	<input style="display: none" type="radio" name="quiz_theme_id" value="<?php echo (int) $theme_id; ?>"
		<?php checked( $saved_quiz_theme, $theme_id, true ); ?>>
	<div class="theme-screenshot" id="qsm-theme-screenshot">
		<img alt="" src="<?php echo $folder_slug . $theme_name . '/screenshot.png'; ?>" />
		<div class="downloaded-theme-button">
			<span class="button button-primary"><?php _e( 'Select', 'quiz-master-next' ); ?></span>
		</div>
	</div>
	<span class="more-details" style="display: none;"><?php _e( 'Templates', 'quiz-master-next' ); ?></span>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo $theme['theme_name']; ?></h2>
		<div class="theme-actions">
			<?php
			if ( $saved_quiz_theme != $theme_id ) {
				if ( $wizard_theme_list == 'wizard_theme_list' ) {
					?>
			<?php
				} else {
					?>
			<button class="button qsm-activate-theme"><?php _e( 'Activate', 'quiz-master-next' ); ?></button>
			<?php
				}
				?>
			<?php } ?>
			<?php if ( $saved_quiz_theme == $theme_id ) { ?>
			<a class="button button-primary qsm-customize-color-settings"
				href="#"><?php _e( 'Customize', 'quiz-master-next' ); ?></a>
			<?php } ?>
		</div>
	</div>
</div>
<?php
			do_action( 'qsm_add_after_themes' );
		}
	}
}

function qsm_get_default_wizard_themes() {
	global $mlwQuizMasterNext;
	global $themes_data;
	$installed_themes    = $mlwQuizMasterNext->theme_settings->get_installed_themes();
	$default_themes      = array( 'Breeze', 'Fragrance', 'Pool', 'Ivory' );
	$default_themes_data = array();
	$keys_to_unset       = array();
	if ( ! empty( $themes_data ) ) {
		foreach ( $default_themes as $theme ) {
			$key = array_search( $theme, array_column( $installed_themes, 'theme_name' ) );
			if ( $key !== false ) { // installed themes to be removed
				$key_to_unset = array_search( $theme, array_column( $themes_data, 'name' ) );
				if ( $key_to_unset !== false ) {
					$keys_to_unset[] = $key_to_unset;
				}
			} else {
				$key_to_move = array_search( $theme, array_column( $themes_data, 'name' ) );
				if ( $key_to_move !== false ) {
					array_push( $default_themes_data, $themes_data[ $key_to_move ] );
					// $keys_to_unset[] = $key_to_move;
				}
			}
		}
		foreach ( $installed_themes as $theme ) {
			$key = array_search( $theme['theme_name'], array_column( $themes_data, 'name' ) );
			if ( $key !== false ) { // installed themes to be removed
				$keys_to_unset[] = $key;
			}
		}
	}

	$keys_to_unset = array_unique( $keys_to_unset );
	rsort( $keys_to_unset );
	foreach ( $keys_to_unset as $key ) {
		unset( $themes_data[ $key ] );
	}

	if ( ! empty( $default_themes_data ) ) {
		foreach ( $default_themes_data  as $key => $theme ) {
			$theme_name       = $theme['name'];
			$theme_url        = $theme['link'];
			$theme_screenshot = $theme['img'];
			$theme_demo       = $theme['demo'];
			?>
<div class="theme-wrapper theme market-theme">
	<div class="theme-screenshot" id="qsm-theme-screenshot">
		<img alt="" src="<?php echo $theme_screenshot; ?>" />
		<div class="market-theme-url">
			<a class="button button-primary" target="_blank" rel="noopener"
				href="<?php echo $theme_demo; ?>?utm_source=plugin&utm_medium=wizard"><?php _e( 'Live Preview', 'quiz-master-next' ); ?></a>
			<a class="button" target="_blank" rel="noopener"
				href="<?php echo $theme_url; ?>?utm_source=plugin&utm_medium=wizard"><?php _e( 'Buy Now', 'quiz-master-next' ); ?></a>
		</div>
	</div>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo $theme_name; ?></h2>
	</div>
</div>
<?php
		}
	}
}

function qsm_get_market_themes() {
	global $themes_data;
	if ( ! empty( $themes_data ) ) {
		foreach ( $themes_data  as $key => $theme ) {
			$theme_name       = $theme['name'];
			$theme_url        = $theme['link'];
			$theme_screenshot = $theme['img'];
			$theme_demo       = $theme['demo'];
			?>
<div class="theme-wrapper theme market-theme">
	<div class="theme-screenshot" id="qsm-theme-screenshot">
		<img alt="" src="<?php echo $theme_screenshot; ?>" />
		<div class="market-theme-url">
			<a class="button button-primary" target="_blank" rel="noopener"
				href="<?php echo $theme_demo; ?>?utm_source=plugin&utm_medium=wizard"><?php _e( 'Live Preview', 'quiz-master-next' ); ?></a>
			<a class="button" target="_blank" rel="noopener"
				href="<?php echo $theme_url; ?>?utm_source=plugin&utm_medium=wizard"><?php _e( 'Buy Now', 'quiz-master-next' ); ?></a>
		</div>
	</div>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo $theme_name; ?></h2>
	</div>
</div>
<?php
		}
	} else {
		?>
<div class="empty-market-place">
	<span class="dashicons dashicons-welcome-widgets-menus"></span><br />
	<span class="no-themes-message"><?php _e( 'No more themes found.', 'quiz-master-next' ); ?></span>
</div>
<?php
	}
}

/**
 * Display roadmap page
 *
 * @since 7.1.11
 */
function qsm_generate_roadmap_page() {
	?>
<div class="wrap">
	<style>
	iframe {
		height: 1350px;
	}

	body::-webkit-scrollbar {
		width: 0px;
	}
	</style>
	<iframe src="https://app.productstash.io/roadmaps/5f7b1a36636db50029f51d5c/public" height="1350px" width="100%"
		frameborder="0"></iframe>
	<script>
	var ps_config = {
		productId: "d24ad9de-78c7-4835-a2a8-3f5ee0317f31"
	};
	</script>
	<script type="text/javascript" src="https://app.productstash.io/js/productstash-embed.js" defer="defer"></script>
</div>
<?php
}