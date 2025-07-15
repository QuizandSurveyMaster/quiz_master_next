<?php
$themes_data = array();
global $pro_themes;
$pro_themes  = array( 'qsm-theme-pool', 'qsm-theme-breeze', 'qsm-theme-fragrance', 'qsm-theme-ivory', 'qsm-theme-sigma', 'qsm-theme-fortune', 'qsm-theme-pixel', 'qsm-theme-sapience', 'Breeze', 'Fragrance', 'Pool', 'Ivory', 'Sigma', 'Fortune', 'Pixel', 'Sapience' );

/**
 * @since 6.4.5
 * @param
 */
function qsm_fetch_data_from_xml() {
	$file        = esc_url( 'https://quizandsurveymaster.com/addons.xml' );
	$response    = wp_remote_post( $file, array( 'sslverify' => false ) );

	if ( is_wp_error( $response ) || 404 === $response['response']['code'] ) {
		return '<p>' . __( 'Something went wrong', 'quiz-master-next' ) . '</p>';
	} else {
		$body    = wp_remote_retrieve_body( $response );
		return $xml  = simplexml_load_string( $body );
	}
}

add_action( 'qmn_quiz_created', 'qsm_redirect_to_edit_page', 10, 1 );

/**
 * @since 6.4.5
 * @param int $quiz_id Quiz id.
 */
function qsm_redirect_to_edit_page( $quiz_id ) {
	if ( ! is_qsm_block_api_call() ) {
		link_featured_image( $quiz_id );
		$url = admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' . $quiz_id );
		wp_safe_redirect( $url );
		exit;
	}
}

/**
 * Links quiz featured image if exists
 *
 * @param int $quiz_id
 * @return void
 */
function link_featured_image( $quiz_id ) {
	$url = isset( $_POST['quiz_featured_image'] ) ? esc_url_raw( wp_unslash( $_POST['quiz_featured_image'] ) ) : '';
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
	global $mlwQuizMasterNext;
	if ( 1 !== intval( get_option( 'qsm_update_db_column', '' ) ) ) {

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
						'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $table, $col_name
					)
				);

				if ( empty( $table_col_obj ) ) {
					$mlwQuizMasterNext->wpdb_alter_table_query( 'ALTER TABLE ' . $table . ' ADD ' . $col_name . ' ' . $col_def );
				}
			}
		}

		update_option( 'qsm_update_db_column', 1 );
	}

	// Update result db
	if ( 1 !== intval( get_option( 'qsm_update_result_db_column', '' ) ) ) {
		global $wpdb;
		$result_table_name       = $wpdb->prefix . 'mlw_results';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'form_type'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $mlwQuizMasterNext->wpdb_alter_table_query( "ALTER TABLE $result_table_name ADD form_type INT NOT NULL" ) ) {
				update_option( 'qsm_update_result_db_column', 1 );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Creating Column form_type in' . $result_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}else {
			update_option( 'qsm_update_result_db_column', 1 );
		}
	}

	/**
	 * Changed the system word to quiz_system in quiz table
	 *
	 * @since 7.0.0
	 */
	if ( 1 !== intval( get_option( 'qsm_update_quiz_db_column', '' ) ) ) {
		global $wpdb;
		$quiz_table_name     = $wpdb->prefix . 'mlw_quizzes';
		$table_quiz_col_obj  = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $quiz_table_name, 'system'
			)
		);
		if ( ! empty( $table_quiz_col_obj ) ) {
			if ( $mlwQuizMasterNext->wpdb_alter_table_query( "ALTER TABLE $quiz_table_name CHANGE `system` `quiz_system` INT(11) NOT NULL;" ) ) {
				update_option( 'qsm_update_quiz_db_column', 1 );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Changing Columns system,quiz_system in' . $quiz_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}else {
			update_option( 'qsm_update_quiz_db_column', 1 );
		}
	}

	/**
	 * Changed result table column data type
	 *
	 * @since 7.0.1
	 */
	if ( 1 !== intval( get_option( 'qsm_update_result_db_column_datatype', '' ) ) ) {
		global $wpdb;
		$result_table_name       = $wpdb->prefix . 'mlw_results';
		$table_quiz_result_obj   = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'quiz_results'
			), ARRAY_A
		);
		if ( isset( $table_quiz_result_obj['DATA_TYPE'] ) && 'text' === $table_quiz_result_obj['DATA_TYPE'] ) {
			if ( $mlwQuizMasterNext->wpdb_alter_table_query( "ALTER TABLE $result_table_name CHANGE `quiz_results` `quiz_results` LONGTEXT;" ) ) {
				update_option( 'qsm_update_result_db_column_datatype', 1 );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Changing Columns quiz_results in' . $result_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}else {
			update_option( 'qsm_update_result_db_column_datatype', 1 );
		}
	}

	/**
	 * Add new column in question table
	 *
	 * @since 7.0.3
	 */
	if ( 1 !== intval( get_option( 'qsm_add_new_column_question_table_table', '' ) ) ) {
		global $wpdb;
		$question_table_name     = $wpdb->prefix . 'mlw_questions';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $question_table_name, 'deleted_question_bank'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $mlwQuizMasterNext->wpdb_alter_table_query( "ALTER TABLE $question_table_name ADD deleted_question_bank INT NOT NULL" ) ) {
				update_option( 'qsm_add_new_column_question_table_table', 1);
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Creating Columns deleted_question_bank in' . $question_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}else {
			update_option( 'qsm_add_new_column_question_table_table', 1);
		}
	}
	/**
	 * Add new column in the results table
	 *
	 * @since 7.3.7
	 */
	if ( 1 !== intval( get_option( 'qsm_update_result_db_column_page_url', '' ) ) ) {
		global $wpdb;
		$result_table_name       = $wpdb->prefix . 'mlw_results';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'page_url'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $mlwQuizMasterNext->wpdb_alter_table_query( "ALTER TABLE $result_table_name ADD page_url varchar(255) NOT NULL" ) ) {
				update_option( 'qsm_update_result_db_column_page_url', 1 );
			} else {
				$error = $wpdb->last_error;
				$mlwQuizMasterNext->log_manager->add( "Error Creating Column page_url in {$result_table_name}", "Tried {$wpdb->last_query} but got {$error}.", 0, 'error' );
			}
		}else {
			update_option( 'qsm_update_result_db_column_page_url', 1 );
		}
	}

	/**
	 * Add new column in the results table
	 *
	 * @since 7.3.7
	 */
	if ( 1 !== intval( get_option( 'qsm_update_result_db_column_page_name', '' ) ) ) {
		global $wpdb;
		$result_table_name       = $wpdb->prefix . 'mlw_results';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'page_name'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $mlwQuizMasterNext->wpdb_alter_table_query( "ALTER TABLE $result_table_name ADD page_name varchar(255) NOT NULL" ) ) {
				update_option( 'qsm_update_result_db_column_page_name', 1 );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Creating Column page_name in' . $result_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}else {
			update_option( 'qsm_update_result_db_column_page_name', 1 );
		}
	}

	/**
	 * Add new column in the results table
	 *
	 * @since 9.0.1
	 */
	if ( 1 !== intval( get_option( 'qsm_update_db_column_charset_utf8mb4_unicode_ci', '' ) ) ) {
		global $wpdb;

		$tables_to_convert = array(
			"{$wpdb->prefix}mlw_qm_audit_trail",
			"{$wpdb->prefix}mlw_questions",
			"{$wpdb->prefix}mlw_quizzes",
			"{$wpdb->prefix}mlw_results",
		);

		$success = true;

		foreach ( $tables_to_convert as $table ) {
			$query = "ALTER TABLE $table CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
			$result = $mlwQuizMasterNext->wpdb_alter_table_query($query);

			if ( ! $result ) {
				$success = false;
				$mlwQuizMasterNext->log_manager->add( 'Error updating column charset utf8mb4_unicode_ci', "Tried $query but got {$wpdb->last_error}.", 0, 'error' );
			}
			update_option( 'qsm_update_db_column_charset_utf8mb4_unicode_ci', 1 );
		}
	}
}

add_action( 'admin_init', 'qsm_change_the_post_type' );

/**
 * @since version 6.4.8
 * Transfer all quiz post to new cpt 'qsm_quiz'
 */
