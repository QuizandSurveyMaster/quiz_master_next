import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import { answerOptionBlockIcon } from "../component/icon";

registerBlockType( metadata.name, {
	icon: answerOptionBlockIcon,
	merge( attributes, attributesToMerge ) {
		return {
			content:
				( attributes.content || '' ) +
				( attributesToMerge.content || '' ),
		};
	},
	edit: Edit,
} );
