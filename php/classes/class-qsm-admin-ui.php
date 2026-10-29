<?php
/**
 * QSM Admin UI kit helpers.
 *
 * Loads the shared admin UI stylesheet, script and icon set, and gives screens
 * small helpers so they call functions instead of copying kit markup.
 *
 * Every helper RETURNS escaped HTML. Arguments documented as "HTML" (control,
 * body, footer, actions, ...) are inserted as given, so pass markup you built
 * with these helpers or escaped yourself. Echo the result, e.g.
 * `echo QSM_Admin_UI::notice( ... ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.`
 *
 * Nothing is styled until markup sits inside a `.qsm-ui` wrapper, so screens
 * opt in one block at a time and add-on output keeps its current look.
 *
 * The full reference, with every component, is the hidden "QSM UI Kit" admin
 * page: wp-admin/admin.php?page=qsm_ui_kit
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shared helpers for the QSM admin UI kit.
 */
class QSM_Admin_UI {

	/**
	 * Whether the icon sprite has been queued for the footer.
	 *
	 * @var bool
	 */
	private static $sprite_queued = false;

	/**
	 * Enqueues the kit stylesheet and script, and queues the icon sprite.
	 *
	 * Screens call this from their enqueue branch.
	 *
	 * @return void
	 */
	public static function enqueue() {
		global $mlwQuizMasterNext;
		// QSM_ADMIN_UI_VERSION tracks the kit's API (add-ons can check it); the plugin version busts the cache on every release.
		$version = isset( $mlwQuizMasterNext->version ) ? $mlwQuizMasterNext->version : QSM_ADMIN_UI_VERSION;
		wp_enqueue_style( 'qsm_admin_ui_css', QSM_PLUGIN_CSS_URL . '/qsm-admin-ui.css', array(), $version );
		wp_enqueue_script( 'qsm_admin_ui_js', QSM_PLUGIN_JS_URL . '/qsm-admin-ui.js', array( 'jquery' ), $version, true );
		wp_localize_script(
			'qsm_admin_ui_js',
			'qsmAdminUI',
			array(
				'version' => QSM_ADMIN_UI_VERSION,
				'i18n'    => array(
					'copied'   => __( 'Copied to clipboard', 'quiz-master-next' ),
					'copyFail' => __( 'Could not copy. Select the text and copy it manually.', 'quiz-master-next' ),
					'show'     => __( 'Show', 'quiz-master-next' ),
					'hide'     => __( 'Hide', 'quiz-master-next' ),
					'undo'     => __( 'Undo', 'quiz-master-next' ),
					'inserted' => __( 'Inserted', 'quiz-master-next' ),
				),
			)
		);
		if ( ! self::$sprite_queued ) {
			self::$sprite_queued = true;
			add_action( 'admin_footer', array( __CLASS__, 'print_icon_sprite' ) );
		}
	}