function qsm_change_the_post_type() {
	$all_plugins = get_plugins();
	if ( empty( $all_plugins['sensei-lms/sensei-lms.php'] ) && 1 !== intval( get_option( 'qsm_change_the_post_type', '' ) ) ) {
		$post_arr    = array(
			'post_type'      => 'quiz',
			'posts_per_page' => -1,
			'post_status'    => array( 'publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'trash' ),
		);
		$my_query    = new WP_Query( $post_arr );

		if ( $my_query->have_posts() ) {
			while ( $my_query->have_posts() ) {
				$my_query->the_post();

				$post_id     = get_the_ID();
				$post_obj    = get_post( $post_id );
				if ( 'trash' === $post_obj->post_status ) {
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
	$type        = isset( $single_option['type'] ) ? $single_option['type'] : 'text';
	$show        = isset( $single_option['show'] ) ? explode( ',', $single_option['show'] ) : array();
	$show_class  = '';
	if ( $show ) {
		foreach ( $show as $show_value ) {
			$show_class .= 'qsm_show_question_type_' . trim( $show_value ) . ' ';
		}
		$show_class .= ' qsm_hide_for_other';
	}
	$tooltip         = '';
	$document_text   = '';
	if ( isset( $single_option['tooltip'] ) && '' !== $single_option['tooltip'] ) {
		$tooltip .= '<span class="dashicons dashicons-editor-help qsm-tooltips-icon">';
		$tooltip .= '<span class="qsm-tooltips">' . esc_html( $single_option['tooltip'] ) . '</span>';
		$tooltip .= '</span>';
	}
	if ( isset( $single_option['documentation_link'] ) && '' !== $single_option['documentation_link'] ) {
		$document_text   .= '<a class="qsm-question-doc" href="' . esc_url( $single_option['documentation_link'] ) . '" target="_blank" title="' . __( 'View Documentation', 'quiz-master-next' ) . '">';
		$document_text   .= '<span class="dashicons dashicons-editor-help"></span>';
		$document_text   .= '</a>';
	}
	switch ( $type ) {
		case 'text':
			?>
			<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo isset( $single_option['default'] ) ? esc_html( $single_option['default'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>" />
			</div>
			<?php
			break;

		case 'number':
			?>
			<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<input type="number" name="<?php echo esc_attr( $key ); ?>" value="<?php echo isset( $single_option['default'] ) ? esc_html( $single_option['default'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>" />
			</div>
			<?php
			break;

		case 'select':
			?>
			<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
					<?php
					$default = isset( $single_option['default'] ) ? $single_option['default'] : '';
					if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
						foreach ( $single_option['options'] as $okey => $value ) {
							?>
							<option value="<?php echo esc_attr( $okey ); ?>" <?php echo ( $okey === $default ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr( $value ); ?></option>
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
			<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"><?php echo isset( $single_option['default'] ) ? esc_html( $single_option['default'] ) : ''; ?></textarea>
			</div>
			<?php
			break;

		case 'category':
			?>
			<div id="category_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<div id="categories">
					<a id="qsm-category-add-toggle" class="hide-if-no-js">
						<?php esc_html_e( '+ Add New Category', 'quiz-master-next' ); ?>
					</a>
					<p id="qsm-category-add" style="display: none;">
						<input type="radio" style="display: none;" name="category" class="category-radio" id="new_category_new" value="new_category"><label for="new_category_new"><input type='text' id='new_category' value='' placeholder="Add new category" /></label>
					</p>
				</div>
			</div>
			<?php
			break;

		case 'multi_category':
			?>
			<div id="multi_category_area" class="qsm-row multi_category_area <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<input type='text' class='qsm-category-filter' placeholder=' <?php esc_html_e( ' Search', 'quiz-master-next' ); ?> '>
				<div id="multi_categories_wrapper" class="categorydiv qsm_categories_list">
					<ul id=" multicategories_checklist" class="qsm_category_checklist categorychecklist form-no-clear">
						<?php
						wp_terms_checklist(
							0, array(
								'taxonomy'             => 'qsm_category',
								'descendants_and_self' => 0,
								'selected_cats'        => false,
								'echo'                 => true,
							)
						);
						?>
					</ul>
				</div>
				<a href='javascript:void(0)' class='add-multiple-category'><?php esc_html_e( '+ Add New Category ', 'quiz-master-next' ); ?></a>
			</div>
			<?php
			break;

		case 'multi_checkbox':
			?>
			<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
				<?php
				$parent_key  = $key;
				$default     = isset( $single_option['default'] ) ? $single_option['default'] : '';
				if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
					foreach ( $single_option['options'] as $key => $value ) {
						?>
						<input name="<?php echo esc_attr( $parent_key ); ?>[]" type="checkbox" value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $default ) ? 'checked' : ''; ?> />
						<?php echo esc_attr( $value ); ?><br />
						<?php
					}
				}
				?>
			</div>
			<?php
			break;

		case 'single_checkbox':
			?>
			<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row <?php echo esc_attr( $show_class ); ?>">
				<label>
					<?php
					$parent_key  = $key;
					$default     = isset( $single_option['default'] ) ? $single_option['default'] : '';
					if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
						foreach ( $single_option['options'] as $key => $value ) {
							?>
							<input name="<?php echo esc_attr( $parent_key ); ?>" id="<?php echo esc_attr( $parent_key ); ?>" type="checkbox"value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $default ) ? 'checked' : ''; ?> />
							<?php
						}
					}
					?>
					<?php echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : ''; ?>
					<?php echo wp_kses_post( $tooltip ); ?>
					<?php echo wp_kses_post( $document_text ); ?>
				</label>
			</div>
			<?php
			break;

		default:
		// Do nothing
	}
}

/**
 * Generate Question Options
 * @since  8.0
 * @param arr $single_option
 */
function qsm_generate_question_option( $key, $single_option ) {
	$type        = isset( $single_option['type'] ) ? $single_option['type'] : 'text';
	$show        = isset( $single_option['show'] ) ? explode( ',', $single_option['show'] ) : array();
	$show_class  = '';
	if ( in_array( $key, array( 'correct_answer_info', 'comments', 'hint' ), true ) ) {
		$show_class .= 'core-option ';
	}
	if ( $show ) {
		$show_class .= 'qsm_hide_for_other ';
		foreach ( $show as $show_value ) {
			$show_class .= 'qsm_show_question_type_' . trim( $show_value ) . ' ';
		}
	}
	$tooltip = '';
	if ( isset( $single_option['tooltip'] ) && '' !== $single_option['tooltip'] ) {
		$tooltip .= '<span class="dashicons dashicons-editor-help qsm-tooltips-icon">';
		$tooltip .= '<span class="qsm-tooltips">' . esc_html( $single_option['tooltip'] ) . '</span>';
		$tooltip .= '</span>';
	}
	?>
	<div id="<?php echo esc_attr( $key ); ?>_area" class="qsm-row qsm-toggle-box <?php echo esc_attr( $show_class ); ?>">
		<label class="qsm-toggle-box-handle">
			<?php echo isset( $single_option['heading'] ) ? wp_kses_post( $single_option['heading'] ) : ''; ?>
			<?php echo wp_kses_post( $tooltip ); ?>
			<span class="toggle-indicator" aria-hidden="true"></span>
		</label>
		<div class="qsm-toggle-box-content qsm-editor-wrap">
			<?php
			switch ( $type ) {
				case 'text':
					if ( isset( $single_option['label'] ) ) {
						?><label><?php echo wp_kses_post( $single_option['label'] ); ?></label><?php
					}
					?>
					<input type="text" name="<?php echo esc_attr( $key ); ?>" value="<?php echo isset( $single_option['default'] ) ? esc_html( $single_option['default'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>" />
					<?php
					break;
				case 'multi_text':
					$parent_key = $key;
					if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
						foreach ( $single_option['options'] as $key => $value ) {
							?>
							<label><?php echo wp_kses_post( $value ); ?>
								<input name="<?php echo esc_attr( $parent_key ); ?>[<?php echo esc_attr( $key ); ?>]" type="text" id="<?php echo esc_attr( $parent_key . '-' . $key ); ?>" />
							</label>
							<br />
							<?php
						}
					}
					break;
				case 'number':
					if ( isset( $single_option['label'] ) ) {
						?><label><?php echo wp_kses_post( $single_option['label'] ); ?></label><?php
					}
					?>
					<input type="number" name="<?php echo esc_attr( $key ); ?>" value="<?php echo isset( $single_option['default'] ) ? esc_html( $single_option['default'] ) : ''; ?>" id="<?php echo esc_attr( $key ); ?>" />
					<?php
					break;

				case 'select':
					if ( isset( $single_option['label'] ) ) {
						?><label><?php echo wp_kses_post( $single_option['label'] ); ?></label><?php
					}
					?>
					<select name="<?php echo esc_attr( $key ); ?>" id="<?php echo esc_attr( $key ); ?>">
						<?php
						$default = isset( $single_option['default'] ) ? $single_option['default'] : '';
						if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
							foreach ( $single_option['options'] as $okey => $value ) {
								?>
								<option value="<?php echo esc_attr( $okey ); ?>" <?php echo ( $okey === $default ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr( $value ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<?php
					break;

				case 'textarea':
					if ( isset( $single_option['label'] ) ) {
						?><label><?php echo wp_kses_post( $single_option['label'] ); ?></label><?php
					}
					?>
					<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"><?php echo isset( $single_option['default'] ) ? esc_html( $single_option['default'] ) : ''; ?></textarea>
					<?php
					break;

				case 'multi_checkbox':
					$parent_key  = $key;
					$default     = isset( $single_option['default'] ) ? explode( ',', $single_option['default'] ) : '';
					if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
						foreach ( $single_option['options'] as $key => $value ) {
							?>
							<label>
								<input name="<?php echo esc_attr( $parent_key ); ?>[]" type="checkbox" value="<?php echo esc_attr( $key ); ?>" <?php echo in_array( $key, $default, true ) ? 'checked' : ''; ?> />
								<?php echo esc_attr( $value ); ?>
							</label>
							<br />
							<?php
						}
					}
					break;

				case 'single_checkbox':
					$parent_key  = $key;
					$default     = isset( $single_option['default'] ) ? $single_option['default'] : '';
					if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
						?>
						<label>
							<?php
							foreach ( $single_option['options'] as $key => $value ) {
								?>
								<input name="<?php echo esc_attr( $parent_key ); ?>" id="<?php echo esc_attr( $parent_key ); ?>" type="checkbox"value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $default ) ? 'checked' : ''; ?> />
								<?php
							}
							echo isset( $single_option['label'] ) ? wp_kses_post( $single_option['label'] ) : '';
							?>
						</label>
						<?php
					}
					break;

				default:
				do_action( 'qsm_generate_question_option_after', $key, $single_option );
				// Do nothing
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * @since 8.2.3
 * Settings to create Quiz
 */
if ( ! function_exists( 'qsm_settings_to_create_quiz' ) ) {
	function qsm_settings_to_create_quiz( $require_field = false ) {
		global $globalQuizsetting, $mlwQuizMasterNext, $themes_data;

		$quiz_setting_option = array(
			'form_type'                              => array(
				'label'   => __( 'Form Type', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['form_type'],
				'default' => 0,
				'type'    => 'select',
				'options' => array(
					array(
						'label' => __( 'Quiz', 'quiz-master-next' ),
						'value' => 0,
					),
					array(
						'label' => __( 'Survey', 'quiz-master-next' ),
						'value' => 1,
					),
					array(
						'label' => __( 'Simple Form', 'quiz-master-next' ),
						'value' => 2,
					),
				),
			),
			'system'                                 => array(
				'label'   => __( 'Grading System', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['system'],
				'default' => 0,
				'type'    => 'radio',
				'options' => array(
					array(
						'label' => __( 'Correct/Incorrect', 'quiz-master-next' ),
						'value' => 0,
					),
					array(
						'label' => __( 'Points', 'quiz-master-next' ),
						'value' => 1,
					),
					array(
						'label' => __( 'Both', 'quiz-master-next' ),
						'value' => 3,
					),
				),
				'help'    => __( 'Select the system for grading the quiz.', 'quiz-master-next' ),
			),
			'show_question_featured_image_in_result' => array(
				'label'   => __( 'Show question featured image in results page', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['show_question_featured_image_in_result'],
				'type'    => 'toggle',
				'options' => array(
					array(
						'value' => 1,
					),
				),
				'default' => 0,
			),
			'enable_pagination_quiz'                 => array(
				'label'   => __( 'Show current page number', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['enable_pagination_quiz'],
				'type'    => 'toggle',
				'options' => array(
					array(
						'value' => 1,
					),
				),
				'default' => 0,
			),
			'progress_bar'                           => array(
				'label'   => __( 'Show progress bar', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['progress_bar'],
				'type'    => 'toggle',
				'options' => array(
					array(
						'value' => 1,
					),
				),
				'default' => 0,
			),
			'require_log_in'                         => array(
				'label'   => __( 'Require User Login', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['require_log_in'],
				'type'    => 'toggle',
				'options' => array(
					array(
						'value' => 1,
					),
				),
				'default' => 0,
				'help'    => __( 'Enabling this allows only logged in users to take the quiz', 'quiz-master-next' ),
			),
			'timer_limit'                            => array(
				'label'   => __( 'Time Limit (in Minute)', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['timer_limit'],
				'type'    => 'number',
				'default' => 0,
				'help'    => __( 'Leave 0 for no time limit', 'quiz-master-next' ),
			),
			'pagination'                             => array(
				'label'   => __( 'Questions Per Page', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['pagination'],
				'type'    => 'number',
				'default' => 0,
				'help'    => __( 'Override the default pagination created on questions tab', 'quiz-master-next' ),
			),
			'enable_contact_form'                    => array(
				'label'   => __( 'Display a contact form after quiz', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['contact_info_location'],
				'type'    => 'toggle',
				'options' => array(
					array(
						'value' => 1,
					),
				),
			),
			'disable_first_page'                     => array(
				'label'   => __( 'Disable first page on quiz', 'quiz-master-next' ),
				'value'   => $globalQuizsetting['disable_first_page'],
				'type'    => 'toggle',
				'options' => array(
					array(
						'value' => 1,
					),
				),
				'default' => 0,
			),
		);
		$quiz_setting_option = apply_filters( 'qsm_quiz_wizard_settings_option', $quiz_setting_option );
		$fields = array();
		foreach ( $quiz_setting_option as $key => $single_setting ) {
			$single_setting['id'] = $key;
			if ( ! $require_field ) {
				echo '<div class="input-group" id="qsm-quiz-options-' . esc_html( $key ) . '">';
				QSM_Fields::generate_field( $single_setting, $single_setting['value'] );
				echo '</div>';
			} else {
				$fields[] = $single_setting;
			}
		}
		if ( $require_field ) {
			return $fields;
		}
	}
}

/**
 * @since 7.0
 * @return array Template Variable
 */
function qsm_text_template_variable_list() {
	$variable_list   = array(
		'Core' => array(
			'%POINT_SCORE%'               => __( 'Score for the quiz when using points', 'quiz-master-next' ),
			'%MAXIMUM_POINTS%'            => __( 'Maximum possible points one can score', 'quiz-master-next' ),
			'%MINIMUM_POINTS%'            => __( 'Minimum possible points one can score', 'quiz-master-next' ),
			'%AVERAGE_POINT%'             => __( 'The average amount of points user had per question', 'quiz-master-next' ),
			'%AMOUNT_CORRECT%'            => __( 'The number of correct answers the user had', 'quiz-master-next' ),
			'%AMOUNT_INCORRECT%'          => __( 'The number of incorrect answers the user had', 'quiz-master-next' ),
			'%AMOUNT_ATTEMPTED%'          => __( 'The number of questions are attempted', 'quiz-master-next' ),
			'%TOTAL_QUESTIONS%'           => __( 'The total number of questions in the quiz', 'quiz-master-next' ),
			'%CORRECT_SCORE%'             => __( 'Score for the quiz when using correct answers', 'quiz-master-next' ),
			'%USER_NAME%'                 => __( 'The name the user entered before the quiz', 'quiz-master-next' ),
			'%FULL_NAME%'                 => __( 'The full name of user with first name and last name', 'quiz-master-next' ),
			'%USER_BUSINESS%'             => __( 'The business the user entered before the quiz', 'quiz-master-next' ),
			'%USER_PHONE%'                => __( 'The phone number the user entered before the quiz', 'quiz-master-next' ),
			'%USER_EMAIL%'                => __( 'The email the user entered before the quiz', 'quiz-master-next' ),
			'%QUIZ_NAME%'                 => __( 'The name of the quiz', 'quiz-master-next' ),
			'%QUIZ_LINK%'                 => __( 'The link of the quiz', 'quiz-master-next' ),
			'%QUESTIONS_ANSWERS%'         => __( 'Shows the question, the answer the user provided, and the correct answer', 'quiz-master-next' ),
			'%COMMENT_SECTION%'           => __( 'The comments the user entered into comment box if enabled', 'quiz-master-next' ),
			'%TIMER%'                     => __( 'The amount of time user spent on quiz in seconds', 'quiz-master-next' ),
			'%TIMER_MINUTES%'             => __( 'The amount of time user spent on quiz in minutes i.e. If total time is 3 minutes 38 seconds. This will output 3', 'quiz-master-next' ),
			'%TIMER_SECONDS%'             => __( 'The left over seconds user spent on quiz. i.e. If total time is 3 minutes 38 seconds. This will output 38', 'quiz-master-next' ),
			'%CATEGORY_POINTS_X%'         => __( 'X: Category name - The amount of points a specific category earned.', 'quiz-master-next' ),
			'%CATEGORY_SCORE_X%'          => __( 'X: Category name - This variable displays the percentage achieved in the selected category.', 'quiz-master-next' ),
			'%CATEGORY_AVERAGE_POINTS%'   => __( 'The average points from all categories.', 'quiz-master-next' ),
			'%CATEGORY_AVERAGE_SCORE%'    => __( 'The average score from all categories.', 'quiz-master-next' ),
			'%QUESTION_MAX_POINTS%'       => __( 'Maximum points of the question', 'quiz-master-next' ),
			'%RESULT_LINK%'               => __( 'The link of the result page.', 'quiz-master-next' ),
			'%CONTACT_X%'                 => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
			'%CONTACT_ALL%'               => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
			'%AVERAGE_CATEGORY_POINTS_X%' => __( 'X: Category name - The average amount of points a specific category earned.', 'quiz-master-next' ),
			'%QUESTION_ANSWER_X%'         => __( 'X = Question ID. It will show result of particular question.', 'quiz-master-next' ),
			'%ANSWER_X%'                  => __( 'X = Question ID. It will show result of particular question.', 'quiz-master-next' ),
			'%TIME_FINISHED%'             => __( 'Display time after quiz submission.', 'quiz-master-next' ),
			'%QUESTIONS_ANSWERS_EMAIL%'   => __( 'Shows the question, the answer provided by user, and the correct answer.', 'quiz-master-next' ),
		),
	);
	$variable_list   = apply_filters( 'qsm_text_variable_list', $variable_list );
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
		if ( 1 !== intval( get_option( 'qsm_upated_question_type_val' ) ) ) {
			$table_name  = $wpdb->prefix . 'mlw_questions';
			$status      = $wpdb->query(
				$wpdb->prepare(
					'UPDATE ' . $table_name . " SET `question_type_new` = REPLACE( `question_type_new`, 'fill-in-the-blank', %d )", 14
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
	$install = false;

	$quiz_table_name = $wpdb->prefix . 'mlw_quizzes';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_table_name'" ) !== $quiz_table_name ) {
		$install = true;
	}

	$quiz_theme_table_name = $wpdb->prefix . 'mlw_themes';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$quiz_theme_table_name'" ) !== $quiz_theme_table_name ) {
		$install = true;
	}

	$question_terms_table_name = $wpdb->prefix . 'mlw_question_terms';
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$question_terms_table_name'" ) !== $question_terms_table_name ) {
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
	if ( isset( $_GET['page'] ) && sanitize_text_field( wp_unslash( $_GET['page'] ) ) == 'quiz-master-next/mlw_quizmaster2.php' ) {
		wp_safe_redirect( admin_url( 'admin.php?page=qsm_dashboard' ) );
		exit;
	}
}

add_action( 'admin_page_access_denied', 'qsm_admin_page_access_func' );

function qsm_fetch_theme_data() {
	global $themes_data;
	$themes_data = qsm_get_widget_data( 'themes' );
}

function qsm_get_installed_theme( $saved_quiz_theme, $wizard_theme_list = '', $caller = '' ) {
	global $mlwQuizMasterNext;
	global $pro_themes;
	$active_themes   = $mlwQuizMasterNext->theme_settings->get_active_themes();
	$theme_folders   = array();
	if ( ! empty( $active_themes ) ) {
		foreach ( $active_themes as $dir ) {
			$theme_dir = WP_PLUGIN_DIR . '/' . $dir['theme'];
			if ( is_dir( $theme_dir ) ) {
				$theme_folders[] = $dir;
			}
		}
	}
	if ( 'qsm_theme_defaults' !== $caller ) {
	?>
	<div class="theme-wrapper qsm-default-theme theme <?php echo '' == $saved_quiz_theme || 0 == $saved_quiz_theme ? 'active' : ''; ?>">
		<input style="display: none" type="radio" name="quiz_theme_id" value="0" <?php checked( $saved_quiz_theme, '0', true ); ?>>
		<div class="theme-screenshot" id="qsm-theme-screenshot">
			<img alt="" src="<?php echo esc_url( QSM_PLUGIN_URL ) . '/assets/screenshot-default-theme.png'; ?>">
			<div class="downloaded-theme-button">
				<span class="button button-primary"><?php esc_html_e( 'Select', 'quiz-master-next' ); ?></span>
			</div>
		</div>
		<div class="theme-id-container">
			<h2 class="theme-name" id="emarket-name"><?php esc_html_e( 'Default Theme', 'quiz-master-next' ); ?></h2>
			<div class="theme-actions">
				<?php if ( 0 !== $saved_quiz_theme && '' !== $saved_quiz_theme ) { ?>
					<button class="button qsm-activate-theme"><?php esc_html_e( 'Activate', 'quiz-master-next' ); ?></button>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php } do_action( 'qsm_add_after_default_theme' ); ?>
	<?php
	if ( $theme_folders ) {
		foreach ( $theme_folders as $key => $theme ) {
			$theme_name  = $theme['theme'];
			$theme_id    = $theme['id'];
			$default_themes = array( 'Fortune', 'Sigma', 'Pixel', 'Sapience', 'Breeze', 'Fragrance', 'Pool', 'Ivory' );
			if ( 'qsm_theme_defaults' === $caller && ! in_array( $theme['theme_name'], $default_themes, true ) ) {
				continue;
			}
			?>
			<div class="theme-wrapper <?php echo esc_attr( $theme_name ); ?> theme <?php echo $theme_id == $saved_quiz_theme ? 'active' : ''; ?>">
				<input style="display: none" type="radio" name="quiz_theme_id" value="<?php echo intval( $theme_id ); ?>" <?php checked( $saved_quiz_theme, $theme_id, true ); ?>>
				<div class="theme-screenshot" id="qsm-theme-screenshot">
					<img alt="" src="<?php echo esc_url( WP_PLUGIN_URL . '/' . $theme_name . '/screenshot.png' ); ?>" />
					<div class="downloaded-theme-button">
						<span class="button button-primary"><?php esc_html_e( 'Select', 'quiz-master-next' ); ?></span>
					</div>
				</div>
				<span class="more-details" style="display: none;"><?php esc_html_e( 'Templates', 'quiz-master-next' ); ?></span>
				<div class="theme-id-container">
					<h2 class="theme-name" id="emarket-name"><?php echo esc_attr( $theme['theme_name'] ); ?></h2>
					<div class="theme-actions">
						<?php
						$button = "";
						if ( $saved_quiz_theme === $theme_id || 'qsm_theme_defaults' === $caller ) {
							if ( 'qsm_theme_defaults' === $caller ) {
								$button = '<a class="button button-primary qsm-customize-color-settings" data-modal-id="' . esc_attr( $theme_id ) . '" href="javascript:void(0)">' . esc_html__( 'Customize', 'quiz-master-next' ) .' </a>';
							} else {
								$button = '<a class="button button-primary qsm-customize-color-settings" href="javascript:void(0)">' . esc_html__( 'Customize', 'quiz-master-next' ) .' </a>';
							}
						}elseif ( 'wizard_theme_list' !== $wizard_theme_list ) {
							$button = '<button class="button qsm-activate-theme"> ' . esc_html__( 'Activate', 'quiz-master-next' ) . '</button>';
						}
						$button = apply_filters( 'qsm_themes_action_button', $button, $theme, $active_themes );
						echo wp_kses_post($button);
						?>
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
	global $pro_themes;
	$installed_themes    = $mlwQuizMasterNext->theme_settings->get_installed_themes();
	$default_themes      = array( 'Fortune', 'Sigma', 'Pixel', 'Sapience', 'Breeze', 'Fragrance', 'Pool', 'Ivory', 'Companion', 'Serene' );
	$default_themes_data = array();
	$keys_to_unset       = array();
	if ( ! empty( $themes_data ) ) {
		foreach ( $default_themes as $theme ) {
			$key = array_search( $theme, array_column( $installed_themes, 'theme_name' ), true );
			if ( false !== $key && $installed_themes[ $key ]['theme_active'] && file_exists( WP_PLUGIN_DIR . '/' . $installed_themes[ $key ]['theme'] ) ) { // installed themes to be removed
				$key_to_unset = array_search( $theme, array_column( $themes_data, 'name' ), true );
				if ( false !== $key_to_unset ) {
					$keys_to_unset[] = $key_to_unset;
				}
			} else {
				$key_to_move = array_search( $theme, array_column( $themes_data, 'name' ), true );
				if ( false !== $key_to_move ) {
					array_push( $default_themes_data, $themes_data[ $key_to_move ] );
					// $keys_to_unset[] = $key_to_move;
				}
			}
		}
		foreach ( $installed_themes as $theme ) {
			$key = array_search( $theme['theme_name'], array_column( $themes_data, 'name' ), true );
			if ( false !== $key ) { // installed themes to be removed
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
		?><div class="themes-container"><?php
		foreach ( $default_themes_data as $key => $theme ) {
			$theme_name          = $theme['name'];
			$theme_screenshot    = $theme['img'];
			$theme_url           = qsm_get_utm_link( $theme['link'], 'new_quiz', 'themes', 'quizsurvey_buy_' . sanitize_title( $theme_name ) );
			$theme_demo          = qsm_get_utm_link( $theme['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title( $theme_name ) );
			?>
			<div class="theme-wrapper theme market-theme">
				<div class="theme-screenshot" id="qsm-theme-screenshot">
					<?php if ( in_array( $theme_name, $pro_themes, true ) ) { ?>
						<span class="qsm-badge"><?php esc_html_e( 'Paid', 'quiz-master-next' ); ?></span>
					<?php } ?>
					<img alt="" src="<?php echo esc_url( $theme_screenshot ); ?>" />
					<div class="market-theme-url">
						<a class="button button-primary" target="_blank" rel="noopener" href="<?php echo esc_url( $theme_demo ); ?>"><?php esc_html_e( 'Live Preview', 'quiz-master-next' ); ?></a>
						<a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( $theme_url ); ?>"><?php echo in_array( $theme_name, $pro_themes, true ) ? esc_html__( 'Buy Now', 'quiz-master-next' ) : esc_html__( 'Download', 'quiz-master-next' ); ?>
						</a>
					</div>
				</div>
				<div class="theme-id-container">
					<h2 class="theme-name" id="emarket-name"><?php echo esc_attr( $theme_name ); ?></h2>
				</div>
			</div>
			<?php
		}
		?></div><?php
	}
}

function qsm_get_market_themes() {
	global $themes_data, $pro_themes;
	if ( ! empty( $themes_data ) ) {
		?><div class="themes-container"><?php
		foreach ( $themes_data as $key => $theme ) {
			$theme_name          = $theme['name'];
			$theme_screenshot    = $theme['img'];
			$theme_url           = qsm_get_utm_link( $theme['link'], 'new_quiz', 'themes', 'quizsurvey_buy_' . sanitize_title( $theme_name ) );
			$theme_demo          = qsm_get_utm_link( $theme['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title( $theme_name ) );
			?>
			<div class="theme-wrapper theme market-theme">
				<div class="theme-screenshot" id="qsm-theme-screenshot">
					<?php if ( in_array( $theme_name, $pro_themes, true ) ) { ?>
						<span class="qsm-badge"><?php esc_html_e( 'Paid', 'quiz-master-next' ); ?></span>
					<?php } ?>
					<img alt="" src="<?php echo esc_url( $theme_screenshot ); ?>" />
					<div class="market-theme-url">
						<a class="button button-primary" target="_blank" rel="noopener"	href="<?php echo esc_url( $theme_demo ); ?>"><?php esc_html_e( 'Live Preview', 'quiz-master-next' ); ?></a>
						<a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( $theme_url ); ?>">
							<?php echo in_array( $theme_name, $pro_themes, true ) ? esc_html__( 'Buy Now', 'quiz-master-next' ) : esc_html__( 'Download', 'quiz-master-next' ); ?>
						</a>
					</div>
				</div>
				<div class="theme-id-container">
					<h2 class="theme-name" id="emarket-name"><?php echo esc_html( $theme_name ); ?></h2>
				</div>
			</div>
			<?php
		}
		?></div><?php
	} else {
		?>
		<div class="empty-market-place">
			<span class="dashicons dashicons-welcome-widgets-menus"></span><br />
			<span class="no-themes-message"><?php esc_html_e( 'No more themes found.', 'quiz-master-next' ); ?></span>
		</div>
		<?php
	}
}

/**
 * Sanitizes multi-dimentional array
 *
 * @since 7.3.5
 */
function qsm_sanitize_rec_array( $array, $textarea = false ) {
	if ( ! is_array( $array ) ) {
        return $textarea ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
    }
    foreach ( $array as $key => $value ) {
        if ( is_array( $value ) ) {
            $array[ $key ] = qsm_sanitize_rec_array( $value, $textarea );
        } else {
			$array[ $key ] = $textarea ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
        }
    }
	return $array;
}

function qsm_advance_question_type_upgrade_popup() {
	$qsm_pop_up_arguments = array(
		"id"           => 'modal-advanced-question-type',
		"title"        => __('Go Beyond Standard Questions', 'quiz-master-next'),
		"description"  => __('Make your quizzes more engaging with the Advanced Question Types Addon.', 'quiz-master-next'),
		"chart_image"  => plugins_url('', dirname(__FILE__)) . '/images/advanced_question_type.png',
		"information"  => __('QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today OR you can buy Advanced Question Addon separately.', 'quiz-master-next'),
		"buy_btn_text" => __('Buy Advanced Questions Addon', 'quiz-master-next'),
		"doc_link"     => qsm_get_plugin_link( 'docs/question-types', 'qsm_list', 'advance-question_type', 'advance-question-upsell_read_documentation', 'qsm_plugin_upsell' ),
		"upgrade_link" => qsm_get_plugin_link( 'pricing', 'qsm_list', 'advance-question_type', 'advance-question-upsell_upgrade', 'qsm_plugin_upsell' ),
		"addon_link"   => qsm_get_plugin_link( 'downloads/advanced-question-types', 'qsm_list', 'advance-question_type', 'advance-question-upsell_buy_addon', 'qsm_plugin_upsell' ),
		"list_items"   => array(
			__("Use Matching Pairs for interactive learning", "quiz-master-next"),
			__("Add Radio Grid & Checkbox Grid for structured responses", "quiz-master-next"),
			__("Enhance user experience with flexible question formats", "quiz-master-next"),
		),
	);
	qsm_admin_upgrade_popup($qsm_pop_up_arguments);
}

function qsm_admin_upgrade_popup( $args = array(), $type = 'popup' ) {
	if ( 'page' == $type ) {
	?>
	<div class="qsm-upgrade-page-content">
		<div class="qsm-upgrade-page-content-upper">
			<div class="qsm-upgrade-page-lock-image-wrap">
				<div class="qsm-upgrade-page-lock-image">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/Lock.png' ); ?>" alt="Lock.png">
				</div>
				<h2><?php echo esc_html( $args['title'] ); ?></h2>
			</div>
			<div class="qsm-upgrade-page-description">
				<p class="qsm-upgrade-description"><?php echo esc_html( $args['description'] ); ?></p>
				<?php if ( ! empty($args['list_items']) ) : ?>
					<div class="qsm-upgrade-list-items">
						<ul>
							<?php foreach ( $args['list_items'] as $item_description ) : ?>
								<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html($item_description); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>
			<div class="qsm-upgrade-page-button qsm-<?php echo esc_attr( $type ); ?>-upgrade-buttons">
				<a href="<?php echo esc_url( $args['upgrade_link'] ); ?>" target="_blank" class="button button-hero qsm_bundle" rel="noopener"><?php esc_html_e( 'Upgrade to Premium', 'quiz-master-next' ); ?></a>
			</div>
		</div>
		<div class="qsm-upgrade-buttons-links">
		<?php
		if ( ! empty( $args['doc_link'] ) ) {
			?>
			<a href="<?php echo esc_url( $args['addon_link'] ); ?>" target="_blank" rel="noopener" ><?php esc_html_e( 'Learn more', 'quiz-master-next' ); ?></a>
			<a href="<?php echo esc_url( $args['doc_link'] ); ?>" target="_blank" rel="noopener" ><?php esc_html_e( 'How it works?', 'quiz-master-next' ); ?></a>
			<?php
		}
		?>
		</div>
	</div>
	<?php
	}
	?>
	<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-popup-upgrade qsm-updated-upgrade-popup" id="<?php echo esc_attr( $args['id'] ); ?>" aria-hidden="false"  style="display:none">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true">
				<header class="qsm-popup__header qsm-question-bank-header">
					<div class="qsm-popup__title qsm-upgrade-box-title" id="modal-2-title"></div>
					<a class="qsm-popup__close qsm-popup-upgrade-close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<main class="qsm-popup__content" id="modal-2-content">
					<?php qsm_admin_upgrade_content( $args ); ?>
				</main>
			</div>
		</div>
	</div>
	<?php
}

function qsm_admin_upgrade_content( $args = array(), $type = 'popup' ) {
	$defaults    = array(
		"id"           => '',
		"title"        => '',
		"description"  => '',
		"chart_image"  => '',
		"warning"      => '',
		"information"  => '',
		"buy_btn_text" => __( 'Buy Addon', 'quiz-master-next' ),
		"doc_link"     => qsm_get_plugin_link( 'docs/add-ons', 'qsm', 'upgrade-box', 'read_documentation', 'qsm_plugin_upsell' ),
		"upgrade_link" => qsm_get_plugin_link( 'pricing', 'qsm', 'upgrade-box', 'upgrade', 'qsm_plugin_upsell' ),
		"addon_link"   => qsm_get_plugin_link( 'addons', 'qsm', 'upgrade-box', 'buy_addon', 'qsm_plugin_upsell' ),
		"benefits"     => array(),
		"use_cases"    => array(),
	);
	/* Benefits and Use Cases parameter structure
	 * array(
	 *   'briefing' => 'Something which will be displayed above the list.',
	 *   'list_items'  => array( 'Item 1', 'Item 2', 'Item 3' ),
	 * )
	*/
	$args        = wp_parse_args( $args, $defaults );
	?>
	<div class="qsm-upgrade-box">

		<div class="qsm-upgrade-box-content">
			<div class="qsm-upgrade-box-lock-image-wrap">
				<div class="qsm-upgrade-box-lock-image">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/Lock.png' ); ?>" alt="Lock.png">
				</div>
				<h2><?php echo esc_html( $args['title'] ); ?></h2>
			</div>
			<div class="qsm-upgrade-box-description">
				<p class="qsm-upgrade-description"><?php echo esc_html( $args['description'] ); ?></p>
				<?php if ( ! empty($args['list_items']) ) : ?>
					<div class="qsm-upgrade-list-items">
						<ul>
							<?php foreach ( $args['list_items'] as $item_description ) : ?>
								<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html($item_description); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>
			</div>

			<div class="">
				<div class="qsm-upgrade-box-button qsm-<?php echo esc_attr( $type ); ?>-upgrade-buttons">
					<a href="<?php echo esc_url( $args['upgrade_link'] ); ?>" target="_blank" class="button button-hero qsm_bundle" rel="noopener"><?php esc_html_e( 'Upgrade to Premium', 'quiz-master-next' ); ?></a>
				</div>
				<div class="qsm-upgrade-buttons-links">
					<a href="<?php echo esc_url( $args['addon_link'] ); ?>" target="_blank" rel="noopener" ><?php esc_html_e( 'Learn more', 'quiz-master-next' ); ?></a>
					<a href="<?php echo esc_url( $args['doc_link'] ); ?>" target="_blank" rel="noopener" ><?php esc_html_e( 'How it works?', 'quiz-master-next' ); ?></a>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Generates theme setting feilds
 *
 * @since 8.0.5
 * @param string $type input type
* @return string $label input label
* @return string $name input name
* @return string $value value
* @return string $default_value default value
* @return string $options other options array
 * @return html
 */
function qsm_quiz_theme_settings( $type, $label, $name, $value, $default_value, $options = array( 'button_text' => '' ) ) {
	?>
	<tr valign="top">
		<th scope="row" class="qsm-opt-tr">
			<label for="form_type"><?php echo esc_attr( $label ); ?></label>
			<?php if ( isset( $options['helper_text'] ) && '' !== $options['helper_text'] ) { ?>
				<span class="dashicons dashicons-editor-help qsm-tooltips-icon">
					<span class="qsm-tooltips"><?php esc_html( $options['helper_text'] ); ?></span>
				</span>
			<?php } ?>
		</th>
		<td align ="right">
			<?php
			switch ( $type ) {
				case 'image':
					?>
					<input class="quiz-theme-option-image-input" name="settings[<?php echo esc_attr( $name ); ?>]" type="hidden" value="<?php echo esc_attr( $value ); ?>" >
					<a class="quiz-theme-option-image-button button" <?php echo ! empty( $value ) ? 'style="display:none"' : ''; ?> href="javascript:void(0);"><span class="dashicons dashicons-format-image"></span> <?php esc_html_e( 'Select Image', 'quiz-master-next' ); ?></a>
					<div class="qsm-theme-option-image <?php echo empty( $value ) ? 'qsm-d-none' : ''; ?>">
						<img src="<?php echo esc_attr( $value ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="quiz-theme-option-image-thumbnail"><br/>
						<a class="button button-small qsm-theme-option-image-remove" href="javascript:void(0)"><?php esc_html_e('Remove', 'quiz-master-next'); ?></a>
					</div>
					<a class="button <?php echo empty( $default_value != $value ) ? 'qsm-d-none' : ''; ?> qsm-theme-option-image-default" href="javascript:void(0)" data-default="<?php echo esc_attr( $default_value ); ?>" ><?php esc_html_e('Default', 'quiz-master-next'); ?></a>
					<?php
					break;
				case 'color':
					?>
					<input name="settings[<?php echo esc_attr( $name ); ?>]" type="text" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default_value ); ?>" class="qsm-color-field" data-alpha-enabled="true" data-label="<?php echo esc_attr( $options['button_text'] ); ?>" />
					<?php
					break;
				case 'hover_color':
						?>
						<input name="settings[<?php echo esc_attr( $name ); ?>]" type="text" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default_value ); ?>" class="qsm-color-field" data-label="<?php echo esc_attr( $options['button_text'] ); ?>" />
						<input name="settings[<?php echo esc_attr( $options['hover_name'] ); ?>]" type="text" value="<?php echo esc_attr( $options['hover_value'] ); ?>" data-default-color="<?php echo esc_attr( $options['hover_default_value'] ); ?>" class="qsm-color-field" data-label="<?php echo esc_attr( $options['hover_button_text'] ); ?>" />
						<?php
		            break;
				case 'checkbox':
					?>
					<input name="settings[<?php echo esc_attr( $name ); ?>]" type="checkbox" value="<?php echo esc_attr( $value ); ?>" <?php echo $value ? "checked" : ""; ?> />
					<?php
					break;
				case 'input_control':
						?>
						<input name="settings[<?php echo esc_attr( $name ); ?>]" type="number" value="<?php echo esc_attr( $value ); ?>" class="qsm-number-field" />
						<?php
						$param = array(
							'name'  => "settings[". $options['unit_name'] ."]",
							'value' => $options['unit_value'],
						);
						qsm_get_input_control_unit( $param ); ?>
						<?php
		            break;
				case 'dropdown':
					$param = array(
						'name'          => "settings[". $name ."]",
						'value'         => $value,
						'default_value' => $default_value,
					);
					qsm_get_input_label_selected( $param );
		            break;
				default:
					?>
					<input name="settings[<?php echo esc_attr( $name ); ?>]" type="text" value="<?php echo esc_attr( $value ); ?>"/>
					<?php
					break;
			} ?>
		</td>
	</tr>
	<?php
}

function qsm_extra_template_and_leaderboard( $variable_list ) {
	if ( ! class_exists( 'QSM_Extra_Variables' ) ) {
		global $mlwQuizMasterNext;
		$template_array = array(
			'%QUESTION_ANSWER_CORRECT%'    => __('This variable shows all questions and answers for questions the user got correct.', 'quiz-master-next'),
			'%QUESTION_ANSWER_INCORRECT%'  => __('This variable shows all questions and answers for questions the user got incorrect.', 'quiz-master-next'),
			'%QUESTION_ANSWER_GROUP_X%'    => __('X: Answer value - This variable shows all questions and answers for questions where the user selected the matching answer.', 'quiz-master-next'),
    		'%CUSTOM_MESSAGE_POINTS_X%'    => __('X: Points range and message e.g. ( CUSTOM_MESSAGE_POINTS_loser:0-49;winner:50-100; ) - Shows a custom message based on the amount of points a user has earned.', 'quiz-master-next'),
    		'%CUSTOM_MESSAGE_CORRECT_X%'   => __('X: Score range and message e.g. ( CUSTOM_MESSAGE_POINTS_loser:0-49;winner:50-100; ) - Shows a custom message based on the score a user has earned.', 'quiz-master-next'),
			'%QUIZ_TIME%'                  => __('This variable displays the total time of quiz.', 'quiz-master-next'),
			'%QUIZ_PERCENTAGE%'            => __('This variable displays the obtained percentage of quiz.', 'quiz-master-next'),
			'%CATEGORY_PERCENTAGE_X%'      => __('X:Category Name - This variable displays the percentage of any selected category out of the total quiz score.', 'quiz-master-next'),
			'%COUNT_UNATTEMPTED%'          => __('This variable displays the total number of questions not attempted or not counted by the user.', 'quiz-master-next'),
			'%QUESTION_ANSWER_ATTEMPTED%'  => __('This variable displays only attempted questions answers on the result page.', 'quiz-master-next'),
			'%SUBMISSION_DATE%'            => __('This variable displays the quiz submission date.', 'quiz-master-next'),
			'%RETAKE_QUIZ_BUTTON%'         => __('This variable displays the quiz retake button.', 'quiz-master-next'),
		  	'%REMAINING_QUIZ_ATTEMPTS%'    => __('This variable displays the quiz remaining attempts.', 'quiz-master-next'),
		    '%CATEGORY_MAX_POINTS_X%'      => __('X:Category Name - This variable displays the max points of any selected category can earn in the total quiz score.', 'quiz-master-next'),
		    '%CATEGORY_WISE_PERCENTAGE_X%' => __('X:Category Name - This variable displays the percentage of points that earned for that category.', 'quiz-master-next'),
		);
		$extra_variables = array(
			'Extra Template Variables' => $template_array,
		);
		$variable_list = array_merge($variable_list, $extra_variables);
	}
	if ( ! class_exists('Mlw_Qmn_Al_Widget') ) {
		global $mlwQuizMasterNext;
		$template_array = array(
			'%LEADERBOARD_POSITION%'     => __('Display User Position out of total results (ie. 15 out of 52)', 'quiz-master-next' ),
			'%LEADERBOARD_POSITION_URL%' => __('Display Leaderboard URL to check position.', 'quiz-master-next'  ),
		);

		$leaderboard = array(
			'Advanced Leaderboard' => $template_array,
		);
		$variable_list = array_merge($variable_list, $leaderboard );
	}
	if ( ! class_exists('QSM_Advanced_Assessment') ) {
		$template_array = array(
			'%ANSWER_LABEL_POINTS%'       => __( 'The amount of points of all labels earned.', 'quiz-master-next' ),
			'%ANSWER_LABEL_POINTS_X%'     => __( 'X: Answer label slug - The amount of points a specific label earned.', 'quiz-master-next' ),
			'%ANSWER_LABEL_COUNTS%'       => __( 'The amount of counts of all labels earned.', 'quiz-master-next' ),
			'%ANSWER_LABEL_COUNTS_X%'     => __( 'X: Answer label slug - The amount of counts a specific label earned.', 'quiz-master-next' ),
			'%ANSWER_LABEL_PERCENTAGE%'   => __( 'The amount of percentage of all labels earned.', 'quiz-master-next' ),
			'%ANSWER_LABEL_PERCENTAGE_X%' => __( 'X: Answer label slug - The amount of percentage a specific label earned.', 'quiz-master-next' ),
			'%MOST_SELECTED_LABEL%'       => __( 'Shows the most frequently chosen label(s).', 'quiz-master-next' ),
			'%HIGHEST_SCORING_LABEL%'     => __( 'Shows the label(s) with highest points earned.', 'quiz-master-next' ),
			'%LOWEST_SCORING_LABEL%'      => __( 'Shows the label(s) with lowest points earned.', 'quiz-master-next' ),
			'%LEAST_SELECTED_LABEL%'      => __( 'Shows the label(s) least frequently chosen.', 'quiz-master-next' ),
		);
		$advanced_assessment = array(
			'Advanced Assessment' => $template_array,
		);
		$variable_list = array_merge( $variable_list, $advanced_assessment );
	}
	return $variable_list;
}
/**
 * This function prepare input unit options.
 *
 * @version 8.0.9
 * @param array $param  List of attributes for a input control
 *
 * @return HTML
 */
function qsm_get_input_control_unit( $param ) {

	if ( empty( $param['name'] ) ) {
		return;
	}

	$value = '';

	if ( ! empty( $param['value'] ) ) {
		$value = $param['value'];
	}


	$unit_options = array( 'px', '%', 'em', 'rem', 'vw', 'vh' );

	/**
	 * Filters the input units.
	 *
	 * @param array $unit_options List of units.
	 */
	$unit_options = apply_filters( 'qsm_input_units', $unit_options );

	$options = '';
	foreach ( $unit_options as $unit ) {

		$is_selected = '';
		if ( $value === $unit ) {
			$is_selected = 'selected';
		}

		$options .= sprintf(
			'<option value="%1$s" %2$s >%1$s</option>',
			esc_attr( $unit ),
			esc_attr( $is_selected )
		);
	}
	$allowed_tags = array(
		'option' => array(
			'value'    => array(),
			'selected' => array(),
		),
	);
	echo sprintf(
		'<select name="%1$s" class="qsm-theme-option-unit"> %2$s </select>',
		esc_attr( $param['name'] ),
		wp_kses( $options, $allowed_tags )
	);

}

function qsm_get_input_label_selected( $param ) {
    if ( empty( $param['name'] ) ) {
        return;
    }
    $value = '';

    if ( ! empty( $param['value'] ) ) {
        $value = $param['value'];
    }

    $options = '';
    foreach ( $value as $key => $val ) {
        $is_selected = '';
        if ( $key == $param['default_value'] ) {
            $is_selected = 'selected';
        }
        $options .= sprintf(
            '<option value="%1$s" %2$s >%3$s</option>',
            esc_attr( $key ),
            esc_attr( $is_selected ),
            esc_attr( $val )
        );
    }
    $allowed_tags = array(
        'option' => array(
            'value'    => array(),
            'selected' => array(),
        ),
    );
    echo sprintf(
        '<select name="%1$s"> %2$s </select>',
        esc_attr( $param['name'] ),
        wp_kses( $options ,$allowed_tags)
    );
}
function qsm_advanced_assessment_quiz_page_content() {
	?>
	<div class="wrap qsm-answer-labels-page">
		<h1>
			<?php esc_html_e( 'Answer Labels', 'quiz-master-next' ); ?>
		</h1>
		<?php
			$args = array(
				"id"           => 'advanced-assessment',
				"title"        => __( 'Advanced Assessment, Smarter Results', 'quiz-master-next' ),
				"description"  => __( 'Unlock Personalized Quiz Experiences with the Advanced Assessment Addon.', 'quiz-master-next' ),
				"chart_image"  => plugins_url( '', dirname( __FILE__ ) ) . '/images/advance-assessment-chart.png',
				"warning"      => __( 'Missing Feature - Advanced Assessment Add-on required', 'quiz-master-next' ),
				"information"  => __( 'Get all our add-ons at a discounted rate with the QSM Addon Bundle and save up to 95% today! Alternatively, you can also purchase the Advanced Assessment Addon separately.', 'quiz-master-next' ),
				"buy_btn_text" => __( 'Buy Quiz Advanced Assessment', 'quiz-master-next' ),
				"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/advanced-assessment', 'quiz-documentation', 'plugin', 'advanced-assessment', 'qsm_plugin_upsell' ),
				"upgrade_link" => qsm_get_plugin_link( 'pricing', 'quiz-documentation', 'plugin', 'advanced-assessment', 'qsm_plugin_upsell' ),
				"addon_link"   => qsm_get_plugin_link( 'downloads/advanced-assessment', 'quiz-documentation', 'plugin', 'advanced-assessment', 'qsm_plugin_upsell' ),
				"list_items"   => array(
					__("Assign custom labels to answers", "quiz-master-next"),
					__("Customize result pages based on quiz performance", "quiz-master-next"),
					__("Analyse quiz results using charts and tables", "quiz-master-next"),
				),
			);
			qsm_admin_upgrade_popup( $args, 'page' );
		?>
	</div>
	<?php
}

function qsm_extra_shortcode_popup_window_button( $quiz_id, $categories ) {
	if ( ! class_exists('QSM_Extra_Shortcodes') ) {
		$qsm_pop_up_arguments = array(
			"id"           => 'modal-extra-shortcodes',
			"title"        => __('Unlock More Customization with Extra Shortcodes', 'quiz-master-next'),
			"description"  => __('Enhance quiz display and functionality with the Extra Shortcodes Addon.', 'quiz-master-next'),
			"chart_image"  => plugins_url('', dirname(__FILE__)) . '/images/extra-shortcodes.png',
			"information"  => __('QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today OR you can buy QSM Extra Shortodes Addon separately.', 'quiz-master-next'),
			"buy_btn_text" => __('Buy QSM Extra Shortodes Addon', 'quiz-master-next'),
			"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/extra-shortcodes/', 'qsm_list', 'extrashortcodea_button', 'extra-shortcodes-upsell_read_documentation', 'qsm_plugin_upsell' ),
			"upgrade_link" => qsm_get_plugin_link( 'pricing', 'qsm_list', 'extrashortcodea_button', 'extra-shortcodes-upsell_upgrade', 'qsm_plugin_upsell' ),
			"addon_link"   => qsm_get_plugin_link( 'downloads/extra-shortcodes', 'qsm_list', 'extrashortcodea_button', 'extra-shortcodes-upsell_buy_addon', 'qsm_plugin_upsell' ),
			"list_items"   => array(
				__("Show user scores dynamically anywhere", "quiz-master-next"),
				__("Display quiz rankings, leaderboards & results", "quiz-master-next"),
				__("Personalize quiz pages with flexible shortcode options", "quiz-master-next"),
			),
		);
		qsm_admin_upgrade_popup($qsm_pop_up_arguments);
		?>
		<button type="button" class="button qsm-extra-shortcode-popup qsm-extra-shortcode-conditional-button">
			<img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/flow-chart.svg'); ?>" alt="flow-chart.svg"/>
			<?php esc_html_e('Output Rules', 'quiz-master-next'); ?>
		</button>
	<?php }
}

function qsm_webhooks_popup_window_section() {
	if ( ! class_exists('QSM_Webhooks') ) {
		$args = array(
			"id"           => 'modal-qsm-webhooks',
			"title"        => __( 'Automate Your Workflow with QSM Webhooks', 'quiz-master-next' ),
			"description"  => __( 'Enhance your quizzes with seamless integrations using the QSM Webhooks Addon.', 'quiz-master-next' ),
			"chart_image"  => plugins_url( '', dirname( __FILE__ ) ) . '/images/proctor_quiz_chart.png',
			"warning"      => __( 'Missing Feature - webhook Add-On required', 'quiz-master-next' ),
			"information"  => __( 'QSM Addon Bundle is the best way to get all our add-ons at a discount. Upgrade to save 95% today. OR you can buy Webhooks Addon separately.', 'quiz-master-next' ),
			"buy_btn_text" => __( 'Buy Webhooks Addon', 'quiz-master-next' ),
			"doc_link"     => qsm_get_plugin_link( 'docs/add-ons/qsm-webhooks', 'qsm_list', 'webhooks_button', 'webhooks_read_documentation', 'qsm_plugin_upsell' ),
			"upgrade_link" => qsm_get_plugin_link( 'pricing', 'qsm_list', 'webhooks_button', 'webhooks_upgrade', 'qsm_plugin_upsell' ),
			"addon_link"   => qsm_get_plugin_link( 'downloads/webhooks', 'qsm_list', 'webhooks_button', 'webhooks_buy_addon', 'qsm_plugin_upsell' ),
			"list_items"   => array(
				__("Automatically send quiz results to any system in real-time.", "quiz-master-next"),
				__("Format data as JSON, XML, or Form Data to fit your needs.", "quiz-master-next"),
				__("Connect effortlessly with CRMs, email platforms, analytics tools, and more.", "quiz-master-next"),
			),
		);
		qsm_admin_upgrade_popup( $args );
	}
}


// Hook into WordPress' AJAX action
add_action('wp_ajax_qsm_insert_quiz_template', 'qsm_insert_quiz_template_callback');
/**
 * Handles the AJAX request to insert or update a QSM quiz template.
 *
 * @return void Sends a JSON response and exits the script.
 */
function qsm_insert_quiz_template_callback() {
    global $wpdb;

	// validate nonce
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'qsm_add_template' ) && is_user_logged_in() ) {

		// Sanitize the incoming data
		$template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
		$template_name = isset($_POST['template_name']) ? sanitize_text_field(wp_unslash($_POST['template_name'])) : "";
		$template_type = isset($_POST['template_type']) ? sanitize_text_field(wp_unslash($_POST['template_type'])) : "";
		$template_content = isset($_POST['template_content']) ? wp_unslash($_POST['template_content']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$filtered_content = preg_replace_callback(
			'/<qsmvariabletag>([^<]+)<\/qsmvariabletag>/u',
			function ( $matches ) {
				return '%' . wp_strip_all_tags(preg_replace('/^\s+|\s+$/u', '', $matches[1])) . '%';
			},
			$template_content
		);
		$filtered_content = preg_replace_callback(
			'/<qsmextrashortcodetag>([^<]+)<\/qsmextrashortcodetag>/u',
			function ( $matches ) {
				return wp_strip_all_tags(preg_replace('/^\s+|\s+$/u', '', $matches[1]));
			},
			$filtered_content
		);

		$table_name = $wpdb->prefix . 'mlw_quiz_output_templates';

		if ( $template_id ) {
			// Replace (Update) existing template
			$update_data = array(
				'template_content' => $filtered_content,
			);
			$where = array( 'id' => $template_id );

			$updated = $wpdb->update(
				$table_name,
				$update_data,
				$where,
				array( '%s' ),
				array( '%d' )
			);

			if ( false !== $updated ) {
				$template_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $template_id), ARRAY_A);
				wp_send_json_success($template_data);
			} else {
				wp_send_json_error(array( 'message' => __('Failed to update template.', 'quiz-master-next') ));
			}
		} else {
			// Insert new template
			$template_data = array(
				'template_name'    => $template_name,
				'template_type'    => $template_type,
				'template_content' => $filtered_content,
				'created_at'       => current_time('mysql'),
			);

			$wpdb->insert(
				$table_name,
				$template_data,
				array( '%s', '%s', '%s', '%s' ) // Format of the inserted data
			);

			$template_data['id'] = $wpdb->insert_id;

			if ( $template_data['id'] ) {
				wp_send_json_success($template_data);
			} else {
				wp_send_json_error(array( 'message' => __('Failed to insert template.', 'quiz-master-next') ));
			}
		}
	} else {
		wp_send_json_error( [ 'message' => __( 'Invalid nonce. Busted.', 'quiz-master-next' ) ] );
        wp_die();
	}
}

add_action( 'wp_ajax_qsm_remove_my_templates', 'qsm_remove_my_templates_handler' );
/**
 * Handles the AJAX request to remove a template.
 * template ID, then attempts to remove the specified template from the database.
 *
 * @return void Sends a JSON response and exits the script.
 */
function qsm_remove_my_templates_handler() {
    global $wpdb;
	if ( ! isset( $_POST['nonce'] ) ||
        ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'qsm_remove_template' )
    ) {
        wp_send_json_error( [ 'message' => __( 'Invalid nonce. Action not authorized.', 'quiz-master-next' ) ] );
        wp_die();
    }

    if ( ! isset( $_POST['id'] ) || ! absint( wp_unslash( $_POST['id'] ) ) ) {
        wp_send_json_error( [ 'message' => __( 'Invalid template ID.', 'quiz-master-next' ) ] );
        wp_die();
    }

    $template_id = absint( wp_unslash( $_POST['id'] ) );
    $table_name = $wpdb->prefix . 'mlw_quiz_output_templates';
    $result = $wpdb->delete( $table_name, [ 'id' => $template_id ], [ '%d' ] );
    if ( $result ) {
        wp_send_json_success( [ 'message' => __( 'Template removed successfully.', 'quiz-master-next' ) ] );
    } else {
        wp_send_json_error( [ 'message' => __( 'Failed to remove the template.', 'quiz-master-next' ) ] );
    }
    wp_die();
}

/**
 * Displays popups for managing and previewing QSM templates.
 *
 * @param array  $template_from_script Array of pre-defined templates.
 * @param array  $my_templates         Array of user-created templates.
 * @param string $type                 The template type to manage (e.g., "result", "email").
 *
 * @return void
 */
function qsm_result_and_email_popups_for_templates( $template_from_script, $my_templates, $type ) {
	?>
	<div class="qsm-popup qsm-popup-slide" id="qsm-<?php echo esc_attr( $type ); ?>-page-templates" aria-hidden="true" style="display:none;">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="qsm-<?php echo esc_attr( $type ); ?>-page-templates-title">
				<header class="qsm-popup__header">
					<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-header-left">
						<img class="qsm-<?php echo esc_attr( $type ); ?>-page-template-header-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/icon-200x200.png'); ?>" alt="icon-200x200.png"/>
						<h2 class="qsm-popup__title" id="qsm-<?php echo esc_attr( $type ); ?>-page-templates-title">
							<?php echo esc_html( ucfirst( $type ) ) . esc_html__( ' Templates', 'quiz-master-next' ); ?>
						</h2>
					</div>
					<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-header-right">
						<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-header">
							<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-header-tabs">
								<a class="qsm-<?php echo esc_attr( $type ); ?>-page-tmpl-header-links active" data-tab="page" href="javascript:void(0)"><?php esc_html_e( 'QSM Templates', 'quiz-master-next' ); ?></a>
								<a class="qsm-<?php echo esc_attr( $type ); ?>-page-tmpl-header-links" data-tab="my" href="javascript:void(0)"><?php esc_html_e( 'My Templates', 'quiz-master-next' ); ?></a>
							</div>
						</div>
						<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-header-close">
							<a style="display: none;" class="qsm-preview-template-image-close button button-secondary" data-type="<?php echo esc_attr( $type ); ?>" href="javascript:void(0)"><img class="qsm-dashboard-help-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/left-arrow.png'); ?>" alt="left-arrow.png"/><?php esc_html_e( 'Back', 'quiz-master-next' ); ?></a>
							<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
						</div>
					</div>
				</header>
				<main class="qsm-popup__content" id="qsm-<?php echo esc_attr( $type ); ?>-page-templates-content" data-type="<?php echo esc_attr( $type ); ?>" data-<?php echo esc_attr( $type ); ?>-page="">
				<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-container qsm-<?php echo esc_attr( $type ); ?>-page-template-common">
					<?php
					if ( ! empty($template_from_script) ) {
						foreach ( $template_from_script as $key => $single_template ) {
							if ( $type == $single_template['template_type'] || 'both' == $single_template['template_type'] ) {
								$image_url = QSM_PLUGIN_URL . 'assets/screenshot-default-theme.png';
								if ( '' != $single_template['template_preview'] ) {
									$image_url = QSM_PLUGIN_URL . 'assets/'.$single_template['template_preview'];
								}
								?>
								<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-card " data-url="<?php echo esc_url( $image_url ); ?>" >
									<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-card-content" data-indexid="<?php echo esc_html($key); ?>">
										<img class="qsm-<?php echo esc_attr( $type ); ?>-page-template-card-image" src="<?php echo esc_url( $image_url ); ?>" alt="page-template-card">
									</div>
									<div class="qsm-<?php echo esc_attr( $type ); ?>-page-template-card-buttons">
										<button class="qsm-<?php echo esc_attr( $type ); ?>-page-template-preview-button button button-secondary" data-indexid="<?php echo esc_html($key); ?>"><?php esc_html_e( 'Preview', 'quiz-master-next' ); ?></button>
										<button class="qsm-<?php echo esc_attr( $type ); ?>-page-template-use-button button button-secondary" data-structure="default" data-indexid="<?php echo esc_html($key); ?>"><img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/download-line-blue.svg'); ?>" alt="download-line-blue.svg" /><?php esc_html_e( 'Insert', 'quiz-master-next' ); ?></button>
									</div>
								</div>
								<?php
							}
						}
					} else {
						qsm_display_fullscreen_error();
					}
					?>
				</div>
				<div class="qsm-<?php echo esc_attr( $type ); ?>-my-template-container qsm-<?php echo esc_attr( $type ); ?>-page-template-common">
				<table class="qsm-my-templates-table wp-list-table widefat fixed striped">
					<tbody class="qsm-my-templates-table-body">
					<?php if ( ! empty($my_templates) ) { ?>
						<tr>
						<th width="60%"><?php echo esc_html__( 'Template Name', 'quiz-master-next' ); ?></th>
						<th><?php echo esc_html__( 'Created At', 'quiz-master-next' ); ?></th>
						<th><?php echo esc_html__( 'Actions', 'quiz-master-next' ); ?></th>
						</tr>
					<?php } else {
							// translators: %s is the template type.
							$no_templates_message = sprintf( __( 'No %s templates found.', 'quiz-master-next' ), esc_html( $type ) );?>
						<tr class="qsm-no-templates-row">
							<td colspan="3" class="qsm-no-templates-message">
								<?php echo esc_html( $no_templates_message ); ?>
							</td>
						</tr>
						<?php } ?>
					</tbody></table>
				</div>
				<div class="qsm-preview-<?php echo esc_attr( $type ); ?>-page-template-container " style="display: none;">
					<div class="qsm-<?php echo esc_attr( $type ); ?>-template-dependency-addons">
					</div>
					<div class="qsm-preview-template-image-wrapper">
						<img class="qsm-preview-template-image" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/screenshot-default-theme.png'); ?>" alt="screenshot-default-theme.png"/>
					</div>
				</div>
				</main>
			</div>
		</div>
	</div>
	<?php
}
/**
 * Outputs a JavaScript template for rendering rows in the QSM Result and Email template table.
 * This function generates an HTML `<script>` tag containing a Handlebars-style template
 *
 * @return void
 */
function qsm_result_and_email_row_templates(){
	?>
	<script type="text/template" id="tmpl-qsm-my-template-rows">
		<tr>
			<td>{{data.template_name}}</td>
			<td>{{data.created_at}}</td>
			<td class="qsm-my-template-rows-actions">
				<a title="<?php esc_attr_e( 'Use Template', 'quiz-master-next' ); ?>" class="qsm-{{data.template_type}}-page-template-use-button" data-structure="custom" data-indexid="{{data.indexid}}"><?php echo esc_html__( 'Use Template', 'quiz-master-next' ); ?></a>
				<a class="qsm-{{data.template_type}}-page-template-remove-button" data-type="{{data.template_type}}" data-id="{{data.id}}"><img class="qsm-common-svg-image-class" src="<?php echo esc_url(QSM_PLUGIN_URL . 'assets/trash-light.svg'); ?>" alt="trash-light.svg"/></a>
				<span class="qsm-my-template-action-response"></span>
			</td>
		</tr>
	</script>
	<?php
}

function qsm_get_plugin_status_by_path( $path ) {
	if ( is_plugin_active($path) ) {
		return 'activated';
	} elseif ( '' != $path && file_exists(WP_PLUGIN_DIR . '/' . $path) ) {
		return 'installed';
	} else {
		return 'not_installed';
	}
}


/**
 * Retrieve the QSM dependency plugin list option.
 *
 * @return array|null Returns the plugin list array if the option exists, or null if not.
 */
function qsm_get_dependency_plugin_list() {
	$qsm_admin_dd = qsm_get_parsing_script_data();
	$all_addons = isset( $qsm_admin_dd['all_addons'] ) ? $qsm_admin_dd['all_addons'] : array();

	$dependency_array = array();

	foreach ( $all_addons as $key => $addon ) {
		$path = $addon['path'] ?? '';
		$addon_link          = qsm_get_utm_link( $addon['link'], 'result_or_email', 'templates', 'template_preview_' . sanitize_title( $addon['name'] ) );
		$dependency_array[] = [
			'id'     => $addon['id'],
			'name'   => $addon['name'],
			'link'   => $addon_link,
			'status' => qsm_get_plugin_status_by_path($path), // Use the common function
		];
	}

	return $dependency_array;
}
function qsm_create_theme_defaults_tab() {
	global $mlwQuizMasterNext, $wpdb;
    $themes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_themes", ARRAY_A );
	if ( empty( $themes ) ) {
		return;
	}
	$active_themes   = $mlwQuizMasterNext->theme_settings->get_active_themes();

	if ( empty($active_themes) ) {
		return;
	}
	$pro_themes = array( 'Fortune', 'Sigma', 'Pixel', 'Sapience', 'Breeze', 'Fragrance', 'Pool', 'Ivory' );

	$has_pro_theme = false;
	foreach ( $active_themes as $theme ) {
		if ( in_array($theme['theme_name'], $pro_themes, true) ) {
			$has_pro_theme = true;
			break;
		}
	}

	if ( ! $has_pro_theme ) {
		return;
	}
	?>
	<a href="?page=qmn_global_settings&tab=qsm-theme-defaults" class="nav-tab <?php echo ! empty( $_GET['tab'] ) && 'qsm-theme-defaults' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Theme Defaults', 'quiz-master-next' ); ?></a>
    <?php
}
add_action( 'qsm_global_settings_page_add_tab_after', 'qsm_create_theme_defaults_tab' );

/**
 * Generates the content for the theme defaults tab in the global settings page.
 *
 * This function handles the saving of theme default settings and displays the theme settings form popup.
 * If the save settings form is submitted, it updates the theme default settings in the database.
 * It also displays a list of installed themes with their default settings, allowing the admin to modify them.
 */
function qsm_create_theme_defaults_tab_content() {
    global $mlwQuizMasterNext, $wpdb;

    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );

    if ( isset( $_POST['save_theme_default_settings_nonce'], $_POST['settings'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['save_theme_default_settings_nonce'] ) ), 'save_theme_default_settings' ) ) {
        unset( $_POST['save_theme_default_settings_nonce'] );
        unset( $_POST['_wp_http_referer'] );
        $settings_array = qsm_sanitize_rec_array( wp_unslash( $_POST['settings'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $theme_id = isset( $_POST['qsm_theme_id'] ) ? sanitize_text_field( wp_unslash( $_POST['qsm_theme_id'] ) ) : 0;
        $results = $wpdb->update(
            $wpdb->prefix . 'mlw_themes',
            array(
                'default_settings' => maybe_serialize( $settings_array ),
            ),
            array(
                'id' => $theme_id,
            ),
            array( '%s' ),
            array( '%d' )
        );
        $mlwQuizMasterNext->alertManager->newAlert(
            __( 'The theme default settings saved successfully.', 'quiz-master-next' ),
            'success'
        );
        $mlwQuizMasterNext->audit_manager->new_audit( "Default theme settings have been saved", '', '' );
    }

    if ( ! empty( $_GET['tab'] ) && 'qsm-theme-defaults' === $_GET['tab'] ) {
        $themes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}mlw_themes", ARRAY_A );
        ?>
        <div class="theme-browser qsm-global-settings-theme-browser">
            <div class="themes wp-clearfix">
                <?php
                qsm_get_installed_theme( 0, '', 'qsm_theme_defaults' );
                ?>
            </div>
        </div>
        <?php
        if ( ! empty( $themes ) ) {
            foreach ( $themes as $theme ) {
				$default_themes = array( 'Fortune', 'Sigma', 'Pixel', 'Sapience', 'Breeze', 'Fragrance', 'Pool', 'Ivory' );
				if ( ! in_array( $theme['theme_name'], $default_themes, true ) ) {
					continue;
				}
                ?>
                <div class="qsm-popup qsm-popup-slide qsm-theme-color-settings qsm-theme-color-settings-<?php echo esc_attr( $theme['id'] ); ?>" id="qsm-theme-color-settings-<?php echo esc_attr( $theme['id'] ); ?>" aria-hidden="true">
                    <div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
                        <div class="qsm-popup__container" role="dialog" aria-modal="true" aria-labelledby="modal-2-title">
                            <form action="" method="post" class="qsm-theme-settings-frm">
                                <header class="qsm-popup__header">
                                    <h2 class="qsm-popup__title" id="modal-2-title">
                                        <?php esc_html_e( 'Theme Settings', 'quiz-master-next' ); ?>
                                    </h2>
                                    <a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
                                </header>
                                <main class="qsm-popup__content" id="theme-color-settings-content">
                                    <?php wp_nonce_field( 'save_theme_default_settings', 'save_theme_default_settings_nonce' ); ?>
                                    <table class="form-table" style="width: 100%;">
                                        <?php
                                        $get_theme_default_settings = maybe_unserialize( $theme['default_settings'] );
                                        ?>
                                    </table>
                                	<?php do_action( 'qsm_theme_option_' . strtolower( $theme['theme_name'] ), '', '', $get_theme_default_settings, $get_theme_default_settings ); ?>
                                    <input type="hidden" name="qsm_theme_id" value="<?php echo esc_attr( $theme['id'] ); ?>">
                                </main>
                                <footer class="qsm-popup__footer">
                                    <button type="submit" id="qsm-save-theme-settings"
                                        class="button button-primary"><?php esc_html_e( 'Save Settings', 'quiz-master-next' ); ?></button>
                                    <button class="button" data-micromodal-close
                                        aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
                                </footer>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <?php
    }
}
add_action( 'qsm_global_settings_page_added_tab_content', 'qsm_create_theme_defaults_tab_content' );

function qsm_display_header_section_links(){
	global $mlwQuizMasterNext;
	?>
		<div id="welcome_panel" class=" <?php qsm_check_close_hidden_box( 'welcome_panel' ); ?>">
			<div class="qsm-dashboard-welcome-panel-wrap">
				<div class="qsm-welcome-panel-content">
					<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/logo-blue.svg' ); ?>" alt="logo-blue.svg">
					<p class="current_version">
						<?php
						/* translators: %1$s: The current version of the Quiz Master Next plugin. */
						echo esc_html( sprintf( __( 'Version: %1$s', 'quiz-master-next' ), $mlwQuizMasterNext->version ) );
						?>
					</p>
				</div>
				<ul class="qsm-welcome-panel-menu">
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('contact-support', 'dashboard', 'useful_links', 'dashboard_support') )?>" class="welcome-icon"><img class="qsm-help-tab-icon" alt="" src="<?php echo esc_url( QSM_PLUGIN_URL . 'assets/Support.svg' ) ?>"><?php esc_html_e( 'Support', 'quiz-master-next' ); ?></a></li>
					<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link('docs', 'dashboard', 'next_steps', 'dashboard_read_document') )?>" class="welcome-icon"><span class="dashicons dashicons-media-document"></span><?php esc_html_e( 'Docs', 'quiz-master-next' ); ?></a></li>
					<?php do_action( 'qsm_welcome_panel_links' ); ?>
				</ul>
			</div>
		</div>
	<?php
}


function qsm_display_promotion_links_section() {
	?>
		<ul class="qsm-display-footer-promotion-links">
			<li><a target="_blank" rel="noopener" href="https://github.com/QuizandSurveyMaster/quiz_master_next" class="welcome-icon"><?php esc_html_e( 'Github', 'quiz-master-next' ); ?></a><span>/</span></li>
			<li><a target="_blank" rel="noopener" href="https://www.facebook.com/groups/516958552587745" class="welcome-icon"><?php esc_html_e( 'Facebook', 'quiz-master-next' ); ?></a><span>/</span></li>
			<li><a target="_blank" rel="noopener" href="<?php echo esc_url( qsm_get_utm_link('https://next.expresstech.io/qsm', 'dashboard', 'next_steps', 'dashboard_roadmap') )?>" class="welcome-icon"><?php esc_html_e( 'Roadmap', 'quiz-master-next' ); ?></a></li>
		</ul>
	<?php
}

function qsm_get_parsing_script_data( $file_name = 'parsing_script.json' ) {
    global $wp_filesystem;
    if ( empty($wp_filesystem) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }
    $file_path = QSM_PLUGIN_PATH . 'data/'.$file_name;
    if ( ! $wp_filesystem->exists($file_path) ) {
        return false; // File not found
    }
    $json_data = $wp_filesystem->get_contents($file_path);
    $decoded_data = json_decode($json_data, true);
    return isset($decoded_data) ? $decoded_data : false;
}

function qsm_display_fullscreen_error() {
    $support_link = qsm_get_plugin_link('contact-support', 'dashboard-error', 'useful_links', 'dashboard_support');
    ?>
    <div id="qsm-dashboard-error-container">
        <div class="qsm-dashboard-error-content">
            <h3><?php esc_html_e('Unable To Load Required Data', 'quiz-master-next'); ?></h3>
            <p><?php esc_html_e('We couldn\'t load the required data, contact our support team for assistance, or you can still create a quiz.', 'quiz-master-next'); ?></p>
            <ul>
                <li><?php esc_html_e('Check if any security plugins or firewalls are blocking connections.', 'quiz-master-next'); ?></li>
                <li><?php esc_html_e('If the issue persists, contact our support team for assistance.', 'quiz-master-next'); ?></li>
            </ul>
            <a href="<?php echo esc_url($support_link); ?>" class="qsm-dashboard-error-btn" target="_blank">
                <?php esc_html_e('Troubleshoot Now', 'quiz-master-next'); ?>
            </a>
        </div>
    </div>
    <?php
}
