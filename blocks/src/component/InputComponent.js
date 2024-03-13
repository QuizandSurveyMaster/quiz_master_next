import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import {
    Button,
	TextControl,
	ToggleControl,
	SelectControl,
	CheckboxControl,
	RadioControl,
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
                    if ( 1 < data.options.length ) {
                        data.type = 'select';
                    } else {
                        data.label = data.options[0].label;
                    }
                break;
                default:
                    break;
            }
        }
        data.label = qsmIsEmpty( data.label ) ? '': qsmStripTags( data.label );
        data.help = qsmIsEmpty( data.help ) ? '': qsmStripTags( data.help );
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

    const getComponent = ( ) => {
		const key = 'quiz-create-toggle-'+id;
		switch ( type ) {
			case 'toggle':
				return (
					<ToggleControl
						label={ label }
						help={ help }
						checked={ ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id]  }
						onChange={ () => onChangeFunc( ( ( ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id] ) ? 0 : 1 ), id ) }
					/>
				);
				break;
			case 'select':
				return (	
					<SelectControl
						label={ label }
						value={ quizAttr[id] ?? defaultvalue }
						options={ options }
						onChange={ ( val ) => onChangeFunc( val, id) }
						help={ help }
						__nextHasNoMarginBottom
					/>
				);
				break;
			case 'number':
				return (	
					<TextControl
						type='number'
						label={ label }
						value={ quizAttr[id] ?? defaultvalue }
						onChange={ ( val ) => onChangeFunc( val, id) }
						help={ help }
						__nextHasNoMarginBottom
					/>
				);
				break;
			case 'text':
				return (
					<TextControl
						type='text'
						label={ label }
						value={ quizAttr[id] ?? defaultvalue }
						onChange={ ( val ) => onChangeFunc( val, id) }
						help={ help }
						__nextHasNoMarginBottom
					/>
				);
			break;
            case 'checkbox':
				return (
					<CheckboxControl
						label={ label }
						help={ help }
						checked={ ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id]  }
						onChange={ () => onChangeFunc( ( ( ! qsmIsEmpty( quizAttr[id] ) && '1' == quizAttr[id] ) ? 0 : 1 ), id ) }
					/>
				);
			break;
			default:
				return (
					<TextControl
						type='text'
						label={ label }
						value={ quizAttr[id] ?? defaultvalue }
						onChange={ ( val ) => onChangeFunc( val, id) }
						help={ help }
						__nextHasNoMarginBottom
					/>
				);
				break;
		}
	}

    return getComponent();
}