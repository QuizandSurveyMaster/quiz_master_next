/**
 * QSM Results list (New UI): row selection, the bulk bar and the delete confirms.
 *
 * Works on #qsm-results-list only. The forms post the same fields as before
 * (delete_results[], bulk_delete, bulk_permanent_delete, result_id, delete_quiz_name
 * and their nonces), so the server side is unchanged.
 */
( function ( $ ) {
	'use strict';

	var $root = $( '#qsm-results-list' );
	if ( ! $root.length || ! window.QSMAdminUI ) {
		return;
	}
	var i18n = window.qsmResultsList || {};

	function format( one, many, n ) {
		return String( 1 === n ? one : many ).replace( '%s', n );
	}

	function boxes() {
		return $root.find( '.qmn_delete_checkbox' );
	}

	// Keeps the select-all box, row highlight and bulk bar in step with the row boxes.
	function sync() {
		var $boxes = boxes();
		var count = $boxes.filter( ':checked' ).length;
		var all = $root.find( '#qmn_check_all' ).get( 0 );
		$boxes.each( function () {
			$( this ).closest( 'tr' ).toggleClass( 'is-selected', this.checked );
		} );
		if ( all ) {
			all.checked = count > 0 && count === $boxes.length;
			all.indeterminate = count > 0 && count < $boxes.length;
		}
		$root.find( '[data-qsm-results-bulkbar]' ).prop( 'hidden', 0 === count );
		$root.find( '[data-qsm-results-count]' ).text( format( i18n.selectedOne, i18n.selectedMany, count ) );
		return count;
	}

	$root.on( 'change', '#qmn_check_all', function () {
		boxes().prop( 'checked', this.checked );
		sync();
	} );
	$root.on( 'change', '.qmn_delete_checkbox', sync );
	$root.on( 'click', '[data-qsm-results-clear]', function () {
		boxes().prop( 'checked', false );
		sync();
	} );

	// Bulk bar: open the matching confirm with the selected count in its title.
	$root.on( 'click', '[data-qsm-results-bulk]', function () {
		var count = sync();
		var permanent = 'permanent' === $( this ).attr( 'data-qsm-results-bulk' );
		var $dialog = $( permanent ? '#qsm-results-permanent-dialog' : '#qsm-results-bulk-dialog' );
		if ( ! count ) {
			return;
		}
		$dialog.find( '.qsm-ui-modal-head h2' ).text(
			permanent ? format( i18n.permanentOne, i18n.permanentMany, count ) : format( i18n.bulkOne, i18n.bulkMany, count )
		);
		if ( ! permanent && i18n.bulkTextOne ) {
			$dialog.find( '.qsm-ui-modal-head p' ).text( 1 === count ? i18n.bulkTextOne : i18n.bulkTextMany );
		}
		window.QSMAdminUI.openDialog( $dialog );
	} );

	$root.on( 'click', '[data-qsm-results-bulk-submit]', function () {
		var form = $root.find( '#qsm-results-bulk-form' ).get( 0 );
		if ( this.disabled || ! form ) {
			return;
		}
		$( '#bulk_permanent_delete' ).val( 'permanent' === $( this ).attr( 'data-qsm-results-bulk-submit' ) ? '1' : '0' );
		// One POST only: a double click would delete twice and log two audit entries.
		$( this ).prop( 'disabled', true );
		window.QSMAdminUI.loading( this, true );
		form.submit();
	} );

	// Row action: delete one result.
	$root.on( 'click', '.qsm-ui-result-delete', function ( e ) {
		var $link = $( this );
		e.preventDefault();
		$( '#result_id' ).val( $link.attr( 'data-result-id' ) );
		$( '#delete_quiz_name' ).val( $link.attr( 'data-quiz-name' ) );
		$root.find( '[data-qsm-results-delete-text]' ).text( String( i18n.deleteFor || '%s' ).replace( '%s', $link.attr( 'data-quiz-name' ) ) );
		window.QSMAdminUI.openDialog( '#qsm-results-delete-dialog' );
	} );

	$root.on( 'submit', '#qsm-results-delete-form', function ( e ) {
		if ( $( this ).data( 'qsmSubmitted' ) ) {
			e.preventDefault();
			return;
		}
		$( this ).data( 'qsmSubmitted', true );
		window.QSMAdminUI.loading( $( this ).find( '[type="submit"]' ), true );
	} );

	sync();
}( jQuery ) );
