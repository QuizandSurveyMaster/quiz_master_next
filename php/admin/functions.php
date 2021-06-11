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
						<div class="qsm-logo"><img src="<?php echo QSM_PLUGIN_URL; ?>/assets/icon-128x128.png"></div>
						<div class="qsm-wizard-wrap active" data-show="select_themes">
							<!-- <span class="qsm-wizard-step-number">1</span> -->
							<span
								class="qsm-wizard-step-text"><?php echo _e( 'Select themes', 'quiz-master-next' ); ?></span>
						</div>
						<div class="qsm-wizard-wrap" data-show="quiz_settings">
							<!-- <span class="qsm-wizard-step-number">2</span> -->
							<span
								class="qsm-wizard-step-text"><?php echo _e( 'Quiz Settings', 'quiz-master-next' ); ?></span>
						</div>
						<div class="qsm-wizard-wrap" data-show="addons_list">
							<!-- <span class="qsm-wizard-step-number">3</span> -->
							<span class="qsm-wizard-step-text"><?php echo _e( 'Addons', 'quiz-master-next' ); ?></span>
							<span class="qsm-wizard-step-text-optional">
								<?php echo _e( '(Optional)', 'quiz-master-next' ); ?>
							</span>
						</div>
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
						$all_settings        = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_options' );
						$quiz_setting_option = array(
							'form_type'              => array(
								'option_name' => 'Form Type',
								'value'       => 0,
							),
							'system'                 => array(
								'option_name' => 'Graded System',
								'value'       => 0,
							),
							'pagination'             => array(
								'option_name' => 'Questions Per Page',
								'value'       => 0,
							),
							'progress_bar'           => array(
								'option_name' => 'Show Progress Bar',
								'value'       => 0,
							),
							'timer_limit'            => array(
								'option_name' => 'Time Limit (in Minute)',
								'value'       => 0,
							),
							'enable_pagination_quiz' => array(
								'option_name' => 'Show current page number',
								'value'       => 0,
							),
							'require_log_in'         => array(
								'option_name' => 'Require User Login',
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
											href="#"><?php _e( 'Downloaded', 'quiz-master-next' ); ?></a></li>
									<?php if ( ! empty( $themes_data ) ) { ?>
									<li><a data-show="browse_themes"
											href="#"><?php _e( 'Browse Themes', 'quiz-master-next' ); ?></a></li>
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
							<!-- <div class="qsm-addon-browse-addons"> -->
							<!-- <div class="qsm-addon-anchor-left">
									<div class="qsm-add-addon">
										<a class="active"
											href="#qsm_popular_addons"><?php // _e( 'Popular Addons', 'quiz-master-next' ); ?></a>
										<a
											href="#qsm_onsale_addons"><?php // _e( 'On Sale Addons', 'quiz-master-next' ); ?></a>
										<a
											href="#qsm_new_addons"><?php // _e( 'Recently Updated Addons', 'quiz-master-next' ); ?></a>
									</div>
								</div> -->
							<!-- </div> -->
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
												<a href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
													target="_blank">
													<span class="addon-itd-wrap">
														<img src="<?php echo $single_arr['img']; ?>" />
													</span>
													<span class="addon-price">
														<a class="addon-get-link"
															href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
															target="_blank">
															<?php
															_e( 'Buy now', 'quiz-master-next' );
															echo ' : $ ';
															echo array_values( $single_arr['price'] )[0];
															?>
															<!-- <span class="dashicons dashicons-arrow-right-alt2"></span> -->
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
									<!-- <div class="qsm_popular_addons" id="qsm_onsale_addons" style="display: none;">
										<?php
										$qsm_onsale_addons = qsm_get_widget_data( 'on_sale_products' );
										if ( empty( $qsm_onsale_addons ) ) {
											$qsm_admin_dd      = qsm_fetch_data_from_script();
											$qsm_onsale_addons = isset( $qsm_admin_dd['on_sale_products'] ) ? $qsm_admin_dd['on_sale_products'] : array();
										}
										?>
										<div class="popuar-addon-ul">
											<?php
											if ( $qsm_onsale_addons ) {
												foreach ( $qsm_onsale_addons as $key => $single_arr ) {
													?>
											<div>
												<div class="addon-itd-wrap">
													<div class="addon-image"
														style="background-image: url('<?php echo $single_arr['img']; ?>')">
													</div>
													<div class="addon-title-descption">
														<a class="addon-title" href="<?php echo $single_arr['link']; ?>"
															target="_blank">
															<?php echo $single_arr['name']; ?>
														</a>
														<span class="description">
															<?php echo wp_trim_words( $single_arr['description'], 8 ); ?>
														</span>
														<?php
														if ( str_word_count( $single_arr['description'] ) > 9 ) {
															echo '<a class="read-more" href="' . $single_arr['link'] . '">' . __( 'Show more', 'quiz-master-next' ) . '</a>';
														}
														?>
													</div>
												</div>
												<div class="addon-price">
													<button
														class="button button-primary addon-price-btn"><?php _e( 'Price: ', 'quiz-master-next' ); ?>$<?php echo array_values( $single_arr['price'] )[0]; ?></button>
													<a class="button button-primary addon-get-link"
														href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
														target="_blank"><?php _e( 'Get This Addon', 'quiz-master-next' ); ?>
														<span class="dashicons dashicons-arrow-right-alt2"></span></a>
												</div>
											</div>
													<?php
												}
											}
											?>
										</div>
									</div>
									<div class="qsm_popular_addons" id="qsm_new_addons" style="display: none;">
										<?php
										$new_addons = qsm_get_widget_data( 'new_addons' );
										if ( empty( $popular_addons ) ) {
											$qsm_admin_dd = qsm_fetch_data_from_script();
											$new_addons   = isset( $qsm_admin_dd['new_addons'] ) ? $qsm_admin_dd['new_addons'] : array();
										}
										?>
										<div class="popuar-addon-ul">
											<?php
											if ( $new_addons ) {
												foreach ( $new_addons as $key => $single_arr ) {
													if ( trim( $single_arr['name'] ) == 'Starter Bundle' || trim( $single_arr['name'] ) == 'Premium Bundle' ) {
														continue;
													}
													?>
											<div>
												<div class="addon-itd-wrap">
													<div class="addon-image"
														style="background-image: url('<?php echo $single_arr['img']; ?>')">
													</div>
													<div class="addon-title-descption">
														<a class="addon-title" href="<?php echo $single_arr['link']; ?>"
															target="_blank">
															<?php echo $single_arr['name']; ?>
														</a>
														<span class="description">
															<?php echo wp_trim_words( $single_arr['description'], 8 ); ?>
														</span>
														<?php
														if ( str_word_count( $single_arr['description'] ) > 9 ) {
															echo '<a class="read-more" href="' . $single_arr['link'] . '">' . __( 'Show more', 'quiz-master-next' ) . '</a>';
														}
														?>
													</div>
												</div>
												<div class="addon-price">
													<button
														class="button button-primary addon-price-btn"><?php _e( 'Price: ', 'quiz-master-next' ); ?>$<?php echo array_values( $single_arr['price'] )[0]; ?></button>
													<a class="button button-primary addon-get-link"
														href="<?php echo $single_arr['link']; ?>?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
														target="_blank"><?php _e( 'Get This Addon', 'quiz-master-next' ); ?>
														<span class="dashicons dashicons-arrow-right-alt2"></span></a>
												</div>
											</div>
													<?php
												}
											}
											?>
										</div>
									</div> -->
								</div>
							</div>
							<div class="qsm-addon-list-right">
								<span><?php _e( '40+ addons available', 'quiz-master-next' ); ?></span>
								<a style="text-decoration: none; font-size: 15px;"
									href="http://quizandsurveymaster.com/addons/?utm_source=qsm-addons-page&utm_medium=plugin&utm_content=all-addons-top&utm_campaign=qsm_plugin"
									target="_blank"><?php _e( 'Browse All Addons', 'quiz-master-next' ); ?></a>
							</div>
							<?php
										// $addons = array(
										// array(
										// 'name' => 'Reporting And Analysis',
										// 'link' => 'https://quizandsurveymaster.com/downloads/results-analysis/',
										// 'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Reporting-And-Analysis.jpg',
										// 'attribute' => 'recommended',
										// ),
										// array(
										// 'name' => 'Export & Import',
										// 'link' => 'https://quizandsurveymaster.com/downloads/export-import/',
										// 'img' => 'https://t6k8i7j6.stackpathcdn.com/wp-content/uploads/edd/2020/04/Export-Import.jpg',
										// 'attribute' => 'recommended',
										// ),
										// );
										// $addons = apply_filters('qsm_addon_list_wizard', $addons);
										// $recommended_addon_str = '';
										// $recommended_addon_str .= '<ul>';
										// if ($addons) {
										// foreach ($addons as $single_addon) {
										// $recommended_addon_str .= '<li>';
										// if (isset($single_addon['attribute']) && $single_addon['attribute'] != '') {
										// $attr = $single_addon['attribute'];
										// $recommended_addon_str .= '<span class="ra-attr qra-att-' . $attr . '">' . $attr . '</span>';
										// }
										// $link = isset($single_addon['link']) ? $single_addon['link'] : '';
										// $recommended_addon_str .= '<a target="_blank" href="' . $link . '">';
										// if (isset($single_addon['img']) && $single_addon['img'] != '') {
										// $img = $single_addon['img'];
										// $recommended_addon_str .= '<img src="' . $img . '"/>';
										// }
										// $recommended_addon_str .= '</a>';
										// $recommended_addon_str .= '</li>';
										// }
										// } else {
										// $recommended_addon_str .= 'No addons found!';
										// }
										// $recommended_addon_str .= '</ul>';
										// echo $recommended_addon_str;
							?>
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
		'%TIMER_MINUTES%'           => __( 'The amount of time user spent on quiz in minutes', 'quiz-master-next' ),
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
	$quiz_table_name = $wpdb->prefix . 'mlw_quizzes';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_table_name'" ) != $quiz_table_name ) {
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
	$dummy_data  = array(
		array(
			'name' => 'Breeze',
			'img'  => 'https://st.depositphotos.com/1018248/3475/i/600/depositphotos_34752923-stock-photo-breeze-swaying-the-grass.jpg',
			'link' => 'https://quizandsurveymaster.com/?post_type=download&p=3437',
		),
		array(
			'name' => 'Fragrance',
			'img'  => 'data:image/jpeg;base64,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',
			'link' => 'https://quizandsurveymaster.com/?post_type=download&p=3438',
		),
		array(
			'name' => 'Pool',
			'img'  => 'data:image/jpeg;base64,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',
			'link' => 'https://quizandsurveymaster.com/?post_type=download&p=3439',
		),
		array(
			'name' => 'Ivory',
			'img'  => 'https://static.euronews.com/articles/stories/04/87/87/40/400x225_cmsv2_82ec1fbe-3798-5436-8d1b-b6d9d58cbb63-4878740.jpg',
			'link' => 'https://quizandsurveymaster.com/?post_type=download&p=3439',
		),
		array(
			'name' => 'Elegance',
			'img'  => 'https://pbs.twimg.com/profile_images/520238432753168386/2W0fFd5C.jpeg',
			'link' => 'https://quizandsurveymaster.com/?post_type=download&p=3439',
		),
	);
	$themes_data = qsm_get_widget_data( 'popular_products' );
	if ( empty( $themes_data ) ) {
		$qsm_admin_dd = qsm_fetch_data_from_script();
		$themes_data  = isset( $qsm_admin_dd['popular_products'] ) ? $qsm_admin_dd['popular_products'] : array();
	}
	$themes_data = array_merge( $dummy_data, $themes_data );
}

function qsm_get_installed_theme( $saved_quiz_theme, $wizard_theme_list = '' ) {
	global $mlwQuizMasterNext;
	$active_themes = $mlwQuizMasterNext->theme_settings->get_active_themes( array( 'theme', 'theme_name' ) );
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
		<img src="<?php echo QSM_PLUGIN_URL . '/assets/screenshot-default-theme.png'; ?>">
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
		<img src="<?php echo $folder_slug . $theme_name . '/screenshot.png'; ?>" />
	</div>
	<span class="more-details" style="display: none;"><?php _e( 'Templates', 'quiz-master-next' ); ?></span>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo $theme['theme_name']; ?></h2>
		<div class="theme-actions">
			<?php
			if ( $saved_quiz_theme != $theme_id ) {
				if ( $wizard_theme_list == 'wizard_theme_list' ) {
					?>
			<!-- <button class="button qsm-activate-theme"><?php // _e('Select Theme', 'quiz-master-next'); ?></button> -->
			<?php
				} else {
					?>
			<button class="button qsm-activate-theme"><?php _e( 'Activate', 'quiz-master-next' ); ?></button>
			<?php
				}
				?>
			<!-- <a class="button button-primary load-customize hide-if-no-customize" href="#"><?php // _e('Live Preview', 'quiz-master-next') ?></a> -->
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
					$keys_to_unset[] = $key_to_move;
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
			?>
<div class="theme-wrapper theme market-theme">
	<div class="theme-screenshot" id="qsm-theme-screenshot">
		<img src="<?php echo $theme_screenshot; ?>" />
		<a class="button button-primary market-theme-url" target="__blank"
			href="<?php echo $theme_url; ?>"><?php _e( 'Live Preview', 'quiz-master-next' ); ?></a>
	</div>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo $theme_name; ?></h2>
	</div>
</div>
<?php
		}
	}
}
function qsm_get_market_themes_label() {
	global $themes_data;
	if ( ! empty( $themes_data ) ) {
	}
}

function qsm_get_market_themes() {
	global $themes_data;
	if ( ! empty( $themes_data ) ) {
		foreach ( $themes_data  as $key => $theme ) {
			$theme_name       = $theme['name'];
			$theme_url        = $theme['link'];
			$theme_screenshot = $theme['img'];
			?>
<div class="theme-wrapper theme market-theme">
	<div class="theme-screenshot" id="qsm-theme-screenshot">
		<img src="<?php echo $theme_screenshot; ?>" />
		<a class="button button-primary market-theme-url" target="__blank"
			href="<?php echo $theme_url; ?>"><?php _e( 'Live Preview', 'quiz-master-next' ); ?></a>
	</div>
	<div class="theme-id-container">
		<h2 class="theme-name" id="emarket-name"><?php echo $theme_name; ?></h2>
	</div>
</div>
<?php
		}
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
	</style>
	<iframe src="https://app.productstash.io/roadmaps/5f7b1a36636db50029f51d5c/public" height="900" width="100%"
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