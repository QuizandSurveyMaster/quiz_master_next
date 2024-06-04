import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { escapeAttribute } from "@wordpress/escape-html";
import {
    Button,
	TextControl,
	ToggleControl,
	SelectControl,
	CheckboxControl,
	RadioControl,
	TextareaControl,
} from '@wordpress/components';
import { qsmIsEmpty, qsmStripTags } from '../helper';

/**
 * Create Input component based on data provided
 * id: attribute name
 * type: input type
 */
const noop = () => {};
export default function InputComponent( {
     className='', 
     quizAttr, 
     setAttributes,
     data,
     onChangeFunc = noop,
} ) {

    const processData = ( ) => {
        data.defaultvalue = data.default;
        if ( ! qsmIsEmpty( data?.options ) ) {
            switch (data.type) {
                case 'checkbox':
                    if ( 1 === data.options.length ) {
                        data.type = 'toggle';
                    }
                    data.label = data.options[0].label;
                break;
                case 'radio':
                    if ( 1 == data.options.length ) {
						data.label = data.options[0].label;
						data.type = 'toggle';
                    } else {
						data.type = 'select';
					}
                break;
                default:
                    break;
            }
        }
        data.label = qsmIsEmpty( data.label ) ? '': escapeAttribute( data.label );
        data.help = qsmIsEmpty( data.help ) ? '': escapeAttribute( data.help );
        return data;
    }
    
    const newData = processData();
    const {
        id,
        label='',
        type,
        help='',
        options=[],
        defaultvalue 
    } = newData;

    return(
		<>
		{ 'toggle' === type && (
			<ToggleControl
				label={ label }
				help={ help }
				checked={ ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id]  }
				onChange={ () => onChangeFunc( ( ( ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id] ) ? 0 : 1 ), id ) }
			/>
		)}
		{ 'select' === type &&  (	
			<SelectControl
				label={ label }
				value={ quizAttr[id] ?? defaultvalue }
				options={ options }
				onChange={ ( val ) => onChangeFunc( val, id) }
				help={ help }
				__nextHasNoMarginBottom
			/>
		)}
		{ 'number' === type &&  (	
			<TextControl
				type='number'
				label={ label }
				value={ quizAttr[id] ?? defaultvalue }
				onChange={ ( val ) => onChangeFunc( val, id) }
				help={ help }
				__nextHasNoMarginBottom
			/>
		)}
		{ 'text' === type &&  (	
			<TextControl
				type='text'
				label={ label }
				value={ quizAttr[id] ?? defaultvalue }
				onChange={ ( val ) => onChangeFunc( val, id) }
				help={ help }
				__nextHasNoMarginBottom
			/>
		)}
		{ 'textarea' === type &&  (	
			<TextareaControl
                label={ label }
				value={ quizAttr[id] ?? defaultvalue }
				onChange={ ( val ) => onChangeFunc( val, id) }
				help={ help }
				__nextHasNoMarginBottom
        	/>
		)}
		{ 'checkbox' === type &&  (	
			<CheckboxControl
				label={ label }
				help={ help }
				checked={ ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id]  }
				onChange={ () => onChangeFunc( ( ( ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id] ) ? 0 : 1 ), id ) }
			/>
		)}
		{ 'radio' === type &&  (	
			<RadioControl
				label={ label }
				help={ help }
				selected={ quizAttr[id] ?? defaultvalue }
				options={ options }
				onChange={ ( val ) => onChangeFunc( val, id) }
			/>
		)}
		</>
	);
}