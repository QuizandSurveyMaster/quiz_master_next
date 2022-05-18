<?php

/**
 * QSM Data Store Interface
 *
 * @version  8.0
 */
interface QSM_Data_Store_Interface {

	/**
	 * Method to create a new record.
	 *
	 * @param $data Data object.
	 */
	public function create( &$data );

	/**
	 * Method to read a record.
	 *
	 * @param $data Data object.
	 */
	public function read( &$data );

	/**
	 * Updates a record in the database.
	 *
	 * @param $data Data object.
	 */
	public function update( &$data );

	/**
	 * Deletes a record from the database.
	 *
	 * @param  $data Data object.
	 * @param  array $args Array of args to pass to the delete method.
	 * @return bool result
	 */
	public function delete( &$data, $args = array() );
}
