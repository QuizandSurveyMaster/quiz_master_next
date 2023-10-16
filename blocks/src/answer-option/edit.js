import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	InspectorControls,
	RichText,
	InnerBlocks,
	useBlockProps,
} from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
	__experimentalHStack as HStack,
	RangeControl,
	RadioControl,
	SelectControl,
} from '@wordpress/components';
import { createBlock } from '@wordpress/blocks';
import { qsmIsEmpty, qsmStripTags } from '../helper';
export default function Edit( props ) {
	
	//check for QSM initialize data
	if ( 'undefined' === typeof qsmBlockData ) {
		return null;
	}

	const { className, attributes, setAttributes, isSelected, clientId, context, mergeBlocks, onReplace,
onRemove } = props;

	const quizID = context['quiz-master-next/quizID'];
	const pageID = context['quiz-master-next/pageID'];
	const questionID = context['quiz-master-next/questionID'];
	const questionType = context['quiz-master-next/questionType'];
	const name = 'qsm/quiz-answer-option';
	const {
		optionID,
		content,
		points,
		isCorrect
	} = attributes;

	/**Initialize block from server */
	useEffect( () => {
		let shouldSetQSMAttr = true;
		if ( shouldSetQSMAttr ) {

			
		}
		
		//cleanup
		return () => {
			shouldSetQSMAttr = false;
		};
		
	}, [ quizID ] );

	const blockProps = useBlockProps( {
		className: '',
	} );

	//Decode htmlspecialchars
	const decodeHtml = (html) => {
		var txt = document.createElement("textarea");
		txt.innerHTML = html;
		return txt.value;
	}

	const inputType = ['4','10'].includes( questionType ) ? "checkbox":"radio";
	
	return (
	<>
	<InspectorControls>
		<PanelBody title={ __( 'Settings', 'quiz-master-next' ) } initialOpen={ true }>
			<TextControl
				type='number'
				label={ __( 'Points', 'quiz-master-next' ) }
				help={ __( 'Answer points', 'quiz-master-next' ) }
				value={ points }
				onChange={ ( points ) => setAttributes( { points } ) }
			/>
			<ToggleControl
				label={ __( 'Correct', 'quiz-master-next' ) }
				checked={ ! qsmIsEmpty( isCorrect ) && '1' == isCorrect  }
				onChange={ () => setAttributes( { isCorrect : ( ( ! qsmIsEmpty( isCorrect ) && '1' == isCorrect ) ? 0 : 1 ) } ) }
			/>
		</PanelBody>
	</InspectorControls>
	<div  { ...blockProps } >
		<HStack
			className="edit-post-document-actions__title"
			spacing={ 1 }
			justify='left'
		>
 		<input type={ inputType }  readOnly tabIndex="-1" />
		<RichText
			tagName='p'
			title={ __( 'Answer options', 'quiz-master-next' ) }
			aria-label={ __( 'Question answer', 'quiz-master-next' ) }
			placeholder={  __( 'Your Answer', 'quiz-master-next' ) }
			value={ content }
			onChange={ ( content ) => setAttributes( { content: qsmStripTags( content ) } ) }
			onSplit={ ( value, isOriginal ) => {
				let newAttributes;

				if ( isOriginal || value ) {
					newAttributes = {
						...attributes,
						content: value,
					};
				}

				const block = createBlock( name, newAttributes );

				if ( isOriginal ) {
					block.clientId = clientId;
				}

				return block;
			} }
			onMerge={ mergeBlocks }
			onReplace={ onReplace }
			onRemove={ onRemove }
			allowedFormats={ [ ] }
			withoutInteractiveFormatting
			className={ 'qsm-question-answer-option' }
			identifier='text'
		/>
		</HStack>
	</div>
	</>
	);
}
