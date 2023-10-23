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
} );
