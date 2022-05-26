<?php

/**
 * Update DB for 8.0
 *
 * @return void
 */
function qsm_update_80() {
	/**
	 * Call legacy code.
	 */
	QSM_Legacy::update();

	/**
	 * Set migration flags for fresh install
	 */
	update_option( 'qsm_multiple_category_enabled', gmdate( time() ) );
}

/**
 * Update DB version for 8.0
 *
 * @return void
 */
function qsm_update_80_db_version() {
	QSM_Install::update_db_version( '8.0' );
}
