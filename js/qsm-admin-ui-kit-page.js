/**
 * QSM UI Kit admin page only: demo behaviours for the reference page.
 *
 * Shipped kit behaviour lives in qsm-admin-ui.js. What is here only makes
 * the page's sample data move (fake saves, sample rows, sample moves), so a
 * screen should never depend on it.
 */
( function ( $ ) {
	'use strict';

	var ui = window.QSMAdminUI;
	var $page = $( '.qsm-ui-kitpage' );
	if ( ! $page.length || ! ui ) {
		return;
	}

	// Side navigation built from the sections, so it never drifts from the page.
	var $nav = $( '#qsmk-nav' );
	var lastGroup = null;
	$page.find( '.kit-section' ).each( function () {
		var group = $( this ).attr( 'data-group' );
		if ( group !== lastGroup ) {
			$nav.append( $( '<div class="kit-nav-group"></div>' ).text( group ) );
			lastGroup = group;
		}
		$( this ).find( '.kit-item' ).each( function () {
			var $a = $( '<a></a>' ).attr( 'href', '#' + this.id );
			$a.append( $( '<b></b>' ).text( $( this ).attr( 'data-id' ) ) ).append( document.createTextNode( $( this ).find( '.kit-item-head h3' ).text() ) );
			$nav.append( $a );
		} );
	} );

	// Sample links go nowhere.
	$page.on( 'click', 'a[href="#"]', function ( e ) {
		e.preventDefault();
	} );

	// Loading buttons (B-02, F-03, P-06).
	$page.on( 'click', '[data-kit-loading]', function () {
		var b = this;
		ui.loading( b, true );
		setTimeout( function () {
			ui.loading( b, false );
		}, 1200 );
	} );

	// Dialog buttons that "do" something.
	$page.on( 'click', '[data-kit-toast-after]', function () {
		ui.toast( $( this ).attr( 'data-kit-toast-after' ) );
	} );

	// Searchable select (F-17) through the shipped helper.
	ui.select2( $page.find( '[data-kit-select2]' ) );

	// WP colour picker (F-21), as Core starts it today.
	if ( $.fn.wpColorPicker ) {
		$page.find( '.qsm-ui-kit-color' ).wpColorPicker();
	}

	// Sortable lists (G-02, D-04) through the shipped helper.
	ui.sortable( $page.find( '[data-kit-pages-board] [data-kit-sortable]' ), { connectWith: '[data-kit-pages-board] [data-kit-sortable]', items: '> li' } );
	ui.sortable( $page.find( 'tbody[data-kit-sortable]' ), { items: '> tr' } );

	// Question board selection (G-02).
	function boardSync() {
		var n = $page.find( '[data-kit-q-select]:checked' ).length;
		$page.find( '[data-kit-board-bar]' ).prop( 'hidden', ! n );
		$page.find( '[data-kit-board-count]' ).text( n + ' selected' );
	}
	$page.on( 'change', '[data-kit-q-select]', boardSync );
	$page.on( 'change', '[data-kit-page-select]', function () {
		$( this ).closest( '.qsm-ui-page-block' ).find( '[data-kit-q-select]' ).prop( 'checked', this.checked );
		boardSync();
	} );
	$page.on( 'click', '[data-kit-board-clear]', function () {
		$page.find( '[data-kit-q-select], [data-kit-page-select]' ).prop( 'checked', false );
		boardSync();
	} );
	$page.on( 'click', '[data-kit-move-selected], [data-kit-move-to]', function () {
		var to = Number.parseInt( $( this ).attr( 'data-kit-move-selected' ) || $( this ).attr( 'data-kit-move-to' ), 10 );
		var $list = $page.find( '[data-kit-pages-board] [data-kit-sortable]' ).eq( to );
		var $rows = undefined !== $( this ).attr( 'data-kit-move-selected' ) ? $page.find( '[data-kit-q-select]:checked' ).closest( 'li' ) : $( this ).closest( 'li.qsm-ui-sort-item' );
		$list.append( $rows );
		$page.find( '[data-kit-q-select], [data-kit-page-select]' ).prop( 'checked', false );
		boardSync();
		ui.toast( 'Moved to page ' + ( to + 1 ) );
	} );

	// Data table (D-01): select all, bulk bar, search, sort.
	var $table = $page.find( '[data-kit-table]' );
	function bulkSync() {
		var $boxes = $table.find( 'tbody input[type="checkbox"]' );
		var n = $boxes.filter( ':checked' ).length;
		$boxes.each( function () {
			$( this ).closest( 'tr' ).toggleClass( 'is-selected', this.checked );
		} );
		$table.find( '[data-kit-select-all]' ).prop( { checked: n && n === $boxes.length, indeterminate: n && n < $boxes.length } );
		$page.find( '[data-kit-bulkbar]' ).prop( 'hidden', ! n );
		$page.find( '[data-kit-bulk-count]' ).text( n + ' selected' );
	}
	$table.on( 'change', 'tbody input[type="checkbox"]', bulkSync );
	$table.on( 'change', '[data-kit-select-all]', function () {
		$table.find( 'tbody input[type="checkbox"]' ).prop( 'checked', this.checked );
		bulkSync();
	} );
	$page.on( 'click', '[data-kit-bulk-clear]', function () {
		$table.find( 'input[type="checkbox"]' ).prop( 'checked', false );
		bulkSync();
	} );
	$page.on( 'input', '[data-kit-table-search]', function () {
		var q = this.value.toLowerCase();
		$table.find( 'tbody tr' ).each( function () {
			$( this ).prop( 'hidden', !! q && $( this ).text().toLowerCase().indexOf( q ) < 0 );
		} );
	} );
	$table.on( 'click', 'th .qsm-ui-sort', function () {
		var $th = $( this ).closest( 'th' );
		var col = $th.index();
		var asc = 'ascending' !== $th.attr( 'aria-sort' );
		var num = undefined !== $( this ).attr( 'data-kit-num' ) || undefined !== $( this ).attr( 'data-kit-date' );
		$th.siblings( '[aria-sort]' ).attr( 'aria-sort', 'none' );
		$th.attr( 'aria-sort', asc ? 'ascending' : 'descending' );
		var rows = $table.find( 'tbody tr' ).get().sort( function ( a, b ) {
			var x = $( a ).children().eq( col ).text().trim();
			var y = $( b ).children().eq( col ).text().trim();
			var r = num ? ( Number.parseFloat( x.replace( /[^0-9.\-]/g, '' ) ) || Date.parse( x ) || 0 ) - ( Number.parseFloat( y.replace( /[^0-9.\-]/g, '' ) ) || Date.parse( y ) || 0 ) : x.localeCompare( y );
			return asc ? r : -r;
		} );
		$table.find( 'tbody' ).append( rows );
	} );

	// Condition builder (R-02) and field mapping (R-03): add / remove sample rows.
	$page.on( 'click', '[data-kit-add-cond]', function () {
		var $group = $( this ).closest( '[data-kit-cond-group]' );
		var $row = $group.find( '.qsm-ui-cond-row' ).last().clone();
		$row.find( '.qsm-ui-cond-join' ).text( 'AND' );
		$row.find( 'input' ).val( '' );
		$( this ).parent().before( $row );
	} );
	$page.on( 'click', '[data-kit-remove-cond]', function () {
		var $group = $( this ).closest( '[data-kit-cond-group]' );
		if ( $group.find( '.qsm-ui-cond-row' ).length > 1 ) {
			$( this ).closest( '.qsm-ui-cond-row' ).remove();
			$group.find( '.qsm-ui-cond-join' ).first().text( 'IF' );
		}
	} );
	$page.on( 'click', '[data-kit-add-row]', function () {
		var $body = $page.find( '[data-kit-map-body]' );
		var $row = $body.find( 'tr' ).last().clone();
		$row.find( 'input' ).val( '' );
		$body.append( $row );
	} );
	$page.on( 'click', '[data-kit-remove-row]', function () {
		var $body = $( this ).closest( '[data-kit-map-body]' );
		if ( $body.find( 'tr' ).length > 1 ) {
			$( this ).closest( 'tr' ).remove();
		}
	} );

	// Question editor card (P-06): collapse, and the description toggle.
	$page.on( 'click', '[data-kit-qcard-toggle]', function () {
		var $card = $( this ).closest( '[data-kit-qcard]' );
		var collapsed = undefined === $card.attr( 'data-collapsed' );
		$card.attr( 'data-collapsed', collapsed ? '' : null );
		$card.find( '> header [data-kit-qcard-toggle]' ).attr( 'aria-expanded', collapsed ? 'false' : 'true' );
	} );
	$page.on( 'click', '[data-kit-toggle-target]', function () {
		var $t = $( $( this ).attr( 'data-kit-toggle-target' ) );
		$t.prop( 'hidden', ! $t.prop( 'hidden' ) );
	} );

	// Category tree (F-25): "Add category" sample form.
	$page.on( 'click', '[data-kit-tree-add-toggle]', function () {
		var $form = $( this ).siblings( '[data-kit-tree-add]' );
		$form.prop( 'hidden', ! $form.prop( 'hidden' ) );
	} );
	$page.on( 'click', '[data-kit-tree-add-save]', function () {
		var $form = $( this ).closest( '[data-kit-tree-add]' );
		var name = $.trim( $form.find( '[data-kit-tree-new]' ).val() );
		if ( name ) {
			ui.toast( 'Category "' + name + '" added' );
			$form.find( '[data-kit-tree-new]' ).val( '' );
			$form.prop( 'hidden', true );
		}
	} );
}( jQuery ) );
