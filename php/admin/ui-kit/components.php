<?php
/**
 * QSM UI Kit page: component markup.
 *
 * GENERATED from the approved kit (Agentric artifact art_767b848ce6eec056,
 * round 3) with class names moved to the qsm-ui- prefix. Only approved items
 * are here; on-hold and dropped items are listed on the page. Developer page,
 * so its demo copy is not translated. When the kit changes, edit this file by
 * hand; it is not regenerated at build time.
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="kit-section" id="qsmk-s-foundations" data-group="Foundations">
	<h2 class="qsm-ui-h2">Foundations <small class="qsm-ui-caption"> · tokens</small></h2>
	<p class="qsm-ui-small">Shared CSS variables. Every component reads these, so changing a colour here changes it everywhere.</p>
	<article class="kit-item" id="qsmk-T-01" data-id="T-01">
		<header class="kit-item-head"><span class="kit-id">T-01</span><h3 class="qsm-ui-h3">Colour tokens</h3><span class="kit-where">Everywhere</span></header>
			<div class="kit-demo">
				<p class="kit-label">Brand &amp; text</p>
				<div class="kit-swatches">
					<div class="kit-swatch"><i style="background:#315efb"></i><div><b>--qsm-blue</b><span>#315EFB</span></div></div>
					<div class="kit-swatch"><i style="background:#2449d8"></i><div><b>--qsm-blue-dark</b><span>#2449D8 hover</span></div></div>
					<div class="kit-swatch"><i style="background:#eef2ff"></i><div><b>--qsm-blue-soft</b><span>#EEF2FF selected</span></div></div>
					<div class="kit-swatch"><i style="background:#172033"></i><div><b>--qsm-ink</b><span>#172033 text</span></div></div>
					<div class="kit-swatch"><i style="background:#657188"></i><div><b>--qsm-muted</b><span>#657188 help</span></div></div>
					<div class="kit-swatch"><i style="background:#dce2eb"></i><div><b>--qsm-line</b><span>#DCE2EB borders</span></div></div>
					<div class="kit-swatch"><i style="background:#f8faff"></i><div><b>--qsm-surface-2</b><span>#F8FAFF</span></div></div>
					<div class="kit-swatch"><i style="background:#6c3fd1"></i><div><b>--qsm-purple</b><span>#6C3FD1 Pro</span></div></div>
				</div>
				<p class="kit-label" style="margin-top:18px">Status</p>
				<div class="kit-swatches">
					<div class="kit-swatch"><i style="background:#e8f7ef;border-bottom:4px solid #13764f"></i><div><b>success</b><span>#13764F / #E8F7EF</span></div></div>
					<div class="kit-swatch"><i style="background:#fff6e5;border-bottom:4px solid #c27a0e"></i><div><b>warning</b><span>#92590A / #FFF6E5</span></div></div>
					<div class="kit-swatch"><i style="background:#fff0f2;border-bottom:4px solid #d92d43"></i><div><b>danger</b><span>#D92D43 / #FFF0F2</span></div></div>
					<div class="kit-swatch"><i style="background:#eef2ff;border-bottom:4px solid #315efb"></i><div><b>info</b><span>#315EFB / #EEF2FF</span></div></div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'T-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-T-02" data-id="T-02">
		<header class="kit-item-head"><span class="kit-id">T-02</span><h3 class="qsm-ui-h3">Typography</h3><span class="kit-where">Page titles, card titles, labels, help text</span></header>
			<div class="kit-demo qsm-ui-stack">
				<p class="qsm-ui-h1">Page title · 25 / 700</p>
				<p class="qsm-ui-h2">Section title · 19 / 700</p>
				<p class="qsm-ui-h3">Card title · 16 / 700</p>
				<p class="qsm-ui-body">Body text · 14 / 400. The quiz will show one question per page.</p>
				<p class="qsm-ui-small">Small / secondary · 13 / 400</p>
				<p class="qsm-ui-caption">Help text · 12 / 400 · used under every field</p>
				<p class="qsm-ui-overline">Overline · table headers · 11 / 700 caps</p>
				<p class="qsm-ui-body">Inline code: <code>[qsm quiz=12]</code> <code>%USER_NAME%</code></p>
			</div>
			<div class="kit-spec">System font stack (same as WP admin) so no font download. Monospace for shortcodes, variables and custom CSS.</div>
		<?php qsm_ui_kit_php_example( 'T-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-T-03" data-id="T-03">
		<header class="kit-item-head"><span class="kit-id">T-03</span><h3 class="qsm-ui-h3">Spacing, radius & elevation</h3><span class="kit-where">Everywhere</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<p class="kit-label">Radius</p>
					<div class="qsm-ui-row" style="gap:14px">
						<div style="width:56px;height:56px;border:1px solid var(--qsm-line);border-radius:6px;display:grid;place-items:center;font-size:12px">6</div>
						<div style="width:56px;height:56px;border:1px solid var(--qsm-line);border-radius:8px;display:grid;place-items:center;font-size:12px">8</div>
						<div style="width:56px;height:56px;border:1px solid var(--qsm-line);border-radius:10px;display:grid;place-items:center;font-size:12px">10</div>
						<div style="width:56px;height:56px;border:1px solid var(--qsm-line);border-radius:14px;display:grid;place-items:center;font-size:12px">14</div>
					</div>
					<p class="qsm-ui-caption" style="margin-top:8px">6 chips · 8 controls · 10 small boxes · 14 cards</p>
				</div>
				<div>
					<p class="kit-label">Elevation</p>
					<div class="qsm-ui-row" style="gap:18px">
						<div style="width:90px;height:56px;border-radius:10px;background:#fff;border:1px solid var(--qsm-line);display:grid;place-items:center;font-size:12px">flat</div>
						<div style="width:90px;height:56px;border-radius:10px;background:#fff;box-shadow:var(--qsm-shadow);border:1px solid #e6eaf1;display:grid;place-items:center;font-size:12px">card</div>
						<div style="width:90px;height:56px;border-radius:10px;background:#fff;box-shadow:var(--qsm-shadow-pop);display:grid;place-items:center;font-size:12px">popover</div>
					</div>
				</div>
				<div>
					<p class="kit-label">Control heights</p>
					<p class="qsm-ui-small">42px default · 34px small (tables, toolbars) · 48px large (wizard)</p>
					<p class="qsm-ui-small" style="margin-top:6px">Spacing steps: 4 · 8 · 12 · 16 · 24 · 32</p>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'T-03' ); ?>
	</article>
	<article class="kit-item" id="qsmk-T-04" data-id="T-04">
		<header class="kit-item-head"><span class="kit-id">T-04</span><h3 class="qsm-ui-h3">Icons</h3><span class="kit-where">Buttons, tabs, menus, empty states</span></header>
			<div class="kit-demo">
				<?php qsm_ui_kit_icons(); ?>
			</div>
			<div class="kit-spec">One inline SVG sprite in the style of Lucide (stroke 2, 18px). Replaces the mix of Dashicons and image icons. In PHP: <code>QSM_Admin_UI::icon( 'trash' )</code>.</div>
		<?php qsm_ui_kit_php_example( 'T-04' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-buttons" data-group="Actions">
	<h2 class="qsm-ui-h2">Buttons <small class="qsm-ui-caption"> · actions</small></h2>
	<p class="qsm-ui-small">One primary button per area. Destructive actions are red and ask for confirmation.</p>
	<article class="kit-item" id="qsmk-B-01" data-id="B-01">
		<header class="kit-item-head"><span class="kit-id">B-01</span><h3 class="qsm-ui-h3">Button variants</h3><span class="kit-where">Save, Add question, Cancel, Delete, links</span></header>
			<div class="kit-demo qsm-ui-stack">
				<div class="qsm-ui-row">
					<button class="qsm-ui-btn qsm-ui-btn-primary" type="button">Save changes</button>
					<button class="qsm-ui-btn" type="button">Preview</button>
					<button class="qsm-ui-btn qsm-ui-btn-quiet" type="button">Cancel</button>
					<button class="qsm-ui-btn qsm-ui-btn-danger" type="button">Delete quiz</button>
					<button class="qsm-ui-btn qsm-ui-btn-danger-ghost" type="button">Remove</button>
					<button class="qsm-ui-btn qsm-ui-btn-link" type="button">Learn more</button>
				</div>
				<div class="qsm-ui-row">
					<button class="qsm-ui-btn qsm-ui-btn-primary" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-plus"/></svg>Add question</button>
					<button class="qsm-ui-btn" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-eye"/></svg>Preview</button>
					<button class="qsm-ui-btn" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-download"/></svg>Export CSV</button>
					<a class="qsm-ui-btn qsm-ui-btn-quiet" href="#"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-ext"/></svg>View docs</a>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'B-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-B-02" data-id="B-02">
		<header class="kit-item-head"><span class="kit-id">B-02</span><h3 class="qsm-ui-h3">Sizes, states & icon buttons</h3><span class="kit-where">Toolbars, table rows, question cards</span></header>
			<div class="kit-demo qsm-ui-stack">
				<div class="qsm-ui-row">
					<button class="qsm-ui-btn qsm-ui-btn-primary qsm-ui-btn-sm" type="button">Small</button>
					<button class="qsm-ui-btn qsm-ui-btn-primary" type="button">Default</button>
					<button class="qsm-ui-btn qsm-ui-btn-primary qsm-ui-btn-lg" type="button">Large</button>
					<button class="qsm-ui-btn qsm-ui-btn-primary" type="button" disabled>Disabled</button>
					<button class="qsm-ui-btn qsm-ui-btn-primary" type="button" data-kit-loading>Click to load</button>
					<button class="qsm-ui-btn" type="button" data-kit-loading>Secondary load</button>
				</div>
				<div class="qsm-ui-row">
					<button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button>
					<button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Duplicate"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-dup"/></svg></button>
					<button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Settings"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-settings"/></svg></button>
					<button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button>
					<button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="More"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-more"/></svg></button>
				</div>
			</div>
			<div class="kit-spec">Icon-only buttons always carry an <code>aria-label</code>. Loading state keeps the button width so the layout doesn't jump.</div>
		<?php qsm_ui_kit_php_example( 'B-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-B-03" data-id="B-03">
		<header class="kit-item-head"><span class="kit-id">B-03</span><h3 class="qsm-ui-h3">Button group & split button</h3><span class="kit-where">Save / Save &amp; preview, Publish options</span></header>
			<div class="kit-demo qsm-ui-row" style="gap:24px">
				<div class="qsm-ui-btn-group">
					<button class="qsm-ui-btn" type="button">Day</button>
					<button class="qsm-ui-btn" type="button">Week</button>
					<button class="qsm-ui-btn" type="button">Month</button>
				</div>
				<div class="qsm-ui-menu-wrap">
					<div class="qsm-ui-btn-group">
						<button class="qsm-ui-btn qsm-ui-btn-primary" type="button">Save quiz</button>
						<button class="qsm-ui-btn qsm-ui-btn-primary" type="button" aria-label="More save options" aria-haspopup="menu" data-qsm-ui-menu><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-chev"/></svg></button>
					</div>
					<ul class="qsm-ui-menu" role="menu" hidden>
						<li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-eye"/></svg>Save &amp; preview</button></li>
						<li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-file"/></svg>Save as draft</button></li>
						<li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-dup"/></svg>Save as new quiz</button></li>
					</ul>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'B-03' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-inputs" data-group="Form fields">
	<h2 class="qsm-ui-h2">Text inputs <small class="qsm-ui-caption"> · form fields</small></h2>
	<p class="qsm-ui-small">Every field has a label, optional help text, and an error state. Width classes (<code>qsm-w-sm/md/lg</code>) keep short values from stretching across the screen.</p>
	<article class="kit-item" id="qsmk-F-01" data-id="F-01">
		<header class="kit-item-head"><span class="kit-id">F-01</span><h3 class="qsm-ui-h3">Text, email, URL</h3><span class="kit-where">Quiz name, contact fields, redirect URL, email To/From</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-name">Quiz name <span class="qsm-ui-req">*</span></label>
					<input class="qsm-ui-input" id="qsmk-d-name" type="text" value="Customer Satisfaction Survey">
					<span class="qsm-ui-help">Shown as the quiz title on the front end.</span>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-email">Send results to</label>
					<input class="qsm-ui-input" id="qsmk-d-email" type="email" placeholder="admin@example.com">
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-url">Redirect after submission</label>
					<div class="qsm-ui-input-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-link"/></svg><input class="qsm-ui-input" id="qsmk-d-url" type="url" placeholder="https://example.com/thank-you"></div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-02" data-id="F-02">
		<header class="kit-item-head"><span class="kit-id">F-02</span><h3 class="qsm-ui-h3">Field states</h3><span class="kit-where">All fields: focus, error, success, disabled, read-only</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-s1">Default</label>
					<input class="qsm-ui-input" id="qsmk-d-s1" placeholder="Placeholder text">
				</div>
				<div class="is-invalid">
					<label class="qsm-ui-field-label" for="qsmk-d-s2">Error <span class="qsm-ui-req">*</span></label>
					<input class="qsm-ui-input" id="qsmk-d-s2" value="" aria-invalid="true" aria-describedby="qsmk-d-s2-msg">
					<span class="qsm-ui-field-msg is-error" id="qsmk-d-s2-msg"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-alert"/></svg>Quiz name is required.</span>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-s3">Success</label>
					<input class="qsm-ui-input is-valid" id="qsmk-d-s3" value="QSM-PRO-XXXX-1234">
					<span class="qsm-ui-field-msg is-success"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-ok"/></svg>License is active.</span>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-s4">Disabled</label>
					<input class="qsm-ui-input" id="qsmk-d-s4" value="Locked by add-on" disabled>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-s5">Read-only</label>
					<input class="qsm-ui-input" id="qsmk-d-s5" value="quiz_12" readonly>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-s6">With tooltip <button class="qsm-ui-tip" type="button" data-tip="Leave empty to use the site's admin email.">?</button></label>
					<input class="qsm-ui-input" id="qsmk-d-s6" placeholder="Hover the ?">
				</div>
			</div>
			<div class="kit-spec">Error state is server-rendered too (PHP validation), not only JS. The message is linked with <code>aria-describedby</code>.</div>
		<?php qsm_ui_kit_php_example( 'F-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-03" data-id="F-03">
		<header class="kit-item-head"><span class="kit-id">F-03</span><h3 class="qsm-ui-h3">Prefix / suffix / attached button</h3><span class="kit-where">Time limit (minutes), pass mark (%), price, license key + Activate</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-time">Time limit</label>
					<div class="qsm-ui-input-group qsm-ui-w-sm"><input class="qsm-ui-input" id="qsmk-d-time" type="number" value="30"><span class="qsm-ui-addon">minutes</span></div>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-pass">Pass mark</label>
					<div class="qsm-ui-input-group qsm-ui-w-sm"><input class="qsm-ui-input" id="qsmk-d-pass" type="number" value="70"><span class="qsm-ui-addon">%</span></div>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-price">Price</label>
					<div class="qsm-ui-input-group qsm-ui-w-sm"><span class="qsm-ui-addon">$</span><input class="qsm-ui-input" id="qsmk-d-price" type="number" value="9.99"></div>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-lic">License key</label>
					<div class="qsm-ui-input-group"><span class="qsm-ui-addon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-key"/></svg></span><input class="qsm-ui-input" id="qsmk-d-lic" placeholder="Paste your key"><button class="qsm-ui-btn qsm-ui-btn-primary" type="button" data-kit-loading>Activate</button></div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-03' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-04" data-id="F-04">
		<header class="kit-item-head"><span class="kit-id">F-04</span><h3 class="qsm-ui-h3">Number stepper</h3><span class="kit-where">Questions per page, attempts allowed, points</span></header>
			<div class="kit-demo qsm-ui-row" style="gap:28px">
				<div>
					<span class="qsm-ui-field-label" id="qsmk-d-step-l">Questions per page</span>
					<div class="qsm-ui-stepper" data-qsm-ui-stepper><button type="button" data-step="-1" aria-label="Decrease">−</button><input type="number" min="0" max="50" value="5" aria-labelledby="qsmk-d-step-l"><button type="button" data-step="1" aria-label="Increase">+</button></div>
				</div>
				<div>
					<span class="qsm-ui-field-label" id="qsmk-d-step-l2">Attempts allowed</span>
					<div class="qsm-ui-stepper" data-qsm-ui-stepper><button type="button" data-step="-1" aria-label="Decrease">−</button><input type="number" min="0" max="99" value="0" aria-labelledby="qsmk-d-step-l2"><button type="button" data-step="1" aria-label="Increase">+</button></div>
					<span class="qsm-ui-help">0 = unlimited</span>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-04' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-05" data-id="F-05">
		<header class="kit-item-head"><span class="kit-id">F-05</span><h3 class="qsm-ui-h3">Password / secret</h3><span class="kit-where">API keys for integrations (Mailchimp, AWeber, reCAPTCHA)</span></header>
			<div class="kit-demo">
				<label class="qsm-ui-field-label" for="qsmk-d-pw">API key</label>
				<div class="qsm-ui-input-icon qsm-ui-w-lg"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-lock"/></svg><input class="qsm-ui-input" id="qsmk-d-pw" type="password" value="sk_live_51Hx9example" style="padding-right:44px"><button class="qsm-ui-btn qsm-ui-btn-icon qsm-ui-trail" type="button" aria-label="Show key" data-qsm-ui-reveal="#qsmk-d-pw"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-eye"/></svg></button></div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-05' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-06" data-id="F-06">
		<header class="kit-item-head"><span class="kit-id">F-06</span><h3 class="qsm-ui-h3">Textarea with counter</h3><span class="kit-where">Quiz description, question hint, answer explanation</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-field-head"><label class="qsm-ui-field-label" for="qsmk-d-ta">Hint</label><span class="qsm-ui-counter" data-qsm-ui-counter-for="qsmk-d-ta" data-max="160">0 / 160</span></div>
				<textarea class="qsm-ui-textarea" id="qsmk-d-ta" maxlength="200">Think about which option saves the most time.</textarea>
			</div>
		<?php qsm_ui_kit_php_example( 'F-06' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-07" data-id="F-07">
		<header class="kit-item-head"><span class="kit-id">F-07</span><h3 class="qsm-ui-h3">Search field</h3><span class="kit-where">Quiz list, results, question bank, add-ons</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-input-icon qsm-ui-w-md"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-search"/></svg><input class="qsm-ui-input" type="search" placeholder="Search quizzes…" aria-label="Search quizzes"></div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-07' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-08" data-id="F-08">
		<header class="kit-item-head"><span class="kit-id">F-08</span><h3 class="qsm-ui-h3">Copy field (shortcode)</h3><span class="kit-where">Quiz shortcode, link shortcode, webhook URL</span></header>
			<div class="kit-demo kit-demo-grid">
				<div class="qsm-ui-copy-field">
					<label class="qsm-ui-field-label" for="qsmk-d-sc">Shortcode</label>
					<div class="qsm-ui-input-group"><input class="qsm-ui-input" id="qsmk-d-sc" value='[qsm quiz=12]' readonly><button class="qsm-ui-btn" type="button" data-qsm-ui-copy="#qsmk-d-sc"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-copy"/></svg>Copy</button></div>
				</div>
				<div class="qsm-ui-copy-field">
					<label class="qsm-ui-field-label" for="qsmk-d-sc2">Link shortcode</label>
					<div class="qsm-ui-input-group"><input class="qsm-ui-input" id="qsmk-d-sc2" value='[qsm_link id=12]Take the quiz[/qsm_link]' readonly><button class="qsm-ui-btn" type="button" data-qsm-ui-copy="#qsmk-d-sc2"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-copy"/></svg>Copy</button></div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-08' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-09" data-id="F-09">
		<header class="kit-item-head"><span class="kit-id">F-09</span><h3 class="qsm-ui-h3">Code editor (custom CSS)</h3><span class="kit-where">Style tab → Custom CSS, Settings → custom JS</span></header>
			<div class="kit-demo">
				<label class="qsm-ui-field-label" for="qsmk-d-css">Custom CSS</label>
				<textarea class="qsm-ui-textarea is-code" id="qsmk-d-css" spellcheck="false">.qsm-quiz-container {
  max-width: 720px;
  border-radius: 12px;
}</textarea>
				<span class="qsm-ui-help">In WP this is wrapped with <code>wp_enqueue_code_editor()</code> (CodeMirror) for line numbers and highlighting.</span>
			</div>
		<?php qsm_ui_kit_php_example( 'F-09' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-select" data-group="Form fields">
	<h2 class="qsm-ui-h2">Selection controls <small class="qsm-ui-caption"> · form fields</small></h2>
	<p class="qsm-ui-small">Use a toggle for on/off settings, radio cards when each option needs an explanation, and a searchable select when the list is long (pages, quizzes, categories).</p>
	<article class="kit-item" id="qsmk-F-10" data-id="F-10">
		<header class="kit-item-head"><span class="kit-id">F-10</span><h3 class="qsm-ui-h3">Toggle switch</h3><span class="kit-where">All yes/no quiz options (most of the Options tab)</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-switch-list">
					<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" checked><span class="qsm-ui-switch-copy"><strong>Randomize questions</strong><span>Show questions in a different order for each attempt.</span></span></label>
					<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox"><span class="qsm-ui-switch-copy"><strong>Show progress bar</strong><span>Display how far the user is through the quiz.</span></span></label>
					<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" checked disabled><span class="qsm-ui-switch-copy"><strong>Save results to database</strong><span>Disabled: required by the Analytics add-on.</span></span></label>
					<label class="qsm-ui-switch-line"><input class="qsm-ui-switch qsm-ui-switch-sm" type="checkbox" checked><span class="qsm-ui-switch-copy"><strong>Small switch</strong><span>For tables and dense lists.</span></span></label>
				</div>
			</div>
			<div class="kit-spec">Saves immediately on change? <strong>No</strong>: toggles save with the screen's existing Save button, as today (X-01). The sticky save bar (L-09) is on hold.</div>
		<?php qsm_ui_kit_php_example( 'F-10' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-11" data-id="F-11">
		<header class="kit-item-head"><span class="kit-id">F-11</span><h3 class="qsm-ui-h3">Checkbox & checkbox group</h3><span class="kit-where">Contact field options, result columns to export, user roles allowed</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<span class="qsm-ui-field-label">Who can take this quiz</span>
					<label class="qsm-ui-check"><input type="checkbox" checked><span>Logged-in users</span></label>
					<label class="qsm-ui-check"><input type="checkbox"><span>Guests<small>Visitors who aren't logged in</small></span></label>
					<label class="qsm-ui-check"><input type="checkbox" data-qsm-ui-indeterminate><span>Specific roles (partial)</span></label>
					<label class="qsm-ui-check"><input type="checkbox" disabled><span>Disabled option</span></label>
				</div>
				<div>
					<span class="qsm-ui-field-label">Inline group</span>
					<div class="qsm-ui-inline-choices">
						<label class="qsm-ui-check"><input type="checkbox" checked><span>Name</span></label>
						<label class="qsm-ui-check"><input type="checkbox" checked><span>Email</span></label>
						<label class="qsm-ui-check"><input type="checkbox"><span>Phone</span></label>
						<label class="qsm-ui-check"><input type="checkbox"><span>Business</span></label>
					</div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-11' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-12" data-id="F-12">
		<header class="kit-item-head"><span class="kit-id">F-12</span><h3 class="qsm-ui-h3">Radio buttons</h3><span class="kit-where">Short option lists (2–4 options)</span></header>
			<div class="kit-demo">
				<span class="qsm-ui-field-label">Show correct answers</span>
				<div class="qsm-ui-inline-choices">
					<label class="qsm-ui-check"><input type="radio" name="d-r1" checked><span>Never</span></label>
					<label class="qsm-ui-check"><input type="radio" name="d-r1"><span>After each question</span></label>
					<label class="qsm-ui-check"><input type="radio" name="d-r1"><span>On the results page</span></label>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-12' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-13" data-id="F-13">
		<header class="kit-item-head"><span class="kit-id">F-13</span><h3 class="qsm-ui-h3">Radio cards</h3><span class="kit-where">Quiz type, grading system, submit behaviour, form type</span></header>
			<div class="kit-demo qsm-ui-stack">
				<div class="qsm-ui-radio-cards">
					<label class="qsm-ui-radio-card"><input type="radio" name="d-rc" checked><span><strong>Correct / incorrect</strong><small>Each question has right answers. Score is a percentage.</small></span></label>
					<label class="qsm-ui-radio-card"><input type="radio" name="d-rc"><span><strong>Points</strong><small>Each answer is worth points. Score is the total.</small></span></label>
					<label class="qsm-ui-radio-card"><input type="radio" name="d-rc"><span><strong>Both</strong><small>Track correct answers and points together.</small></span></label>
				</div>
				<div class="qsm-ui-radio-cards">
					<label class="qsm-ui-radio-card"><span class="qsm-ui-rc-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-quiz"/></svg></span><span style="flex:1"><strong>Quiz</strong><small>Graded, with a score</small></span><input type="radio" name="d-rc2" checked></label>
					<label class="qsm-ui-radio-card"><span class="qsm-ui-rc-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-survey"/></svg></span><span style="flex:1"><strong>Survey</strong><small>No right answers</small></span><input type="radio" name="d-rc2"></label>
					<label class="qsm-ui-radio-card"><span class="qsm-ui-rc-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-form"/></svg></span><span style="flex:1"><strong>Simple form</strong><small>Collect information only</small></span><input type="radio" name="d-rc2"></label>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-13' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-15" data-id="F-15">
		<header class="kit-item-head"><span class="kit-id">F-15</span><h3 class="qsm-ui-h3">Segmented control</h3><span class="kit-where">Small mode switches: Desktop/Mobile preview, Score/Points, Visual/HTML</span></header>
			<div class="kit-demo qsm-ui-row" style="gap:24px">
				<div class="qsm-ui-segmented" role="radiogroup" aria-label="Preview size">
					<label><input type="radio" name="d-seg" checked><span>Desktop</span></label>
					<label><input type="radio" name="d-seg"><span>Tablet</span></label>
					<label><input type="radio" name="d-seg"><span>Mobile</span></label>
				</div>
				<div class="qsm-ui-segmented" role="radiogroup" aria-label="Threshold basis">
					<label><input type="radio" name="d-seg2" checked><span>Score %</span></label>
					<label><input type="radio" name="d-seg2"><span>Points</span></label>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-15' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-16" data-id="F-16">
		<header class="kit-item-head"><span class="kit-id">F-16</span><h3 class="qsm-ui-h3">Native select</h3><span class="kit-where">Short fixed lists: time unit, email format, results per page</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-sel">Pagination</label>
					<select class="qsm-ui-select qsm-ui-w-md" id="qsmk-d-sel"><option>One question per page</option><option>All questions on one page</option><option>Custom pages</option></select>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-sel2">Disabled</label>
					<select class="qsm-ui-select qsm-ui-w-md" id="qsmk-d-sel2" disabled><option>Not available</option></select>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-16' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-17" data-id="F-17">
		<header class="kit-item-head"><span class="kit-id">F-17</span><h3 class="qsm-ui-h3">Searchable select</h3><span class="kit-where">Page selector, quiz selector, category, FunnelKit list (replaces Select2 look)</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<span class="qsm-ui-field-label" id="qsmk-d-combo1-l">Results page</span>
					<select class="qsm-ui-select" id="qsmk-d-combo1" aria-labelledby="qsmk-d-combo1-l" data-kit-select2>
						<optgroup label="Pages"><option>Thank you</option><option selected>Quiz results</option><option>Home</option><option>About us</option></optgroup>
						<optgroup label="Posts"><option>Welcome to our quiz</option><option>Your score explained</option></optgroup>
					</select>
				</div>
			</div>
			<div class="kit-spec">This is Select2, started with <code>QSMAdminUI.select2( $select, options )</code>, which adds the opt-in <code>qsm-ui-select2</code> class (X-04). AJAX search keeps working. Grouped options and a secondary label are supported.</div>
		<?php qsm_ui_kit_php_example( 'F-17' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-25" data-id="F-25">
		<header class="kit-item-head"><span class="kit-id">F-25</span><h3 class="qsm-ui-h3">Category tree (sub-categories)</h3><span class="kit-where">Question editor → Category, question bank filter, Add new category</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<span class="qsm-ui-field-label" id="qsmk-d-tree-l">Category <small class="qsm-ui-caption">(question editor)</small></span>
					<div class="qsm-ui-tree-picker">
						<div class="qsm-ui-input-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-search"/></svg><input class="qsm-ui-input qsm-ui-input-sm" type="search" placeholder="Search categories…" aria-label="Search categories"></div>
						<ul class="qsm-ui-tree" aria-labelledby="qsmk-d-tree-l">
							<li><div class="qsm-ui-tree-row"><button class="qsm-ui-tree-toggle" type="button" aria-expanded="true" aria-label="Collapse"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-chev"/></svg></button><label class="qsm-ui-check"><input type="checkbox" value="3"><span>Product</span></label><span class="qsm-ui-tree-count">14</span></div>
								<ul>
									<li><div class="qsm-ui-tree-row"><span class="qsm-ui-tree-spacer"></span><label class="qsm-ui-check"><input type="checkbox" value="7" checked><span>Pricing</span></label><span class="qsm-ui-tree-count">6</span></div></li>
									<li><div class="qsm-ui-tree-row"><button class="qsm-ui-tree-toggle" type="button" aria-expanded="true" aria-label="Collapse"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-chev"/></svg></button><label class="qsm-ui-check"><input type="checkbox" value="8"><span>Features</span></label><span class="qsm-ui-tree-count">8</span></div>
										<ul>
											<li><div class="qsm-ui-tree-row"><span class="qsm-ui-tree-spacer"></span><label class="qsm-ui-check"><input type="checkbox" value="12" checked><span>Reporting</span></label><span class="qsm-ui-tree-count">3</span></div></li>
											<li><div class="qsm-ui-tree-row"><span class="qsm-ui-tree-spacer"></span><label class="qsm-ui-check"><input type="checkbox" value="13"><span>Integrations</span></label><span class="qsm-ui-tree-count">5</span></div></li>
										</ul></li>
								</ul></li>
							<li><div class="qsm-ui-tree-row"><button class="qsm-ui-tree-toggle" type="button" aria-expanded="true" aria-label="Collapse"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-chev"/></svg></button><label class="qsm-ui-check"><input type="checkbox" value="4"><span>Service</span></label><span class="qsm-ui-tree-count">9</span></div>
								<ul>
									<li><div class="qsm-ui-tree-row"><span class="qsm-ui-tree-spacer"></span><label class="qsm-ui-check"><input type="checkbox" value="9"><span>Support</span></label><span class="qsm-ui-tree-count">5</span></div></li>
									<li><div class="qsm-ui-tree-row"><span class="qsm-ui-tree-spacer"></span><label class="qsm-ui-check"><input type="checkbox" value="10"><span>Onboarding</span></label><span class="qsm-ui-tree-count">4</span></div></li>
								</ul></li>
							<li><div class="qsm-ui-tree-row"><span class="qsm-ui-tree-spacer"></span><label class="qsm-ui-check"><input type="checkbox" value="5"><span>Satisfaction</span></label><span class="qsm-ui-tree-count">11</span></div></li>
						</ul>
						<div class="qsm-ui-tree-foot">
							<div class="qsm-ui-row" style="gap:6px;flex:1;flex-wrap:wrap" data-kit-tree-chips></div>
							<button class="qsm-ui-btn qsm-ui-btn-link" type="button" data-kit-tree-add-toggle><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>Add category</button>
							<div class="qsm-ui-tree-add" data-kit-tree-add hidden>
								<input class="qsm-ui-input qsm-ui-input-sm" placeholder="Category name" aria-label="New category name" data-kit-tree-new>
								<select class="qsm-ui-select qsm-ui-input-sm" aria-label="Parent category" data-kit-tree-parent><option value="">No parent</option></select>
								<button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-primary" type="button" data-kit-tree-add-save>Add</button>
							</div>
						</div>
					</div>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-tree-filter">Category <small class="qsm-ui-caption">(filter dropdown)</small></label>
					<select class="qsm-ui-select" id="qsmk-d-tree-filter">
						<option value="">All categories</option>
						<option>Product</option>
						<option>&nbsp;&nbsp;&nbsp;— Pricing</option>
						<option>&nbsp;&nbsp;&nbsp;— Features</option>
						<option>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;— Reporting</option>
						<option>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;— Integrations</option>
						<option>Service</option>
						<option>&nbsp;&nbsp;&nbsp;— Support</option>
						<option>&nbsp;&nbsp;&nbsp;— Onboarding</option>
						<option>Satisfaction</option>
					</select>
					<label class="qsm-ui-check" style="margin-top:10px"><input type="checkbox" checked><span>Include sub-categories<small>Filtering by “Product” also shows Pricing, Features, Reporting…</small></span></label>
					<p class="qsm-ui-caption" style="margin-top:14px">Where a category is shown as text (question rows, results), it uses the full path: <span class="qsm-ui-cat-chip">Product › Features › Reporting</span></p>
				</div>
			</div>
			<div class="kit-spec">Answers the F-18 question. Sub-categories use the existing <code>qsm_category</code> taxonomy, term IDs and parent field, so how categories are saved doesn't change. The editor picker is today's <code>wp_terms_checklist</code> tree (with its search box) restyled, plus collapse, a search that keeps the parents of matches visible, and a quick “Add category” with a Parent dropdown, like the current Add New Category popup. Ticking a child doesn't tick its parent, same as today.</div>
		<?php qsm_ui_kit_php_example( 'F-25' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-special" data-group="Form fields">
	<h2 class="qsm-ui-h2">Specialised inputs <small class="qsm-ui-caption"> · form fields</small></h2>
	<p class="qsm-ui-small">Inputs QSM needs beyond the basics: dates for scheduling, colours and sliders for styling, media for questions, and template variables for emails and results.</p>
	<article class="kit-item" id="qsmk-F-19" data-id="F-19">
		<header class="kit-item-head"><span class="kit-id">F-19</span><h3 class="qsm-ui-h3">Range slider</h3><span class="kit-where">Style tab: font size, border radius, spacing; rating scale</span></header>
			<div class="kit-demo">
				<label class="qsm-ui-field-label" for="qsmk-d-range">Button radius</label>
				<div class="qsm-ui-range qsm-ui-w-lg"><input type="range" id="qsmk-d-range" min="0" max="30" value="8"><output for="qsmk-d-range" data-unit="px">8px</output></div>
			</div>
		<?php qsm_ui_kit_php_example( 'F-19' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-20" data-id="F-20">
		<header class="kit-item-head"><span class="kit-id">F-20</span><h3 class="qsm-ui-h3">Date, time & schedule</h3><span class="kit-where">Quiz start / end date, results date filter</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-date">Start date</label>
					<div class="qsm-ui-input-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-cal"/></svg><input class="qsm-ui-input" id="qsmk-d-date" type="date" value="2026-11-01"></div>
				</div>
				<div>
					<label class="qsm-ui-field-label" for="qsmk-d-dt">Closes at</label>
					<input class="qsm-ui-input" id="qsmk-d-dt" type="datetime-local" value="2026-11-30T18:00">
				</div>
				<div>
					<span class="qsm-ui-field-label">Date range</span>
					<div class="qsm-ui-row" style="flex-wrap:nowrap"><input class="qsm-ui-input" type="date" aria-label="From" value="2026-10-01"><span class="qsm-ui-small">to</span><input class="qsm-ui-input" type="date" aria-label="To" value="2026-10-27"></div>
				</div>
			</div>
			<div class="kit-spec">Native date inputs (no jQuery datetimepicker). Times are shown in the site timezone; say so in help text.</div>
		<?php qsm_ui_kit_php_example( 'F-20' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-21" data-id="F-21">
		<header class="kit-item-head"><span class="kit-id">F-21</span><h3 class="qsm-ui-h3">Colour picker</h3><span class="kit-where">Style tab: primary colour, text, background, buttons</span></header>
			<div class="kit-demo qsm-ui-row" style="gap:28px;align-items:flex-start">
				<div><span class="qsm-ui-field-label">Primary colour</span><span class="qsm-ui-color"><input type="text" class="qsm-ui-kit-color" value="#315efb" data-alpha-enabled="true" aria-label="Primary colour"></span></div>
				<div><span class="qsm-ui-field-label">Background</span><span class="qsm-ui-color"><input type="text" class="qsm-ui-kit-color" value="#ffffff" data-alpha-enabled="true" aria-label="Background"></span></div>
			</div>
			<div class="kit-spec">The real WordPress colour picker (wp-color-picker-alpha, as today). Only its trigger is restyled, and only when Core wraps it in <code>&lt;span class="qsm-ui-color"&gt;</code>; the picker panel stays WordPress's own.</div>
		<?php qsm_ui_kit_php_example( 'F-21' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-23" data-id="F-23">
		<header class="kit-item-head"><span class="kit-id">F-23</span><h3 class="qsm-ui-h3">File drop zone</h3><span class="kit-where">Import quizzes / questions (CSV, JSON), Tools page</span></header>
			<div class="kit-demo">
				<label class="qsm-ui-dropzone">
					<svg aria-hidden="true" focusable="false" class="qsm-ui-icon" style="width:28px;height:28px"><use href="#qsm-ui-i-upload"/></svg>
					<span><strong>Drop a file here</strong> or click to browse</span>
					<span class="qsm-ui-caption">CSV or JSON, up to 10 MB</span>
					<input type="file" class="qsm-ui-sr" accept=".csv,.json" multiple>
				</label>

			</div>
		<?php qsm_ui_kit_php_example( 'F-23' ); ?>
	</article>
	<article class="kit-item" id="qsmk-F-24" data-id="F-24">
		<header class="kit-item-head"><span class="kit-id">F-24</span><h3 class="qsm-ui-h3">Rich text editor + template variables</h3><span class="kit-where">Text tab, Emails, Results pages, question text</span></header>
			<div class="kit-demo qsm-ui-stack">
				<div>
					<div class="qsm-ui-field-head"><span class="qsm-ui-field-label">Results page message</span>
						<div class="qsm-ui-var-picker" data-qsm-ui-var-target="qsmkeditor">
							<button class="qsm-ui-btn qsm-ui-btn-sm" type="button" data-qsm-ui-var-toggle aria-expanded="false"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-var"/></svg>Insert variable</button>
							<div class="qsm-ui-var-panel" hidden>
								<header><div class="qsm-ui-input-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-search"/></svg><input class="qsm-ui-input qsm-ui-input-sm" type="search" placeholder="Search variables…" aria-label="Search variables"></div></header>
								<ul>
									<li class="qsm-ui-var-group">User</li>
									<li><button type="button" data-qsm-ui-var="%USER_NAME%"><code>%USER_NAME%</code><small>Name the user entered</small></button></li>
									<li><button type="button" data-qsm-ui-var="%USER_EMAIL%"><code>%USER_EMAIL%</code><small>Email the user entered</small></button></li>
									<li class="qsm-ui-var-group">Score</li>
									<li><button type="button" data-qsm-ui-var="%CORRECT_SCORE%"><code>%CORRECT_SCORE%</code><small>Score as a percentage</small></button></li>
									<li><button type="button" data-qsm-ui-var="%POINT_SCORE%"><code>%POINT_SCORE%</code><small>Total points earned</small></button></li>
									<li><button type="button" data-qsm-ui-var="%AMOUNT_CORRECT%"><code>%AMOUNT_CORRECT%</code><small>Number of correct answers</small></button></li>
									<li class="qsm-ui-var-group">Quiz</li>
									<li><button type="button" data-qsm-ui-var="%QUIZ_NAME%"><code>%QUIZ_NAME%</code><small>Name of the quiz</small></button></li>
									<li><button type="button" data-qsm-ui-var="%QUESTIONS_ANSWERS%"><code>%QUESTIONS_ANSWERS%</code><small>Every question with the user's answer</small></button></li>
								</ul>
							</div>
						</div>
					</div>
					<?php qsm_ui_kit_editor(); ?>
				</div>
				<div class="qsm-ui-var-note">Variables are replaced when the result is shown. <button class="qsm-ui-btn-link qsm-ui-btn" type="button">See all 60+ variables</button></div>
			</div>
			<div class="kit-spec">The editor is the real <code>wp_editor()</code> / TinyMCE inside <code>.qsm-ui-editor</code>; only its frame is restyled. The picker inserts into it (or into a textarea) through <code>data-qsm-ui-var-target</code>, and fires <code>qsm-ui:insert-variable</code> first so a screen can handle it itself. The variable picker replaces today's long variable list shown next to every editor.</div>
		<?php qsm_ui_kit_php_example( 'F-24' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-layout" data-group="Layout">
	<h2 class="qsm-ui-h2">Layout &amp; navigation <small class="qsm-ui-caption"> · structure</small></h2>
	<p class="qsm-ui-small">How a screen is put together: header, tabs, cards and setting rows. Each screen keeps its existing Save button (X-01).</p>
	<article class="kit-item" id="qsmk-L-01" data-id="L-01">
		<header class="kit-item-head"><span class="kit-id">L-01</span><h3 class="qsm-ui-h3">Page header</h3><span class="kit-where">Every admin page; quiz edit header with shortcode + status</span></header>
			<div class="kit-demo is-canvas">
				<nav class="qsm-ui-breadcrumb" aria-label="Breadcrumb"><a href="#">Quizzes</a><span>/</span><span>Customer Satisfaction Survey</span></nav>
				<div class="qsm-ui-page-header" style="margin:0">
					<div>
						<div class="qsm-ui-row"><h2 class="qsm-ui-h1">Customer Satisfaction Survey</h2><span class="qsm-ui-status">Published</span></div>
						<p class="qsm-ui-subtitle">ID 12 · Last edited 5 minutes ago · <code>[qsm quiz=12]</code></p>
					</div>
					<div class="qsm-ui-page-actions">
						<button class="qsm-ui-btn" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-eye"/></svg>Preview</button>
						<button class="qsm-ui-btn qsm-ui-btn-primary" type="button">Save changes</button>
					</div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'L-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-02" data-id="L-02">
		<header class="kit-item-head"><span class="kit-id">L-02</span><h3 class="qsm-ui-h3">Quiz edit tabs</h3><span class="kit-where">Questions · Contact · Text · Options · Emails · Results · Style · add-on tabs</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-tabs" role="tablist" data-qsm-ui-tabs>
					<button class="qsm-ui-tab" role="tab" aria-selected="true" aria-controls="qsmk-tp-1" id="qsmk-t-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-quiz"/></svg>Questions <span class="qsm-ui-count">12</span></button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-2" id="qsmk-t-2" tabindex="-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-users"/></svg>Contact</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-3" id="qsmk-t-3" tabindex="-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-text"/></svg>Text</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-4" id="qsmk-t-4" tabindex="-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-settings"/></svg>Options</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-5" id="qsmk-t-5" tabindex="-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-mail"/></svg>Emails <span class="qsm-ui-count">2</span></button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-6" id="qsmk-t-6" tabindex="-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-chart"/></svg>Results pages</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-7" id="qsmk-t-7" tabindex="-1"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-palette"/></svg>Style</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-8" id="qsmk-t-8" tabindex="-1">FunnelKit</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-9" id="qsmk-t-9" tabindex="-1">Logic</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-10" id="qsmk-t-10" tabindex="-1">Certificate</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-tp-11" id="qsmk-t-11" tabindex="-1">Mailchimp</button>
				</div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-1" aria-labelledby="qsmk-t-1"><p class="qsm-ui-small">Questions panel. Arrow keys move between tabs.</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-2" aria-labelledby="qsmk-t-2" hidden><p class="qsm-ui-small">Contact panel</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-3" aria-labelledby="qsmk-t-3" hidden><p class="qsm-ui-small">Text panel</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-4" aria-labelledby="qsmk-t-4" hidden><p class="qsm-ui-small">Options panel</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-5" aria-labelledby="qsmk-t-5" hidden><p class="qsm-ui-small">Emails panel</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-6" aria-labelledby="qsmk-t-6" hidden><p class="qsm-ui-small">Results pages panel</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-7" aria-labelledby="qsmk-t-7" hidden><p class="qsm-ui-small">Style panel</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-8" aria-labelledby="qsmk-t-8" hidden><p class="qsm-ui-small">FunnelKit add-on panel: its own UI, unchanged.</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-9" aria-labelledby="qsmk-t-9" hidden><p class="qsm-ui-small">Logic add-on panel: its own UI, unchanged.</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-10" aria-labelledby="qsmk-t-10" hidden><p class="qsm-ui-small">Certificate add-on panel: its own UI, unchanged.</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-tp-11" aria-labelledby="qsmk-t-11" hidden><p class="qsm-ui-small">Mailchimp add-on panel: its own UI, unchanged.</p></div>
			</div>
			<div class="kit-spec">Add-on tabs stay in the tab bar as today, after the Core tabs. Tabs registered through <code>register_quiz_settings_tabs()</code> appear there with the same order, slugs and URLs. When there are many tabs, the bar wraps onto a second row instead of hiding any. Only the tab buttons get the new look; each add-on's panel content stays as it is today.</div>
		<?php qsm_ui_kit_php_example( 'L-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-03" data-id="L-03">
		<header class="kit-item-head"><span class="kit-id">L-03</span><h3 class="qsm-ui-h3">Sub-tabs (pill)</h3><span class="kit-where">Options tab sections, Text tab groups, Results filters</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-tabs qsm-ui-tabs-pill" role="tablist" data-qsm-ui-tabs>
					<button class="qsm-ui-tab" role="tab" aria-selected="true" aria-controls="qsmk-sp-1" id="qsmk-st-1">General</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-sp-2" id="qsmk-st-2" tabindex="-1">Display</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-sp-3" id="qsmk-st-3" tabindex="-1">Submission</button>
					<button class="qsm-ui-tab" role="tab" aria-selected="false" aria-controls="qsmk-sp-4" id="qsmk-st-4" tabindex="-1">Advanced</button>
				</div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-sp-1" aria-labelledby="qsmk-st-1"><p class="qsm-ui-small">General options…</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-sp-2" aria-labelledby="qsmk-st-2" hidden><p class="qsm-ui-small">Display options…</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-sp-3" aria-labelledby="qsmk-st-3" hidden><p class="qsm-ui-small">Submission options…</p></div>
				<div class="qsm-ui-tab-panel" role="tabpanel" id="qsmk-sp-4" aria-labelledby="qsmk-st-4" hidden><p class="qsm-ui-small">Advanced options…</p></div>
			</div>
		<?php qsm_ui_kit_php_example( 'L-03' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-04" data-id="L-04">
		<header class="kit-item-head"><span class="kit-id">L-04</span><h3 class="qsm-ui-h3">Side navigation</h3><span class="kit-where">Global Settings, Tools, Add-on settings page</span></header>
			<div class="kit-demo is-canvas">
				<div class="qsm-ui-sidenav-layout">
					<nav class="qsm-ui-sidenav" aria-label="Settings sections">
						<a href="#" aria-current="page"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-settings"/></svg>General</a>
						<a href="#"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-quiz"/></svg>Quiz defaults</a>
						<a href="#"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-mail"/></svg>Emails</a>
						<a href="#"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-lock"/></svg>Security</a>
						<span class="qsm-ui-overline">Add-ons</span>
						<a href="#"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-bolt"/></svg>FunnelKit</a>
						<a href="#"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-bolt"/></svg>Mailchimp</a>
					</nav>
					<div class="qsm-ui-card"><div class="qsm-ui-card-head"><div><h3 class="qsm-ui-h3">General</h3><p>Settings that apply to every quiz.</p></div></div><div class="qsm-ui-card-body"><p class="qsm-ui-small">Content area…</p></div></div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'L-04' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-05" data-id="L-05">
		<header class="kit-item-head"><span class="kit-id">L-05</span><h3 class="qsm-ui-h3">Card</h3><span class="kit-where">Groups related settings on every screen</span></header>
			<div class="kit-demo is-canvas">
				<div class="qsm-ui-card">
					<div class="qsm-ui-card-head">
						<div><h3 class="qsm-ui-h3">Timer</h3><p>Limit how long users have to finish the quiz.</p></div>
						<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" checked data-qsm-ui-controls="#qsmk-d-card-body"><span class="qsm-ui-small" style="font-weight:600;color:var(--qsm-ink)">Enabled</span></label>
					</div>
					<div class="qsm-ui-card-body" id="qsmk-d-card-body">
						<div class="qsm-ui-setting-row">
							<div class="qsm-ui-setting-label">Time limit<span class="qsm-ui-help">0 = no limit</span></div>
							<div><div class="qsm-ui-input-group qsm-ui-w-sm"><input class="qsm-ui-input" type="number" value="30" aria-label="Time limit"><span class="qsm-ui-addon">minutes</span></div></div>
						</div>
					</div>
					<div class="qsm-ui-card-foot"><button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-quiet" type="button">Reset</button><button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-primary" type="button">Save</button></div>
				</div>
			</div>
			<div class="kit-spec">The header switch can enable/disable everything in the card (body greys out, as in FunnelKit). The card footer is optional; most screens keep their existing Save button (X-01).</div>
		<?php qsm_ui_kit_php_example( 'L-05' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-06" data-id="L-06">
		<header class="kit-item-head"><span class="kit-id">L-06</span><h3 class="qsm-ui-h3">Setting row</h3><span class="kit-where">Options tab, global settings, every add-on tab</span></header>
			<div class="kit-demo is-canvas">
				<div class="qsm-ui-card"><div class="qsm-ui-card-body">
					<div class="qsm-ui-setting-row">
						<div class="qsm-ui-setting-label is-top">Form type</div>
						<div class="qsm-ui-row" style="gap:16px;flex-wrap:wrap"><label class="qsm-ui-check"><input type="radio" name="form_type" value="0" checked><span>Quiz</span></label><label class="qsm-ui-check"><input type="radio" name="form_type" value="1"><span>Survey</span></label><label class="qsm-ui-check"><input type="radio" name="form_type" value="2"><span>Simple form</span></label></div>
					</div>
					<div class="qsm-ui-setting-row" data-show-if="form_type:0">
						<div class="qsm-ui-setting-label"><label for="qsmk-d-l06-grade">Grading system</label><span class="qsm-ui-help">Quiz only</span></div>
						<div><select class="qsm-ui-select qsm-ui-w-md" id="qsmk-d-l06-grade" name="system"><option value="0">Correct / Incorrect</option><option value="1">Points</option><option value="3">Both</option></select></div>
					</div>
					<div class="qsm-ui-setting-row" data-show-if="form_type:0;system:!1">
						<div class="qsm-ui-setting-label is-top">Correct answer logic<span class="qsm-ui-help">Quiz, and grading is not Points only</span></div>
						<div class="qsm-ui-stack"><label class="qsm-ui-check"><input type="radio" name="correct_answer_logic" value="1"><span>Accept all correct answers</span></label><label class="qsm-ui-check"><input type="radio" name="correct_answer_logic" value="0" checked><span>Accept any correct answers</span></label></div>
					</div>
					<div class="qsm-ui-setting-row">
						<div class="qsm-ui-setting-label"><label for="qsmk-d-l06-qpp">Questions per page</label></div>
						<div><input class="qsm-ui-input qsm-ui-w-sm" id="qsmk-d-l06-qpp" name="pagination" type="number" min="0" step="1" value="0"><span class="qsm-ui-help">0 = use the pages built on the Questions tab. Same field and saved value as today.</span></div>
					</div>
					<div class="qsm-ui-setting-row">
						<div class="qsm-ui-setting-label is-top">Pages</div>
						<div class="qsm-ui-switch-list">
							<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" name="enable_pagination_quiz"><span class="qsm-ui-switch-copy"><strong>Show current page number</strong><span>Always available: pages can come from Questions per page or from the Questions tab.</span></span></label>
							<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" name="progress_bar"><span class="qsm-ui-switch-copy"><strong>Show progress bar</strong></span></label>
						</div>
					</div>
					<div class="qsm-ui-setting-row">
						<div class="qsm-ui-setting-label"><label for="qsmk-d-l06-total">Limit number of questions</label></div>
						<div><input class="qsm-ui-input qsm-ui-w-sm" id="qsmk-d-l06-total" name="question_from_total" type="number" min="0" value="0"><span class="qsm-ui-help">0 = all questions</span></div>
					</div>
					<div class="qsm-ui-setting-row" data-enable-if="question_from_total:!0">
						<div class="qsm-ui-setting-label">By category</div>
						<div><label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" name="limit_category_checkbox"><span class="qsm-ui-switch-copy"><strong>Limit questions per category</strong><span class="qsm-ui-rule-note">Set “Limit number of questions” above 0 to use this.</span></span></label></div>
					</div>
					<div class="qsm-ui-setting-row">
						<div class="qsm-ui-setting-label"><label for="qsmk-d-l06-end">End date</label></div>
						<div><input class="qsm-ui-input qsm-ui-w-md" id="qsmk-d-l06-end" name="scheduled_time_end" type="datetime-local"></div>
					</div>
					<div class="qsm-ui-setting-row" data-enable-if="scheduled_time_end:!0">
						<div class="qsm-ui-setting-label">After the end date</div>
						<div><label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" name="not_allow_after_expired_time"><span class="qsm-ui-switch-copy"><strong>Do not allow quiz submission after the end date/time</strong><span class="qsm-ui-rule-note">Set an end date to use this.</span></span></label></div>
					</div>
				</div></div>
			</div>
			<div class="kit-spec"><p style="margin-top:0">Try it: pick Survey, pick Points, set a question limit, set an end date. The current page number no longer depends on Questions per page: QSM also makes pages on the Questions tab, so it is always shown.</p>
				<p>Two kinds of rule, both written on the row and handled by one script for every field type:</p>
				<ul>
					<li><b>Hide</b> (<code>data-show-if</code>): the row only shows when the rule matches. Hidden rows stay in the form, so saved values are kept.</li>
					<li><b>Disable</b> (<code>data-enable-if</code>): the row stays visible but greyed out with a one-line reason. This matches what QSM does today for the end date and the question limit.</li>
				</ul>
				<p>A rule can need more than one setting (<code>form_type:0;system:!1</code> means Quiz and not Points); <code>!0</code> means set, or not 0.</p>
				<p><b>Today's conditions, each carried over unchanged:</b></p>
				<table class="qsm-ui-table"><thead><tr><th>Setting</th><th>Depends on</th><th>Effect</th></tr></thead><tbody>
					<tr><td>Grading system</td><td>Form type = Quiz</td><td>Hide</td></tr>
					<tr><td>Correct answer logic</td><td>Quiz and grading ≠ Points</td><td>Hide</td></tr>
					<tr><td>Limit by category</td><td>Limit number of questions ≠ 0</td><td>Disable</td></tr>
					<tr><td>Questions per category / category list</td><td>Limit by category on or off; per-category count &gt; 0</td><td>Hide (swap)</td></tr>
					<tr><td>Do not allow after end date</td><td>End date set</td><td>Disable</td></tr>
					<tr><td>Limit by email</td><td>Attempts &gt; 0</td><td>Disable</td></tr>
					<tr><td>Highlight correct answer</td><td>Show correct answer right away is on</td><td>Hide</td></tr>
					<tr><td>Page number, progress bar, questions per page</td><td>Theme ≠ Default (quiz wizard only)</td><td>Hide</td></tr>
				</tbody></table>
				<p style="margin-bottom:0">Add-on settings keep their own scripts. They can use the same rules, but they don't have to. Field names and stored values stay as they are; only the layout changes. Label column 220px; stacks on phones.</p></div>
		<?php qsm_ui_kit_php_example( 'L-06' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-07" data-id="L-07">
		<header class="kit-item-head"><span class="kit-id">L-07</span><h3 class="qsm-ui-h3">Conditional reveal</h3><span class="kit-where">Fields that only matter when another option is on</span></header>
			<div class="kit-demo">
				<label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" data-qsm-ui-reveal-target="#qsmk-d-reveal"><span class="qsm-ui-switch-copy"><strong>Redirect after submission</strong><span>Send users to another page instead of showing results.</span></span></label>
				<div class="qsm-ui-reveal" id="qsmk-d-reveal" hidden>
					<label class="qsm-ui-field-label" for="qsmk-d-rev-url">Redirect URL</label>
					<input class="qsm-ui-input qsm-ui-w-lg" id="qsmk-d-rev-url" placeholder="https://">
				</div>
			</div>
			<div class="kit-spec">Hidden fields are still rendered server-side and only hidden by JS, so they keep working if the JS doesn't load.</div>
		<?php qsm_ui_kit_php_example( 'L-07' ); ?>
	</article>
	<article class="kit-item" id="qsmk-L-08" data-id="L-08">
		<header class="kit-item-head"><span class="kit-id">L-08</span><h3 class="qsm-ui-h3">Accordion</h3><span class="kit-where">Emails list, Results pages list, FAQ / help, advanced options</span></header>
			<div class="kit-demo">
				<details class="qsm-ui-collapse" open>
					<summary><span class="qsm-ui-badge is-info no-dot">1</span>Admin notification <small>· to admin@example.com</small><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-chev"><use href="#qsm-ui-i-chev"/></svg></summary>
					<div class="qsm-ui-collapse-body"><p class="qsm-ui-small" style="margin:0">Email settings go here…</p></div>
				</details>
				<details class="qsm-ui-collapse">
					<summary><span class="qsm-ui-badge is-info no-dot">2</span>User confirmation <small>· to %USER_EMAIL%</small><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-chev"><use href="#qsm-ui-i-chev"/></svg></summary>
					<div class="qsm-ui-collapse-body"><p class="qsm-ui-small" style="margin:0">Email settings go here…</p></div>
				</details>
				<details class="qsm-ui-collapse">
					<summary>Advanced options<svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-chev"><use href="#qsm-ui-i-chev"/></svg></summary>
					<div class="qsm-ui-collapse-body"><p class="qsm-ui-small" style="margin:0">Rarely-used options go here…</p></div>
				</details>
			</div>
		<?php qsm_ui_kit_php_example( 'L-08' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-builders" data-group="Builders">
	<h2 class="qsm-ui-h2">Repeaters &amp; builders <small class="qsm-ui-caption"> · compound</small></h2>
	<p class="qsm-ui-small">Rows users add, remove and reorder. These carry the most custom JS today, so building them once saves the most work.</p>
	<article class="kit-item" id="qsmk-R-02" data-id="R-02">
		<header class="kit-item-head"><span class="kit-id">R-02</span><h3 class="qsm-ui-h3">Condition builder</h3><span class="kit-where">Results pages, Emails (send when…), Logic add-on, FunnelKit trigger</span></header>
			<div class="kit-demo">
				<p class="qsm-ui-small" style="margin:0 0 10px">Show this results page when <strong>all</strong> of these match:</p>
				<div class="qsm-ui-cond-group" data-kit-cond-group>
					<div class="qsm-ui-cond-row">
						<span class="qsm-ui-cond-join">IF</span>
						<select class="qsm-ui-select qsm-ui-input-sm" aria-label="Field"><option>Score %</option><option>Total points</option><option>Category score</option><option>Answer to question</option></select>
						<select class="qsm-ui-select qsm-ui-input-sm" aria-label="Operator"><option>is greater than</option><option>is less than</option><option>is equal to</option><option>is not equal to</option></select>
						<input class="qsm-ui-input qsm-ui-input-sm" value="70" aria-label="Value">
						<button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Remove condition" data-kit-remove-cond><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button>
					</div>
					<div class="qsm-ui-cond-row">
						<span class="qsm-ui-cond-join">AND</span>
						<select class="qsm-ui-select qsm-ui-input-sm" aria-label="Field"><option>Category score</option><option>Score %</option></select>
						<select class="qsm-ui-select qsm-ui-input-sm" aria-label="Operator"><option>is greater than</option></select>
						<input class="qsm-ui-input qsm-ui-input-sm" value="50" aria-label="Value">
						<button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Remove condition" data-kit-remove-cond><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button>
					</div>
					<div style="margin-top:10px"><button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-quiet" type="button" data-kit-add-cond><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>Add condition</button></div>
				</div>
				<div class="qsm-ui-cond-or">OR</div>
				<button class="qsm-ui-add-dashed" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>Add condition group</button>
			</div>
		<?php qsm_ui_kit_php_example( 'R-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-R-03" data-id="R-03">
		<header class="kit-item-head"><span class="kit-id">R-03</span><h3 class="qsm-ui-h3">Field mapping table</h3><span class="kit-where">FunnelKit, Mailchimp, Webhooks, Google Sheets, Zapier: map QSM data to a service</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-table-wrap">
					<table class="qsm-ui-table" style="min-width:560px">
						<thead><tr><th style="width:38%">FunnelKit field</th><th>QSM value</th><th class="qsm-ui-col-actions"><span class="qsm-ui-sr">Remove</span></th></tr></thead>
						<tbody data-kit-map-body>
							<tr><td><select class="qsm-ui-select qsm-ui-input-sm" aria-label="FunnelKit field"><option>First name</option><option>Phone</option><option>Company</option></select></td><td><input class="qsm-ui-input qsm-ui-input-sm" value="%USER_NAME%" aria-label="QSM value"></td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Remove mapping" data-kit-remove-row><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></td></tr>
							<tr><td><select class="qsm-ui-select qsm-ui-input-sm" aria-label="FunnelKit field"><option>Quiz score</option></select></td><td><input class="qsm-ui-input qsm-ui-input-sm" value="%CORRECT_SCORE%" aria-label="QSM value"></td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Remove mapping" data-kit-remove-row><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></td></tr>
						</tbody>
					</table>
				</div>
				<div class="qsm-ui-row" style="justify-content:space-between;margin-top:12px"><span class="qsm-ui-help" style="margin:0">Use any template variable as the value.</span><button class="qsm-ui-btn qsm-ui-btn-sm" type="button" data-kit-add-row><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>Add mapping</button></div>
			</div>
			<div class="kit-spec">Straight from the FunnelKit tab. Every integration add-on will reuse this one component.</div>
		<?php qsm_ui_kit_php_example( 'R-03' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-tables" data-group="Tables">
	<h2 class="qsm-ui-h2">Tables <small class="qsm-ui-caption"> · data</small></h2>
	<p class="qsm-ui-small">Replaces <code>WP_List_Table</code> styling on the Quizzes, Results and Question Bank pages. The data and server-side paging stay; only the look changes.</p>
	<article class="kit-item" id="qsmk-D-01" data-id="D-01">
		<header class="kit-item-head"><span class="kit-id">D-01</span><h3 class="qsm-ui-h3">Data table (full)</h3><span class="kit-where">Quizzes list, Results list, Question bank, Failed submissions</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-bulkbar" data-kit-bulkbar hidden><span data-kit-bulk-count>0 selected</span><button class="qsm-ui-btn" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-download"/></svg>Export</button><button class="qsm-ui-btn is-danger" type="button" data-qsm-ui-open="#qsmk-d-confirm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-trash"/></svg>Delete</button><button class="qsm-ui-btn qsm-ui-btn-quiet" type="button" style="margin-left:auto;background:none;border-color:transparent" data-kit-bulk-clear>Clear</button></div>
				<div class="qsm-ui-table-toolbar">
					<div class="qsm-ui-row">
						<div class="qsm-ui-input-icon" style="width:240px"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-search"/></svg><input class="qsm-ui-input qsm-ui-input-sm" type="search" placeholder="Search results…" aria-label="Search results" data-kit-table-search></div>
						<select class="qsm-ui-select qsm-ui-input-sm" style="width:160px" aria-label="Filter by quiz"><option>All quizzes</option><option>Customer survey</option><option>Product quiz</option></select>
						<select class="qsm-ui-select qsm-ui-input-sm" style="width:140px" aria-label="Filter by status"><option>Any status</option><option>Passed</option><option>Failed</option></select>
					</div>
					<div class="qsm-ui-row"><button class="qsm-ui-btn qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-settings"/></svg>Columns</button><button class="qsm-ui-btn qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-download"/></svg>Export</button></div>
				</div>
				<div class="qsm-ui-table-wrap">
					<table class="qsm-ui-table" data-kit-table>
						<thead><tr>
							<th class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select all" data-kit-select-all></label></th>
							<th aria-sort="none"><button class="qsm-ui-sort" type="button">Name</button></th>
							<th aria-sort="none"><button class="qsm-ui-sort" type="button">Quiz</button></th>
							<th aria-sort="descending" class="qsm-ui-num"><button class="qsm-ui-sort" type="button" data-kit-num>Score</button></th>
							<th>Status</th>
							<th aria-sort="none"><button class="qsm-ui-sort" type="button" data-kit-date>Date</button></th>
							<th class="qsm-ui-col-actions"><span class="qsm-ui-sr">Actions</span></th>
						</tr></thead>
						<tbody>
							<tr><td class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select Priya Shah"></label></td><td><a class="qsm-ui-cell-title" href="#">Priya Shah</a><span class="qsm-ui-cell-sub">priya@example.com</span><div class="qsm-ui-row-actions"><a href="#">View</a><a href="#">Resend email</a><a href="#" class="is-danger">Delete</a></div></td><td>Customer survey</td><td class="qsm-ui-num">92%</td><td><span class="qsm-ui-badge is-success">Passed</span></td><td>2026-10-26</td><td class="qsm-ui-col-actions"><div class="qsm-ui-menu-wrap"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Actions for Priya Shah" data-qsm-ui-menu aria-haspopup="menu"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-more"/></svg></button><ul class="qsm-ui-menu" role="menu" hidden><li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-eye"/></svg>View result</button></li><li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-mail"/></svg>Resend email</button></li><li><hr></li><li><button role="menuitem" type="button" class="is-danger" data-qsm-ui-open="#qsmk-d-confirm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-trash"/></svg>Delete</button></li></ul></div></td></tr>
							<tr><td class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select Marco Ruiz"></label></td><td><a class="qsm-ui-cell-title" href="#">Marco Ruiz</a><span class="qsm-ui-cell-sub">marco@example.com</span><div class="qsm-ui-row-actions"><a href="#">View</a><a href="#">Resend email</a><a href="#" class="is-danger">Delete</a></div></td><td>Product quiz</td><td class="qsm-ui-num">48%</td><td><span class="qsm-ui-badge is-danger">Failed</span></td><td>2026-10-25</td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Actions"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-more"/></svg></button></td></tr>
							<tr><td class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select Guest"></label></td><td><a class="qsm-ui-cell-title" href="#">Guest</a><span class="qsm-ui-cell-sub">No email</span><div class="qsm-ui-row-actions"><a href="#">View</a><a href="#" class="is-danger">Delete</a></div></td><td>Customer survey</td><td class="qsm-ui-num">75%</td><td><span class="qsm-ui-badge is-warning">In review</span></td><td>2026-10-24</td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Actions"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-more"/></svg></button></td></tr>
							<tr><td class="qsm-ui-col-check"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select Aiko Tanaka"></label></td><td><a class="qsm-ui-cell-title" href="#">Aiko Tanaka</a><span class="qsm-ui-cell-sub">aiko@example.com</span><div class="qsm-ui-row-actions"><a href="#">View</a><a href="#" class="is-danger">Delete</a></div></td><td>Onboarding check</td><td class="qsm-ui-num">100%</td><td><span class="qsm-ui-badge is-success">Passed</span></td><td>2026-10-22</td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Actions"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-more"/></svg></button></td></tr>
						</tbody>
					</table>
				</div>
				<div class="qsm-ui-pagination">
					<div class="qsm-ui-row"><span>Showing 1–4 of 248</span><select class="qsm-ui-select qsm-ui-input-sm" style="width:auto" aria-label="Rows per page"><option>20 per page</option><option>50 per page</option><option>100 per page</option></select></div>
					<div class="qsm-ui-pages" role="navigation" aria-label="Pagination"><button type="button" disabled aria-label="Previous page">‹</button><button type="button" aria-current="page">1</button><button type="button">2</button><button type="button">3</button><button type="button" disabled>…</button><button type="button">13</button><button type="button" aria-label="Next page">›</button></div>
				</div>
			</div>
			<div class="kit-spec">Selecting rows shows the dark bulk-action bar. Hover a row for quick links; the ⋯ menu holds the full list. Click headers to sort. Tables scroll sideways on phones rather than squashing.<br><b>Where to use it:</b> only big, growing lists that need search, filters or bulk actions: Quizzes, Results, Question bank, Failed submissions. Small or fixed tables (settings, mappings, result details) stay plain (D-03 / D-04). We won't add the full table anywhere just to make screens match.</div>
		<?php qsm_ui_kit_php_example( 'D-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-D-02" data-id="D-02">
		<header class="kit-item-head"><span class="kit-id">D-02</span><h3 class="qsm-ui-h3">Table empty & loading states</h3><span class="kit-where">Any table with no rows or while loading</span></header>
			<div class="kit-demo kit-demo-grid">
				<div class="qsm-ui-table-wrap">
					<div class="qsm-ui-empty">
						<div class="qsm-ui-empty-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-chart"/></svg></div>
						<h3 class="qsm-ui-h3">No results yet</h3>
						<p>Results appear here once someone completes this quiz.</p>
						<button class="qsm-ui-btn qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-link"/></svg>Copy quiz link</button>
					</div>
				</div>
				<div class="qsm-ui-table-wrap" style="padding:14px" aria-busy="true" aria-label="Loading">
					<div class="qsm-ui-stack" style="gap:14px">
						<div class="qsm-ui-skeleton" style="height:14px;width:40%"></div>
						<div class="qsm-ui-skeleton" style="height:36px"></div>
						<div class="qsm-ui-skeleton" style="height:36px"></div>
						<div class="qsm-ui-skeleton" style="height:36px;width:80%"></div>
					</div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'D-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-D-03" data-id="D-03">
		<header class="kit-item-head"><span class="kit-id">D-03</span><h3 class="qsm-ui-h3">Details table (key / value)</h3><span class="kit-where">Result details page, system info, quiz summary</span></header>
			<div class="kit-demo">
				<table class="qsm-ui-kv">
					<tr><th>Name</th><td>Priya Shah</td></tr>
					<tr><th>Score</th><td>92% · 23 / 25 points</td></tr>
					<tr><th>Time taken</th><td>6 min 12 s</td></tr>
					<tr><th>Submitted</th><td>26 Oct 2026, 14:08</td></tr>
					<tr><th>Q1. How satisfied are you?</th><td><span class="qsm-ui-row" style="gap:8px"><span class="qsm-ui-answer-mark is-correct">✓</span>Very satisfied</span></td></tr>
					<tr><th>Q2. Would you recommend us?</th><td><span class="qsm-ui-row" style="gap:8px"><span class="qsm-ui-answer-mark is-wrong">✕</span>Maybe <span class="qsm-ui-caption">(correct: Yes)</span></span></td></tr>
				</table>
			</div>
		<?php qsm_ui_kit_php_example( 'D-03' ); ?>
	</article>
	<article class="kit-item" id="qsmk-D-04" data-id="D-04">
		<header class="kit-item-head"><span class="kit-id">D-04</span><h3 class="qsm-ui-h3">Sortable table rows</h3><span class="kit-where">Contact form fields, email order, results-page priority</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-table-wrap">
					<table class="qsm-ui-table">
						<thead><tr><th class="qsm-ui-col-handle"><span class="qsm-ui-sr">Reorder</span></th><th>Field label</th><th>Type</th><th>Required</th><th class="qsm-ui-col-actions"><span class="qsm-ui-sr">Actions</span></th></tr></thead>
						<tbody data-kit-sortable>
							<tr class="qsm-ui-sort-row"><td class="qsm-ui-col-handle"><button class="qsm-ui-handle" type="button" aria-label="Reorder Name."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button></td><td class="qsm-ui-cell-title">Name</td><td><span class="qsm-ui-type-tag">Text</span></td><td><input class="qsm-ui-switch qsm-ui-switch-sm" type="checkbox" checked aria-label="Required"></td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button></td></tr>
							<tr class="qsm-ui-sort-row"><td class="qsm-ui-col-handle"><button class="qsm-ui-handle" type="button" aria-label="Reorder Email."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button></td><td class="qsm-ui-cell-title">Email</td><td><span class="qsm-ui-type-tag">Email</span></td><td><input class="qsm-ui-switch qsm-ui-switch-sm" type="checkbox" checked aria-label="Required"></td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button></td></tr>
							<tr class="qsm-ui-sort-row"><td class="qsm-ui-col-handle"><button class="qsm-ui-handle" type="button" aria-label="Reorder Company."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button></td><td class="qsm-ui-cell-title">Company</td><td><span class="qsm-ui-type-tag">Text</span></td><td><input class="qsm-ui-switch qsm-ui-switch-sm" type="checkbox" aria-label="Required"></td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button></td></tr>
							<tr class="qsm-ui-sort-row"><td class="qsm-ui-col-handle"><button class="qsm-ui-handle" type="button" aria-label="Reorder Terms."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button></td><td class="qsm-ui-cell-title">Accept terms</td><td><span class="qsm-ui-type-tag">Checkbox</span></td><td><input class="qsm-ui-switch qsm-ui-switch-sm" type="checkbox" checked aria-label="Required"></td><td class="qsm-ui-col-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button></td></tr>
						</tbody>
					</table>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'D-04' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-dnd" data-group="Drag &amp; drop">
	<h2 class="qsm-ui-h2">Drag &amp; drop <small class="qsm-ui-caption"> · interaction</small></h2>
	<p class="qsm-ui-small">One drag behaviour for every list. In WP this is jQuery UI Sortable (already loaded by Core), started with <code>QSMAdminUI.sortable( $list, options )</code>, which adds the opt-in <code>qsm-ui-sort-placeholder</code> (X-04). Keyboard reordering belongs to G-01, which is on hold.</p>
	<article class="kit-item" id="qsmk-G-02" data-id="G-02">
		<header class="kit-item-head"><span class="kit-id">G-02</span><h3 class="qsm-ui-h3">Question pages board</h3><span class="kit-where">Questions tab: pages with questions, drag between pages</span></header>
			<div class="kit-demo is-canvas">
				<div class="qsm-ui-board-bar" data-kit-board-bar hidden><span data-kit-board-count>0 selected</span>
					<div class="qsm-ui-menu-wrap"><button class="qsm-ui-btn" type="button" data-qsm-ui-menu aria-haspopup="menu"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-move"/></svg>Move to page <svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-chev"/></svg></button>
						<ul class="qsm-ui-menu" role="menu" hidden style="left:0;right:auto"><li><button role="menuitem" type="button" data-kit-move-selected="0">Page 1</button></li><li><button role="menuitem" type="button" data-kit-move-selected="1">Page 2</button></li><li><button role="menuitem" type="button" data-kit-move-selected="2">Page 3</button></li></ul></div>
					<button class="qsm-ui-btn is-danger" type="button" data-qsm-ui-open="#qsmk-d-confirm-sm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-trash"/></svg>Delete</button>
					<button class="qsm-ui-btn qsm-ui-btn-quiet" type="button" style="margin-left:auto;background:none;border-color:transparent;color:#fff" data-kit-board-clear>Clear</button>
				</div>
				<div data-kit-pages-board>
					<section class="qsm-ui-page-block">
						<header class="qsm-ui-page-head"><button class="qsm-ui-handle" type="button" aria-label="Reorder page"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button><span class="qsm-ui-page-title">Page 1</span><span class="qsm-ui-caption">· Intro</span><label class="qsm-ui-check"><input type="checkbox" data-kit-page-select><span>Select all</span></label><div class="qsm-ui-row"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Page settings"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-settings"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete page"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></header>
						<div class="qsm-ui-page-body"><ul class="qsm-ui-sortable" data-kit-sortable data-group="questions">
							<li class="qsm-ui-sort-item qsm-ui-q-row"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select question" data-kit-q-select></label><button class="qsm-ui-handle" type="button" aria-label="Drag to reorder."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button><div class="qsm-ui-grow"><strong>How satisfied are you with our service?</strong><div class="qsm-ui-q-meta"><span class="qsm-ui-type-tag">Multiple choice</span><span class="qsm-ui-cat-chip">Satisfaction</span></div></div><div class="qsm-ui-item-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit question" title="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Duplicate question" title="Duplicate" data-qsm-ui-toast="Question duplicated"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-dup"/></svg></button><div class="qsm-ui-menu-wrap"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Move question to another page" title="Move" data-qsm-ui-menu aria-haspopup="menu"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-move"/></svg></button><ul class="qsm-ui-menu" role="menu" hidden><li><button role="menuitem" type="button" data-kit-move-to="0">Move to Page 1</button></li><li><button role="menuitem" type="button" data-kit-move-to="1">Move to Page 2</button></li><li><button role="menuitem" type="button" data-kit-move-to="2">Move to Page 3</button></li></ul></div><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete question" title="Delete" data-qsm-ui-open="#qsmk-d-confirm-sm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></li>
							<li class="qsm-ui-sort-item qsm-ui-q-row"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select question" data-kit-q-select></label><button class="qsm-ui-handle" type="button" aria-label="Drag to reorder."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button><div class="qsm-ui-grow"><strong>Would you recommend us to a friend?</strong><div class="qsm-ui-q-meta"><span class="qsm-ui-type-tag">Polar</span><span class="qsm-ui-cat-chip">Service › Support</span><span class="qsm-ui-cat-chip">Satisfaction</span></div></div><div class="qsm-ui-item-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit question" title="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Duplicate question" title="Duplicate" data-qsm-ui-toast="Question duplicated"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-dup"/></svg></button><div class="qsm-ui-menu-wrap"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Move question to another page" title="Move" data-qsm-ui-menu aria-haspopup="menu"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-move"/></svg></button><ul class="qsm-ui-menu" role="menu" hidden><li><button role="menuitem" type="button" data-kit-move-to="0">Move to Page 1</button></li><li><button role="menuitem" type="button" data-kit-move-to="1">Move to Page 2</button></li><li><button role="menuitem" type="button" data-kit-move-to="2">Move to Page 3</button></li></ul></div><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete question" title="Delete" data-qsm-ui-open="#qsmk-d-confirm-sm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></li>
						</ul>
						<div class="qsm-ui-add-row"><button class="qsm-ui-btn qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>New question</button><button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-quiet" type="button" data-qsm-ui-open="#qsmk-d-modal"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-list"/></svg>From question bank</button></div></div>
					</section>
					<section class="qsm-ui-page-block">
						<header class="qsm-ui-page-head"><button class="qsm-ui-handle" type="button" aria-label="Reorder page"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button><span class="qsm-ui-page-title">Page 2</span><label class="qsm-ui-check"><input type="checkbox" data-kit-page-select><span>Select all</span></label><div class="qsm-ui-row"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Page settings"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-settings"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete page"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></header>
						<div class="qsm-ui-page-body"><ul class="qsm-ui-sortable" data-kit-sortable data-group="questions">
							<li class="qsm-ui-sort-item qsm-ui-q-row"><label class="qsm-ui-check"><input type="checkbox" aria-label="Select question" data-kit-q-select></label><button class="qsm-ui-handle" type="button" aria-label="Drag to reorder."><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button><div class="qsm-ui-grow"><strong>What could we improve?</strong><div class="qsm-ui-q-meta"><span class="qsm-ui-type-tag">Paragraph</span><span class="qsm-ui-cat-chip">Product › Features</span></div></div><div class="qsm-ui-item-actions"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Edit question" title="Edit"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-edit"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Duplicate question" title="Duplicate" data-qsm-ui-toast="Question duplicated"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-dup"/></svg></button><div class="qsm-ui-menu-wrap"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Move question to another page" title="Move" data-qsm-ui-menu aria-haspopup="menu"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-move"/></svg></button><ul class="qsm-ui-menu" role="menu" hidden><li><button role="menuitem" type="button" data-kit-move-to="0">Move to Page 1</button></li><li><button role="menuitem" type="button" data-kit-move-to="1">Move to Page 2</button></li><li><button role="menuitem" type="button" data-kit-move-to="2">Move to Page 3</button></li></ul></div><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete question" title="Delete" data-qsm-ui-open="#qsmk-d-confirm-sm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></li>
						</ul>
						<div class="qsm-ui-add-row"><button class="qsm-ui-btn qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>New question</button><button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-quiet" type="button" data-qsm-ui-open="#qsmk-d-modal"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-list"/></svg>From question bank</button></div></div>
					</section>
					<section class="qsm-ui-page-block">
						<header class="qsm-ui-page-head"><button class="qsm-ui-handle" type="button" aria-label="Reorder page"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></button><span class="qsm-ui-page-title">Page 3</span><span class="qsm-ui-caption">· empty</span><label class="qsm-ui-check"><input type="checkbox" data-kit-page-select><span>Select all</span></label><div class="qsm-ui-row"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Page settings"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-settings"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete page" data-qsm-ui-open="#qsmk-d-confirm-sm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></header>
						<div class="qsm-ui-page-body"><ul class="qsm-ui-sortable" data-kit-sortable data-group="questions"></ul>
						<div class="qsm-ui-add-row"><button class="qsm-ui-btn qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>New question</button><button class="qsm-ui-btn qsm-ui-btn-sm qsm-ui-btn-quiet" type="button" data-qsm-ui-open="#qsmk-d-modal"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-list"/></svg>From question bank</button></div></div>
					</section>
				</div>
				<button class="qsm-ui-add-dashed" type="button" style="margin-top:14px"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-plus"/></svg>Add page</button>
			</div>
			<div class="kit-spec">Each row matches today's question row: select box, drag handle, question text, then the <b>type shown once</b> and <b>categories</b> (full path for sub-categories). No question numbers, points or grading details on the board. The actions are today's four: Edit (opens the P-06 accordion), Duplicate, Move (choose a page, like today's Move popup) and Delete (asks first). Ticking rows or “Select all” shows a bar to move or delete them together. Drag still works between pages, including into an empty page.</div>
		<?php qsm_ui_kit_php_example( 'G-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-G-03" data-id="G-03">
		<header class="kit-item-head"><span class="kit-id">G-03</span><h3 class="qsm-ui-h3">Drag & drop states</h3><span class="kit-where">Reference for devs: what each drag state looks like</span></header>
			<div class="kit-demo kit-demo-grid">
				<div><p class="kit-label">Resting</p><div class="qsm-ui-sort-item"><span class="qsm-ui-handle"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></span><div class="qsm-ui-grow"><strong>Question</strong></div></div></div>
				<div><p class="kit-label">Picked up (keyboard)</p><div class="qsm-ui-sort-item is-grabbed"><span class="qsm-ui-handle"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></span><div class="qsm-ui-grow"><strong>Question</strong></div></div></div>
				<div><p class="kit-label">Placeholder while dragging</p><div class="qsm-ui-sort-item is-dragging"><span class="qsm-ui-handle"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-grip"/></svg></span><div class="qsm-ui-grow"><strong>Question</strong></div></div></div>
				<div><p class="kit-label">Drop target</p><div class="qsm-ui-page-block qsm-ui-drop-target" style="padding:16px;text-align:center" ><span class="qsm-ui-small">Drop here</span></div></div>
			</div>
		<?php qsm_ui_kit_php_example( 'G-03' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-feedback" data-group="Feedback">
	<h2 class="qsm-ui-h2">Feedback &amp; status <small class="qsm-ui-caption"> · messaging</small></h2>
	<p class="qsm-ui-small">One notice style instead of the mix of WP admin notices, QSM alerts and add-on popups.</p>
	<article class="kit-item" id="qsmk-N-01" data-id="N-01">
		<header class="kit-item-head"><span class="kit-id">N-01</span><h3 class="qsm-ui-h3">Inline notices</h3><span class="kit-where">Save success, license expired, add-on requirement, upgrade warnings</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-notice"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg><div class="qsm-ui-notice-body"><strong>New results renderer</strong><p>Template overrides only work with the new renderer turned on.</p><div class="qsm-ui-notice-actions"><button class="qsm-ui-btn qsm-ui-btn-link" type="button">Turn it on</button></div></div><button class="qsm-ui-btn qsm-ui-btn-icon qsm-ui-dismiss" type="button" aria-label="Dismiss" data-qsm-ui-dismiss><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-x"/></svg></button></div>
				<div class="qsm-ui-notice is-success"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-ok"/></svg><div class="qsm-ui-notice-body"><p>Quiz settings saved.</p></div><button class="qsm-ui-btn qsm-ui-btn-icon qsm-ui-dismiss" type="button" aria-label="Dismiss" data-qsm-ui-dismiss><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-x"/></svg></button></div>
				<div class="qsm-ui-notice is-warning"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-alert"/></svg><div class="qsm-ui-notice-body"><strong>FunnelKit Automations is not active</strong><p>Install and activate FunnelKit Automations to use this integration.</p></div></div>
				<div class="qsm-ui-notice is-danger"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-alert"/></svg><div class="qsm-ui-notice-body"><strong>License expired</strong><p>Updates and support stopped on 12 Oct 2026.</p><div class="qsm-ui-notice-actions"><button class="qsm-ui-btn qsm-ui-btn-link" type="button" style="color:var(--qsm-red)">Renew license</button></div></div></div>
			</div>
		<?php qsm_ui_kit_php_example( 'N-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-N-02" data-id="N-02">
		<header class="kit-item-head"><span class="kit-id">N-02</span><h3 class="qsm-ui-h3">Toast</h3><span class="kit-where">Quick confirmations after AJAX: question saved, copied, deleted (with Undo)</span></header>
			<div class="kit-demo qsm-ui-row">
				<button class="qsm-ui-btn" type="button" data-qsm-ui-toast="Question saved">Show success toast</button>
				<button class="qsm-ui-btn" type="button" data-qsm-ui-toast="Question deleted" data-qsm-ui-toast-undo>Toast with Undo</button>
				<button class="qsm-ui-btn" type="button" data-qsm-ui-toast="Couldn't save. Check your connection." data-qsm-ui-toast-error>Error toast</button>
			</div>
			<div class="kit-spec">Disappears after 4 s, announced to screen readers (<code>role="status"</code>). Errors that need action use an inline notice instead.</div>
		<?php qsm_ui_kit_php_example( 'N-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-N-03" data-id="N-03">
		<header class="kit-item-head"><span class="kit-id">N-03</span><h3 class="qsm-ui-h3">Badges & status pills</h3><span class="kit-where">Quiz status, result pass/fail, add-on active, question type, Pro</span></header>
			<div class="kit-demo qsm-ui-stack">
				<div class="qsm-ui-row">
					<span class="qsm-ui-badge">Draft</span>
					<span class="qsm-ui-badge is-success">Published</span>
					<span class="qsm-ui-badge is-warning">Scheduled</span>
					<span class="qsm-ui-badge is-danger">Closed</span>
					<span class="qsm-ui-badge is-info">New</span>
					<span class="qsm-ui-badge is-pro">PRO</span>
				</div>
				<div class="qsm-ui-row">
					<span class="qsm-ui-status">Connected</span>
					<span class="qsm-ui-status is-off">Not connected</span>
					<span class="qsm-ui-type-tag">Multiple choice</span>
					<span class="qsm-ui-type-tag">Short answer</span>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'N-03' ); ?>
	</article>
	<article class="kit-item" id="qsmk-N-04" data-id="N-04">
		<header class="kit-item-head"><span class="kit-id">N-04</span><h3 class="qsm-ui-h3">Progress, spinner & skeleton</h3><span class="kit-where">Import/export, database migration, loading panels</span></header>
			<div class="kit-demo kit-demo-grid">
				<div>
					<div class="qsm-ui-progress-label"><span>Migrating results</span><span>62%</span></div>
					<div class="qsm-ui-progress"><span style="width:62%"></span></div>
					<p class="qsm-ui-caption" style="margin-top:6px">1,240 of 2,000 rows</p>
				</div>
				<div class="qsm-ui-row" style="gap:12px"><div class="qsm-ui-spinner" role="status" aria-label="Loading"></div><span class="qsm-ui-small">Loading questions…</span></div>
				<div class="qsm-ui-stack" style="gap:8px"><div class="qsm-ui-skeleton" style="height:16px;width:60%"></div><div class="qsm-ui-skeleton" style="height:12px"></div><div class="qsm-ui-skeleton" style="height:12px;width:85%"></div></div>
			</div>
		<?php qsm_ui_kit_php_example( 'N-04' ); ?>
	</article>
	<article class="kit-item" id="qsmk-N-05" data-id="N-05">
		<header class="kit-item-head"><span class="kit-id">N-05</span><h3 class="qsm-ui-h3">Empty state</h3><span class="kit-where">No quizzes, no questions, no results, no add-ons</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-empty">
					<div class="qsm-ui-empty-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-quiz"/></svg></div>
					<h3 class="qsm-ui-h3">Create your first quiz</h3>
					<p>Start from a template or from scratch. It takes about two minutes.</p>
					<div class="qsm-ui-row"><button class="qsm-ui-btn qsm-ui-btn-primary" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-plus"/></svg>Create quiz</button><button class="qsm-ui-btn" type="button">Browse templates</button></div>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'N-05' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-overlays" data-group="Overlays">
	<h2 class="qsm-ui-h2">Overlays <small class="qsm-ui-caption"> · dialogs &amp; menus</small></h2>
	<p class="qsm-ui-small">Built on the native <code>&lt;dialog&gt;</code> element (focus trap and Esc to close for free). Used for Core's own new popups. micromodal and <code>.qsm-popup</code> stay loaded unchanged, because 20 add-ons use them (see X-04).</p>
	<article class="kit-item" id="qsmk-O-01" data-id="O-01">
		<header class="kit-item-head"><span class="kit-id">O-01</span><h3 class="qsm-ui-h3">Modal dialog</h3><span class="kit-where">Add question from bank, quiz preview, new email, settings that need focus</span></header>
			<div class="kit-demo qsm-ui-row">
				<button class="qsm-ui-btn" type="button" data-qsm-ui-open="#qsmk-d-modal">Open modal</button>
			</div>
		<?php qsm_ui_kit_php_example( 'O-01' ); ?>
	</article>
	<article class="kit-item" id="qsmk-O-02" data-id="O-02">
		<header class="kit-item-head"><span class="kit-id">O-02</span><h3 class="qsm-ui-h3">Confirm (destructive)</h3><span class="kit-where">Delete quiz, delete results, reset settings</span></header>
			<div class="kit-demo qsm-ui-row">
				<button class="qsm-ui-btn qsm-ui-btn-danger-ghost" type="button" data-qsm-ui-open="#qsmk-d-confirm-sm">Delete question…</button>
				<button class="qsm-ui-btn qsm-ui-btn-danger-ghost" type="button" data-qsm-ui-open="#qsmk-d-confirm">Delete quiz…</button>
			</div>
			<div class="kit-spec">Two sizes. <b>Small</b> (a question, a page, an email): one sentence and the buttons. <b>Delete quiz</b>: the same content as today's popup (<code>#modal-5</code>): the question, the two existing checkboxes (<code>qsm_delete_question_from_qb</code>, <code>qsm_delete_from_db</code>) with the same names and values, the same form, nonce and hidden fields. No results count and no export link. The only addition is typing <code>DELETE</code> before the Delete button turns on. Only the look changes. Focus starts on Cancel and Esc closes.</div>
		<?php qsm_ui_kit_php_example( 'O-02' ); ?>
	</article>
	<article class="kit-item" id="qsmk-O-04" data-id="O-04">
		<header class="kit-item-head"><span class="kit-id">O-04</span><h3 class="qsm-ui-h3">Dropdown menu</h3><span class="kit-where">Row actions (⋯), Add-ons tab overflow, split button</span></header>
			<div class="kit-demo">
				<div class="qsm-ui-menu-wrap">
					<button class="qsm-ui-btn" type="button" data-qsm-ui-menu aria-haspopup="menu">Quiz actions <svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-chev"/></svg></button>
					<ul class="qsm-ui-menu" role="menu" hidden style="left:0;right:auto">
						<li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-eye"/></svg>Preview</button></li>
						<li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-dup"/></svg>Duplicate<kbd>⌘D</kbd></button></li>
						<li><button role="menuitem" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-download"/></svg>Export</button></li>
						<li><hr></li>
						<li><button role="menuitem" type="button" class="is-danger" data-qsm-ui-open="#qsmk-d-confirm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-trash"/></svg>Delete</button></li>
					</ul>
				</div>
			</div>
		<?php qsm_ui_kit_php_example( 'O-04' ); ?>
	</article>
	<article class="kit-item" id="qsmk-O-05" data-id="O-05">
		<header class="kit-item-head"><span class="kit-id">O-05</span><h3 class="qsm-ui-h3">Tooltip</h3><span class="kit-where">Short explanations next to labels and icon buttons</span></header>
			<div class="kit-demo">
				<span class="qsm-ui-body">Grading system <button class="qsm-ui-tip" type="button" data-tip="How each answer contributes to the final score.">?</button></span>
			</div>
			<div class="kit-spec">Text only, one or two lines. Anything longer belongs in help text under the field.</div>
		<?php qsm_ui_kit_php_example( 'O-05' ); ?>
	</article>
</section>
<section class="kit-section" id="qsmk-s-display" data-group="Data display">
	<h2 class="qsm-ui-h2">Data display &amp; product pieces <small class="qsm-ui-caption"> · QSM-specific</small></h2>
	<p class="qsm-ui-small">Composite pieces specific to QSM: dashboard numbers, add-on cards, Pro upsells and the question card.</p>
	<article class="kit-item" id="qsmk-P-04" data-id="P-04">
		<header class="kit-item-head"><span class="kit-id">P-04</span><h3 class="qsm-ui-h3">Pro lock & upsell banner</h3><span class="kit-where">Pro-only settings inside free Core; upgrade prompts</span></header>
			<div class="kit-demo qsm-ui-stack">
				<div class="qsm-ui-locked">
					<div class="qsm-ui-locked-content">
						<div class="qsm-ui-setting-row"><div class="qsm-ui-setting-label">Send certificate</div><div><label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" checked tabindex="-1"><span>On</span></label></div></div>
						<div class="qsm-ui-setting-row"><div class="qsm-ui-setting-label">Template</div><div><select class="qsm-ui-select qsm-ui-w-md" tabindex="-1" aria-label="Template"><option>Classic</option></select></div></div>
					</div>
					<div class="qsm-ui-locked-overlay"><span class="qsm-ui-badge is-pro">PRO</span><strong>Certificates are a Pro feature</strong><p>Give every passing user a branded PDF certificate.</p><button class="qsm-ui-btn qsm-ui-btn-primary qsm-ui-btn-sm" type="button"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-lock"/></svg>Unlock with Pro</button></div>
				</div>
				<div class="qsm-ui-upsell"><span class="qsm-ui-addon-logo" style="background:linear-gradient(135deg,#6c3fd1,#315efb)"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-star"/></svg></span><div><strong>Get 40+ add-ons with QSM Pro</strong><p>Logic, certificates, payments, integrations and more.</p></div><button class="qsm-ui-btn qsm-ui-btn-primary" type="button">See plans</button></div>
			</div>
			<div class="kit-spec">One upsell pattern across Core keeps upgrade links consistent and trackable (see KB: QSM in-plugin link tracking).</div>
		<?php qsm_ui_kit_php_example( 'P-04' ); ?>
	</article>
	<article class="kit-item" id="qsmk-P-05" data-id="P-05">
		<header class="kit-item-head"><span class="kit-id">P-05</span><h3 class="qsm-ui-h3">License card</h3><span class="kit-where">Every add-on settings page, Global settings → Licenses</span></header>
			<div class="kit-demo is-canvas">
				<div class="qsm-ui-card"><div class="qsm-ui-card-head"><div><h3 class="qsm-ui-h3">FunnelKit license</h3><p>Activate to receive updates and support.</p></div><span class="qsm-ui-status">Active</span></div>
					<div class="qsm-ui-card-body">
						<div class="qsm-ui-input-group qsm-ui-w-lg"><span class="qsm-ui-addon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-key"/></svg></span><input class="qsm-ui-input" value="••••••••••••3f9a" readonly aria-label="License key"><button class="qsm-ui-btn qsm-ui-btn-danger-ghost" type="button">Deactivate</button></div>
						<p class="qsm-ui-caption" style="margin-top:8px">Expires 14 Mar 2027 · 2 of 5 sites used</p>
					</div>
				</div>
			</div>
			<div class="kit-spec">Today every add-on ships its own license markup + CSS. One shared component removes that from 40+ add-ons.</div>
		<?php qsm_ui_kit_php_example( 'P-05' ); ?>
	</article>
	<article class="kit-item" id="qsmk-P-06" data-id="P-06">
		<header class="kit-item-head"><span class="kit-id">P-06</span><h3 class="qsm-ui-h3">Question editor card</h3><span class="kit-where">Questions tab: editing one question</span></header>
			<div class="kit-demo is-canvas">
				<div class="qsm-ui-question-card" data-kit-qcard>
					<header><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Collapse question" aria-expanded="true" data-kit-qcard-toggle><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-chev"><use href="#qsm-ui-i-chev"/></svg></button><span class="qsm-ui-q-num">1</span><strong>How satisfied are you with our service?</strong><span class="qsm-ui-type-tag">Multiple choice</span>
						<div class="qsm-ui-row"><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Duplicate question" data-qsm-ui-toast="Question duplicated"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-dup"/></svg></button><button class="qsm-ui-btn qsm-ui-btn-icon is-danger" type="button" aria-label="Delete question" data-qsm-ui-open="#qsmk-d-confirm-sm"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></button></div></header>
					<div class="qsm-ui-qe">
						<div class="qsm-ui-qe-main">
							<div><label class="qsm-ui-field-label" for="qsmk-d-qt">Question title</label><input class="qsm-ui-input" id="qsmk-d-qt" value="How satisfied are you with our service?">
								<button class="qsm-ui-btn qsm-ui-btn-link" type="button" style="margin-top:8px" data-kit-toggle-target="#qsmk-d-qdesc">Edit description</button>
								<div id="qsmk-d-qdesc" hidden style="margin-top:8px"><textarea class="qsm-ui-textarea" rows="3" aria-label="Question description">Think about your last three months with us.</textarea><span class="qsm-ui-help">Rich text editor (F-24) in the real screen.</span></div></div>
							<div data-show-if="qtype:0,1,2,4,10,13">
								<div class="qsm-ui-qe-head"><span class="qsm-ui-field-label" style="margin:0">Answers</span>
									<div class="qsm-ui-row" style="gap:6px"><label class="qsm-ui-small" for="qsmk-d-anstype">Answers type</label><a class="qsm-ui-doc" href="#" title="View Documentation" aria-label="Answers type documentation"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg></a><select class="qsm-ui-select qsm-ui-input-sm" id="qsmk-d-anstype" name="change-answer-editor" style="width:auto"><option value="text">Text Answers</option><option value="rich">Rich Answers</option><option value="image">Image Answers</option></select></div></div>
								<p class="qsm-ui-caption" style="margin-top:10px">Answer list: today's answer editor stays here unchanged (R-01 is on hold).</p>
							</div>
							<div data-show-if="qtype:3,5,7,12,14"><label class="qsm-ui-field-label" for="qsmk-d-qcorrect">Correct answer</label><input class="qsm-ui-input" id="qsmk-d-qcorrect" placeholder="Leave empty if any answer is accepted"></div>
							<div data-show-if="qtype:!6"><div class="qsm-ui-label-line"><label class="qsm-ui-field-label" for="qsmk-d-qinfo">Correct answer info</label><a class="qsm-ui-doc" href="#" title="View Documentation" aria-label="Correct answer info documentation"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg></a></div><textarea class="qsm-ui-textarea" id="qsmk-d-qinfo" rows="2" placeholder="Shown with the results, e.g. why this answer is right"></textarea></div>
							<div class="qsm-ui-grid-2" data-show-if="qtype:!6">
								<div><div class="qsm-ui-label-line"><label class="qsm-ui-field-label" for="qsmk-d-qhint">Hint</label><a class="qsm-ui-doc" href="#" title="View Documentation" aria-label="Hint documentation"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg></a></div><input class="qsm-ui-input" id="qsmk-d-qhint" placeholder="Optional"></div>
								<div><div class="qsm-ui-label-line"><label class="qsm-ui-field-label" for="qsmk-d-qcomment">Comment box</label><a class="qsm-ui-doc" href="#" title="View Documentation" aria-label="Comment box documentation"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg></a></div><select class="qsm-ui-select" id="qsmk-d-qcomment"><option>None</option><option>Small text field</option><option>Large text area</option></select></div>
							</div>
							<details class="qsm-ui-collapse" data-show-if="qtype:!6">
								<summary>Advanced options<small>· limits, autofill, file upload</small><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-chev"><use href="#qsm-ui-i-chev"/></svg></summary>
								<div class="qsm-ui-collapse-body qsm-ui-stack">
									<div class="qsm-ui-grid-2" data-show-if="qtype:3,5,14">
										<div><label class="qsm-ui-field-label" for="qsmk-d-qmin">Minimum characters</label><input class="qsm-ui-input" id="qsmk-d-qmin" type="number" min="0" value="0"></div>
										<div><label class="qsm-ui-field-label" for="qsmk-d-qmax">Maximum characters</label><input class="qsm-ui-input" id="qsmk-d-qmax" type="number" min="0" value="0"><span class="qsm-ui-help">0 = no limit</span></div>
									</div>
									<div data-show-if="qtype:4,10"><label class="qsm-ui-field-label" for="qsmk-d-qmaxsel">Maximum choices a user can select</label><input class="qsm-ui-input qsm-ui-w-sm" id="qsmk-d-qmaxsel" type="number" min="0" value="0"><span class="qsm-ui-help">0 = no limit</span></div>
									<div data-show-if="qtype:14"><span class="qsm-ui-field-label">Match answers</span><label class="qsm-ui-check"><input type="radio" name="d-qmatch" checked><span>Randomly</span></label><label class="qsm-ui-check"><input type="radio" name="d-qmatch"><span>Sequentially</span></label></div>
									<label class="qsm-ui-check" data-show-if="qtype:3,5,14"><input type="checkbox"><span>Case sensitive<small>Require the right uppercase and lowercase letters</small></span></label>
									<label class="qsm-ui-check" data-show-if="qtype:3,5,7,14"><input type="checkbox"><span>Hide browser autofill</span></label>
									<div class="qsm-ui-stack" data-show-if="qtype:11">
										<div><label class="qsm-ui-field-label" for="qsmk-d-qfile">File size limit</label><div class="qsm-ui-input-group qsm-ui-w-sm"><input class="qsm-ui-input" id="qsmk-d-qfile" type="number" value="4"><span class="qsm-ui-addon">MB</span></div></div>
										<div><span class="qsm-ui-field-label">Allowed file types</span><div class="qsm-ui-row" style="gap:16px;flex-wrap:wrap"><label class="qsm-ui-check"><input type="checkbox" checked><span>Image</span></label><label class="qsm-ui-check"><input type="checkbox" checked><span>PDF</span></label><label class="qsm-ui-check"><input type="checkbox"><span>Doc</span></label><label class="qsm-ui-check"><input type="checkbox"><span>Excel</span></label><label class="qsm-ui-check"><input type="checkbox"><span>Text</span></label><label class="qsm-ui-check"><input type="checkbox"><span>Video</span></label></div></div>
									</div>
									<p class="qsm-ui-caption" style="margin:0">Add-ons add their own options here (Logic, Advanced Question Types…) through the same hook as today.</p>
								</div>
							</details>
						</div>
						<aside class="qsm-ui-qe-side">
							<div class="qsm-ui-qe-group"><label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" checked><span class="qsm-ui-switch-copy"><strong>Published</strong><span>Unpublished questions are skipped.</span></span></label></div>
							<div class="qsm-ui-qe-group"><div class="qsm-ui-label-line" style="margin:0"><label class="qsm-ui-field-label" for="qsmk-d-qtype">Question type</label><a class="qsm-ui-doc" href="#" title="View Documentation" aria-label="Question type documentation"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg></a></div>
								<select class="qsm-ui-select" id="qsmk-d-qtype" name="qtype">
									<option value="0">Multiple choice</option><option value="1">Horizontal multiple choice</option><option value="4">Multiple response</option><option value="10">Horizontal multiple response</option><option value="2">Dropdown</option><option value="13">Polar</option>
									<option value="3">Short answer</option><option value="5">Paragraph</option><option value="7">Number</option><option value="12">Date</option><option value="14">Fill in the blank</option><option value="11">File upload</option><option value="6">Text / HTML section</option>
								</select>
								</div>
							<div class="qsm-ui-qe-group" data-show-if="qtype:!6"><label class="qsm-ui-switch-line"><input class="qsm-ui-switch" type="checkbox" checked><span class="qsm-ui-switch-copy"><strong>Required</strong><span>Users must answer.</span></span></label></div>
							<div class="qsm-ui-qe-group"><div class="qsm-ui-label-line" style="margin:0"><span class="qsm-ui-field-label">Category</span><a class="qsm-ui-doc" href="#" title="View Documentation" aria-label="Category documentation"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-info"/></svg></a></div>
								<div class="qsm-ui-row" style="gap:6px;flex-wrap:wrap"><span class="qsm-ui-cat-chip">Satisfaction</span></div>
								<button class="qsm-ui-btn qsm-ui-btn-sm" type="button" style="justify-self:start">Change category</button>
								<span class="qsm-ui-caption">Opens the F-25 category tree.</span></div>
							<div class="qsm-ui-qe-group"><span class="qsm-ui-field-label" style="margin:0">Featured image</span>
								<button class="qsm-ui-btn qsm-ui-btn-sm" type="button" style="justify-self:start"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon qsm-ui-icon-sm"><use href="#qsm-ui-i-image"/></svg>Set image</button>
								<span class="qsm-ui-caption">WordPress media library, as today (F-22 is on hold).</span></div>
						</aside>
					</div>
					<div class="qsm-ui-qe-foot"><span class="qsm-ui-caption">Saved 2 min ago</span><div class="qsm-ui-row"><button class="qsm-ui-btn" type="button" data-kit-qcard-toggle>Collapse</button><button class="qsm-ui-btn qsm-ui-btn-primary" type="button" data-kit-loading data-qsm-ui-toast="Question saved">Save question</button></div></div>
				</div>
			</div>
			<div class="kit-spec">Opens inline as an accordion from the question row (G-02), as today. It has every option the current editor has: title and description, the <b>Answers type dropdown</b> (Text / Rich / Image Answers, same <code>change-answer-editor</code> field as today) with correct and points, correct answer info, hint, comment box, published, question type, required, category, featured image, and the type-specific options (min/max characters, max choices, match answers, case sensitive, autofill, file size and types). <b>Try changing the question type:</b> fields that don't apply hide. The answer list itself stays as it is today for now (R-01 is on hold). <b>Documentation links stay as they are:</b> the same help icon next to each label, with the same URLs (<code>documentation_link</code> / <code>qsm_get_plugin_link()</code>), opening in a new tab. Only the icon is restyled.</div>
		<?php qsm_ui_kit_php_example( 'P-06' ); ?>
	</article>
</section>
<dialog class="qsm-ui-modal" id="qsmk-d-confirm-sm" aria-labelledby="qsmk-d-confirm-sm-t" aria-describedby="qsmk-d-confirm-sm-d">
	<div class="qsm-ui-modal-head"><div class="qsm-ui-confirm-head"><div class="qsm-ui-modal-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-trash"/></svg></div><div><h2 class="qsm-ui-h2" id="qsmk-d-confirm-sm-t">Delete this question?</h2><p class="qsm-ui-small" id="qsmk-d-confirm-sm-d" style="margin-top:4px">It's removed from this quiz. Its copy in the question bank stays.</p></div></div><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Close" data-qsm-ui-close><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-x"/></svg></button></div>
	<div class="qsm-ui-modal-foot"><button class="qsm-ui-btn" type="button" data-qsm-ui-close autofocus>Cancel</button><button class="qsm-ui-btn qsm-ui-btn-danger" type="button" data-qsm-ui-close data-kit-toast-after="Question deleted">Delete question</button></div>
</dialog>
<dialog class="qsm-ui-modal" id="qsmk-d-confirm" aria-labelledby="qsmk-d-confirm-t" aria-describedby="qsmk-d-confirm-d">
	<div class="qsm-ui-modal-head"><div class="qsm-ui-confirm-head"><div class="qsm-ui-modal-icon"><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-alert"/></svg></div><div><h2 class="qsm-ui-h2" id="qsmk-d-confirm-t">Delete</h2><p class="qsm-ui-small" id="qsmk-d-confirm-d" style="margin-top:4px">Are you sure you want to delete this quiz or survey?</p></div></div><button class="qsm-ui-btn qsm-ui-btn-icon" type="button" aria-label="Close" data-qsm-ui-close><svg aria-hidden="true" focusable="false" class="qsm-ui-icon"><use href="#qsm-ui-i-x"/></svg></button></div>
	<div class="qsm-ui-modal-body">
		<div class="qsm-ui-stack">
			<label class="qsm-ui-check"><input type="checkbox" name="qsm_delete_question_from_qb" value="1"><span>Delete question from question bank?</span></label>
			<label class="qsm-ui-check"><input type="checkbox" name="qsm_delete_from_db" value="1"><span>Delete items from database?</span></label>
		</div>
		<div class="qsm-ui-confirm-type"><label class="qsm-ui-field-label" for="qsmk-d-confirm-type">Type <code>DELETE</code> to confirm</label><input class="qsm-ui-input" id="qsmk-d-confirm-type" autocomplete="off" spellcheck="false" data-qsm-ui-confirm-text="DELETE" data-qsm-ui-confirm-target="#qsmk-d-confirm-btn"></div>
	</div>
	<div class="qsm-ui-modal-foot"><button class="qsm-ui-btn" type="button" data-qsm-ui-close autofocus>Cancel</button><button class="qsm-ui-btn qsm-ui-btn-danger" type="button" data-qsm-ui-close id="qsmk-d-confirm-btn" disabled data-kit-toast-after="Quiz deleted">Delete</button></div>
</dialog>
