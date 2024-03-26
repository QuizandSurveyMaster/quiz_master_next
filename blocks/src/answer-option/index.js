import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import { answerOptionBlockIcon } from "../component/icon";

registerBlockType( metadata.name, {
	icon: answerOptionBlockIcon,
	__experimentalLabel( attributes, { context } ) {
		const { content } = attributes;

		const customName = attributes?.metadata?.name;
		const hasContent = content?.length > 0;

		// In the list view, use the answer content as the label.
		// If the content is empty, fall back to the default label.
		if ( context === 'list-view' && ( customName || hasContent ) ) {
			return customName || content;
		}
	},
	merge( attributes, attributesToMerge ) {
		return {
			content:
				( attributes.content || '' ) +
				( attributesToMerge.content || '' ),
		};
	},
	edit: Edit,
} );
