import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import metadata from './block.json';
import { qsmBlockIcon } from './component/icon';
import { createHigherOrderComponent } from '@wordpress/compose';

const save = ( props ) => null;
registerBlockType( metadata.name, {
	icon: qsmBlockIcon,
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	save: save,
} );


const withMyPluginControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const { name, className, attributes, setAttributes, isSelected, clientId, context } = props;
		if ( 'core/group' !== name ) {
			return <BlockEdit key="edit" { ...props } />;
		}

		console.log("props",props);
		return (
			<>
				<BlockEdit key="edit" { ...props } />
			</>
		);
	};
}, 'withMyPluginControls' );

wp.hooks.addFilter(
	'editor.BlockEdit',
	'my-plugin/with-inspector-controls',
	withMyPluginControls
);