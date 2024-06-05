import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { escapeAttribute } from "@wordpress/escape-html";
import {
	InspectorControls,
	store as blockEditorStore,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { useDispatch, useSelect, select } from '@wordpress/data';
import { isURL } from '@wordpress/url';
import {
	PanelBody,
	TextControl,
	ToggleControl,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { createBlock } from '@wordpress/blocks';
import ImageType from '../component/ImageType';
import { qsmIsEmpty, qsmStripTags, qsmDecodeHtml } from '../helper';
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
	const answerType = context['quiz-master-next/answerType'];
	const questionChanged = context['quiz-master-next/questionChanged'];

	const name = 'qsm/quiz-answer-option';
	const {
		optionID,
		content,
		caption,
		points,
		isCorrect
	} = attributes;

	const {
		selectBlock,
	} = useDispatch( blockEditorStore );

	//Use to to update block attributes using clientId
	const { updateBlockAttributes } = useDispatch( blockEditorStore );

	const questionClientID = useSelect(
		( select ) => {
			//get parent gutena form clientIds
			let questionClientID = select( blockEditorStore ).getBlockParentsByBlockName( clientId,'qsm/quiz-question', true );
			return qsmIsEmpty( questionClientID ) ? '': questionClientID[0];
		},
		[ clientId ]
	);

	//detect change in question
	useEffect( () => {
		let shouldSetChanged = true;
		if ( shouldSetChanged && isSelected && ! qsmIsEmpty( questionClientID ) && false === questionChanged ) {
			updateBlockAttributes( questionClientID, { isChanged: true } );
		}

		//cleanup
		return () => {
			shouldSetChanged = false;
		};
	}, [
		content,
		caption,
		points,
		isCorrect
	] )

	/**Initialize block from server */
	useEffect( () => {
		let shouldSetQSMAttr = true;
		if ( shouldSetQSMAttr  ) {
			if ( ! qsmIsEmpty( content ) && isURL( content ) && ( -1 !== content.indexOf('https://') || -1 !== content.indexOf('http://') ) && ['rich','text'].includes( answerType ) ) {
				setAttributes({
					content:'',
					caption:''
				})
			}
		}
		
		//cleanup
		return () => {
			shouldSetQSMAttr = false;
		};
		
	}, [ answerType ] );

	const blockProps = useBlockProps( {
		className: isSelected ? ' is-highlighted ': '',
	} );

	const inputType = ['4','10'].includes( questionType ) ? "checkbox":"radio";
	
	return (
	<>
	<InspectorControls>
		<PanelBody title={ __( 'Settings', 'quiz-master-next' ) } initialOpen={ true }>
			{ /**Image answer option */
				'image' === answerType &&
				<TextControl
					type='text'
					label={ __( 'Caption', 'quiz-master-next' ) }
					value={ caption }
					onChange={ ( caption ) => setAttributes( { caption: escapeAttribute( caption ) } ) }
				/>
			}
			<TextControl
				type='number'
				label={ __( 'Points', 'quiz-master-next' ) }
				help={ __( 'Answer points', 'quiz-master-next' ) }
				value={ points }
				onChange={ ( points ) => setAttributes( { points } ) }
			/>
			{
				['0','4','1','10', '2'].includes( questionType ) &&
				<ToggleControl
					label={ __( 'Correct', 'quiz-master-next' ) }
					checked={ ! qsmIsEmpty( isCorrect ) && '1' == isCorrect  }
					onChange={ () => setAttributes( { isCorrect : ( ( ! qsmIsEmpty( isCorrect ) && '1' == isCorrect ) ? 0 : 1 ) } ) }
				/>
			}
		</PanelBody>
	</InspectorControls>
	<div  { ...blockProps } >
		<HStack
			className="edit-post-document-actions__title"
			spacing={ 1 }
			justify='left'
		>
 		<input type={ inputType } disabled={ true }  readOnly tabIndex="-1" />
		{ /**Text answer option*/
			! ['rich','image'].includes( answerType ) && 
			<RichText
				tagName='p'
				title={ __( 'Answer options', 'quiz-master-next' ) }
				aria-label={ __( 'Question answer', 'quiz-master-next' ) }
				placeholder={  __( 'Your Answer', 'quiz-master-next' ) }
				value={ escapeAttribute( content ) }
				onChange={ ( content ) => setAttributes( { content: escapeAttribute( content ) } ) }
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
		}
		{ /**Rich Text answer option */
		   'rich' === answerType && 
			<RichText
				tagName='p'
				title={ __( 'Answer options', 'quiz-master-next' ) }
				aria-label={ __( 'Question answer', 'quiz-master-next' ) }
				placeholder={  __( 'Your Answer', 'quiz-master-next' ) }
				value={ qsmDecodeHtml( decodeEntities( content ) ) }
				onChange={ ( content ) => setAttributes( { content } ) }
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
				className={ 'qsm-question-answer-option' }
				identifier='text'
				__unstableEmbedURLOnPaste
				__unstableAllowPrefixTransformations
			/>
		}
		{ /**Image answer option */
			'image' === answerType &&
			<ImageType 
			url={ isURL( content ) ? content: ''  }
			caption={ caption }
			setURLCaption={ ( url, caption ) => setAttributes({
				content: isURL( url ) ? url: '',
				caption: caption
			}) }
			/>
		}
		
		</HStack>
	</div>
	</>
	);
}
