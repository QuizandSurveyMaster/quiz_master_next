/**
 * QSM Admin UI kit behaviours (jQuery).
 *
 * Everything is opt-in through data-qsm-ui-* attributes (plus data-show-if /
 * data-enable-if) and only runs inside a `.qsm-ui` wrapper, so add-on markup
 * is never touched. Markup added after load: call QSMAdminUI.init( container ).
 *
 * Public API: window.QSMAdminUI
 *   toast( message, { type: 'error', undo: fn, duration: ms } )
 *   openDialog( el ) / closeDialog( el )
 *   loading( button, isLoading )
 *   select2( $select, options )   start Select2 with the kit look (F-17)
 *   sortable( $list, options )    start jQuery UI Sortable with the kit placeholder (G-02, D-04)
 *   refreshRules( container )     re-evaluate data-show-if / data-enable-if
 *   init( container )
 */
( function ( $, window, document ) {
	'use strict';

	var settings = window.qsmAdminUI || {};
	var i18n = $.extend(
		{
			copied: 'Copied to clipboard',
			copyFail: 'Could not copy. Select the text and copy it manually.',
			show: 'Show',
			hide: 'Hide',
			undo: 'Undo',
			inserted: 'Inserted'
		},
		settings.i18n || {}
	);
	var ROOT = '.qsm-ui';

	// data-qsm-ui-* references are selectors (usually "#id"); a bad one must not break the handler.
	function ref( sel ) {
		if ( ! sel ) {
			return $();
		}
		if ( '#' === sel.charAt( 0 ) && document.getElementById( sel.slice( 1 ) ) ) {
			return $( document.getElementById( sel.slice( 1 ) ) );
		}
		try {
			return $( sel );
		} catch ( e ) {
			return $();
		}
	}

	function icon( name ) {
		return '<svg class="qsm-ui-icon" aria-hidden="true" focusable="false"><use href="#qsm-ui-i-' + name + '"></use></svg>';
	}

	/* ---------- Toasts (N-02) ---------- */
	function toast( message, opts ) {
		opts = opts || {};
		var $box = $( '.qsm-ui-toasts' );
		if ( ! $box.length ) {
			$box = $( '<div class="qsm-ui-toasts" role="status" aria-live="polite"></div>' );
		}
		// A modal <dialog> sits in the top layer, above anything in <body>: show the toast inside it.
		var $open = $( 'dialog.qsm-ui-modal[open]' ).last();
		$box.appendTo( $open.length ? $open : document.body );
		var isError = 'error' === opts.type;
		var $t = $( '<div class="qsm-ui-toast"></div>' ).toggleClass( 'is-error', isError );
		$t.append( icon( isError ? 'alert' : 'ok' ) ).append( $( '<span></span>' ).text( message ) );
		if ( 'function' === typeof opts.undo ) {
			$( '<button type="button" class="qsm-ui-toast-action"></button>' ).text( i18n.undo ).on( 'click', function () {
				$t.remove();
				opts.undo();
			} ).appendTo( $t );
		}
		$box.append( $t );
		setTimeout( function () {
			$t.remove();
		}, opts.duration || 4000 );
		return $t;
	}

	/* ---------- Buttons (B-02) ---------- */
	function loading( button, isLoading ) {
		$( button ).toggleClass( 'is-loading', !! isLoading ).attr( 'aria-busy', isLoading ? 'true' : null );
	}

	/* ---------- Dialogs (O-01, O-02) ---------- */
	function openDialog( el ) {
		var d = $( el ).get( 0 );
		if ( ! d || 'function' !== typeof d.showModal ) {
			return;
		}
		$( d ).find( '[data-qsm-ui-confirm-text]' ).val( '' ).trigger( 'input' );
		d.showModal();
		var focus = d.querySelector( '[data-qsm-ui-initial-focus]' );
		if ( focus ) {
			focus.focus();
		}
		$( d ).trigger( 'qsm-ui:dialog-open' );
	}

	function closeDialog( el ) {
		var d = $( el ).closest( 'dialog' ).get( 0 ) || $( el ).get( 0 );
		if ( d && d.open ) {
			d.close();
		}
	}

	/* ---------- Rules: data-show-if / data-enable-if (L-06) ----------
	 * "field:v1,v2"  matches when the field has one of the values
	 * "field:!0"     matches when it has anything but 0
	 * "a:0;b:!1"     every clause must match
	 * Checkboxes read 1 / 0; empty fields read 0; radios read the checked one.
	 * Hidden rows stay in the form. Greyed-out rows keep their fields enabled so
	 * saved values still submit; they are only marked aria-disabled and taken
	 * out of the tab order.
	 */
	function fieldValue( $ctrls ) {
		// toggle( unchecked_value ) prints a hidden input with the same name before the checkbox: read the real control.
		var $real = $ctrls.not( '[type="hidden"]' );
		if ( $real.length ) {
			$ctrls = $real;
		}
		var c = $ctrls.filter( function () {
			return 'radio' !== this.type || this.checked;
		} ).get( 0 );
		if ( ! c ) {
			return '';
		}
		if ( 'checkbox' === c.type ) {
			return c.checked ? '1' : '0';
		}
		return '' === c.value || null === c.value ? '0' : String( c.value );
	}

	function ruleMatches( rule, $scope ) {
		return String( rule ).split( ';' ).every( function ( part ) {
			var i = part.indexOf( ':' );
			if ( i < 1 ) {
				return true;
			}
			var name = $.trim( part.slice( 0, i ) );
			var vals = $.trim( part.slice( i + 1 ) );
			var neg = '!' === vals.charAt( 0 );
			var list = ( neg ? vals.slice( 1 ) : vals ).split( ',' );
			var $ctrls = $scope.find( '[name="' + name.replace( /"/g, '\\"' ) + '"]' );
			var hit = list.indexOf( fieldValue( $ctrls ) ) > -1;
			return neg ? ! hit : hit;
		} );
	}

	function ruleScope( row ) {
		var $form = $( row ).closest( 'form' );
		return $form.length ? $form : $( row ).closest( ROOT );
	}

	function applyRule( row ) {
		var $row = $( row );
		var $scope = ruleScope( row );
		if ( undefined !== $row.attr( 'data-show-if' ) ) {
			$row.prop( 'hidden', ! ruleMatches( $row.attr( 'data-show-if' ), $scope ) );
		}
		if ( undefined !== $row.attr( 'data-enable-if' ) ) {
			var ok = ruleMatches( $row.attr( 'data-enable-if' ), $scope );
			$row.toggleClass( 'is-disabled-by-rule', ! ok );
			$row.find( 'input, select, textarea, button' ).each( function () {
				var $f = $( this );
				if ( ! ok ) {
					if ( undefined === $f.data( 'qsmUiTabindex' ) ) {
						$f.data( 'qsmUiTabindex', $f.attr( 'tabindex' ) || '' );
						$f.data( 'qsmUiAriaDisabled', $f.attr( 'aria-disabled' ) || '' );
					}
					$f.attr( { 'aria-disabled': 'true', tabindex: '-1' } );
				} else if ( undefined !== $f.data( 'qsmUiTabindex' ) ) {
					var t = $f.data( 'qsmUiTabindex' );
					var ad = $f.data( 'qsmUiAriaDisabled' );
					$f.removeData( 'qsmUiTabindex qsmUiAriaDisabled' );
					if ( ad ) {
						$f.attr( 'aria-disabled', ad );
					} else {
						$f.removeAttr( 'aria-disabled' );
					}
					if ( t ) {
						$f.attr( 'tabindex', t );
					} else {
						$f.removeAttr( 'tabindex' );
					}
				}
			} );
		}
	}

	function refreshRules( container ) {
		$( container || document ).find( ROOT + ' [data-show-if], ' + ROOT + ' [data-enable-if]' ).addBack( '[data-show-if], [data-enable-if]' ).each( function () {
			if ( $( this ).closest( ROOT ).length ) {
				applyRule( this );
			}
		} );
	}

	/* ---------- Counters (F-06) ---------- */
	function updateCounter( c ) {
		var $c = $( c );
		var ta = document.getElementById( $c.attr( 'data-qsm-ui-counter-for' ) );
		if ( ! ta ) {
			return;
		}
		var max = parseInt( $c.attr( 'data-max' ), 10 ) || 0;
		var len = ta.value.length;
		$c.text( max ? len + ' / ' + max : String( len ) ).toggleClass( 'is-over', !! max && len > max );
	}

	/* ---------- Tabs (L-02, L-03) ---------- */
	function selectTab( tab ) {
		var $tab = $( tab );
		$tab.closest( '[data-qsm-ui-tabs]' ).find( '[role="tab"]' ).each( function () {
			var on = this === tab;
			$( this ).attr( { 'aria-selected': on ? 'true' : 'false', tabindex: on ? null : '-1' } );
			var panel = document.getElementById( $( this ).attr( 'aria-controls' ) );
			if ( panel ) {
				panel.hidden = ! on;
			}
		} );
		$tab.trigger( 'qsm-ui:tab-change' );
	}

	/* ---------- Menus (O-04, B-03) ---------- */
	function closeMenus( except ) {
		$( ROOT + ' .qsm-ui-menu' ).not( except || [] ).prop( 'hidden', true );
		$( ROOT + ' [data-qsm-ui-menu]' ).filter( function () {
			return ! except || $( this ).closest( '.qsm-ui-menu-wrap' ).find( '.qsm-ui-menu' ).get( 0 ) !== $( except ).get( 0 );
		} ).attr( 'aria-expanded', 'false' );
	}

	/* ---------- Template variable picker (F-24) ---------- */
	function insertVariable( targetId, text ) {
		if ( window.tinymce && targetId ) {
			var ed = window.tinymce.get( targetId );
			if ( ed && ! ed.isHidden() ) {
				ed.execCommand( 'mceInsertContent', false, text );
				return true;
			}
		}
		var field = targetId ? document.getElementById( targetId ) : null;
		if ( field && 'selectionStart' in field ) {
			var s = field.selectionStart;
			var e = field.selectionEnd;
			field.value = field.value.slice( 0, s ) + text + field.value.slice( e );
			field.selectionStart = field.selectionEnd = s + text.length;
			$( field ).trigger( 'input' ).trigger( 'change' );
			return true;
		}
		return false;
	}

	/* ---------- Category tree (F-25) ---------- */
	function filterTree( $tree, q ) {
		q = $.trim( q ).toLowerCase();
		var walk = function ( li ) {
			var $li = $( li );
			var own = $li.children( '.qsm-ui-tree-row' ).text().toLowerCase().indexOf( q ) > -1;
			var child = false;
			$li.children( 'ul' ).children( 'li' ).each( function () {
				child = walk( this ) || child;
			} );
			var show = ! q || own || child;
			$li.prop( 'hidden', ! show );
			return show && ( own || child );
		};
		$tree.find( '.qsm-ui-tree' ).first().children( 'li' ).each( function () {
			walk( this );
		} );
	}

	/* ---------- Init (one-off state that isn't event-driven) ---------- */
	function init( container ) {
		var $c = $( container || document );
		$c.find( ROOT + ' [data-qsm-ui-indeterminate]' ).each( function () {
			this.indeterminate = true;
		} );
		$c.find( ROOT + ' [data-qsm-ui-counter-for]' ).each( function () {
			updateCounter( this );
		} );
		$c.find( ROOT + ' [data-qsm-ui-controls]' ).each( function () {
			ref( $( this ).attr( 'data-qsm-ui-controls' ) ).toggleClass( 'qsm-ui-is-disabled', ! this.checked );
		} );
		$c.find( ROOT + ' [data-qsm-ui-reveal-target]' ).each( function () {
			ref( $( this ).attr( 'data-qsm-ui-reveal-target' ) ).prop( 'hidden', ! this.checked );
		} );
		$c.find( ROOT + ' [data-qsm-ui-confirm-text]' ).each( function () {
			$( this ).trigger( 'input' );
		} );
		refreshRules( $c );
	}

	/* ---------- Delegated events ---------- */
	var $doc = $( document );

	// Rules: any change inside a kit wrapper re-evaluates the rules in its scope.
	$doc.on( 'change input', ROOT + ' input, ' + ROOT + ' select, ' + ROOT + ' textarea', function () {
		// Only rows inside a kit wrapper (the scope can be a whole form with add-on markup in it).
		ruleScope( this ).find( '[data-show-if], [data-enable-if]' ).each( function () {
			if ( $( this ).closest( ROOT ).length ) {
				applyRule( this );
			}
		} );
	} );

	// Stepper (F-04): change the value, keep min/max, fire the normal events.
	$doc.on( 'click', ROOT + ' [data-qsm-ui-stepper] button', function () {
		var input = $( this ).closest( '[data-qsm-ui-stepper]' ).find( 'input' ).get( 0 );
		if ( ! input || input.disabled || input.readOnly ) {
			return;
		}
		var step = parseFloat( input.step ) || 1;
		var v = ( parseFloat( input.value ) || 0 ) + step * parseInt( $( this ).attr( 'data-step' ), 10 );
		if ( '' !== input.min && v < parseFloat( input.min ) ) {
			v = parseFloat( input.min );
		}
		if ( '' !== input.max && v > parseFloat( input.max ) ) {
			v = parseFloat( input.max );
		}
		var decimals = ( String( step ).split( '.' )[ 1 ] || '' ).length;
		input.value = decimals ? v.toFixed( decimals ) : v;
		$( input ).trigger( 'input' ).trigger( 'change' );
	} );

	// Show / hide a secret (F-05).
	$doc.on( 'click', ROOT + ' [data-qsm-ui-reveal]', function () {
		var input = ref( $( this ).attr( 'data-qsm-ui-reveal' ) ).get( 0 );
		if ( ! input ) {
			return;
		}
		var show = 'password' === input.type;
		input.type = show ? 'text' : 'password';
		$( this ).attr( 'aria-label', show ? i18n.hide : i18n.show ).attr( 'aria-pressed', show ? 'true' : 'false' );
	} );

	// Character counter (F-06).
	$doc.on( 'input', ROOT + ' textarea, ' + ROOT + ' input', function () {
		if ( this.id ) {
			$( ROOT + ' [data-qsm-ui-counter-for="' + this.id + '"]' ).each( function () {
				updateCounter( this );
			} );
		}
	} );

	// Copy (F-08).
	$doc.on( 'click', ROOT + ' [data-qsm-ui-copy]', function () {
		var src = ref( $( this ).attr( 'data-qsm-ui-copy' ) ).get( 0 );
		if ( ! src ) {
			return;
		}
		var text = 'value' in src ? src.value : $( src ).text();
		var fallback = function () {
			var ok = false;
			try {
				if ( src.select ) {
					src.select();
				}
				ok = document.execCommand( 'copy' );
			} catch ( e ) {}
			toast( ok ? i18n.copied : i18n.copyFail, ok ? {} : { type: 'error' } );
		};
		if ( navigator.clipboard && window.isSecureContext ) {
			navigator.clipboard.writeText( text ).then( function () {
				toast( i18n.copied );
			}, fallback );
		} else {
			fallback();
		}
	} );

	// Range output (F-19): <output> shows value + data-unit.
	$doc.on( 'input', ROOT + ' .qsm-ui-range input[type="range"]', function () {
		var $o = $( this ).siblings( 'output' );
		$o.text( this.value + ( $o.attr( 'data-unit' ) || '' ) );
	} );

	// File drop zone (F-23): highlight while dragging; dropped files go to the input.
	$doc.on( 'dragenter dragover', ROOT + ' .qsm-ui-dropzone', function ( e ) {
		e.preventDefault();
		$( this ).addClass( 'is-over' );
	} );
	$doc.on( 'dragleave drop', ROOT + ' .qsm-ui-dropzone', function ( e ) {
		e.preventDefault();
		$( this ).removeClass( 'is-over' );
		var dt = e.originalEvent && e.originalEvent.dataTransfer;
		var input = $( this ).find( 'input[type="file"]' ).get( 0 );
		if ( 'drop' === e.type && dt && dt.files.length && input ) {
			try {
				input.files = dt.files;
				$( input ).trigger( 'change' );
			} catch ( err ) {}
		}
	} );

	// Dismissible notice (N-01).
	$doc.on( 'click', ROOT + ' [data-qsm-ui-dismiss]', function () {
		var $n = $( this ).closest( '.qsm-ui-notice' );
		$n.trigger( 'qsm-ui:dismiss' ).remove();
	} );

	// Card header switch greys out the body (L-05); switch reveals a block (L-07).
	$doc.on( 'change', ROOT + ' [data-qsm-ui-controls]', function () {
		ref( $( this ).attr( 'data-qsm-ui-controls' ) ).toggleClass( 'qsm-ui-is-disabled', ! this.checked );
	} );
	$doc.on( 'change', ROOT + ' [data-qsm-ui-reveal-target]', function () {
		ref( $( this ).attr( 'data-qsm-ui-reveal-target' ) ).prop( 'hidden', ! this.checked );
	} );

	// Toast buttons, mostly for the kit page (N-02).
	$doc.on( 'click', ROOT + ' [data-qsm-ui-toast]', function () {
		var $b = $( this );
		toast( $b.attr( 'data-qsm-ui-toast' ), {
			type: undefined !== $b.attr( 'data-qsm-ui-toast-error' ) ? 'error' : '',
			undo: undefined !== $b.attr( 'data-qsm-ui-toast-undo' ) ? function () {} : null
		} );
	} );

	// Tabs: click + arrow keys / Home / End.
	$doc.on( 'click', ROOT + ' [data-qsm-ui-tabs] [role="tab"]', function ( e ) {
		if ( 'A' === this.tagName && ! $( this ).attr( 'aria-controls' ) ) {
			return; // Link tabs (quiz edit tabs) navigate as normal.
		}
		e.preventDefault();
		selectTab( this );
	} );
	$doc.on( 'keydown', ROOT + ' [data-qsm-ui-tabs] [role="tab"]', function ( e ) {
		var $tabs = $( this ).closest( '[data-qsm-ui-tabs]' ).find( '[role="tab"]' );
		var i = $tabs.index( this );
		var map = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: $tabs.length - 1 };
		if ( ! ( e.key in map ) ) {
			return;
		}
		e.preventDefault();
		var next = $tabs.get( ( map[ e.key ] + $tabs.length ) % $tabs.length );
		if ( $( next ).attr( 'aria-controls' ) ) {
			selectTab( next );
		}
		next.focus();
	} );

	// Menus.
	$doc.on( 'click', ROOT + ' [data-qsm-ui-menu]', function ( e ) {
		e.stopPropagation();
		var $menu = $( this ).closest( '.qsm-ui-menu-wrap' ).find( '.qsm-ui-menu' ).first();
		var open = $menu.prop( 'hidden' );
		closeMenus( $menu );
		$menu.prop( 'hidden', ! open );
		$( this ).attr( 'aria-expanded', open ? 'true' : 'false' );
		if ( open ) {
			$menu.find( 'button, a' ).first().trigger( 'focus' );
		}
	} );
	$doc.on( 'keydown', ROOT + ' .qsm-ui-menu', function ( e ) {
		var $items = $( this ).find( 'button:not(:disabled), a' );
		var i = $items.index( document.activeElement );
		if ( 'ArrowDown' === e.key || 'ArrowUp' === e.key ) {
			e.preventDefault();
			$items.eq( ( i + ( 'ArrowDown' === e.key ? 1 : -1 ) + $items.length ) % $items.length ).trigger( 'focus' );
		} else if ( 'Escape' === e.key ) {
			$( this ).prop( 'hidden', true );
			$( this ).closest( '.qsm-ui-menu-wrap' ).find( '[data-qsm-ui-menu]' ).attr( 'aria-expanded', 'false' ).trigger( 'focus' );
		}
	} );
	$doc.on( 'click', ROOT + ' .qsm-ui-menu button, ' + ROOT + ' .qsm-ui-menu a', function () {
		var $menu = $( this ).closest( '.qsm-ui-menu' ).prop( 'hidden', true );
		$menu.closest( '.qsm-ui-menu-wrap' ).find( '[data-qsm-ui-menu]' ).attr( 'aria-expanded', 'false' );
	} );
	$doc.on( 'click', function () {
		closeMenus();
	} );

	// Dialogs: open / close buttons, click on the backdrop closes.
	$doc.on( 'click', ROOT + ' [data-qsm-ui-open]', function ( e ) {
		e.preventDefault();
		openDialog( ref( $( this ).attr( 'data-qsm-ui-open' ) ) );
	} );
	$doc.on( 'click', ROOT + ' [data-qsm-ui-close]', function () {
		closeDialog( this );
	} );
	$doc.on( 'click', ROOT + ' dialog.qsm-ui-modal', function ( e ) {
		if ( e.target === this ) {
			this.close();
		}
	} );

	// Type-to-confirm (O-02): the button turns on when the text matches.
	$doc.on( 'input', ROOT + ' [data-qsm-ui-confirm-text]', function () {
		ref( $( this ).attr( 'data-qsm-ui-confirm-target' ) ).prop( 'disabled', $.trim( this.value ) !== $( this ).attr( 'data-qsm-ui-confirm-text' ) );
	} );

	// Template variable picker (F-24).
	$doc.on( 'click', ROOT + ' [data-qsm-ui-var-toggle]', function ( e ) {
		e.stopPropagation();
		var $panel = $( this ).closest( '.qsm-ui-var-picker' ).find( '.qsm-ui-var-panel' );
		var open = $panel.prop( 'hidden' );
		$( ROOT + ' .qsm-ui-var-panel' ).not( $panel ).prop( 'hidden', true );
		$panel.prop( 'hidden', ! open );
		$( this ).attr( 'aria-expanded', open ? 'true' : 'false' );
		if ( open ) {
			$panel.find( 'input[type="search"]' ).trigger( 'focus' );
		}
	} );
	$doc.on( 'click', ROOT + ' .qsm-ui-var-panel', function ( e ) {
		e.stopPropagation();
	} );
	$doc.on( 'input', ROOT + ' .qsm-ui-var-panel input[type="search"]', function () {
		var q = this.value.toLowerCase();
		$( this ).closest( '.qsm-ui-var-panel' ).find( '[data-qsm-ui-var]' ).each( function () {
			$( this ).parent().prop( 'hidden', !! q && $( this ).text().toLowerCase().indexOf( q ) < 0 );
		} );
	} );
	$doc.on( 'click', ROOT + ' [data-qsm-ui-var]', function () {
		var $picker = $( this ).closest( '.qsm-ui-var-picker' );
		var text = $( this ).attr( 'data-qsm-ui-var' );
		var target = $picker.attr( 'data-qsm-ui-var-target' );
		$picker.find( '.qsm-ui-var-panel' ).prop( 'hidden', true );
		$picker.find( '[data-qsm-ui-var-toggle]' ).attr( 'aria-expanded', 'false' );
		var event = $.Event( 'qsm-ui:insert-variable' );
		$picker.trigger( event, [ text ] );
		if ( ! event.isDefaultPrevented() && insertVariable( target, text ) ) {
			toast( text + ' ' + i18n.inserted.toLowerCase() );
		}
	} );
	$doc.on( 'click', function () {
		$( ROOT + ' .qsm-ui-var-panel' ).prop( 'hidden', true );
		$( ROOT + ' [data-qsm-ui-var-toggle]' ).attr( 'aria-expanded', 'false' );
	} );

	// Category tree (F-25): collapse, and a search that keeps the parents of matches.
	$doc.on( 'click', ROOT + ' .qsm-ui-tree-toggle', function () {
		var open = 'true' !== $( this ).attr( 'aria-expanded' );
		$( this ).attr( 'aria-expanded', open ? 'true' : 'false' );
		$( this ).closest( 'li' ).attr( 'aria-expanded', open ? 'true' : 'false' ).children( 'ul' ).prop( 'hidden', ! open );
	} );
	$doc.on( 'input', ROOT + ' .qsm-ui-tree-picker input[type="search"]', function () {
		filterTree( $( this ).closest( '.qsm-ui-tree-picker' ), this.value );
	} );

	/* ---------- Library helpers (X-04: only Core's own instances get the kit look) ---------- */
	function select2( $select, options ) {
		$select = $( $select );
		if ( ! $.fn.select2 ) {
			return $select;
		}
		var opts = $.extend( { width: '100%' }, options || {} );
		opts.selectionCssClass = $.trim( ( opts.selectionCssClass || '' ) + ' qsm-ui-select2' );
		opts.dropdownCssClass = $.trim( ( opts.dropdownCssClass || '' ) + ' qsm-ui-select2' );
		// Inside a modal <dialog> the dropdown must render in the dialog, or it opens behind the backdrop.
		if ( ! opts.dropdownParent && $select.closest( 'dialog' ).length ) {
			opts.dropdownParent = $select.closest( 'dialog' );
		}
		return $select.select2( opts );
	}

	function sortable( $list, options ) {
		$list = $( $list );
		if ( ! $.fn.sortable ) {
			return $list;
		}
		var opts = $.extend( { handle: '.qsm-ui-handle', tolerance: 'pointer' }, options || {} );
		opts.placeholder = $.trim( ( opts.placeholder || '' ) + ' qsm-ui-sort-placeholder' );
		return $list.sortable( opts );
	}

	window.QSMAdminUI = {
		version: settings.version || '',
		toast: toast,
		loading: loading,
		openDialog: openDialog,
		closeDialog: closeDialog,
		refreshRules: refreshRules,
		select2: select2,
		sortable: sortable,
		init: init
	};

	$( function () {
		init( document );
	} );
}( jQuery, window, document ) );
