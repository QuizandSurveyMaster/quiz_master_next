<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that handles migration logic
 *
 * @since 7.3.0
 */
class QSM_Migrate {


	public function __construct() {
		add_action( 'wp_ajax_enable_multiple_categories', array( $this, 'enable_multiple_categories' ) );
	}

	/**
	 * This function enables multiple categories feature in QSM
	 *
	 * @since 7.3.0
	 * @return void
	 */
	public function enable_multiple_categories() {
		global $wpdb;
		global $mlwQuizMasterNext;
		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : '';
		switch ( $value ) {
			case 'enable':
				$new_category  = '';
				$term_id       = 0;
				$values_array  = array();
				$result        = false;
				$category_data = $wpdb->get_results( "SELECT question_id, quiz_id, category FROM {$wpdb->prefix}mlw_questions WHERE category <> '' ORDER BY category" );
				if ( ! empty( $category_data ) ) {
					foreach ( $category_data as $data ) {
						if ( $new_category != $data->category ) {
							$term_data = get_term_by( 'name', $data->category, 'qsm_category' );
							if ( $term_data ) {
								$term_id = $term_data->term_id;
							} else {
								$term_array  = wp_insert_term( $data->category, 'qsm_category' );
								$term_id     = $term_array['term_id'];
							}
						}
						$values_array[] = "($data->question_id, $data->quiz_id, $term_id, 'qsm_category')";
					}
					$values          = join( ',', $values_array );
					$insert_query    = stripslashes( $wpdb->prepare( "INSERT INTO {$wpdb->prefix}mlw_question_terms (question_id, quiz_id, term_id, taxonomy) VALUES %1s", $values ) );
					$result          = $wpdb->query( $insert_query );
					if ( $result > 0 ) {
						update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
						$response    = array(
							'status' => true,
							'count'  => $result,
						);
						$update      = "UPDATE {$wpdb->prefix}mlw_questions SET category = '' ";
						$updated     = $wpdb->query( $update );
					} else {
						$response = array(
							'status' => false,
						);
					}
				} else {
					$response    = array(
						'status' => true,
						'count'  => 0,
					);
					update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
				}
				echo wp_json_encode( $response );
				break;

			case 'cancel':
				update_option( 'qsm_multiple_category_enabled', 'cancelled' );
				return true;
				break;
		}
		exit;
	}

	/**
	 * This function check which regime of category is available and returns category data accordingly
	 *
	 * @param string $name
	 * @return array
	 */
	public function get_category_data( $name ) {
		$enabled  = get_option( 'qsm_multiple_category_enabled' );
		$migrated = false;
		if ( $enabled && 'cancelled' !== $enabled ) {
			$migrated = true;
		}

		$response = array(
			'migrated' => $migrated,
		);

		if ( $migrated ) {
			$cats = explode( '_', $name );
			$ids  = array();
			foreach ( $cats as $category ) {
				$category = trim( $category );
				if ( '' !== $category ) {
					$cat_data = get_term_by( 'name', $category, 'qsm_category' );
					if ( $cat_data ) {
						$ids[] = $cat_data->term_id;
					}
				}
			}

			if ( ! empty( $ids ) ) {
				$response['ids'] = $ids;
			} else {
				$response['migrated'] = false;
				$response['name']     = $name;
			}
		} else {
			$response['name'] = $name;
		}

		return $response;
	}
}
