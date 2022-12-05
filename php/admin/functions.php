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
	link_featured_image( $quiz_id );
	$url = admin_url( 'admin.php?page=mlw_quiz_options&quiz_id=' . $quiz_id );
	wp_safe_redirect( $url );
	exit;
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
					$wpdb->query( 'ALTER TABLE ' . $table . ' ADD ' . $col_name . ' ' . $col_def );
				}
			}
		}

		update_option( 'qsm_update_db_column', '1' );
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
			if ( $wpdb->query( "ALTER TABLE $result_table_name ADD form_type INT NOT NULL" ) ) {
				update_option( 'qsm_update_result_db_column', '1' );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Creating Column form_type in' . $result_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
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
			if ( $wpdb->query( "ALTER TABLE $quiz_table_name CHANGE `system` `quiz_system` INT(11) NOT NULL;" ) ) {
				update_option( 'qsm_update_quiz_db_column', '1' );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Changing Columns system,quiz_system in' . $quiz_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
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
			if ( $wpdb->query( "ALTER TABLE $result_table_name CHANGE `quiz_results` `quiz_results` LONGTEXT;" ) ) {
				update_option( 'qsm_update_result_db_column_datatype', '1' );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Changing Columns quiz_results in' . $result_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}
	}

	/**
	 * Add new column in question table
	 *
	 * @since 7.0.3
	 */
	if ( get_option( 'qsm_add_new_column_question_table_table', '1' ) <= 3 ) {
		$total_count_val         = get_option( 'qsm_add_new_column_question_table_table', '1' );
		global $wpdb;
		$question_table_name     = $wpdb->prefix . 'mlw_questions';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $question_table_name, 'deleted_question_bank'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $wpdb->query( "ALTER TABLE $question_table_name ADD deleted_question_bank INT NOT NULL" ) ) {
				$inc_val = $total_count_val + 1;
				update_option( 'qsm_add_new_column_question_table_table', $inc_val );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Creating Columns deleted_question_bank in' . $question_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}
	}
	/**
	 * Add new column in the results table
	 *
	 * @since 7.3.7
	 */
	if ( get_option( 'qsm_update_result_db_column_page_url', '' ) != '1' ) {
		global $wpdb;
		$result_table_name       = $wpdb->prefix . 'mlw_results';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'page_url'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $wpdb->query( "ALTER TABLE $result_table_name ADD page_url varchar(255) NOT NULL" ) ) {
				update_option( 'qsm_update_result_db_column_page_url', '1' );
			} else {
				$error = $wpdb->last_error;
				$mlwQuizMasterNext->log_manager->add( "Error Creating Column page_url in {$result_table_name}", "Tried {$wpdb->last_query} but got {$error}.", 0, 'error' );
			}
		}
	}

	/**
	 * Add new column in the results table
	 *
	 * @since 7.3.7
	 */
	if ( get_option( 'qsm_update_result_db_column_page_name', '' ) != '1' ) {
		global $wpdb;
		$result_table_name       = $wpdb->prefix . 'mlw_results';
		$table_result_col_obj    = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s ', $wpdb->dbname, $result_table_name, 'page_name'
			)
		);
		if ( empty( $table_result_col_obj ) ) {
			if ( $wpdb->query( "ALTER TABLE $result_table_name ADD page_name varchar(255) NOT NULL" ) ) {
				update_option( 'qsm_update_result_db_column_page_name', '1' );
			} else {
				$mlwQuizMasterNext->log_manager->add( 'Error Creating Column page_name in' . $result_table_name, "Tried {$wpdb->last_query} but got {$wpdb->last_error}.", 0, 'error' );
			}
		}
	}
}

add_action( 'admin_init', 'qsm_change_the_post_type' );

/**
 * @since version 6.4.8
 * Transfer all quiz post to new cpt 'qsm_quiz'
 */
