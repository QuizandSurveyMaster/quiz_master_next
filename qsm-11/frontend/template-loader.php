<?php
/**
 * QSM Template Loader Functions
 *
 * Handles template loading with theme override capability
 *
 * @package QSM
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get template part with theme override support (New System)
 *
 * @param string $slug Template slug (e.g., 'pagination', 'questions/multiple-choice')
 * @param array  $args Arguments to pass to template
 * @return string Template output
 */
function qsm_new_get_template_part( $slug, $args = array() ) {
	// Debug: Log template requests
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'QSM Template Loader: Requesting template: ' . $slug );
	}
	
	// Hook before template render
	do_action( 'qsm_new_before_template_render', $slug, $args );
	
	$located = qsm_new_locate_template( $slug . '.php' );
	
	// Debug: Log template location result
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'QSM Template Loader: Template located at: ' . ( $located ? $located : 'NOT FOUND' ) );
	}
	
	ob_start();
	
	if ( $located ) {
		// Make args available to template
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args );
		}
		
		// Include the template
		include $located;
	} else {
		// Template not found, show error in debug mode
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			echo '<!-- QSM New Template not found: ' . esc_html( $slug ) . ' -->';
			echo '<!-- Searched in: ' . esc_html( QSM_PLUGIN_PATH . 'qsm-11/templates/' . $slug . '.php' ) . ' -->';
		}
		// Always show a fallback for pagination
		if ( $slug === 'pagination' ) {
			echo '<div class="qsm-pagination qsm-navigation qmn_pagination border margin-bottom">';
			echo '<a href="javascript:void(0);" class="qsm-btn qsm-previous qmn_btn mlw_qmn_quiz_link mlw_previous qsm-secondary">Previous</a>';
			echo '<a href="javascript:void(0);" class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next mlw_custom_next qsm-primary">Next</a>';
			echo '<input type="submit" value="Submit" class="qsm-btn qsm-submit-btn qmn_btn qsm-primary">';
			echo '</div>';
		}
	}
	
	$output = ob_get_clean();
	
	// Hook after template render
	$output = apply_filters( 'qsm_new_after_template_render', $output, $slug, $args );
	
	return $output;
}

/**
 * Locate a template file with theme override support (New System)
 *
 * Looks for templates in the following order:
 * 1. yourtheme/qsm-11/templates/template-name.php
 * 2. yourtheme/qsm/templates/template-name.php
 * 3. yourtheme/qsm/new-frontend/template-name.php
 * 4. QSM themes (qsm-theme-*)/templates/template-name.php
 * 5. QSM addons (qsm-/templates/template-name.php
 * 6. plugin/qsm-11/templates/template-name.php
 *
 * @param string $template_name Template filename (can include subdirectories)
 * @return string|false Template path or false if not found
 */
function qsm_new_locate_template( $template_name ) {
	$template_path = 'qsm-11/templates/';
	$default_path = QSM_PLUGIN_PATH . 'qsm-11/templates/';
	
	// Hook to allow custom template override paths
	$template_override_path = apply_filters( 'qsm_new_template_override_path', $template_path, $template_name );
	
	$located = false;

	// Look in QSM themes and addons (plugins that start with 'qsm')
	$located = qsm_locate_template_in_qsm_plugins( $template_name );
	
	// Look in plugin templates folder
	if ( ! $located && file_exists( $default_path . $template_name ) ) {
		$located = $default_path . $template_name;
	}

	// Allow plugins to filter the located template
	$located = apply_filters( 'qsm_new_locate_template', $located, $template_name, $template_path, $default_path );
	
	return $located;
}

/**
 * Locate template in QSM themes and addons
 *
 * Searches for templates in plugins that start with 'qsm'
 * Priority order: qsm-theme-* plugins first, then other qsm-* plugins
 *
 * @param string $template_name Template filename (can include subdirectories)
 * @return string|false Template path or false if not found
 */
