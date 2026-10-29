<?php
/**
 * Add-on compatibility check for the admin UI kit (ClickUp CU-14ypaj0p56j).
 *
 * Run from the plugin root, no WordPress needed:
 *
 *     php tests/admin-ui-compat/check.php
 *
 * It fails (exit 1) when a change would let the kit restyle or rebind add-on UI:
 *
 * 1. CSS scope: every selector in css/qsm-admin-ui.css styles a kit element.
 *    The selector must name a `qsm-ui-*` class, or its subject must carry
 *    `.qsm-ui` (the token wrapper). `.qsm-ui table` or `.qsm-ui .qsm-row` would
 *    reach add-on markup printed inside a kit wrapper, so they fail. A plain
 *    Core/add-on class (qsm-*, select2-*, button-*, form-table) in the subject
 *    needs a kit class in the same compound, or an ancestor that is a library
 *    opt-in (qsm-ui-select2, qsm-ui-popup, qsm-ui-sort-*, qsm-ui-color,
 *    qsm-ui-editor around Core's own wp_editor), because
 *    add-on markup can be printed inside kit cards and rows.
 * 2. Existing Core CSS stays as it is: no kit class appears in
 *    qsm-admin.css, common.css, admin-dashboard.css or qsm-admin-question.css.
 * 3. Kit JS changes no library globally (Select2/Sortable/Datepicker defaults,
 *    widget prototypes, micromodal), binds delegated handlers only under ROOT,
 *    and never stops propagation (add-on document handlers must still fire).
 * 4. Hook contract: every Core PHP hook and JS event that the add-ons in
 *    qsm-addons listen to (addon-php-hooks.txt, addon-js-events.txt) is still
 *    fired by Core. Removing or renaming one breaks an add-on silently.
 *
 * Refresh the hook lists from the qsm-addons repo when add-ons change; see README.md.
 *
 * @package QSM
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

$root     = dirname( dirname( __DIR__ ) );
$failures = array();

/**
 * Reads a file relative to the plugin root.
 *
 * @param string $root Plugin root.
 * @param string $path Relative path.
 * @return string
 */
function qsm_compat_read( $root, $path ) {
	$file = $root . '/' . $path;
	if ( ! is_readable( $file ) ) {
		fwrite( STDERR, "Missing file: $path\n" );
		exit( 2 );
	}
	return file_get_contents( $file );
}

/**
 * Returns every selector in a stylesheet, with its line number.
 *
 * Comments and @keyframes bodies are skipped; @media / @supports blocks are read.
 *
 * @param string $css Stylesheet.
 * @return array List of array( selector, line ).
 */
function qsm_compat_selectors( $css ) {
	$css = preg_replace_callback(
		'#/\*.*?\*/#s',
		function ( $m ) {
			return str_repeat( "\n", substr_count( $m[0], "\n" ) );
		},
		$css
	);
	$out    = array();
	$len    = strlen( $css );
	$buf    = '';
	$depth  = 0;
	$skip   = -1; // Depth at which a @keyframes body started.
	$line   = 1;
	$start  = 1;
	$blocks = array();
	for ( $i = 0; $i < $len; $i++ ) {
		$c = $css[ $i ];
		if ( "\n" === $c ) {
			$line++;
		}
		if ( '{' === $c ) {
			$prelude = trim( $buf );
			$buf     = '';
			$depth++;
			if ( -1 !== $skip ) {
				continue;
			}
			if ( '' !== $prelude && '@' === $prelude[0] ) {
				if ( preg_match( '/^@(-[a-z]+-)?keyframes\b|^@font-face\b|^@page\b/i', $prelude ) ) {
					$skip = $depth;
				}
				continue;
			}
			foreach ( explode( ',', $prelude ) as $selector ) {
				$out[] = array( trim( preg_replace( '/\s+/', ' ', $selector ) ), $start );
			}
			$skip = $depth; // A declaration block: ignore until it closes.
			continue;
		}
		if ( '}' === $c ) {
			if ( $depth === $skip ) {
				$skip = -1;
			}
			$depth--;
			$buf = '';
			continue;
		}
		if ( ';' === $c && -1 === $skip ) {
			$buf = ''; // @import / @charset.
			continue;
		}
		if ( '' === trim( $buf ) && '' !== trim( $c ) ) {
			$start = $line;
		}
		$buf .= $c;
	}
	return $out;
}

