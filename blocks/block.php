<?php
/**
 * Converts our main shortcode to a simple Gutenberg block.
 * Uses ServerSideRender for now. Need to create a better block soon.
 *
 * Heavily built upon the GPL demo block: https://gist.github.com/pento/cf38fd73ce0f13fcf0f0ae7d6c4b685d
 *
 * @package QSM
 */

/**
 * Register our block.
 */
function qsm_block_init() {
	// Register our block editor script.
	wp_register_script(
		'qsm-quiz-block',
		plugins_url( 'block.js', __FILE__ ),
		array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
	);
	// Register our block, and explicitly define the attributes we accept.
	register_block_type( 'qsm/main-block', array(
		'attributes'      => array(
			'quiz' => array(
				'type' => 'string',
			),
		),
		'editor_script'   => 'qsm-quiz-block',
		'render_callback' => 'qsm_block_render',
	) );
}
add_action( 'init', 'qsm_block_init' );

/**
 * The block renderer.
 *
 * This simply calls our main shortcode renderer.
 *
 * @param array $attributes The attributes that were set on the block.
 */
function qsm_block_render( $attributes ) {
	global $qmnQuizManager;
	return $qmnQuizManager->display_shortcode( $attributes );
}