	/**
	 * Prints the SVG symbol sprite used by QSM_Admin_UI::icon().
	 *
	 * @return void
	 */
	public static function print_icon_sprite() {
		$file = QSM_PLUGIN_PATH . 'assets/qsm-admin-ui-icons.svg';
		if ( is_readable( $file ) ) {
			echo file_get_contents( $file ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- static SVG shipped with the plugin.
		}
	}

	/**
	 * Returns the icon names in the sprite.
	 *
	 * @return array
	 */
	public static function icon_names() {
		$file = QSM_PLUGIN_PATH . 'assets/qsm-admin-ui-icons.svg';
		if ( ! is_readable( $file ) ) {
			return array();
		}
		preg_match_all( '/id="qsm-ui-i-([a-z0-9-]+)"/', file_get_contents( $file ), $matches ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- static SVG shipped with the plugin.
		return $matches[1];
	}

	/**
	 * Returns the markup for one kit icon (T-04).
	 *
	 * @param string $name  Icon name without prefix, e.g. 'grip', 'edit', 'trash'.
	 * @param string $class Extra classes, e.g. 'qsm-ui-icon-sm'.
	 * @return string
	 */
	public static function icon( $name, $class = '' ) {
		return sprintf(
			'<svg class="qsm-ui-icon %1$s" aria-hidden="true" focusable="false"><use href="#qsm-ui-i-%2$s"></use></svg>',
			esc_attr( $class ),
			esc_attr( $name )
		);
	}

	/**
	 * Builds an HTML attribute string.
	 *
	 * true prints the bare attribute, false / null / '' skip it. Keys must be
	 * literal attribute names written by the developer, never user input.
	 *
	 * @param array $attrs Attribute => value.
	 * @return string Leading space included when not empty.
	 */
	public static function attrs( $attrs ) {
		$out = '';
		foreach ( (array) $attrs as $key => $value ) {
			if ( false === $value || null === $value || '' === $value ) {
				continue;
			}
			if ( true === $value ) {
				$out .= ' ' . esc_attr( $key );
				continue;
			}
			$out .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
		}
		return $out;
	}

	/**
	 * Joins class names, dropping empty ones.
	 *
	 * @return string
	 */
	private static function classes() {
		return trim( implode( ' ', array_filter( func_get_args() ) ) );
	}

	/**
	 * Merges defaults into args.
	 *
	 * @param array $args     Caller args.
	 * @param array $defaults Defaults.
	 * @return array
	 */
	private static function args( $args, $defaults ) {
		return array_merge( $defaults, (array) $args );
	}

	/**
	 * Button (B-01, B-02).
	 *
	 * @param string $label Visible label, or the aria-label for an icon-only button.
	 * @param array  $args {
	 *     @type string $variant   '', 'primary', 'quiet', 'danger', 'danger-ghost', 'link'.
	 *     @type string $size      '', 'sm', 'lg'.
	 *     @type string $icon      Icon name shown before the label.
	 *     @type bool   $icon_only Render only the icon; $label becomes aria-label.
	 *     @type string $href      Render an <a> instead of a <button>.
	 *     @type string $type      Button type. Default 'button'.
	 *     @type string $class     Extra classes.
	 *     @type array  $attrs     Extra attributes (id, name, value, data-*, disabled, ...).
	 * }
	 * @return string
	 */
	public static function button( $label, $args = array() ) {
		$args    = self::args(
			$args,
			array(
				'variant'   => '',
				'size'      => '',
				'icon'      => '',
				'icon_only' => false,
				'href'      => '',
				'type'      => 'button',
				'class'     => '',
				'attrs'     => array(),
			)
		);
		$classes = self::classes(
			'qsm-ui-btn',
			$args['variant'] ? 'qsm-ui-btn-' . $args['variant'] : '',
			$args['size'] ? 'qsm-ui-btn-' . $args['size'] : '',
			$args['icon_only'] ? 'qsm-ui-btn-icon' : '',
			$args['class']
		);
		$icon    = $args['icon'] ? self::icon( $args['icon'], $args['icon_only'] || 'sm' !== $args['size'] ? '' : 'qsm-ui-icon-sm' ) : '';
		$inner   = $args['icon_only'] ? $icon : $icon . esc_html( $label );
		$attrs   = array_merge( array( 'class' => $classes ), $args['icon_only'] ? array( 'aria-label' => $label ) : array(), (array) $args['attrs'] );
		if ( $args['href'] ) {
			$attrs = array_merge( array( 'href' => esc_url( $args['href'] ) ), $attrs );
			return '<a' . self::attrs( $attrs ) . '>' . $inner . '</a>';
		}
		$attrs = array_merge( array( 'type' => $args['type'] ), $attrs );
		return '<button' . self::attrs( $attrs ) . '>' . $inner . '</button>';
	}

	/**
	 * Opens a card (L-05). Close it with QSM_Admin_UI::card_close().
	 *
	 * @param array $args {
	 *     @type string $title       Card title.
	 *     @type string $description Text under the title (basic HTML allowed).
	 *     @type string $actions     HTML for the right side of the header (a switch, a button).
	 *     @type string $id          Card id.
	 *     @type string $body_id     Body id, e.g. for a header switch that greys out the body (data-qsm-ui-controls).
	 *     @type string $class       Extra classes.
	 * }
	 * @return string
	 */
	public static function card_open( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'title'       => '',
				'description' => '',
				'actions'     => '',
				'id'          => '',
				'body_id'     => '',
				'class'       => '',
			)
		);
		$html = '<div' . self::attrs(
			array(
				'class' => self::classes( 'qsm-ui-card', $args['class'] ),
				'id'    => $args['id'],
			)
		) . '>';
		if ( $args['title'] || $args['actions'] ) {
			$html .= '<div class="qsm-ui-card-head"><div>';
			$html .= $args['title'] ? '<h3 class="qsm-ui-h3">' . esc_html( $args['title'] ) . '</h3>' : '';
			$html .= $args['description'] ? '<p>' . wp_kses_post( $args['description'] ) . '</p>' : '';
			$html .= '</div>' . $args['actions'] . '</div>';
		}
		$html .= '<div' . self::attrs(
			array(
				'class' => 'qsm-ui-card-body',
				'id'    => $args['body_id'],
			)
		) . '>';
		return $html;
	}

	/**
	 * Closes a card opened with QSM_Admin_UI::card_open().
	 *
	 * @param string $footer Optional footer HTML (buttons).
	 * @return string
	 */
	public static function card_close( $footer = '' ) {
		return '</div>' . ( $footer ? '<div class="qsm-ui-card-foot">' . $footer . '</div>' : '' ) . '</div>';
	}

