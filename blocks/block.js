
var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	TextControl = wp.components.TextControl,
	InspectorControls = wp.editor.InspectorControls;

/*
 * Registers the main QSM block
 */
registerBlockType( 'qsm/main-block', {
	title: 'QSM Block',
	icon: 'feedback',
	category: 'widgets',

	edit: function( props ) {
		return [
			/*
			 * The ServerSideRender element uses the REST API to automatically call
			 * php_block_render() in your PHP code whenever it needs to get an updated
			 * view of the block.
			 */
			el( ServerSideRender, {
				block: 'qsm/main-block',
				attributes: props.attributes,
			} ),
			
			el( InspectorControls, {},
				el( TextControl, {
					label: 'Quiz/Survey ID',
					value: props.attributes.quiz,
					onChange: ( value ) => { props.setAttributes( { quiz: value } ); },
				} )
			),
		];
	},

	// We're going to be rendering in PHP, so save() can just return null.
	save: function() {
		return null;
	},
} );