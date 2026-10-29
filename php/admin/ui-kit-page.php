<?php
/**
 * Hidden "QSM UI Kit" admin page: every approved kit component in one place,
 * for developer docs and a visual check before each release.
 *
 * Not in the menu. Open wp-admin/admin.php?page=qsm_ui_kit (manage_options).
 * Developer page, so its sample copy is not translated.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues what the kit page needs: the kit itself, the libraries Core uses
 * for F-17 / F-21 / F-24 / G-02, and the page's own layout and demo script.
 *
 * @return void
 */
function qsm_ui_kit_page_enqueue() {
	global $mlwQuizMasterNext;
	QSM_Admin_UI::enqueue();
	wp_enqueue_script( 'select2-js', QSM_PLUGIN_JS_URL . '/jquery.select2.min.js', array( 'jquery' ), $mlwQuizMasterNext->version, true );
	wp_enqueue_style( 'select2-css', QSM_PLUGIN_CSS_URL . '/jquery.select2.min.css', array(), $mlwQuizMasterNext->version );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker-alpha', QSM_PLUGIN_JS_URL . '/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), $mlwQuizMasterNext->version, true );
	wp_enqueue_editor();
	wp_enqueue_style( 'qsm_admin_ui_kit_page_css', QSM_PLUGIN_CSS_URL . '/qsm-admin-ui-kit-page.css', array( 'qsm_admin_ui_css' ), $mlwQuizMasterNext->version );
	wp_enqueue_script( 'qsm_admin_ui_kit_page_js', QSM_PLUGIN_JS_URL . '/qsm-admin-ui-kit-page.js', array( 'jquery', 'jquery-ui-sortable', 'select2-js', 'wp-color-picker-alpha', 'qsm_admin_ui_js' ), $mlwQuizMasterNext->version, true );
}

/**
 * Renders the kit page.
 *
 * @return void
 */
function qsm_generate_ui_kit_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sorry, you are not allowed to access this page.', 'quiz-master-next' ) );
	}
	$held = array(
		'F-14' => 'Visual tiles',
		'F-18' => 'Multi-select (tags)',
		'F-22' => 'Image / media picker',
		'L-09' => 'Sticky save bar',
		'L-10' => 'Wizard steps',
		'R-01' => 'Answer repeater',
		'G-01' => 'Sortable list (keyboard reordering)',
		'P-01' => 'Stat tiles',
		'P-02' => 'Simple bar chart',
		'P-03' => 'Add-on cards',
		'X-05' => 'Mobile support',
	);
	?>
	<div class="wrap qsm-ui">
		<div class="qsm-ui-text qsm-ui-kitpage">
			<div class="kit-intro">
				<h1 class="qsm-ui-h1">QSM UI Kit <span class="qsm-ui-badge is-info no-dot"><?php echo esc_html( 'v' . QSM_ADMIN_UI_VERSION ); ?></span></h1>
				<p>Every approved component of the new admin UI, rendered by the shipped <code>css/qsm-admin-ui.css</code> and <code>js/qsm-admin-ui.js</code>. Check this page before each release: if something looks wrong here, it looks wrong on every screen that uses it.</p>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
				echo QSM_Admin_UI::notice(
					'Call <code>QSM_Admin_UI::enqueue()</code> from the screen\'s enqueue branch, wrap the block you are converting in <code>&lt;div class="qsm-ui"&gt;</code>, and use the <code>qsm-ui-*</code> classes or the <code>QSM_Admin_UI</code> helpers (each item below shows the helper call where there is one). Nothing outside a <code>.qsm-ui</code> wrapper changes, and add-on markup printed inside one keeps its look because every kit class is prefixed. Behaviour is opt-in through <code>data-qsm-ui-*</code> attributes and <code>data-show-if</code> / <code>data-enable-if</code>; the JS API is <code>window.QSMAdminUI</code>.',
					array( 'title' => 'How to use the kit' )
				);
				?>
				<p class="qsm-ui-small" style="margin-top:14px"><strong>Not built</strong> (on hold): <?php echo esc_html( implode( ', ', array_map( 'qsm_ui_kit_held_label', array_keys( $held ), $held ) ) ); ?>. <strong>Dropped:</strong> O-03 side drawer, X-02 question editor in a drawer. <strong>Light theme only</strong> (X-03).</p>
			</div>
			<div class="kit-layout">
				<nav class="kit-nav" aria-label="Components" id="qsmk-nav"></nav>
				<main class="kit-main">
					<?php require QSM_PLUGIN_PATH . 'php/admin/ui-kit/components.php'; ?>
				</main>
			</div>
		</div>
	</div>
	<?php
}