	/**
	 * Whole card in one call (L-05).
	 *
	 * @param array $args card_open() args plus `body` (HTML) and `footer` (HTML).
	 * @return string
	 */
	public static function card( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'body'   => '',
				'footer' => '',
			)
		);
		return self::card_open( $args ) . $args['body'] . self::card_close( $args['footer'] );
	}

	/**
	 * Builds the data-show-if / data-enable-if attributes for a row.
	 *
	 * Rule syntax: "field:v1,v2" matches when the field named `field` has one of
	 * the values; "field:!0" when it has anything but 0; "a:0;b:!1" needs every
	 * clause. A checkbox reads as 1 / 0, an empty field as 0.
	 *
	 * @param array $args Uses `show_if` and `enable_if`.
	 * @return array
	 */
	private static function rule_attrs( $args ) {
		return array(
			'data-show-if'   => isset( $args['show_if'] ) ? $args['show_if'] : '',
			'data-enable-if' => isset( $args['enable_if'] ) ? $args['enable_if'] : '',
		);
	}

	/**
	 * Setting row: label column + control column (L-06).
	 *
	 * @param array $args {
	 *     @type string $label     Label text.
	 *     @type string $for       Id of the control the label points at. Without it the label is plain text.
	 *     @type string $help      Help text under the label (basic HTML allowed).
	 *     @type string $control   Control HTML.
	 *     @type bool   $top       Align the label with the top of the control (lists, radios). Default false.
	 *     @type bool   $required  Show the required marker.
	 *     @type string $doc       Documentation URL; shows the help icon next to the label.
	 *     @type string $show_if   Hide rule, see rule_attrs(). A hidden row stays in the form, so don't put a `required` field in it (the browser would block submit).
	 *     @type string $enable_if Grey-out rule, see rule_attrs().
	 *     @type string $class     Extra classes.
	 *     @type array  $attrs     Extra attributes.
	 * }
	 * @return string
	 */
	public static function field_row( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'label'     => '',
				'for'       => '',
				'help'      => '',
				'control'   => '',
				'top'       => false,
				'required'  => false,
				'doc'       => '',
				'show_if'   => '',
				'enable_if' => '',
				'class'     => '',
				'attrs'     => array(),
			)
		);
		$label = esc_html( $args['label'] );
		if ( $args['for'] ) {
			$label = '<label for="' . esc_attr( $args['for'] ) . '">' . $label . '</label>';
		}
		$label .= $args['required'] ? '<span class="qsm-ui-req" aria-hidden="true">*</span>' : '';
		$label .= $args['doc'] ? self::doc_link( $args['doc'] ) : '';
		$label .= $args['help'] ? '<span class="qsm-ui-help">' . wp_kses_post( $args['help'] ) . '</span>' : '';
		$attrs  = array_merge( array( 'class' => self::classes( 'qsm-ui-setting-row', $args['class'] ) ), self::rule_attrs( $args ), (array) $args['attrs'] );
		return '<div' . self::attrs( $attrs ) . '><div class="' . esc_attr( self::classes( 'qsm-ui-setting-label', $args['top'] ? 'is-top' : '' ) ) . '">' . $label . '</div><div>' . $args['control'] . '</div></div>';
	}

	/**
	 * Stacked field: label above the control, help and message below (F-01, F-02).
	 *
	 * @param array $args {
	 *     @type string $label    Label text.
	 *     @type string $for      Id of the control.
	 *     @type string $control  Control HTML.
	 *     @type string $help     Help text (basic HTML allowed).
	 *     @type string $error    Error message; marks the field invalid. Link it with aria-describedby="{for}-msg".
	 *     @type string $success  Success message.
	 *     @type bool   $required Show the required marker.
	 *     @type string $tip      Tooltip text next to the label (O-05).
	 *     @type string $doc      Documentation URL.
	 *     @type string $counter  Character counter max for a textarea (F-06); needs `for`.
	 *     @type string $show_if  / enable_if Rules, see rule_attrs().
	 *     @type string $class    Extra classes.
	 * }
	 * @return string
	 */
	public static function field( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'label'     => '',
				'for'       => '',
				'control'   => '',
				'help'      => '',
				'error'     => '',
				'success'   => '',
				'required'  => false,
				'tip'       => '',
				'doc'       => '',
				'counter'   => '',
				'show_if'   => '',
				'enable_if' => '',
				'class'     => '',
			)
		);
		$label = '';
		if ( $args['label'] ) {
			$label  = '<' . ( $args['for'] ? 'label for="' . esc_attr( $args['for'] ) . '"' : 'span' ) . ' class="qsm-ui-field-label">' . esc_html( $args['label'] );
			$label .= $args['required'] ? ' <span class="qsm-ui-req" aria-hidden="true">*</span>' : '';
			$label .= $args['tip'] ? ' ' . self::tip( $args['tip'] ) : '';
			$label .= $args['doc'] ? self::doc_link( $args['doc'] ) : '';
			$label .= '</' . ( $args['for'] ? 'label' : 'span' ) . '>';
		}
		if ( $args['counter'] && $args['for'] ) {
			$label = '<div class="qsm-ui-field-head">' . $label . '<span class="qsm-ui-counter" data-qsm-ui-counter-for="' . esc_attr( $args['for'] ) . '" data-max="' . esc_attr( $args['counter'] ) . '"></span></div>';
		}
		$html = $label . $args['control'];
		if ( $args['error'] ) {
			$html .= '<span class="qsm-ui-field-msg is-error"' . ( $args['for'] ? ' id="' . esc_attr( $args['for'] . '-msg' ) . '"' : '' ) . '>' . self::icon( 'alert', 'qsm-ui-icon-sm' ) . esc_html( $args['error'] ) . '</span>';
		} elseif ( $args['success'] ) {
			$html .= '<span class="qsm-ui-field-msg is-success"' . ( $args['for'] ? ' id="' . esc_attr( $args['for'] . '-msg' ) . '"' : '' ) . '>' . self::icon( 'ok', 'qsm-ui-icon-sm' ) . esc_html( $args['success'] ) . '</span>';
		}
		$html .= $args['help'] ? '<span class="qsm-ui-help">' . wp_kses_post( $args['help'] ) . '</span>' : '';
		$attrs = array_merge( array( 'class' => self::classes( 'qsm-ui-field', $args['error'] ? 'is-invalid' : '', $args['class'] ) ), self::rule_attrs( $args ) );
		return '<div' . self::attrs( $attrs ) . '>' . $html . '</div>';
	}

	/**
	 * Text-like input (F-01, F-02, F-07, F-20).
	 *
	 * @param array $args {
	 *     @type string $type        Input type. Default 'text'.
	 *     @type string $name        Field name.
	 *     @type string $id          Field id.
	 *     @type string $value       Value.
	 *     @type string $placeholder Placeholder.
	 *     @type string $width       '', 'sm', 'md', 'lg'.
	 *     @type string $size        '' or 'sm'.
	 *     @type string $icon        Leading icon (search, link, cal, ...).
	 *     @type bool   $invalid     Error state (adds aria-invalid).
	 *     @type string $class       Extra classes.
	 *     @type array  $attrs       Extra attributes (readonly, disabled, min, max, aria-*...).
	 * }
	 * @return string
	 */
	public static function input( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'type'        => 'text',
				'name'        => '',
				'id'          => '',
				'value'       => '',
				'placeholder' => '',
				'width'       => '',
				'size'        => '',
				'icon'        => '',
				'invalid'     => false,
				'class'       => '',
				'attrs'       => array(),
			)
		);
		$attrs = array_merge(
			array(
				'class'        => self::classes( 'qsm-ui-input', $args['size'] ? 'qsm-ui-input-' . $args['size'] : '', $args['icon'] ? '' : ( $args['width'] ? 'qsm-ui-w-' . $args['width'] : '' ), $args['invalid'] ? 'is-invalid' : '', $args['class'] ),
				'type'         => $args['type'],
				'name'         => $args['name'],
				'id'           => $args['id'],
				'value'        => (string) $args['value'],
				'placeholder'  => $args['placeholder'],
				'aria-invalid' => $args['invalid'] ? 'true' : '',
			),
			(array) $args['attrs']
		);
		$html  = '<input' . self::attrs( $attrs ) . '>';
		if ( $args['icon'] ) {
			$html = '<div class="' . esc_attr( self::classes( 'qsm-ui-input-icon', $args['width'] ? 'qsm-ui-w-' . $args['width'] : '' ) ) . '">' . self::icon( $args['icon'] ) . $html . '</div>';
		}
		return $html;
	}

	/**
	 * Native select (F-16). For a searchable select (F-17) start Select2 on it
	 * with QSMAdminUI.select2( $el, options ) in JS.
	 *
	 * @param array $args {
	 *     @type string       $name     Field name.
	 *     @type string       $id       Field id.
	 *     @type array        $options  value => label, or group label => array( value => label ).
	 *     @type string|array $selected Selected value(s).
	 *     @type string       $width    '', 'sm', 'md', 'lg'.
	 *     @type string       $size     '' or 'sm'.
	 *     @type string       $class    Extra classes.
	 *     @type array        $attrs    Extra attributes (multiple, disabled, ...).
	 * }
	 * @return string
	 */
	public static function select( $args = array() ) {
		$args     = self::args(
			$args,
			array(
				'name'     => '',
				'id'       => '',
				'options'  => array(),
				'selected' => '',
				'width'    => '',
				'size'     => '',
				'class'    => '',
				'attrs'    => array(),
			)
		);
		$selected = array_map( 'strval', (array) $args['selected'] );
		$render   = function ( $options ) use ( $selected ) {
			$html = '';
			foreach ( $options as $value => $label ) {
				$html .= '<option value="' . esc_attr( $value ) . '"' . ( in_array( (string) $value, $selected, true ) ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
			}
			return $html;
		};
		$inner    = '';
		foreach ( (array) $args['options'] as $value => $label ) {
			if ( is_array( $label ) ) {
				$inner .= '<optgroup label="' . esc_attr( $value ) . '">' . $render( $label ) . '</optgroup>';
			} else {
				$inner .= $render( array( $value => $label ) );
			}
		}
		$attrs = array_merge(
			array(
				'class' => self::classes( 'qsm-ui-select', $args['size'] ? 'qsm-ui-input-' . $args['size'] : '', $args['width'] ? 'qsm-ui-w-' . $args['width'] : '', $args['class'] ),
				'name'  => $args['name'],
				'id'    => $args['id'],
			),
			(array) $args['attrs']
		);
		return '<select' . self::attrs( $attrs ) . '>' . $inner . '</select>';
	}

	/**
	 * Textarea (F-06, F-09). Add a counter with field( array( 'counter' => 160 ) ).
	 *
	 * @param array $args name, id, value, placeholder, rows, code (bool, F-09 look), class, attrs.
	 * @return string
	 */
	public static function textarea( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'name'        => '',
				'id'          => '',
				'value'       => '',
				'placeholder' => '',
				'rows'        => '',
				'code'        => false,
				'class'       => '',
				'attrs'       => array(),
			)
		);
		$attrs = array_merge(
			array(
				'class'       => self::classes( 'qsm-ui-textarea', $args['code'] ? 'is-code' : '', $args['class'] ),
				'name'        => $args['name'],
				'id'          => $args['id'],
				'rows'        => $args['rows'],
				'placeholder' => $args['placeholder'],
				'spellcheck'  => $args['code'] ? 'false' : '',
			),
			(array) $args['attrs']
		);
		return '<textarea' . self::attrs( $attrs ) . '>' . esc_textarea( $args['value'] ) . '</textarea>';
	}

	/**
	 * Toggle switch with title and description (F-10). Named toggle() because
	 * `switch` is a reserved word in the PHP versions QSM supports.
	 *
	 * @param array $args {
	 *     @type string $name            Field name.
	 *     @type string $id              Field id.
	 *     @type string $value           Value sent when on. Default '1'.
	 *     @type string $unchecked_value When set, a hidden input sends this when off (keeps 0/1 settings saving as today).
	 *     @type bool   $checked         On.
	 *     @type string $label           Title.
	 *     @type string $description     Line under the title.
	 *     @type string $rule_note       Reason shown when the row is greyed out by data-enable-if.
	 *     @type bool   $small           Small switch (tables).
	 *     @type bool   $disabled        Disabled.
	 *     @type string $aria_label      Accessible name when there is no visible label (e.g. a card header switch).
	 *     @type array  $attrs           Extra attributes for the checkbox.
	 * }
	 * @return string
	 */
	public static function toggle( $args = array() ) {
		$args   = self::args(
			$args,
			array(
				'name'            => '',
				'id'              => '',
				'value'           => '1',
				'unchecked_value' => null,
				'checked'         => false,
				'label'           => '',
				'description'     => '',
				'rule_note'       => '',
				'small'           => false,
				'disabled'        => false,
				'aria_label'      => '',
				'attrs'           => array(),
			)
		);
		$hidden = ( null !== $args['unchecked_value'] && $args['name'] ) ? '<input type="hidden"' . self::attrs(
			array(
				'name'  => $args['name'],
				'value' => (string) $args['unchecked_value'],
			)
		) . '>' : '';
		$input  = '<input' . self::attrs(
			array_merge(
				array(
					'class'      => self::classes( 'qsm-ui-switch', $args['small'] ? 'qsm-ui-switch-sm' : '' ),
					'type'       => 'checkbox',
					'name'       => $args['name'],
					'id'         => $args['id'],
					'value'      => (string) $args['value'],
					'checked'    => (bool) $args['checked'],
					'disabled'   => (bool) $args['disabled'],
					'aria-label' => $args['label'] ? '' : $args['aria_label'],
				),
				(array) $args['attrs']
			)
		) . '>';
		$copy   = '';
		if ( $args['label'] || $args['description'] || $args['rule_note'] ) {
			$copy  = '<span class="qsm-ui-switch-copy">';
			$copy .= $args['label'] ? '<strong>' . esc_html( $args['label'] ) . '</strong>' : '';
			$copy .= $args['description'] ? '<span>' . wp_kses_post( $args['description'] ) . '</span>' : '';
			$copy .= $args['rule_note'] ? '<span class="qsm-ui-rule-note">' . esc_html( $args['rule_note'] ) . '</span>' : '';
			$copy .= '</span>';
		}
		return $hidden . '<label class="qsm-ui-switch-line">' . $input . $copy . '</label>';
	}

	/**
	 * Checkbox or radio group (F-11, F-12).
	 *
	 * @param array $args {
	 *     @type string       $type     'checkbox' or 'radio'.
	 *     @type string       $name     Field name (add [] yourself for a checkbox array).
	 *     @type array        $options  value => label, or value => array( 'label' => '', 'description' => '', 'disabled' => bool ).
	 *     @type string|array $selected Checked value(s).
	 *     @type bool         $inline   Lay the choices out in a row.
	 *     @type string       $id       Id prefix; each choice gets {id}-{n}.
	 *     @type array        $attrs    Extra attributes for every input.
	 * }
	 * @return string
	 */
	public static function choices( $args = array() ) {
		$args     = self::args(
			$args,
			array(
				'type'     => 'checkbox',
				'name'     => '',
				'options'  => array(),
				'selected' => array(),
				'inline'   => false,
				'id'       => '',
				'attrs'    => array(),
			)
		);
		$selected = array_map( 'strval', (array) $args['selected'] );
		$html     = '';
		$n        = 0;
		foreach ( (array) $args['options'] as $value => $option ) {
			$option = is_array( $option ) ? $option : array( 'label' => $option );
			$option = self::args(
				$option,
				array(
					'label'       => '',
					'description' => '',
					'disabled'    => false,
				)
			);
			$n++;
			$input = '<input' . self::attrs(
				array_merge(
					array(
						'type'     => 'radio' === $args['type'] ? 'radio' : 'checkbox',
						'name'     => $args['name'],
						'id'       => $args['id'] ? $args['id'] . '-' . $n : '',
						'value'    => (string) $value,
						'checked'  => in_array( (string) $value, $selected, true ),
						'disabled' => (bool) $option['disabled'],
					),
					(array) $args['attrs']
				)
			) . '>';
			$html .= '<label class="qsm-ui-check">' . $input . '<span>' . esc_html( $option['label'] ) . ( $option['description'] ? '<small>' . esc_html( $option['description'] ) . '</small>' : '' ) . '</span></label>';
		}
		return $args['inline'] ? '<div class="qsm-ui-inline-choices">' . $html . '</div>' : '<div class="qsm-ui-choices">' . $html . '</div>';
	}

	/**
	 * Radio cards (F-13).
	 *
	 * @param array $args name, selected, options (value => array( 'label', 'description', 'icon' )), attrs.
	 * @return string
	 */
	public static function radio_cards( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'name'     => '',
				'options'  => array(),
				'selected' => '',
				'attrs'    => array(),
			)
		);
		$html = '';
		foreach ( (array) $args['options'] as $value => $option ) {
			$option = self::args(
				is_array( $option ) ? $option : array( 'label' => $option ),
				array(
					'label'       => '',
					'description' => '',
					'icon'        => '',
				)
			);
			$input  = '<input' . self::attrs(
				array_merge(
					array(
						'type'    => 'radio',
						'name'    => $args['name'],
						'value'   => (string) $value,
						'checked' => (string) $value === (string) $args['selected'],
					),
					(array) $args['attrs']
				)
			) . '>';
			$copy   = '<span' . ( $option['icon'] ? ' style="flex:1"' : '' ) . '><strong>' . esc_html( $option['label'] ) . '</strong>' . ( $option['description'] ? '<small>' . esc_html( $option['description'] ) . '</small>' : '' ) . '</span>';
			// With an icon the radio sits on the right, as in the kit.
			$html .= '<label class="qsm-ui-radio-card">' . ( $option['icon'] ? '<span class="qsm-ui-rc-icon">' . self::icon( $option['icon'] ) . '</span>' . $copy . $input : $input . $copy ) . '</label>';
		}
		return '<div class="qsm-ui-radio-cards">' . $html . '</div>';
	}

	/**
	 * Input with prefix / suffix / attached button (F-03).
	 *
	 * @param array $args control (HTML), prefix (text), prefix_icon, suffix (text), button (HTML), width.
	 * @return string
	 */
	public static function input_group( $args = array() ) {
		$args   = self::args(
			$args,
			array(
				'control'     => '',
				'prefix'      => '',
				'prefix_icon' => '',
				'suffix'      => '',
				'button'      => '',
				'width'       => '',
			)
		);
		$prefix = '';
		if ( $args['prefix'] || $args['prefix_icon'] ) {
			$prefix = '<span class="qsm-ui-addon">' . ( $args['prefix_icon'] ? self::icon( $args['prefix_icon'], 'qsm-ui-icon-sm' ) : '' ) . esc_html( $args['prefix'] ) . '</span>';
		}
		$suffix = $args['suffix'] ? '<span class="qsm-ui-addon">' . esc_html( $args['suffix'] ) . '</span>' : '';
		return '<div class="' . esc_attr( self::classes( 'qsm-ui-input-group', $args['width'] ? 'qsm-ui-w-' . $args['width'] : '' ) ) . '">' . $prefix . $args['control'] . $suffix . $args['button'] . '</div>';
	}

	/**
	 * Number stepper (F-04). Same input, name and saved value as a plain
	 * number field; the buttons only change the value and fire `change`.
	 *
	 * @param array $args name, id, value, min, max, step, label (aria-label), attrs.
	 * @return string
	 */
	public static function stepper( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'name'  => '',
				'id'    => '',
				'value' => 0,
				'min'   => '',
				'max'   => '',
				'step'  => '',
				'label' => '',
				'attrs' => array(),
			)
		);
		$input = '<input' . self::attrs(
			array_merge(
				array(
					'type'       => 'number',
					'name'       => $args['name'],
					'id'         => $args['id'],
					'value'      => (string) $args['value'],
					'min'        => (string) $args['min'],
					'max'        => (string) $args['max'],
					'step'       => (string) $args['step'],
					'aria-label' => $args['label'],
				),
				(array) $args['attrs']
			)
		) . '>';
		return '<div class="qsm-ui-stepper" data-qsm-ui-stepper><button type="button" data-step="-1" aria-label="' . esc_attr__( 'Decrease', 'quiz-master-next' ) . '">&minus;</button>' . $input . '<button type="button" data-step="1" aria-label="' . esc_attr__( 'Increase', 'quiz-master-next' ) . '">+</button></div>';
	}

	/**
	 * Password / secret field with a show button (F-05).
	 *
	 * @param array $args name, id, value, placeholder, width (default 'lg'), attrs.
	 * @return string
	 */
	public static function secret( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'name'        => '',
				'id'          => '',
				'value'       => '',
				'placeholder' => '',
				'width'       => 'lg',
				'attrs'       => array(),
			)
		);
		$id   = $args['id'] ? $args['id'] : 'qsm-ui-secret-' . wp_rand();
		$html = '<input' . self::attrs(
			array_merge(
				array(
					'class'        => 'qsm-ui-input',
					'type'         => 'password',
					'name'         => $args['name'],
					'id'           => $id,
					'value'        => (string) $args['value'],
					'placeholder'  => $args['placeholder'],
					'autocomplete' => 'off',
					'style'        => 'padding-right:44px',
				),
				(array) $args['attrs']
			)
		) . '>';
		$html .= self::button(
			__( 'Show', 'quiz-master-next' ),
			array(
				'icon'      => 'eye',
				'icon_only' => true,
				'class'     => 'qsm-ui-trail',
				'attrs'     => array( 'data-qsm-ui-reveal' => '#' . $id ),
			)
		);
		return '<div class="' . esc_attr( self::classes( 'qsm-ui-input-icon', $args['width'] ? 'qsm-ui-w-' . $args['width'] : '' ) ) . '">' . self::icon( 'lock' ) . $html . '</div>';
	}

	/**
	 * Read-only value with a Copy button (F-08).
	 *
	 * @param array $args value, id, label, attrs.
	 * @return string
	 */
	public static function copy_field( $args = array() ) {
		$args    = self::args(
			$args,
			array(
				'value' => '',
				'id'    => '',
				'label' => '',
				'attrs' => array(),
			)
		);
		$id      = $args['id'] ? $args['id'] : 'qsm-ui-copy-' . wp_rand();
		$control = self::input(
			array(
				'id'    => $id,
				'value' => $args['value'],
				'attrs' => array_merge(
					array(
						'readonly'   => true,
						'aria-label' => $args['label'] ? '' : __( 'Value to copy', 'quiz-master-next' ),
					),
					(array) $args['attrs']
				),
			)
		);
		$button  = self::button(
			__( 'Copy', 'quiz-master-next' ),
			array(
				'icon'  => 'copy',
				'size'  => 'sm',
				'class' => 'qsm-ui-copy-btn',
				'attrs' => array( 'data-qsm-ui-copy' => '#' . $id ),
			)
		);
		$group   = self::input_group(
			array(
				'control' => $control,
				'button'  => $button,
			)
		);
		return '<div class="qsm-ui-copy-field">' . ( $args['label'] ? '<label class="qsm-ui-field-label" for="' . esc_attr( $id ) . '">' . esc_html( $args['label'] ) . '</label>' : '' ) . $group . '</div>';
	}

	/**
	 * Inline notice (N-01).
	 *
	 * @param string $message Message (basic HTML allowed).
	 * @param array  $args {
	 *     @type string $type        'info', 'success', 'warning', 'danger'.
	 *     @type string $title       Bold first line.
	 *     @type string $actions     HTML for links / buttons under the message.
	 *     @type bool   $dismissible Show a close button (removes the notice in JS only).
	 *     @type string $id          Notice id.
	 * }
	 * @return string
	 */
	public static function notice( $message, $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'type'        => 'info',
				'title'       => '',
				'actions'     => '',
				'dismissible' => false,
				'id'          => '',
			)
		);
		$icons = array(
			'info'    => 'info',
			'success' => 'ok',
			'warning' => 'alert',
			'danger'  => 'alert',
		);
		$type  = isset( $icons[ $args['type'] ] ) ? $args['type'] : 'info';
		$html  = self::icon( $icons[ $type ] ) . '<div class="qsm-ui-notice-body">';
		$html .= $args['title'] ? '<strong>' . esc_html( $args['title'] ) . '</strong>' : '';
		$html .= '<p>' . wp_kses_post( $message ) . '</p>';
		$html .= $args['actions'] ? '<div class="qsm-ui-notice-actions">' . $args['actions'] . '</div>' : '';
		$html .= '</div>';
		if ( $args['dismissible'] ) {
			$html .= self::button(
				__( 'Dismiss', 'quiz-master-next' ),
				array(
					'icon'      => 'x',
					'icon_only' => true,
					'class'     => 'qsm-ui-dismiss',
					'attrs'     => array( 'data-qsm-ui-dismiss' => true ),
				)
			);
		}
		$attrs = array(
			'class' => self::classes( 'qsm-ui-notice', 'info' === $type ? '' : 'is-' . $type ),
			'id'    => $args['id'],
			'role'  => in_array( $type, array( 'warning', 'danger' ), true ) ? 'alert' : 'status',
		);
		return '<div' . self::attrs( $attrs ) . '>' . $html . '</div>';
	}

	/**
	 * Badge (N-03).
	 *
	 * @param string $label Text.
	 * @param string $type  '', 'success', 'warning', 'danger', 'info', 'pro'.
	 * @param bool   $dot   Show the leading dot. Default true.
	 * @return string
	 */
	public static function badge( $label, $type = '', $dot = true ) {
		return '<span class="' . esc_attr( self::classes( 'qsm-ui-badge', $type ? 'is-' . $type : '', $dot ? '' : 'no-dot' ) ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Status pill, e.g. Published / Active (N-03, L-01, P-05).
	 *
	 * @param string $label Text.
	 * @param bool   $on    Green when true, grey when false.
	 * @return string
	 */
	public static function status( $label, $on = true ) {
		return '<span class="' . esc_attr( self::classes( 'qsm-ui-status', $on ? '' : 'is-off' ) ) . '">' . esc_html( $label ) . '</span>';
	}

	/**
	 * Tooltip trigger (O-05). Short text only; anything longer belongs in help text.
	 *
	 * @param string $text Tooltip text.
	 * @return string
	 */
	public static function tip( $text ) {
		return '<button class="qsm-ui-tip" type="button" data-tip="' . esc_attr( $text ) . '" aria-label="' . esc_attr( $text ) . '">?</button>';
	}

	/**
	 * Documentation help icon next to a label (P-06). Same links as today.
	 *
	 * @param string $url   Documentation URL.
	 * @param string $label Accessible label.
	 * @return string
	 */
	public static function doc_link( $url, $label = '' ) {
		$label = $label ? $label : __( 'Documentation (opens in a new tab)', 'quiz-master-next' );
		return '<a class="qsm-ui-doc" href="' . esc_url( $url ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $label ) . '">' . self::icon( 'info' ) . '</a>';
	}

	/**
	 * Empty state (N-05, D-02).
	 *
	 * @param array $args icon, title, text, actions (HTML).
	 * @return string
	 */
	public static function empty_state( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'icon'    => 'quiz',
				'title'   => '',
				'text'    => '',
				'actions' => '',
			)
		);
		$html  = '<div class="qsm-ui-empty"><div class="qsm-ui-empty-icon">' . self::icon( $args['icon'] ) . '</div>';
		$html .= $args['title'] ? '<h3 class="qsm-ui-h3">' . esc_html( $args['title'] ) . '</h3>' : '';
		$html .= $args['text'] ? '<p>' . wp_kses_post( $args['text'] ) . '</p>' : '';
		$html .= $args['actions'] ? '<div class="qsm-ui-row">' . $args['actions'] . '</div>' : '';
		return $html . '</div>';
	}

	/**
	 * Progress bar (N-04).
	 *
	 * @param int|float $percent 0-100.
	 * @param string    $label   Optional label above the bar.
	 * @return string
	 */
	public static function progress( $percent, $label = '' ) {
		$percent = max( 0, min( 100, (float) $percent ) );
		$html    = $label ? '<div class="qsm-ui-progress-label"><span>' . esc_html( $label ) . '</span><span>' . esc_html( round( $percent ) . '%' ) . '</span></div>' : '';
		return $html . '<div class="' . esc_attr( self::classes( 'qsm-ui-progress', $percent >= 100 ? 'is-success' : '' ) ) . '" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' . esc_attr( round( $percent ) ) . '"' . ( $label ? ' aria-label="' . esc_attr( $label ) . '"' : '' ) . '><span style="width:' . esc_attr( $percent ) . '%"></span></div>';
	}

	/**
	 * In-page tabs (L-02 look, L-03 with 'pill'). The panels are yours: give each
	 * one id="{id}-panel-{key}" class="qsm-ui-tab-panel" role="tabpanel", and
	 * `hidden` on all but the active one. For tabs that are links (the quiz edit
	 * tabs), print <a class="qsm-ui-tab" aria-selected="..."> yourself.
	 *
	 * @param array $args id (prefix), tabs (key => label or array( 'label', 'icon', 'count' )), active, pill (bool), label (aria-label).
	 * @return string
	 */
	public static function tabs( $args = array() ) {
		$args   = self::args(
			$args,
			array(
				'id'     => 'qsm-ui-tabs',
				'tabs'   => array(),
				'active' => '',
				'pill'   => false,
				'label'  => '',
			)
		);
		$keys   = array_keys( (array) $args['tabs'] );
		$active = '' !== $args['active'] ? (string) $args['active'] : ( $keys ? (string) $keys[0] : '' );
		$html   = '';
		foreach ( (array) $args['tabs'] as $key => $tab ) {
			$tab   = self::args(
				is_array( $tab ) ? $tab : array( 'label' => $tab ),
				array(
					'label' => '',
					'icon'  => '',
					'count' => '',
				)
			);
			$on    = (string) $key === $active;
			$html .= '<button' . self::attrs(
				array(
					'class'         => 'qsm-ui-tab',
					'type'          => 'button',
					'role'          => 'tab',
					'id'            => $args['id'] . '-tab-' . $key,
					'aria-controls' => $args['id'] . '-panel-' . $key,
					'aria-selected' => $on ? 'true' : 'false',
					'tabindex'      => $on ? '' : '-1',
				)
			) . '>' . ( $tab['icon'] ? self::icon( $tab['icon'], 'qsm-ui-icon-sm' ) : '' ) . esc_html( $tab['label'] ) . ( '' !== $tab['count'] ? ' <span class="qsm-ui-count">' . esc_html( $tab['count'] ) . '</span>' : '' ) . '</button>';
		}
		return '<div' . self::attrs(
			array(
				'class'           => self::classes( 'qsm-ui-tabs', $args['pill'] ? 'qsm-ui-tabs-pill' : '' ),
				'role'            => 'tablist',
				'aria-label'      => $args['label'],
				'data-qsm-ui-tabs' => true,
			)
		) . '>' . $html . '</div>';
	}

	/**
	 * Modal dialog, native <dialog> (O-01, O-02). Open it with a button carrying
	 * data-qsm-ui-open="#id", or QSMAdminUI.openDialog( '#id' ). Print it inside
	 * a .qsm-ui wrapper.
	 *
	 * @param array $args id, title, description, body (HTML), footer (HTML), size ('' or 'lg'), danger (bool: O-02 icon).
	 * @return string
	 */
	public static function dialog( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'id'          => '',
				'title'       => '',
				'description' => '',
				'body'        => '',
				'footer'      => '',
				'size'        => '',
				'danger'      => false,
			)
		);
		$title = '<h2 class="qsm-ui-h2" id="' . esc_attr( $args['id'] . '-title' ) . '">' . esc_html( $args['title'] ) . '</h2>';
		$title .= $args['description'] ? '<p class="qsm-ui-small" style="margin-top:4px">' . wp_kses_post( $args['description'] ) . '</p>' : '';
		$head  = $args['danger'] ? '<div class="qsm-ui-confirm-head"><div class="qsm-ui-modal-icon">' . self::icon( 'alert' ) . '</div><div>' . $title . '</div></div>' : '<div>' . $title . '</div>';
		$close = self::button(
			__( 'Close', 'quiz-master-next' ),
			array(
				'icon'      => 'x',
				'icon_only' => true,
				'attrs'     => array( 'data-qsm-ui-close' => true ),
			)
		);
		$html  = '<dialog' . self::attrs(
			array(
				'class'           => self::classes( 'qsm-ui-modal', $args['size'] ? 'is-' . $args['size'] : '' ),
				'id'              => $args['id'],
				'aria-labelledby' => $args['id'] . '-title',
			)
		) . '>';
		$html .= '<div class="qsm-ui-modal-head">' . $head . $close . '</div>';
		$html .= '<div class="qsm-ui-modal-body">' . $args['body'] . '</div>';
		$html .= $args['footer'] ? '<div class="qsm-ui-modal-foot">' . $args['footer'] . '</div>' : '';
		return $html . '</dialog>';
	}

	/**
	 * Pro lock over a block of settings (P-04).
	 *
	 * @param array $args content (HTML, shown blurred), title, text, button_label, url.
	 * @return string
	 */
	public static function pro_lock( $args = array() ) {
		$args  = self::args(
			$args,
			array(
				'content'      => '',
				'title'        => '',
				'text'         => '',
				'button_label' => __( 'Unlock with Pro', 'quiz-master-next' ),
				'url'          => '',
			)
		);
		$html  = '<div class="qsm-ui-locked"><div class="qsm-ui-locked-content" inert>' . $args['content'] . '</div><div class="qsm-ui-locked-overlay">';
		$html .= self::badge( __( 'PRO', 'quiz-master-next' ), 'pro' );
		$html .= $args['title'] ? '<strong>' . esc_html( $args['title'] ) . '</strong>' : '';
		$html .= $args['text'] ? '<p>' . esc_html( $args['text'] ) . '</p>' : '';
		$html .= $args['url'] ? self::button(
			$args['button_label'],
			array(
				'variant' => 'primary',
				'size'    => 'sm',
				'icon'    => 'lock',
				'href'    => $args['url'],
				'attrs'   => array( 'target' => '_blank' ),
			)
		) : '';
		return $html . '</div></div>';
	}

	/**
	 * Upsell banner (P-04).
	 *
	 * @param array $args title, text, button_label, url, icon.
	 * @return string
	 */
	public static function upsell( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'title'        => '',
				'text'         => '',
				'button_label' => '',
				'url'          => '',
				'icon'         => 'star',
			)
		);
		$html = '<div class="qsm-ui-upsell"><span class="qsm-ui-addon-logo" style="background:linear-gradient(135deg,#6c3fd1,#315efb)">' . self::icon( $args['icon'] ) . '</span><div><strong>' . esc_html( $args['title'] ) . '</strong>' . ( $args['text'] ? '<p>' . esc_html( $args['text'] ) . '</p>' : '' ) . '</div>';
		if ( $args['url'] && $args['button_label'] ) {
			$html .= self::button(
				$args['button_label'],
				array(
					'variant' => 'primary',
					'href'    => $args['url'],
					'attrs'   => array( 'target' => '_blank' ),
				)
			);
		}
		return $html . '</div>';
	}

	/**
	 * License card (P-05). Layout only: the add-on keeps its own field name,
	 * nonce and activate / deactivate handling, and passes them in.
	 *
	 * @param array $args {
	 *     @type string $title    Card title, e.g. "FunnelKit license".
	 *     @type string $description Text under the title.
	 *     @type bool   $active   License is active.
	 *     @type string $control  The key input HTML (name, value as today).
	 *     @type string $button   Activate / Deactivate button HTML.
	 *     @type string $meta     Caption under the field (expiry, sites used).
	 *     @type string $active_label   Default "Active".
	 *     @type string $inactive_label Default "Not active".
	 * }
	 * @return string
	 */
	public static function license_card( $args = array() ) {
		$args = self::args(
			$args,
			array(
				'title'          => '',
				'description'    => '',
				'active'         => false,
				'control'        => '',
				'button'         => '',
				'meta'           => '',
				'active_label'   => __( 'Active', 'quiz-master-next' ),
				'inactive_label' => __( 'Not active', 'quiz-master-next' ),
			)
		);
		$body = self::input_group(
			array(
				'control'     => $args['control'],
				'prefix_icon' => 'key',
				'button'      => $args['button'],
				'width'       => 'lg',
			)
		);
		$body .= $args['meta'] ? '<p class="qsm-ui-caption" style="margin-top:8px">' . esc_html( $args['meta'] ) . '</p>' : '';
		return self::card(
			array(
				'title'       => $args['title'],
				'description' => $args['description'],
				'actions'     => self::status( $args['active'] ? $args['active_label'] : $args['inactive_label'], (bool) $args['active'] ),
				'body'        => $body,
			)
		);
	}
}
