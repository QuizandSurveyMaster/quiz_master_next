<?php


// Include core functions (available in both admin and frontend).
require_once QSM_PLUGIN_PATH . 'php/models/index-models.php';
require_once QSM_PLUGIN_PATH . 'php/models/class-quiz-model.php';
require_once QSM_PLUGIN_PATH . 'php/legacy/class-qsm-legacy.php';

function is_qsm_migrated() {
	global $wpdb;
	$migrated = false;
	if ( version_compare( QSM()->version, '8.0', '>=' ) ) {
		$migrated = true;
	}
	if ( 1 == get_option( 'qsm_db_migrated' ) ) {
		$migrated = true;
	}
	return $migrated;
}

if ( ! function_exists( 'add_qsm_meta' ) ) {

	/**
	 * Add meta data field to a term.
	 *
	 * @param int $object_id Object ID.
	 * @param string $key Metadata name.
	 * @param mixed $value Metadata value.
	 * @param string $type Metadata type.
	 * @return bool False for failure. True for success.
	 */
	function add_qsm_meta( $object_id, $meta_key, $meta_value, $type = false ) {
		global $wpdb;
		$table = "{$wpdb->prefix}qsm_meta";
		if ( ! $type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
		// expected_slashed ($meta_key)
		$meta_key    = wp_unslash( $meta_key );
		$meta_value  = wp_unslash( $meta_value );
		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize( $meta_value );
		/**
		 * Fires immediately before meta of a specific type is added.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the meta object type
		 * (quiz, question, result, or any other type with an associated meta table).
		 *
		 * @param int    $object_id   ID of the object metadata is for.
		 * @param string $meta_key    Metadata key.
		 * @param mixed  $_meta_value Metadata value.
		 */
		do_action( "add_{$type}_meta", $object_id, $meta_key, $_meta_value );
		$result      = $wpdb->insert(
			$table, array(
				'object_id'  => $object_id,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
				'type'       => $type,
			)
		);
		if ( ! $result ) {
			return false;
		}
		$mid = (int) $wpdb->insert_id;
		/**
		 * Fires immediately after meta of a specific type is added.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the meta object type
		 * (quiz, question, result, or any other type with an associated meta table).
		 *
		 * @param int    $mid         The meta ID after successful update.
		 * @param int    $object_id   ID of the object metadata is for.
		 * @param string $meta_key    Metadata key.
		 * @param mixed  $_meta_value Metadata value.
		 */
		do_action( "added_{$type}_meta", $mid, $object_id, $meta_key, $_meta_value );

		return $mid;
	}
}
if ( ! function_exists( 'update_qsm_meta' ) ) {

	/**
	 * Update meta field based on Object ID.
	 *
	 * Use the $type parameter to differentiate between meta fields with the
	 * same key and object ID.
	 *
	 * If the meta field for the object does not exist, it will be added.
	 *
	 * @param int $object_id Object ID.
	 * @param string $key Metadata key.
	 * @param mixed $value Metadata value.
	 * @param string $type Metadata type.
	 * @return bool False on failure, true if success.
	 */
	function update_qsm_meta( $object_id, $meta_key, $meta_value, $type = false ) {
		global $wpdb;
		$table = "{$wpdb->prefix}qsm_meta";
		if ( ! $type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
		// expected_slashed ($meta_key)
		$meta_key    = wp_unslash( $meta_key );
		$meta_value  = wp_unslash( $meta_value );
		$meta_ids    = $wpdb->get_col( $wpdb->prepare( "SELECT `id` FROM `$table` WHERE `object_id` = %d AND `type` = %s AND `meta_key` = %s", $object_id, $type, $meta_key ) );
		if ( empty( $meta_ids ) ) {
			return add_qsm_meta( $object_id, $meta_key, $meta_value, $type );
		}

		$_meta_value = $meta_value;
		$meta_value  = maybe_serialize( $meta_value );
		$where       = array(
			'object_id' => $object_id,
			'meta_key'  => $meta_key,
			'type'      => $type,
		);

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately before updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook name, `$type`, refers to the meta object type
			 * (quiz, question, result, or any other type with an associated meta table).
			 * 
			 * @param int    $meta_id     ID of the metadata entry to update.
			 * @param int    $object_id   ID of the object metadata is for.
			 * @param string $meta_key    Metadata key.
			 * @param mixed  $_meta_value Metadata value.
			 */
			do_action( "update_{$type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
		}

		$result = $wpdb->update( $table, array( 'meta_value' => $meta_value ), $where );
		if ( ! $result ) {
			return false;
		}

		foreach ( $meta_ids as $meta_id ) {
			/**
			 * Fires immediately after updating metadata of a specific type.
			 *
			 * The dynamic portion of the hook name, `$type`, refers to the meta object type
			 * (quiz, question, result, or any other type with an associated meta table).
			 * 
			 * @param int    $meta_id     ID of updated metadata entry.
			 * @param int    $object_id   ID of the object metadata is for.
			 * @param string $meta_key    Metadata key.
			 * @param mixed  $_meta_value Metadata value.
			 */
			do_action( "updated_{$type}_meta", $meta_id, $object_id, $meta_key, $_meta_value );
		}
		return true;
	}
}
if ( ! function_exists( 'get_qsm_meta' ) ) {

	/**
	 * Retrieve meta field for a Object.
	 *
	 * @param int $object_id Object ID.
	 * @param string $key The meta key to retrieve.
	 * @param string $type Metadata type.
	 * @param bool $single Whether to return a single value.
	 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
	 *  is true.
	 */
	function get_qsm_meta( $object_id, $type = false, $meta_key = false, $single = false ) {
		global $wpdb;
		$table = "{$wpdb->prefix}qsm_meta";
		if ( ! $type || ! is_numeric( $object_id ) ) {
			return false;
		}
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
		$query = $wpdb->prepare( "SELECT * FROM `{$table}` WHERE `object_id` = %d AND `type` = %s", $object_id, $type );
		if ( $single ) {
			$query .= $wpdb->prepare( ' AND `meta_key` = %s', $meta_key );
		}
		$meta_list = $wpdb->get_results( $query, ARRAY_A );
		if ( ! empty( $meta_list ) ) {
			$cache = array();
			foreach ( $meta_list as $metarow ) {
				$mkey    = $metarow['meta_key'];
				$mval    = $metarow['meta_value'];
				if ( ! isset( $cache[ $mkey ] ) || ! is_array( $cache[ $mkey ] ) ) {
					$cache[ $mkey ] = array();
				}
				$cache[ $mkey ][] = $mval;
			}
			if ( ! $meta_key ) {
				return $cache;
			}
			if ( isset( $cache[ $meta_key ] ) ) {
				if ( $single ) {
					return maybe_unserialize( $cache[ $meta_key ][0] );
				} else {
					return array_map( 'maybe_unserialize', $cache[ $meta_key ] );
				}
			}
		}

		return null;
	}
}
if ( ! function_exists( 'delete_qsm_meta' ) ) {

	/**
	 * Remove metadata matching criteria from a Object.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param int $object_id Object ID
	 * @param string $meta_key Metadata name.
	 * @param mixed $meta_value Optional. Metadata value.
	 * @return bool False for failure. True for success.
	 */
	function delete_qsm_meta( $object_id, $meta_key, $type = false ) {
		global $wpdb;
		$table = "{$wpdb->prefix}qsm_meta";
		if ( ! $type || ! $meta_key || ! is_numeric( $object_id ) ) {
			return false;
		}
		$object_id = absint( $object_id );
		if ( ! $object_id ) {
			return false;
		}
		$meta_key = wp_unslash( $meta_key );
		/**
		 * Fires immediately before deleting metadata of a specific type.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the meta object type
		 * (quiz, question, result, or any other type with an associated meta table).
		 * 
		 * @param int      $object_id   ID of the object metadata is for.
		 * @param string   $meta_key    Metadata key.
		 */
		do_action( "delete_{$type}_meta", $object_id, $meta_key );

		$count = $wpdb->query( "DELETE FROM `$table` WHERE `object_id` = '$object_id' AND `type` = '$type' AND `meta_key` = '$meta_key'" );
		if ( ! $count ) {
			return false;
		}

		/**
		 * Fires immediately after deleting metadata of a specific type.
		 *
		 * The dynamic portion of the hook name, `$type`, refers to the meta object type
		 * (quiz, question, result, or any other type with an associated meta table).
		 * 
		 * @param int      $object_id   ID of the object metadata is for.
		 * @param string   $meta_key    Metadata key.
		 */
		do_action( "deleted_{$type}_meta", $object_id, $meta_key );

		return true;
	}
}