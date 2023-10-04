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
	RangeControl,
	RadioControl,
	SelectControl,
} from '@wordpress/components';
import { qsmIsEmpty, qsmStripTags } from '../helper';
/**
 * https://github.com/WordPress/gutenberg/blob/HEAD/packages/block-editor/src/components/rich-text/README.md#allowedformats-array
 *  
 */

export default function Edit( props ) {
	//check for QSM initialize data
	if ( 'undefined' === typeof qsmBlockData ) {
		return null;
	}
	
	const { className, attributes, setAttributes, isSelected, clientId, context } = props;

	/** https://github.com/WordPress/gutenberg/issues/22282  */
	const isParentOfSelectedBlock = useSelect( ( select ) => isSelected || select( 'core/block-editor' ).hasSelectedInnerBlock( clientId, true ) );

	const quizID = context['quiz-master-next/quizID'];
	const pageID = context['quiz-master-next/pageID'];
	
	const {
		questionID,
		type,
		description,
		title,
		correctAnswerInfo,
		commentBox,
		category,
		multicategories,
		hint,
		featureImageID,
		featureImageSrc,
		answers,
		answerEditor,
		matchAnswer,
		otherSettings,
		settings,
	} = attributes;

	const [ quesAttr, setQuesAttr ] = useState( settings );

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
		className: isParentOfSelectedBlock ? ' in-editing-mode':'' ,
	} );

	//Decode htmlspecialchars
	const decodeHtml = ( html ) => {
		var txt = document.createElement("textarea");
		txt.innerHTML = html;
		return txt.value;
	}

	const QUESTION_TEMPLATE = [
		[
			'qsm/quiz-answer-option',
			{
				optionID:'0'
			}
		],
		[
			'qsm/quiz-answer-option',
			{
				optionID:'1'
			}
		]

	];

	return (
	<>
	<InspectorControls>
		<PanelBody title={ __( 'Question settings', 'quiz-master-next' ) } initialOpen={ true }>
		<h2 className="block-editor-block-card__title">{ __( 'ID', 'quiz-master-next' )+': '+questionID }</h2>
		{ /** Question Type **/ }
		<SelectControl
			label={ qsmBlockData.question_type.label }
			value={ type || qsmBlockData.question_type.default }
			onChange={ ( type ) =>
				setAttributes( { type } )
			}
			__nextHasNoMarginBottom
		>
			{
			  ! qsmIsEmpty( qsmBlockData.question_type.options ) && qsmBlockData.question_type.options.map( qtypes => 
				(
				<optgroup label={ qtypes.category } key={ "qtypes"+qtypes.category } >
					{
						qtypes.types.map( qtype => 
							(
							   <option value={ qtype.slug } key={ "qtype"+qtype.slug } >{ qtype.name }</option>
							)
						)
					}
				</optgroup>
				)
				)
			}
	   	</SelectControl>
		{/**Answer Type */}
		<SelectControl
			label={ qsmBlockData.answerEditor.label }
			value={ answerEditor || qsmBlockData.answerEditor.default }
			options={ qsmBlockData.answerEditor.options }
			onChange={ ( answerEditor ) =>
				setAttributes( { answerEditor } )
			}
			__nextHasNoMarginBottom
		/>
		</PanelBody>
		{/**Comment Box */}
		<PanelBody title={ qsmBlockData.commentBox.heading } >
		<SelectControl
			label={ qsmBlockData.commentBox.label }
			value={ commentBox || qsmBlockData.commentBox.default }
			options={ qsmBlockData.commentBox.options }
			onChange={ ( commentBox ) =>
				setAttributes( { commentBox } )
			}
			__nextHasNoMarginBottom
		/>
		</PanelBody>
	</InspectorControls>
	<div  { ...blockProps } >
		<RichText
			tagName='h4'
			aria-label={ __( 'Question title', 'quiz-master-next' ) }
			placeholder={  __( 'Type your question here', 'quiz-master-next' ) }
			value={ title }
			onChange={ ( title ) => setAttributes( { title: qsmStripTags( title ) } ) }
			allowedFormats={ [ ] }
			withoutInteractiveFormatting
			className={ 'qsm-question-title' }
		/>
		{
			isParentOfSelectedBlock && 
			<>
			<RichText
				tagName='p'
				aria-label={ __( 'Question description', 'quiz-master-next' ) }
				placeholder={  __( 'Description goes here', 'quiz-master-next' ) }
				value={ decodeHtml( description ) }
				onChange={ ( description ) => setAttributes({ description }) }
				className={ 'qsm-question-description' }
				__unstableEmbedURLOnPaste
				__unstableAllowPrefixTransformations
			/>
			<InnerBlocks
				allowedBlocks={ ['qsm/quiz-answer-option'] }
				template={ QUESTION_TEMPLATE }
			/>
			<RichText
				tagName='p'
				aria-label={ __( 'Correct Answer Info', 'quiz-master-next' ) }
				placeholder={  __( 'Correct answer info goes here', 'quiz-master-next' ) }
				value={ decodeHtml( correctAnswerInfo ) }
				onChange={ ( correctAnswerInfo ) => setAttributes({ correctAnswerInfo }) }
				className={ 'qsm-question-correct-answer-info' }
				__unstableEmbedURLOnPaste
				__unstableAllowPrefixTransformations
			/>
			<RichText
				tagName='p'
				aria-label={ __( 'Hint', 'quiz-master-next' ) }
				placeholder={  __( 'hint goes here', 'quiz-master-next' ) }
				value={ hint }
				onChange={ ( hint ) => setAttributes( { hint: qsmStripTags( hint ) } ) }
				allowedFormats={ [ ] }
				withoutInteractiveFormatting
				className={ 'qsm-question-hint' }
			/>
			</>
		}
	</div>
	</>
	);
}
