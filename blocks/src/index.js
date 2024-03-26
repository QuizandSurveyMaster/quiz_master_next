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