function qsm_locate_template_in_qsm_plugins( $template_name ) {
	$plugins_dir = WP_PLUGIN_DIR . '/';
	$located = false;
	
	// Get all directories in plugins folder
	if ( ! is_dir( $plugins_dir ) ) {
		return false;
	}
	
	// Allow plugins to filter the located template
	$qsm_addon_or_theme = apply_filters( 'qsm_replace_template_from_addon_or_theme', '', $template_name );

	// Search through QSM plugins for template
	$template_paths = $plugins_dir . $qsm_addon_or_theme . '/' . $template_name;
	
	if ( file_exists( $template_paths ) ) {
		$located = $template_paths;
	}
	return $located;
}

/**
 * Get question type template mapping
 *
 * Maps question type IDs to template names
 *
 * @return array Question type mapping
 */
function qsm_get_question_type_templates() {
	$question_types = array(
		'0'  => 'multiple-choice',
		'1'  => 'multiple-choice-horizontal', 
		'2'  => 'drop-down',
		'3'  => 'short-answer',
		'4'  => 'multiple-response',
		'5'  => 'paragraph',
		'6'  => 'text-block',
		'7'  => 'number',
		'8'  => 'opt-in',
		'9'  => 'captcha',
		'10' => 'multiple-response-horizontal',
		'11' => 'file-upload',
		'12' => 'date',
		'13' => 'polar',
		'14' => 'fill-blank',
	);
	
	return apply_filters( 'qsm_question_type_templates', $question_types );
}

/**
 * Get question template for a specific question type
 *
 * @param string|int $question_type Question type ID
 * @param array      $args Template arguments
 * @return string Template output
 */
function qsm_get_question_template( $question_type, $args = array() ) {
	$question_types = qsm_get_question_type_templates();
	$template_name = isset( $question_types[ $question_type ] ) ? $question_types[ $question_type ] : 'multiple-choice';
	
	// Try to load the specific question template
	$template_slug = 'questions/' . $template_name;
	$located = qsm_new_locate_template( $template_slug . '.php' );
	
	// Fallback to multiple-choice if template not found
	if ( ! $located && $template_name !== 'multiple-choice' ) {
		$template_slug = 'questions/multiple-choice';
		$located = qsm_new_locate_template( $template_slug . '.php' );
	}
	
	// Apply filter to allow custom template selection
	$template_slug = apply_filters( 'qsm_question_template_slug', $template_slug, $question_type, $args );
	
	return qsm_new_get_template_part( $template_slug, $args );
}

/**
 * Get template directory path
 *
 * @return string Template directory path
 */
function qsm_get_template_directory() {
	return QSM_PLUGIN_PATH . 'qsm-11/templates/';
}

/**
 * Get theme template directory path
 *
 * @return string Theme template directory path
 */
function qsm_get_theme_template_directory() {
	return get_stylesheet_directory() . '/qsm/templates/';
}

/**
 * Check if template exists in theme
 *
 * @param string $template_name Template name
 * @return bool True if template exists in theme
 */
function qsm_template_exists_in_theme( $template_name ) {
	$theme_template = qsm_get_theme_template_directory() . $template_name;
	return file_exists( $theme_template );
}

/**
 * Register template hooks for backward compatibility
 */
function qsm_register_template_hooks() {
	// Map old hooks to new template system
	$hook_mappings = array(
		'qsm_before_questions' => 'qsm_before_question_pages',
		'qsm_after_questions' => 'qsm_after_question_pages',
		'qsm_before_pagination' => 'qsm_before_pagination_render',
		'qsm_after_pagination' => 'qsm_after_pagination_render',
		'qsm_before_first_page' => 'qsm_before_first_page',
		'qsm_after_last_page' => 'qsm_after_last_page',
	);
	
	foreach ( $hook_mappings as $old_hook => $new_hook ) {
		add_action( $new_hook, function() use ( $old_hook ) {
			do_action( $old_hook );
		} );
	}
}

// Initialize template hooks
// add_action( 'init', 'qsm_register_template_hooks' );
