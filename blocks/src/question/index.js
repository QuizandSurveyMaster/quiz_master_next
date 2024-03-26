import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import { questionBlockIcon } from "../component/icon";

registerBlockType( metadata.name, {
	icon:questionBlockIcon,
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
	__experimentalLabel( attributes, { context } ) {
		const { title } = attributes;

		const customName = attributes?.metadata?.name;
		const hasContent = title?.length > 0;

		// In the list view, use the question title as the label.
		// If the title is empty, fall back to the default label.
		if ( context === 'list-view' && ( customName || hasContent ) ) {
			return customName || title;
		}
	},
} );
