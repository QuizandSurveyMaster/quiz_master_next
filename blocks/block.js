var quizListEndPoint =  wpApiSettings.root+'quiz-survey-master/v2/quizzlist/';
var quizList;
jQuery.ajax( quizListEndPoint , {
	data: null ,
	method: 'GET',
	success : function(response){
		quizList = response;
	}
});

var el = wp.element.createElement,
	registerBlockType = wp.blocks.registerBlockType,
	ServerSideRender = wp.components.ServerSideRender,
	TextControl = wp.components.TextControl,
	SelectControl = wp.components.SelectControl,
	InspectorControls = wp.editor.InspectorControls;

/*
 * Registers the main QSM block
 */
registerBlockType( 'qsm/main-block', {
	title: 'QSM Block',
	icon: 'feedback',
	category: 'widgets',        

	edit: function( props ) {
  	const quiz_arr = quizList;
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
				el( 'p', { style: { padding: '0 16px' } },
					el( SelectControl, {
						label: 'Quiz/Survey ID',
						value: props.attributes.quiz,
						options: quiz_arr,
						onChange: ( value ) => { props.setAttributes( { quiz: value } ); },
					} )
				)				
    	)				
		];
	},

	// We're going to be rendering in PHP, so save() can just return null.
	save: function() {
		return null;
	},
} );