function qsm_change_the_post_type() {
	if ( 1 !== intval( get_option( 'qsm_change_the_post_type', '' ) ) ) {
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
					$default     = isset( $single_option['default'] ) ? $single_option['default'] : '';
					if ( isset( $single_option['options'] ) && is_array( $single_option['options'] ) ) {
						foreach ( $single_option['options'] as $key => $value ) {
							?>
							<label>
								<input name="<?php echo esc_attr( $parent_key ); ?>[]" type="checkbox" value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $default ) ? 'checked' : ''; ?> />
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
				// Do nothing
			}
			?>
		</div>
	</div>
	<?php
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
						<?php esc_html_e( 'Create New Quiz Or Survey', 'quiz-master-next' ); ?></h2>
					<a class="qsm-popup__close" aria-label="Close modal" data-micromodal-close></a>
				</header>
				<form action="" method="post" id="new-quiz-form">
					<?php wp_nonce_field( 'qsm_new_quiz', 'qsm_new_quiz_nonce' ); ?>
					<main class="qsm-popup__content" id="modal-2-content">
						<div class="qsm-wizard-menu">
							<div class="qsm-logo"><img alt="" src="<?php echo esc_url( QSM_PLUGIN_URL ); ?>/assets/icon-128x128.png">
							</div>
							<div class="qsm-wizard-wrap active" data-show="select_themes">
								<span
									class="qsm-wizard-step-text"><?php esc_html_e( 'Select theme', 'quiz-master-next' ); ?></span>
							</div>
							<div class="qsm-wizard-wrap" data-show="quiz_settings">
								<span
									class="qsm-wizard-step-text"><?php esc_html_e( 'Quiz Settings', 'quiz-master-next' ); ?></span>
							</div>
							<div class="qsm-wizard-wrap" data-show="addons_list">
								<span class="qsm-wizard-step-text"><?php esc_html_e( 'Addons', 'quiz-master-next' ); ?></span>
							</div>
							<span class="qsm-wizard-step-text-optional">
								<?php esc_html_e( '(Optional)', 'quiz-master-next' ); ?>
							</span>
						</div>
						<ul style="display: none;" class="qsm-new_menu_tab_items">
							<li class="qsm-new_menu_tab_li active" data-show="quiz_settings">
								<a href="javascript:void(0)">
									<div class="nav-item-label">
										<span class="nav-item-label-icon dashicons dashicons-admin-generic "></span>
										<div class="nav-item-label-content">
											<h4><?php esc_html_e( 'Quiz Setting', 'quiz-master-next' ); ?></h4>
											<span><?php esc_html_e( 'Fill quiz settings as per preferences', 'quiz-master-next' ); ?></span>
										</div>
									</div>
								</a>
							</li>
							<li class="qsm-new_menu_tab_li" data-show="select_themes">
								<a href="javascript:void(0)">
									<div class="nav-item-label">
										<span class="nav-item-label-icon dashicons dashicons-layout"></span>
										<div class="nav-item-label-content">
											<h4><?php esc_html_e( 'Select Themes', 'quiz-master-next' ); ?></h4>
											<span><?php esc_html_e( 'Use pre-made theme to speed up the things.', 'quiz-master-next' ); ?></span>
										</div>
									</div>
								</a>
							</li>
							<li class="qsm-new_menu_tab_li" data-show="addons_list">
								<a href="javascript:void(0)">
									<div class="nav-item-label">
										<span class="nav-item-label-icon dashicons dashicons-welcome-add-page"></span>
										<div class="nav-item-label-content">
											<h4><?php esc_html_e( 'Addons', 'quiz-master-next' ); ?></h4>
											<span><?php esc_html_e( 'Use 40+ addons to customize the quiz.', 'quiz-master-next' ); ?></span>
										</div>
									</div>
								</a>
							</li>
						</ul>
						<div id="quiz_settings" class="qsm-new-menu-elements" style="display: none;">
							<div class="input-group">
								<label for="quiz_name"><?php esc_html_e( 'Quiz Name', 'quiz-master-next' ); ?>
									<span style="color:red">*</span>
									<span
										class="qsm-opt-desc"><?php esc_html_e( 'Enter a name for this Quiz.', 'quiz-master-next' ); ?></span>
								</label>
								<input type="text" class="quiz_name" name="quiz_name" value="" required="">
							</div>
							<div class="input-group featured_image">
								<label for="quiz_name"><?php esc_html_e( 'Quiz Featured Image', 'quiz-master-next' ); ?>
									<span class="qsm-opt-desc">
										<?php esc_html_e( 'Enter an external URL or Choose from Media Library.', 'quiz-master-next' ); ?>
										<?php esc_html_e( 'Can be changed further from style tab', 'quiz-master-next' ); ?>
									</span>
								</label>
								<span id="qsm_span">
									<input type="text" class="quiz_featured_image" name="quiz_featured_image" value="">
									<a id="set_featured_image" class="button "><?php esc_html_e( 'Set Featured Image', 'quiz-master-next' ); ?></a>
								</span>
							</div>
							<?php
							$all_settings        = $mlwQuizMasterNext->quiz_settings->load_setting_fields( 'quiz_options' );
							global $globalQuizsetting;
							$quiz_setting_option = array(
								'form_type'              => array(
									'option_name' => __( 'Form Type', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['form_type'],
								),
								'system'                 => array(
									'option_name' => __( 'Grading System', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['system'],
								),
								'enable_contact_form'    => array(
									'option_name' => __( 'Enable Contact Form', 'quiz-master-next' ),
									'value'       => 0,
									'options'     => array(
										array(
											'label' => __( 'Yes', 'quiz-master-next' ),
											'value' => 1,
										),
										array(
											'label' => __( 'No', 'quiz-master-next' ),
											'value' => 0,
										),
									),
								),
								'timer_limit'            => array(
									'option_name' => __( 'Time Limit (in Minute)', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['timer_limit'],
								),
								'pagination'             => array(
									'option_name' => __( 'Questions Per Page', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['pagination'],
								),
								'enable_pagination_quiz' => array(
									'option_name' => __( 'Show current page number', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['enable_pagination_quiz'],
								),
								'progress_bar'           => array(
									'option_name' => __( 'Show progress bar', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['enable_pagination_quiz'],
								),
								'require_log_in'         => array(
									'option_name' => __( 'Require User Login', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['require_log_in'],
								),
								'disable_first_page'     => array(
									'option_name' => __( 'Disable first page on quiz', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['disable_first_page'],
								),
								'comment_section'        => array(
									'option_name' => __( 'Enable Comment box', 'quiz-master-next' ),
									'value'       => $globalQuizsetting['comment_section'],
								),
							);
							$quiz_setting_option = apply_filters( 'qsm_quiz_wizard_settings_option', $quiz_setting_option );
							if ( $quiz_setting_option ) {
								foreach ( $quiz_setting_option as $key => $single_setting ) {
									$index = array_search( $key, array_column( $all_settings, 'id' ), true );
									if ( is_int( $index ) && isset( $all_settings[ $index ] ) ) {
										$field               = $all_settings[ $index ];
										$field['label']      = $single_setting['option_name'];
										$field['default']    = $single_setting['value'];
									} else {
										$field = array(
											'id'      => $key,
											'label'   => $single_setting['option_name'],
											'type'    => isset( $single_setting['type'] ) ? $single_setting['type'] : 'radio',
											'options' => isset( $single_setting['options'] ) ? $single_setting['options'] : array(),
											'default' => $single_setting['value'],
											'help'    => __( 'Display a contact form before quiz', 'quiz-master-next' ),
										);
									}
									echo '<div class="input-group">';
									QSM_Fields::generate_field( $field, $single_setting['value'] );
									echo '</div>';
								}
							} else {
								esc_html_e( 'No settings found!', 'quiz-master-next' );
							}
							?>
						</div>
						<div id="select_themes" class="qsm-new-menu-elements">
							<div class="theme-browser rendered">
								<div class="themes wp-clearfix">
									<ul class="theme-sub-menu">
										<li class="active">
											<a data-show="downloaded_theme" href="javascript:void(0)"><?php esc_html_e( 'Themes', 'quiz-master-next' ); ?></a></li>
										<?php if ( ! empty( $themes_data ) ) { ?>
											<li>
												<a data-show="browse_themes" href="javascript:void(0)"><?php esc_html_e( 'Explore Marketplace', 'quiz-master-next' ); ?></a></li>
										<?php } ?>
									</ul>
									<div class="theme-wrap" id="browse_themes" style="display: none;">
										<?php qsm_get_market_themes(); ?>
									</div>
									<div class="theme-wrap" id="downloaded_theme">
										<?php
										qsm_get_installed_theme( 0, 'wizard_theme_list' );
										qsm_get_default_wizard_themes();
										?>
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
										?>
										<div class="qsm_popular_addons" id="qsm_popular_addons">
											<div class="popuar-addon-ul">
												<?php
												if ( $popular_addons ) {
													foreach ( $popular_addons as $key => $single_arr ) {
														$link = qsm_get_utm_link( $single_arr['link'], 'new_quiz', 'addons', 'quizsurvey_' . sanitize_title( $single_arr['name'] ) );
														?>
														<div>
															<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
																<span class="addon-itd-wrap">
																	<img alt="" src="<?php echo esc_url( $single_arr['img'] ); ?>" />
																</span>
															</a>
															<a class="addon-get-link" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
																<?php
																esc_html_e( 'Buy now', 'quiz-master-next' );
																echo ' : $ ' . esc_html( array_values( $single_arr['price'] )[0] );
																?>
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
									<span><?php esc_html_e( '40+ addons available', 'quiz-master-next' ); ?></span>
									<a style="text-decoration: none; font-size: 15px;" rel="noopener" href="<?php echo esc_url( qsm_get_plugin_link( 'addons', 'new_quiz', 'addons', 'quizsurvey_all_addons' ) ) ?>" target="_blank"><?php esc_html_e( 'Browse All Addons', 'quiz-master-next' ); ?></a>
								</div>
							</div>
						</div>
					</main>
				</form>
				<footer class="qsm-popup__footer">
					<button id="prev-theme-button"
							class="button qsm-wizard-borderless"><?php esc_html_e( 'Back', 'quiz-master-next' ); ?></button>
					<button id="prev-quiz-button"
							class="button qsm-wizard-borderless"></span><?php esc_html_e( 'Back', 'quiz-master-next' ); ?></button>
					<button class="button qsm-wizard-borderless" data-micromodal-close
							aria-label="Close this dialog window"><?php esc_html_e( 'Cancel', 'quiz-master-next' ); ?></button>
					<button id="next-quiz-button"
							class="button button-primary"><?php esc_html_e( 'Next', 'quiz-master-next' ); ?></button>
					<button id="choose-addons-button"
							class="button button-primary"><?php esc_html_e( 'Next', 'quiz-master-next' ); ?></button>
					<button id="create-quiz-button"
							class="button button-primary"><?php esc_html_e( 'Create Quiz', 'quiz-master-next' ); ?></button>
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
			'%CATEGORY_SCORE_X%'          => __( 'X: Category name - The score a specific category earned.', 'quiz-master-next' ),
			'%CATEGORY_AVERAGE_POINTS%'   => __( 'The average points from all categories.', 'quiz-master-next' ),
			'%CATEGORY_AVERAGE_SCORE%'    => __( 'The average score from all categories.', 'quiz-master-next' ),
			'%QUESTION_MAX_POINTS%'       => __( 'Maximum points of the question', 'quiz-master-next' ),
			'%FACEBOOK_SHARE%'            => __( 'Displays button to share on Facebook.', 'quiz-master-next' ),
			'%TWITTER_SHARE%'             => __( 'Displays button to share on Twitter.', 'quiz-master-next' ),
			'%RESULT_LINK%'               => __( 'The link of the result page.', 'quiz-master-next' ),
			'%CONTACT_X%'                 => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
			'%CONTACT_ALL%'               => __( 'Value user entered into contact field. X is # of contact field. For example, first contact field would be %CONTACT_1%', 'quiz-master-next' ),
			'%AVERAGE_CATEGORY_POINTS_X%' => __( 'X: Category name - The average amount of points a specific category earned.', 'quiz-master-next' ),
			'%QUESTION_ANSWER_X%'         => __( 'X = Question ID. It will show result of particular question.', 'quiz-master-next' ),
			'%ANSWER_X%'                  => __( 'X = Question ID. It will show result of particular question.', 'quiz-master-next' ),
			'%TIME_FINISHED%'             => __( 'Display time after quiz submission.', 'quiz-master-next' ),
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

function qsm_get_installed_theme( $saved_quiz_theme, $wizard_theme_list = '' ) {
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
	<?php do_action( 'qsm_add_after_default_theme' ); ?>
	<?php
	if ( $theme_folders ) {
		foreach ( $theme_folders as $key => $theme ) {
			$theme_name  = $theme['theme'];
			$theme_id    = $theme['id'];
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
						if ( $saved_quiz_theme != $theme_id ) {
							if ( 'wizard_theme_list' !== $wizard_theme_list ) {
								?>
								<button class="button qsm-activate-theme"><?php esc_html_e( 'Activate', 'quiz-master-next' ); ?></button>
								<?php
							}
							?>
						<?php } ?>
						<?php if ( $saved_quiz_theme === $theme_id ) { ?>
							<a class="button button-primary qsm-customize-color-settings" href="javascript:void(0)"><?php esc_html_e( 'Customize', 'quiz-master-next' ); ?></a>
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
		foreach ( $default_themes_data as $key => $theme ) {
			$theme_name          = $theme['name'];
			$theme_screenshot    = $theme['img'];
			$theme_url           = qsm_get_utm_link( $theme['link'], 'new_quiz', 'themes', 'quizsurvey_buy_' . sanitize_title( $theme_name ) );
			$theme_demo          = qsm_get_utm_link( $theme['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title( $theme_name ) );
			?>
			<div class="theme-wrapper theme market-theme">
				<div class="theme-screenshot" id="qsm-theme-screenshot">
					<?php if ( in_array( $theme_name, $pro_themes, true ) ) { ?>
						<span class="qsm-badge"><?php esc_html_e( 'Pro', 'quiz-master-next' ); ?></span>
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
	}
}

function qsm_get_market_themes() {
	global $themes_data, $pro_themes;
	if ( ! empty( $themes_data ) ) {
		foreach ( $themes_data as $key => $theme ) {
			$theme_name          = $theme['name'];
			$theme_screenshot    = $theme['img'];
			$theme_url           = qsm_get_utm_link( $theme['link'], 'new_quiz', 'themes', 'quizsurvey_buy_' . sanitize_title( $theme_name ) );
			$theme_demo          = qsm_get_utm_link( $theme['demo'], 'new_quiz', 'themes', 'quizsurvey_preview_' . sanitize_title( $theme_name ) );
			?>
			<div class="theme-wrapper theme market-theme">
				<div class="theme-screenshot" id="qsm-theme-screenshot">
					<?php if ( in_array( $theme_name, $pro_themes, true ) ) { ?>
						<span class="qsm-badge"><?php esc_html_e( 'Pro', 'quiz-master-next' ); ?></span>
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
	foreach ( (array) $array as $key => $value ) {
		if ( is_array( $value ) ) {
			$array[ $key ] = qsm_sanitize_rec_array( $value );
		} else {
			$array[ $key ] = $textarea ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
		}
	}
	return $array;
}

function qsm_admin_upgrade_popup( $args = array() ) {
	?>
	<div class="qsm-popup qsm-popup-slide qsm-standard-popup qsm-popup-upgrade" id="<?php echo esc_attr( $args['id'] ); ?>" aria-hidden="false"  style="display:none">
		<div class="qsm-popup__overlay" tabindex="-1" data-micromodal-close>
			<div class="qsm-popup__container" role="dialog" aria-modal="true">
				<header class="qsm-popup__header qsm-question-bank-header">
					<div class="qsm-popup__title" id="modal-2-title">
						<?php echo esc_html( $args['title'] ); ?>
						<h5 class="title-tag"><?php esc_html_e( 'PREMIUM', 'quiz-master-next' ); ?></h5>
					</div>
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
	);
	$args        = wp_parse_args( $args, $defaults );
	?>
	<div class="qsm-upgrade-box">
		<?php
		if ( ! empty( $args['warning'] ) ) {
			?><div class="qsm-popup-upgrade-warning">
				<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'php/images/warning.png' ); ?>" alt="warning">
				<span><?php echo esc_html( $args['warning'] ) ?></span>
			</div><?php
		}
		if ( ! empty( $args['title'] ) && 'popup' != $type ) {
			?><h2><?php echo esc_html( $args['title'] ); ?></h2><?php
		}
		if ( ! empty( $args['description'] ) ) {
			?><div class="qsm-upgrade-text qsm-popup-upgrade-text"><?php echo esc_html( $args['description'] ); ?></div><?php
		}
		if ( ! empty( $args['doc_link'] ) ) {
			?><span class="qsm-upgrade-read-icon">
				<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'php/images/read_icon.png' ); ?>" alt="read">
				<a href="<?php echo esc_url( $args['doc_link'] ); ?>" target="_blank" rel="noopener" >
					<?php esc_html_e( 'Read Documentation', 'quiz-master-next' ); ?><span class="dashicons dashicons-arrow-right-alt qsm-upgrade-right-arrow" ></span>
				</a>
			</span><?php
		}
		if ( ! empty( $args['chart_image'] ) ) {
			?><div class="qsm-upgrade-chart"><img src="<?php echo esc_url( $args['chart_image'] ); ?>" alt="chart"></div><?php
		}
		if ( ! empty( $args['information'] ) ) {
			?><div class="qsm-popup-upgrade-info">
				<img src="<?php echo esc_url( QSM_PLUGIN_URL . 'php/images/info.png' ); ?>" alt="information">
				<span><?php echo esc_html( $args['information'] ); ?></span>
			</div><?php
		}
		?>
		<div class="qsm-upgrade-buttons qsm-<?php echo esc_attr( $type ); ?>-upgrade-buttons">
			<a href="<?php echo esc_url( $args['upgrade_link'] ); ?>" target="_blank" class="qsm-popup__btn qsm-popup__btn-primary qsm_bundle" rel="noopener"><?php esc_html_e( 'Upgrade to QSM Pro', 'quiz-master-next' ); ?></a>
			<?php if ( ! empty( $args['addon_link'] ) ) : ?>
				<a href="<?php echo esc_url( $args['addon_link'] ); ?>" target="_blank" class="qsm_export_import"  rel="noopener" ><?php echo esc_html( $args['buy_btn_text'] ); ?></a>
			<?php endif; ?>
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
					<?php
					break;
				case 'color':
					?>
					<input name="settings[<?php echo esc_attr( $name ); ?>]" type="text" value="<?php echo esc_attr( $value ); ?>" data-default-color="<?php echo esc_attr( $default_value ); ?>" class="qsm-color-field" data-label="<?php echo esc_attr( $options['button_text'] ); ?>" />
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