import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	InspectorControls,
	RichText,
	InnerBlocks,
	useBlockProps,
} from '@wordpress/block-editor';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch, useSelect, select } from '@wordpress/data';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
	RangeControl,
	RadioControl,
	SelectControl,
	CheckboxControl,
} from '@wordpress/components';
import FeaturedImage from '../component/FeaturedImage';
import SelectAddCategory from '../component/SelectAddCategory';
import { qsmIsEmpty, qsmStripTags, qsmFormData, qsmValueOrDefault, qsmDecodeHtml, qsmAddObjToFormData } from '../helper';


//check for duplicate questionID attr
const isQuestionIDReserved = ( questionIDCheck, clientIdCheck ) => {
    const blocksClientIds = select( 'core/block-editor' ).getClientIdsWithDescendants();
    return qsmIsEmpty( blocksClientIds ) ? false : blocksClientIds.some( ( blockClientId ) => {
        const { questionID  } = select( 'core/block-editor' ).getBlockAttributes( blockClientId );
		//different Client Id but same questionID attribute means duplicate
        return clientIdCheck !== blockClientId && questionID === questionIDCheck;
    } );
};

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
	const {
		quiz_name,
		post_id,
		rest_nonce
	} = context['quiz-master-next/quizAttr'];
	const pageID = context['quiz-master-next/pageID'];
	const { createNotice } = useDispatch( noticesStore );

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
		required,
		settings,
	} = attributes;

	const [ quesAttr, setQuesAttr ] = useState( settings );
	
	/**Generate question id if not set or in case duplicate questionID ***/
	useEffect( () => {
		let shouldSetID = true;
		if ( shouldSetID ) {
		
			if ( qsmIsEmpty( questionID ) || '0' == questionID || ( ! qsmIsEmpty( questionID ) && isQuestionIDReserved( questionID, clientId ) ) ) {
				
				//create a question
				let newQuestion = qsmFormData( {
					"id": null,
					"rest_nonce": rest_nonce,
					"quizID": quizID,
					"quiz_name": quiz_name,
					"postID": post_id,
					"answerEditor": qsmValueOrDefault( answerEditor, 'text' ),
					"type": qsmValueOrDefault( type , '0' ),
					"name": qsmDecodeHtml( qsmValueOrDefault( description ) ),
					"question_title": qsmValueOrDefault( title ),
					"answerInfo": qsmDecodeHtml( qsmValueOrDefault( correctAnswerInfo ) ),
					"comments": qsmValueOrDefault( commentBox, '1' ),
					"hint": qsmValueOrDefault( hint ),
					"category": qsmValueOrDefault( category ),
					"multicategories": [],
					"required": qsmValueOrDefault( required, 1 ),
					"answers": answers,
					"page": 0,
					"featureImageID": featureImageID,
					"featureImageSrc": featureImageSrc,
					"matchAnswer": null,
				} );

				//AJAX call
				apiFetch( {
					path: '/quiz-survey-master/v1/questions',
					method: 'POST',
					body: newQuestion
				} ).then( ( response ) => {
					console.log("question created", response);
					if ( 'success' == response.status ) {
						let question_id = response.id;
						setAttributes( { questionID: question_id } );
					}
				}).catch(
					( error ) => {
						console.log( 'error',error );
						createNotice( 'error', error.message, {
							isDismissible: true,
							type: 'snackbar',
						} );
					}
				);
			}
		}
		
		//cleanup
		return () => {
			shouldSetID = false;
		};
		
	}, [] );

	//add classes
	const blockProps = useBlockProps( {
		className: isParentOfSelectedBlock ? ' in-editing-mode':'' ,
	} );

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

	//check if a category is selected
	const isCategorySelected = ( termId ) => ( category == termId || multicategories.includes( termId ) );

	//set or unset category
	const setUnsetCatgory = ( termId ) => {
		if ( qsmIsEmpty( category ) && ( qsmIsEmpty( multicategories ) || 0 === multicategories.length ) ) {
			setAttributes({ category: termId });
		} else if ( termId == category ) {
			setAttributes({ category: '' });
		} else {
			let multiCat = ( qsmIsEmpty( multicategories ) || 0 === multicategories.length ) ? [] : multicategories;

			if ( multiCat.includes( termId ) ) {
				//remove category if already set
				multiCat = multiCat.filter( catID =>  catID != termId );
			} else {
				//add category if not set
				multiCat.push( termId );
				//console.log("add multi", termId);
			}

			setAttributes({ 
				category: '',
				multicategories: [ ...multiCat ]
			});
		}
	}

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
		<ToggleControl
			label={ __( 'Required', 'quiz-master-next' ) }
			checked={ ! qsmIsEmpty( required ) && '1' == required  }
			onChange={ () => setAttributes( { required : ( ( ! qsmIsEmpty( required ) && '1' == required ) ? 0 : 1 ) } ) }
		/>
		</PanelBody>
		{/**Categories */}
		<SelectAddCategory 
			isCategorySelected={ isCategorySelected }
			setUnsetCatgory={ setUnsetCatgory }
		/>
		{/**Feature Image */}
		<PanelBody title={ __( 'Featured image', 'quiz-master-next' ) } initialOpen={ true }>
		    <FeaturedImage 
			featureImageID={ featureImageID }
			onUpdateImage={ ( mediaDetails ) => {
				setAttributes({ 
					featureImageID: mediaDetails.id,
					featureImageSrc: mediaDetails.url
				});
			}  }
			onRemoveImage={ ( id ) => {
				setAttributes({ 
					featureImageID: undefined,
					featureImageSrc: undefined,
				});
			}  }
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
			title={ __( 'Question title', 'quiz-master-next' ) }
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
				title={ __( 'Question description', 'quiz-master-next' ) }
				aria-label={ __( 'Question description', 'quiz-master-next' ) }
				placeholder={  __( 'Description goes here', 'quiz-master-next' ) }
				value={ qsmDecodeHtml( description ) }
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
				title={ __( 'Correct Answer Info', 'quiz-master-next' ) }
				aria-label={ __( 'Correct Answer Info', 'quiz-master-next' ) }
				placeholder={  __( 'Correct answer info goes here', 'quiz-master-next' ) }
				value={ qsmDecodeHtml( correctAnswerInfo ) }
				onChange={ ( correctAnswerInfo ) => setAttributes({ correctAnswerInfo }) }
				className={ 'qsm-question-correct-answer-info' }
				__unstableEmbedURLOnPaste
				__unstableAllowPrefixTransformations
			/>
			<RichText
				tagName='p'
				title={ __( 'Hint', 'quiz-master-next' ) }
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