/**
 * Splits a selector into compounds (combinators dropped).
 *
 * @param string $selector Selector.
 * @return array
 */
function qsm_compat_compounds( $selector ) {
	// Attributes only the kit prints count as a kit class; then blank all attributes (their values may hold spaces and > ~ +).
	$selector = preg_replace( '/\[(class[*^~|]?=["\']?qsm-ui-[^\]]*|data-(show-if|enable-if|qsm-ui-[\w-]*)[^\]]*)\]/', '.qsm-ui-attr', $selector );
	$selector = preg_replace( '/\[[^\]]*\]/', '[]', $selector );
	$selector = preg_replace( '/\s*[>+~]\s*/', ' ', $selector );
	return array_values( array_filter( explode( ' ', $selector ), 'strlen' ) );
}

/**
 * Class names in a compound, ignoring those inside :not() / :is() / :has().
 *
 * @param string $compound Compound selector.
 * @return array
 */
function qsm_compat_classes( $compound ) {
	$compound = preg_replace( '/:(not|is|where|has)\([^)]*\)/', '', $compound );
	preg_match_all( '/\.(-?[_a-zA-Z][\w-]*)/', $compound, $m );
	return $m[1];
}

/**
 * Whether a class belongs to the kit.
 *
 * @param string $class Class name.
 * @return bool
 */
function qsm_compat_is_kit( $class ) {
	return 'qsm-ui' === $class || 0 === strpos( $class, 'qsm-ui-' );
}

/**
 * Whether a class is one Core or add-on markup already uses.
 *
 * @param string $class Class name.
 * @return bool
 */
function qsm_compat_is_shared( $class ) {
	return ! qsm_compat_is_kit( $class ) && (bool) preg_match( '/^(qsm|qmn|mlw|select2|button|form-table|wp-|ui-|micromodal|sortable|quiz_)/', $class );
}

// 1. CSS scope.
$kit_css = 'css/qsm-admin-ui.css';
foreach ( qsm_compat_selectors( qsm_compat_read( $root, $kit_css ) ) as $item ) {
	list( $selector, $line ) = $item;
	if ( '' === $selector ) {
		continue;
	}
	$compounds = qsm_compat_compounds( $selector );
	$subject   = qsm_compat_classes( end( $compounds ) );
	$prefixed  = false; // A qsm-ui-* class anywhere.
	$opted_in  = false; // A library opt-in class (qsm-ui-select2, qsm-ui-popup, ...) on an ancestor compound.
	foreach ( $compounds as $index => $compound ) {
		foreach ( qsm_compat_classes( $compound ) as $class ) {
			if ( 0 === strpos( $class, 'qsm-ui-' ) ) {
				$prefixed = true;
				if ( $index < count( $compounds ) - 1 && preg_match( '/^qsm-ui-(select2|popup|sort-|color|editor|toasts)/', $class ) ) {
					$opted_in = true;
				}
			}
		}
	}
	$subject_kit    = (bool) array_filter( $subject, 'qsm_compat_is_kit' );
	$subject_shared = array_filter( $subject, 'qsm_compat_is_shared' );
	if ( ! $prefixed && ! $subject_kit ) {
		$failures[] = "$kit_css:$line `$selector` is not scoped to a kit element (needs a qsm-ui-* class, or .qsm-ui on the subject).";
	} elseif ( ! $prefixed && 1 < count( $compounds ) ) {
		$failures[] = "$kit_css:$line `$selector` styles everything of that kind inside a .qsm-ui wrapper, add-on markup included. Target a qsm-ui-* class.";
	} elseif ( $subject_shared && ! $subject_kit && ! $opted_in ) {
		$failures[] = "$kit_css:$line `$selector` styles a class Core/add-ons already use (" . implode( ', ', $subject_shared ) . '). Add a qsm-ui-* class to that compound (add-on markup can sit inside kit cards and rows).';
	}
}

// 2. Existing Core CSS untouched by the kit.
foreach ( array( 'css/qsm-admin.css', 'css/common.css', 'css/admin-dashboard.css', 'css/qsm-admin-question.css' ) as $legacy_css ) {
	if ( preg_match( '/\.qsm-ui(?![\w-])|\.qsm-ui-/', qsm_compat_read( $root, $legacy_css ), $m, PREG_OFFSET_CAPTURE ) ) {
		$failures[] = "$legacy_css: contains `{$m[0][0]}`. Kit styles live only in $kit_css, so existing Core CSS keeps loading unchanged.";
	}
}