/**
 * "F-14 Visual tiles" for the on-hold list.
 *
 * @param string $id   Kit id.
 * @param string $name Item name.
 * @return string
 */
function qsm_ui_kit_held_label( $id, $name ) {
	return $id . ' ' . $name;
}

/**
 * Prints the icon gallery (T-04).
 *
 * @return void
 */
function qsm_ui_kit_icons() {
	echo '<div class="kit-icons">';
	foreach ( QSM_Admin_UI::icon_names() as $name ) {
		echo '<span>' . QSM_Admin_UI::icon( $name ) . esc_html( $name ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	}
	echo '</div>';
}

/**
 * Prints the real wp_editor() inside the kit frame (F-24).
 *
 * @return void
 */
function qsm_ui_kit_editor() {
	echo '<div class="qsm-ui-editor">';
	wp_editor(
		'<p>Thanks %USER_NAME%! You scored <strong>%CORRECT_SCORE%%</strong> on %QUIZ_NAME%.</p>',
		'qsmkeditor',
		array(
			'textarea_name' => 'qsmkeditor',
			'textarea_rows' => 6,
			'media_buttons' => true,
		)
	);
	echo '</div>';
}

/**
 * Helper examples: the helper call as code, rendered live by the same helper.
 *
 * Keep the `code` string and the `render` closure in step when editing.
 *
 * @return array id => array( 'code' => string, 'render' => callable ).
 */
function qsm_ui_kit_examples() {
	$ui = 'QSM_Admin_UI';
	return array(
		'T-04' => array(
			'code'   => "QSM_Admin_UI::icon( 'trash' );\nQSM_Admin_UI::icon( 'edit', 'qsm-ui-icon-sm' );",
			'render' => function () use ( $ui ) {
				return $ui::icon( 'trash' ) . ' ' . $ui::icon( 'edit', 'qsm-ui-icon-sm' );
			},
		),
		'B-01' => array(
			'code'   => "QSM_Admin_UI::button( 'Save changes', array( 'variant' => 'primary', 'type' => 'submit' ) );\nQSM_Admin_UI::button( 'Add question', array( 'variant' => 'primary', 'icon' => 'plus' ) );\nQSM_Admin_UI::button( 'Delete quiz', array( 'variant' => 'danger' ) );\nQSM_Admin_UI::button( 'View docs', array( 'variant' => 'quiet', 'icon' => 'ext', 'href' => 'https://quizandsurveymaster.com/docs/' ) );",
			'render' => function () use ( $ui ) {
				return '<div class="qsm-ui-row">' . $ui::button(
					'Save changes',
					array(
						'variant' => 'primary',
						'type'    => 'submit',
						'attrs'   => array( 'form' => 'qsmk-no-form' ),
					)
				) . $ui::button(
					'Add question',
					array(
						'variant' => 'primary',
						'icon'    => 'plus',
					)
				) . $ui::button( 'Delete quiz', array( 'variant' => 'danger' ) ) . $ui::button(
					'View docs',
					array(
						'variant' => 'quiet',
						'icon'    => 'ext',
						'href'    => 'https://quizandsurveymaster.com/docs/',
					)
				) . '</div>';
			},
		),
		'B-02' => array(
			'code'   => "QSM_Admin_UI::button( 'Small', array( 'variant' => 'primary', 'size' => 'sm' ) );\nQSM_Admin_UI::button( 'Delete', array( 'icon' => 'trash', 'icon_only' => true, 'class' => 'is-danger' ) );\n// JS: QSMAdminUI.loading( button, true ) while an AJAX call runs.",
			'render' => function () use ( $ui ) {
				return '<div class="qsm-ui-row">' . $ui::button(
					'Small',
					array(
						'variant' => 'primary',
						'size'    => 'sm',
					)
				) . $ui::button(
					'Delete',
					array(
						'icon'      => 'trash',
						'icon_only' => true,
						'class'     => 'is-danger',
					)
				) . '</div>';
			},
		),
		'F-01' => array(
			'code'   => "QSM_Admin_UI::field( array(\n\t'label'    => 'Quiz name',\n\t'for'      => 'quiz_name',\n\t'required' => true,\n\t'help'     => 'Shown as the quiz title on the front end.',\n\t'control'  => QSM_Admin_UI::input( array( 'name' => 'quiz_name', 'id' => 'quiz_name', 'value' => \$name ) ),\n) );",
			'render' => function () use ( $ui ) {
				return $ui::field(
					array(
						'label'    => 'Quiz name',
						'for'      => 'qsmk-x-name',
						'required' => true,
						'help'     => 'Shown as the quiz title on the front end.',
						'control'  => $ui::input(
							array(
								'id'    => 'qsmk-x-name',
								'value' => 'Customer Satisfaction Survey',
							)
						),
					)
				);
			},
		),
		'F-02' => array(
			'code'   => "QSM_Admin_UI::field( array(\n\t'label'   => 'Quiz name',\n\t'for'     => 'quiz_name',\n\t'error'   => 'Quiz name is required.', // server-side validation message\n\t'control' => QSM_Admin_UI::input( array( 'id' => 'quiz_name', 'invalid' => true, 'attrs' => array( 'aria-describedby' => 'quiz_name-msg' ) ) ),\n) );",
			'render' => function () use ( $ui ) {
				return $ui::field(
					array(
						'label'   => 'Quiz name',
						'for'     => 'qsmk-x-err',
						'error'   => 'Quiz name is required.',
						'control' => $ui::input(
							array(
								'id'      => 'qsmk-x-err',
								'invalid' => true,
								'width'   => 'md',
								'attrs'   => array( 'aria-describedby' => 'qsmk-x-err-msg' ),
							)
						),
					)
				);
			},
		),
		'F-03' => array(
			'code'   => "QSM_Admin_UI::input_group( array(\n\t'control' => QSM_Admin_UI::input( array( 'type' => 'number', 'name' => 'timer_limit', 'value' => 30 ) ),\n\t'suffix'  => 'minutes',\n\t'width'   => 'sm',\n) );",
			'render' => function () use ( $ui ) {
				return $ui::input_group(
					array(
						'control' => $ui::input(
							array(
								'type'  => 'number',
								'value' => 30,
								'attrs' => array( 'aria-label' => 'Time limit' ),
							)
						),
						'suffix'  => 'minutes',
						'width'   => 'sm',
					)
				);
			},
		),
		'F-04' => array(
			'code'   => "QSM_Admin_UI::stepper( array( 'name' => 'pagination', 'value' => 5, 'min' => 0, 'max' => 50, 'label' => 'Questions per page' ) );",
			'render' => function () use ( $ui ) {
				return $ui::stepper(
					array(
						'value' => 5,
						'min'   => 0,
						'max'   => 50,
						'label' => 'Questions per page',
					)
				);
			},
		),
		'F-05' => array(
			'code'   => "QSM_Admin_UI::secret( array( 'name' => 'api_key', 'id' => 'api_key', 'value' => \$key ) );",
			'render' => function () use ( $ui ) {
				return $ui::secret(
					array(
						'id'    => 'qsmk-x-secret',
						'value' => 'sk_live_51Hx9example',
						'attrs' => array( 'aria-label' => 'API key' ),
					)
				);
			},
		),
		'F-06' => array(
			'code'   => "QSM_Admin_UI::field( array(\n\t'label'   => 'Hint',\n\t'for'     => 'hint',\n\t'counter' => 160,\n\t'control' => QSM_Admin_UI::textarea( array( 'name' => 'hint', 'id' => 'hint', 'value' => \$hint ) ),\n) );",
			'render' => function () use ( $ui ) {
				return $ui::field(
					array(
						'label'   => 'Hint',
						'for'     => 'qsmk-x-hint',
						'counter' => 160,
						'control' => $ui::textarea(
							array(
								'id'    => 'qsmk-x-hint',
								'value' => 'Think about which option saves the most time.',
							)
						),
					)
				);
			},
		),
		'F-08' => array(
			'code'   => "QSM_Admin_UI::copy_field( array( 'label' => 'Shortcode', 'value' => '[qsm quiz=' . \$quiz_id . ']' ) );",
			'render' => function () use ( $ui ) {
				return $ui::copy_field(
					array(
						'label' => 'Shortcode',
						'id'    => 'qsmk-x-copy',
						'value' => '[qsm quiz=12]',
					)
				);
			},
		),
		'F-09' => array(
			'code'   => "QSM_Admin_UI::textarea( array( 'name' => 'quiz_stye', 'id' => 'quiz_stye', 'value' => \$css, 'code' => true ) );",
			'render' => function () use ( $ui ) {
				return $ui::textarea(
					array(
						'value' => ".qsm-quiz-container {\n  max-width: 720px;\n}",
						'code'  => true,
						'attrs' => array( 'aria-label' => 'Custom CSS' ),
					)
				);
			},
		),
		'F-10' => array(
			'code'   => "QSM_Admin_UI::toggle( array(\n\t'name'            => 'randomness_order',\n\t'checked'         => \$on,\n\t'unchecked_value' => 0, // hidden input, so \"off\" still saves 0 as today\n\t'label'           => 'Randomize questions',\n\t'description'     => 'Show questions in a different order for each attempt.',\n) );",
			'render' => function () use ( $ui ) {
				return '<div class="qsm-ui-switch-list">' . $ui::toggle(
					array(
						'checked'     => true,
						'label'       => 'Randomize questions',
						'description' => 'Show questions in a different order for each attempt.',
					)
				) . '</div>';
			},
		),
		'F-11' => array(
			'code'   => "QSM_Admin_UI::choices( array(\n\t'type'     => 'checkbox',\n\t'name'     => 'contact_fields[]',\n\t'options'  => array( 'name' => 'Name', 'email' => 'Email', 'phone' => 'Phone' ),\n\t'selected' => array( 'name', 'email' ),\n\t'inline'   => true,\n) );",
			'render' => function () use ( $ui ) {
				return $ui::choices(
					array(
						'type'     => 'checkbox',
						'options'  => array(
							'name'  => 'Name',
							'email' => 'Email',
							'phone' => 'Phone',
						),
						'selected' => array( 'name', 'email' ),
						'inline'   => true,
					)
				);
			},
		),
		'F-12' => array(
			'code'   => "QSM_Admin_UI::choices( array(\n\t'type'     => 'radio',\n\t'name'     => 'show_correct',\n\t'options'  => array( 0 => 'Never', 1 => 'After each question', 2 => 'On the results page' ),\n\t'selected' => \$value,\n\t'inline'   => true,\n) );",
			'render' => function () use ( $ui ) {
				return $ui::choices(
					array(
						'type'     => 'radio',
						'name'     => 'qsmk-x-radio',
						'options'  => array(
							0 => 'Never',
							1 => 'After each question',
							2 => 'On the results page',
						),
						'selected' => 0,
						'inline'   => true,
					)
				);
			},
		),
		'F-13' => array(
			'code'   => "QSM_Admin_UI::radio_cards( array(\n\t'name'     => 'form_type',\n\t'selected' => \$form_type,\n\t'options'  => array(\n\t\t0 => array( 'label' => 'Quiz', 'description' => 'Graded, with a score', 'icon' => 'quiz' ),\n\t\t1 => array( 'label' => 'Survey', 'description' => 'No right answers', 'icon' => 'survey' ),\n\t),\n) );",
			'render' => function () use ( $ui ) {
				return $ui::radio_cards(
					array(
						'name'     => 'qsmk-x-rc',
						'selected' => 0,
						'options'  => array(
							0 => array(
								'label'       => 'Quiz',
								'description' => 'Graded, with a score',
								'icon'        => 'quiz',
							),
							1 => array(
								'label'       => 'Survey',
								'description' => 'No right answers',
								'icon'        => 'survey',
							),
						),
					)
				);
			},
		),
		'F-16' => array(
			'code'   => "QSM_Admin_UI::select( array(\n\t'name'     => 'email_format',\n\t'options'  => array( 'html' => 'HTML', 'text' => 'Plain text' ),\n\t'selected' => \$format,\n\t'width'    => 'md',\n) );",
			'render' => function () use ( $ui ) {
				return $ui::select(
					array(
						'options'  => array(
							'html' => 'HTML',
							'text' => 'Plain text',
						),
						'selected' => 'html',
						'width'    => 'md',
						'attrs'    => array( 'aria-label' => 'Email format' ),
					)
				);
			},
		),
		'F-17' => array(
			'code'   => "// PHP: a normal select. JS: start Select2 with the kit look (X-04 opt-in class).\nQSMAdminUI.select2( \$( '#results_page' ), { ajax: { ... } } );",
			'render' => null,
		),
		'L-02' => array(
			'code'   => "QSM_Admin_UI::tabs( array(\n\t'id'   => 'qsm-email',\n\t'tabs' => array( 'admin' => 'Admin', 'user' => array( 'label' => 'User', 'count' => 2 ) ),\n) );\n// Panels: <div class=\"qsm-ui-tab-panel\" role=\"tabpanel\" id=\"qsm-email-panel-admin\">…\n// The quiz edit tabs are links: print <a class=\"qsm-ui-tab\" role=\"tab\" aria-selected=\"true\"> in a [data-qsm-ui-tabs] bar.",
			'render' => null,
		),
		'L-05' => array(
			'code'   => "echo QSM_Admin_UI::card_open( array(\n\t'title'       => 'Timer',\n\t'description' => 'Limit how long users have to finish the quiz.',\n\t'body_id'     => 'qsm-timer-body',\n\t'actions'     => QSM_Admin_UI::toggle( array( 'checked' => true, 'attrs' => array( 'data-qsm-ui-controls' => '#qsm-timer-body' ) ) ),\n) );\n// ... rows ...\necho QSM_Admin_UI::card_close();",
			'render' => function () use ( $ui ) {
				return $ui::card(
					array(
						'title'       => 'Timer',
						'description' => 'Limit how long users have to finish the quiz.',
						'body_id'     => 'qsmk-x-card-body',
						'actions'     => $ui::toggle(
							array(
								'checked' => true,
								'attrs'   => array( 'data-qsm-ui-controls' => '#qsmk-x-card-body' ),
							)
						),
						'body'        => $ui::field_row(
							array(
								'label'   => 'Time limit',
								'help'    => '0 = no limit',
								'control' => $ui::input_group(
									array(
										'control' => $ui::input(
											array(
												'type'  => 'number',
												'value' => 30,
												'attrs' => array( 'aria-label' => 'Time limit' ),
											)
										),
										'suffix'  => 'minutes',
										'width'   => 'sm',
									)
								),
							)
						),
					)
				);
			},
		),
		'L-06' => array(
			'code'   => "QSM_Admin_UI::field_row( array(\n\t'label'     => 'Grading system',\n\t'for'       => 'system',\n\t'show_if'   => 'form_type:0',          // hide unless Quiz\n\t'control'   => QSM_Admin_UI::select( array( 'name' => 'system', 'id' => 'system', 'options' => \$systems, 'selected' => \$system ) ),\n) );\nQSM_Admin_UI::field_row( array(\n\t'label'     => 'By category',\n\t'enable_if' => 'question_from_total:!0', // grey out while the limit is 0\n\t'control'   => QSM_Admin_UI::toggle( array( 'name' => 'limit_category_checkbox', 'label' => 'Limit questions per category', 'rule_note' => 'Set “Limit number of questions” above 0 to use this.' ) ),\n) );",
			'render' => function () use ( $ui ) {
				return '<div class="qsm-ui-card"><div class="qsm-ui-card-body">' . $ui::field_row(
					array(
						'label'   => 'Limit number of questions',
						'for'     => 'qsmk-x-total',
						'control' => $ui::input(
							array(
								'type'  => 'number',
								'name'  => 'qsmk_x_total',
								'id'    => 'qsmk-x-total',
								'value' => 0,
								'width' => 'sm',
							)
						),
					)
				) . $ui::field_row(
					array(
						'label'     => 'By category',
						'enable_if' => 'qsmk_x_total:!0',
						'control'   => $ui::toggle(
							array(
								'label'     => 'Limit questions per category',
								'rule_note' => 'Set “Limit number of questions” above 0 to use this.',
							)
						),
					)
				) . '</div></div>';
			},
		),
		'N-01' => array(
			'code'   => "QSM_Admin_UI::notice( 'Your QSM Pro license expires in 7 days.', array(\n\t'type'        => 'warning',\n\t'title'       => 'License expiring',\n\t'actions'     => QSM_Admin_UI::button( 'Renew', array( 'variant' => 'link', 'href' => \$url ) ),\n\t'dismissible' => true,\n) );",
			'render' => function () use ( $ui ) {
				return $ui::notice(
					'Your QSM Pro license expires in 7 days.',
					array(
						'type'        => 'warning',
						'title'       => 'License expiring',
						'actions'     => $ui::button(
							'Renew',
							array(
								'variant' => 'link',
								'href'    => '#',
							)
						),
						'dismissible' => true,
					)
				);
			},
		),
		'N-02' => array(
			'code'   => "QSMAdminUI.toast( 'Question saved' );\nQSMAdminUI.toast( 'Question deleted', { undo: function () { /* restore */ } } );\nQSMAdminUI.toast( \"Couldn't save. Check your connection.\", { type: 'error' } );",
			'render' => null,
		),
		'N-03' => array(
			'code'   => "QSM_Admin_UI::badge( 'Passed', 'success' );\nQSM_Admin_UI::badge( 'PRO', 'pro' );\nQSM_Admin_UI::status( 'Published' );\nQSM_Admin_UI::status( 'Draft', false );",
			'render' => function () use ( $ui ) {
				return '<div class="qsm-ui-row">' . $ui::badge( 'Passed', 'success' ) . $ui::badge( 'PRO', 'pro' ) . $ui::status( 'Published' ) . $ui::status( 'Draft', false ) . '</div>';
			},
		),
		'N-04' => array(
			'code'   => "QSM_Admin_UI::progress( 62, 'Migrating results' );",
			'render' => function () use ( $ui ) {
				return $ui::progress( 62, 'Migrating results' );
			},
		),
		'N-05' => array(
			'code'   => "QSM_Admin_UI::empty_state( array(\n\t'icon'    => 'quiz',\n\t'title'   => 'No questions yet',\n\t'text'    => 'Add your first question, or pull one in from the question bank.',\n\t'actions' => QSM_Admin_UI::button( 'Add question', array( 'variant' => 'primary', 'icon' => 'plus' ) ),\n) );",
			'render' => function () use ( $ui ) {
				return $ui::empty_state(
					array(
						'icon'    => 'quiz',
						'title'   => 'No questions yet',
						'text'    => 'Add your first question, or pull one in from the question bank.',
						'actions' => $ui::button(
							'Add question',
							array(
								'variant' => 'primary',
								'icon'    => 'plus',
							)
						),
					)
				);
			},
		),
		'O-01' => array(
			'code'   => "echo QSM_Admin_UI::dialog( array(\n\t'id'     => 'qsm-add-from-bank',\n\t'title'  => 'Question bank',\n\t'size'   => 'lg',\n\t'body'   => \$body,\n\t'footer' => QSM_Admin_UI::button( 'Cancel', array( 'attrs' => array( 'data-qsm-ui-close' => true ) ) ) . QSM_Admin_UI::button( 'Add', array( 'variant' => 'primary' ) ),\n) );\nQSM_Admin_UI::button( 'Open', array( 'attrs' => array( 'data-qsm-ui-open' => '#qsm-add-from-bank' ) ) );\n// Existing micromodal popups: add class=\"qsm-ui-popup\" next to \"qsm-popup\" for the same look.",
			'render' => function () use ( $ui ) {
				return $ui::dialog(
					array(
						'id'          => 'qsmk-d-modal',
						'title'       => 'Question bank',
						'description' => 'Add questions to Page 1.',
						'size'        => 'lg',
						'body'        => $ui::input(
							array(
								'type'        => 'search',
								'icon'        => 'search',
								'placeholder' => 'Search questions…',
								'attrs'       => array( 'aria-label' => 'Search questions' ),
							)
						) . $ui::empty_state(
							array(
								'icon'  => 'search',
								'title' => 'Search the question bank',
								'text'  => 'The real list, filters and paging stay on the Questions tab screen.',
							)
						),
						'footer'      => $ui::button(
							'Cancel',
							array(
								'attrs' => array(
									'data-qsm-ui-close' => true,
									'autofocus'         => true,
								),
							)
						) . $ui::button(
							'Add to page',
							array(
								'variant' => 'primary',
								'attrs'   => array(
									'data-qsm-ui-close'    => true,
									'data-kit-toast-after' => 'Questions added',
								),
							)
						),
					)
				) . '<span class="qsm-ui-caption">The button above opens this dialog, built with <code>QSM_Admin_UI::dialog()</code>.</span>';
			},
		),
		'O-02' => array(
			'code'   => "// Type-to-confirm: the Delete button turns on when the text matches.\n<input class=\"qsm-ui-input\" data-qsm-ui-confirm-text=\"DELETE\" data-qsm-ui-confirm-target=\"#delete-btn\">\n<button class=\"qsm-ui-btn qsm-ui-btn-danger\" id=\"delete-btn\" disabled>Delete</button>",
			'render' => null,
		),
		'O-05' => array(
			'code'   => "QSM_Admin_UI::tip( \"Leave empty to use the site's admin email.\" );\nQSM_Admin_UI::doc_link( qsm_get_plugin_link( 'docs/...' ) ); // P-06 help icon, same links as today",
			'render' => function () use ( $ui ) {
				return '<span class="qsm-ui-field-label">Send results to ' . $ui::tip( "Leave empty to use the site's admin email." ) . $ui::doc_link( 'https://quizandsurveymaster.com/docs/' ) . '</span>';
			},
		),
		'P-04' => array(
			'code'   => "QSM_Admin_UI::pro_lock( array(\n\t'content' => \$settings_preview_html,\n\t'title'   => 'Certificates are a Pro feature',\n\t'text'    => 'Give every passing user a branded PDF certificate.',\n\t'url'     => qsm_get_plugin_link( 'pricing', 'qsm', 'certificate', 'lock' ),\n) );\nQSM_Admin_UI::upsell( array( 'title' => 'Get 40+ add-ons with QSM Pro', 'button_label' => 'See plans', 'url' => \$url ) );",
			'render' => function () use ( $ui ) {
				return $ui::upsell(
					array(
						'title'        => 'Get 40+ add-ons with QSM Pro',
						'text'         => 'Logic, certificates, payments, integrations and more.',
						'button_label' => 'See plans',
						'url'          => '#',
					)
				);
			},
		),
		'P-05' => array(
			'code'   => "QSM_Admin_UI::license_card( array(\n\t'title'   => 'FunnelKit license',\n\t'active'  => \$is_active,\n\t'control' => QSM_Admin_UI::input( array( 'name' => \$license_field, 'value' => \$masked, 'attrs' => array( 'readonly' => \$is_active ) ) ),\n\t'button'  => \$activate_or_deactivate_button_html, // the add-on's own form handling\n\t'meta'    => 'Expires 14 Mar 2027',\n) );",
			'render' => function () use ( $ui ) {
				return $ui::license_card(
					array(
						'title'  => 'FunnelKit license',
						'active' => false,
						'control' => $ui::input(
							array(
								'placeholder' => 'Paste your key',
								'attrs'       => array( 'aria-label' => 'License key' ),
							)
						),
						'button' => $ui::button(
							'Activate',
							array(
								'variant' => 'primary',
								'attrs'   => array( 'data-kit-loading' => true ),
							)
						),
					)
				);
			},
		),
	);
}

/**
 * Prints the helper example for one kit item, when it has one.
 *
 * @param string $id Kit id, e.g. 'F-04'.
 * @return void
 */
function qsm_ui_kit_php_example( $id ) {
	static $examples = null;
	if ( null === $examples ) {
		$examples = qsm_ui_kit_examples();
	}
	if ( empty( $examples[ $id ] ) ) {
		return;
	}
	$example = $examples[ $id ];
	echo '<div class="kit-php"><p class="kit-label">' . ( $example['render'] ? 'Rendered by the helper' : 'Code' ) . '</p>';
	if ( $example['render'] ) {
		echo '<div class="kit-demo">' . call_user_func( $example['render'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by QSM_Admin_UI.
	}
	echo '<pre><code>' . esc_html( $example['code'] ) . '</code></pre></div>';
}