// 3. Kit JS.
$kit_js = 'js/qsm-admin-ui.js';
$js     = qsm_compat_read( $root, $kit_js );
$global = array(
	'/\.select2\.defaults/'                 => 'changes Select2 defaults for every instance',
	'/\$\.(ui\.[a-z]+|fn\.[a-z0-9]+)\.prototype/i' => 'patches a library prototype',
	'/\$\.widget\s*\(/'                      => 'redefines a jQuery UI widget',
	'/datepicker\.setDefaults/'              => 'changes Datepicker defaults for every instance',
	'/MicroModal\.(init|show)/'              => 'drives micromodal, which add-ons own (.qsm-popup stays as it is)',
	'/wpColorPicker\s*\(/'                   => 'starts colour pickers itself; Core opts in per instance',
	'/\.stop(Immediate)?Propagation\(/'        => 'stops the click before Core and add-on document handlers ("click outside" closers) see it',
);
foreach ( $global as $pattern => $why ) {
	if ( preg_match( $pattern, $js, $m, PREG_OFFSET_CAPTURE ) ) {
		$failures[] = "$kit_js:" . ( substr_count( substr( $js, 0, $m[0][1] ), "\n" ) + 1 ) . " `{$m[0][0]}` $why.";
	}
}
preg_match_all( '/\$doc\.on\(\s*([\'"][^\'"]+[\'"])\s*,\s*([^,\n]+)/', $js, $handlers, PREG_OFFSET_CAPTURE | PREG_SET_ORDER );
foreach ( $handlers as $h ) {
	$target = trim( $h[2][0] );
	$at     = substr_count( substr( $js, 0, $h[0][1] ), "\n" ) + 1;
	if ( 0 === strpos( $target, 'ROOT' ) ) {
		continue;
	}
	if ( 0 === strpos( $target, 'function' ) ) {
		// An unfiltered document handler may only reset kit state: its body must use ROOT and no other selector.
		$body = substr( $js, $h[0][1], 400 );
		$body = substr( $body, 0, strpos( $body, "\n\t} );" ) ? strpos( $body, "\n\t} );" ) : 400 );
		$uses = preg_match_all( '/\$\(\s*([^)]+)\)/', $body, $calls );
		$ok   = true;
		foreach ( $calls[1] as $arg ) {
			$arg = trim( $arg );
			if ( 'this' !== $arg && 'e.target' !== $arg && 0 !== strpos( $arg, 'ROOT' ) ) {
				$ok = false;
			}
		}
		if ( $ok && false === strpos( $body, 'preventDefault' ) && false === strpos( $body, 'stopPropagation' ) ) {
			continue;
		}
	}
	$failures[] = "$kit_js:$at delegated {$h[1][0]} handler is not limited to ROOT (.qsm-ui).";
}

// 4. Hook contract.
$core = '';
$iter = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $iter as $file ) {
	$path = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
	if ( preg_match( '#^(vendor|node_modules|tests|\.git)/#', $path ) || ! preg_match( '/\.(php|js)$/', $path ) || preg_match( '/\.min\.js$/', $path ) ) {
		continue;
	}
	$core .= file_get_contents( $file->getPathname() ) . "\n";
}
$contracts = array(
	'addon-php-hooks.txt' => '/(?:do_action|do_action_ref_array|apply_filters|apply_filters_ref_array)\(\s*[\'"]%s[\'"]/',
	'addon-js-events.txt' => '/(?:trigger|triggerHandler|doAction|applyFilters)\(\s*[\'"]%s[\'"]/',
);
foreach ( $contracts as $list => $pattern ) {
	foreach ( file( __DIR__ . '/' . $list, FILE_IGNORE_NEW_LINES ) as $hook ) {
		$hook = trim( $hook );
		if ( '' === $hook || '#' === $hook[0] ) {
			continue;
		}
		if ( ! preg_match( sprintf( $pattern, preg_quote( $hook, '/' ) ), $core ) ) {
			$failures[] = "$list: Core no longer fires `$hook`, which an add-on listens to.";
		}
	}
}

if ( $failures ) {
	fwrite( STDERR, 'Add-on compatibility check failed (' . count( $failures ) . "):\n - " . implode( "\n - ", $failures ) . "\n" );
	exit( 1 );
}
echo "Add-on compatibility check passed.\n";
exit( 0 );
