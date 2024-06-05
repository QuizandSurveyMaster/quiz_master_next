import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { escapeAttribute } from "@wordpress/escape-html";
import apiFetch from '@wordpress/api-fetch';
import {
	InspectorControls,
	RichText,
	InnerBlocks,
	useBlockProps,
	store as blockEditorStore,
	BlockControls,
} from '@wordpress/block-editor';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch, useSelect, select } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import {
	PanelBody,
	ToggleControl,
	SelectControl,
	ToolbarGroup, 
	ToolbarButton,
	TextControl,
	CheckboxControl,
	FormTokenField,
	Button,
	ExternalLink,
	Modal
} from '@wordpress/components';
import FeaturedImage from '../component/FeaturedImage';
import SelectAddCategory from '../component/SelectAddCategory';
import { warningIcon, plusIcon } from "../component/icon";
import { qsmIsEmpty, qsmStripTags, qsmFormData, qsmValueOrDefault, qsmDecodeHtml, qsmUniqueArray, qsmMatchingValueKeyArray } from '../helper';


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

	//Get finstion to find index of blocks
	const { 
		getBlockRootClientId, 
		getBlockIndex 
	} = useSelect( blockEditorStore );

	//Get funstion to insert block
	const {
		insertBlock
	} = useDispatch( blockEditorStore );

	const {
		isChanged = false,//use in editor only to detect if any change occur in this block
		questionID,
		type,
		description,
		title,
		correctAnswerInfo,
		commentBox,
		category,
		multicategories=[],
		hint,
		featureImageID,
		featureImageSrc,
		answers,
		answerEditor,
		matchAnswer,
		required,
		settings={},
	} = attributes;
	
	//Variable to decide if correct answer info input field should be available 
	const [ enableCorrectAnsInfo, setEnableCorrectAnsInfo ] = useState( ! qsmIsEmpty( correctAnswerInfo ) );
	//Advance Question modal
	const [ isOpenAdvanceQModal, setIsOpenAdvanceQModal ] = useState( false );

	const proActivated = ( '1' == qsmBlockData.is_pro_activated );
	const isAdvanceQuestionType = ( qtype ) => 14 < parseInt( qtype );

	//Available file types
	const fileTypes = qsmBlockData.file_upload_type.options;

	//Get selected file types
	const selectedFileTypes = () => {
		let file_types = settings?.file_upload_type || qsmBlockData.file_upload_type.default;
		return qsmIsEmpty( file_types ) ? [] : file_types.split(',');
	}

	//Is file type checked
	const isCheckedFileType = ( fileType ) => selectedFileTypes().includes( fileType );

	//Set file type
	const setFileTypes = ( fileType ) => {
		let file_types = selectedFileTypes();
		if ( file_types.includes( fileType ) ) {
			file_types = file_types.filter( file_type => file_type != fileType );
		} else {
			file_types.push( fileType );
		}
		file_types = file_types.join(',');
		setAttributes({
			settings:{
				...settings,
				file_upload_type: file_types
			}
		})
	}
	
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
					"required": qsmValueOrDefault( required, 0 ),
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

	//detect change in question
	useEffect( () => {
		let shouldSetChanged = true;
		if ( shouldSetChanged && isSelected  && false === isChanged ) {
			
			setAttributes( { isChanged: true } );
		}

		//cleanup
		return () => {
			shouldSetChanged = false;
		};
	}, [
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
	] )

	//add classes
	const blockProps = useBlockProps( {
		className: isParentOfSelectedBlock ? ' in-editing-mode is-highlighted ':'' ,
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

	//Get category ancestor
	const getCategoryAncestors = ( termId, categories ) => {
		let parents = [];
		if ( ! qsmIsEmpty( categories[ termId ] ) && '0' != categories[ termId ]['parent'] ) {
			termId = categories[ termId ]['parent'];
			parents.push( termId );
			if ( ! qsmIsEmpty( categories[ termId ] ) && '0' != categories[ termId ]['parent'] ) {
				let ancestor = getCategoryAncestors( termId, categories );
				parents = [ ...parents, ...ancestor ];
			}
		} 
		
		return qsmUniqueArray( parents );
	 }

	//check if a category is selected
	const isCategorySelected = ( termId ) =>  multicategories.includes( termId );

	//set or unset category
	const setUnsetCatgory = ( termId, categories ) => {
		let multiCat = ( qsmIsEmpty( multicategories ) || 0 === multicategories.length ) ? ( qsmIsEmpty( category ) ? [] : [ category ] ) : multicategories;
		
		//Case: category unselected
		if ( multiCat.includes( termId ) ) {
			//remove category if already set
			multiCat = multiCat.filter( catID =>  catID != termId );
			let children = [];
			//check for if any child is selcted 
			multiCat.forEach( childCatID => {
				//get ancestors of category
				let ancestorIds = getCategoryAncestors( childCatID, categories );
				//given unselected category is an ancestor of selected category
				if ( ancestorIds.includes( termId ) ) {
					//remove category if already set
					multiCat = multiCat.filter( catID =>  catID != childCatID );
				}
			});
		} else {
			//add category if not set
			multiCat.push( termId );
			//get ancestors of category
			let ancestorIds = getCategoryAncestors( termId, categories );
			//select all ancestor
			multiCat = [ ...multiCat, ...ancestorIds ];
		}

		multiCat = qsmUniqueArray( multiCat );

		setAttributes({ 
			category: '',
			multicategories: [ ...multiCat ]
		});
	}

	//Notes relation to question type
	const notes = ['12','7','3','5','14'].includes( type ) ? __( 'Note: Add only correct answer options with their respective points score.', 'quiz-master-next' ) : '';

	//set Question type
	const setQuestionType = ( qtype ) => {
		if ( ! qsmIsEmpty( MicroModal ) && ! proActivated && ['15', '16', '17'].includes( qtype ) ) {
			//Show modal for advance question type
			let modalEl = document.getElementById('modal-advanced-question-type');
			if ( ! qsmIsEmpty( modalEl ) ) {
				MicroModal.show('modal-advanced-question-type');
			}
		} else if ( proActivated && isAdvanceQuestionType( qtype ) ) {
			setIsOpenAdvanceQModal( true );
		} else {
			setAttributes( { type: qtype } );
		}
	}

	//insert new Question
	const insertNewQuestion = () => {
		if ( qsmIsEmpty( props?.name )) {
			console.log("block name not found");
			return true;
		}
		const blockToInsert = createBlock( props.name );
	
		const selectBlockOnInsert = true;
		insertBlock(
			blockToInsert,
			getBlockIndex( clientId ) + 1,
			getBlockRootClientId( clientId ),
			selectBlockOnInsert
		);
	}

	//insert new Question
	const insertNewPage = () => {
		const blockToInsert = createBlock( 'qsm/quiz-page' );
		const currentPageClientID = getBlockRootClientId( clientId );
		const newPageIndex = getBlockIndex( currentPageClientID ) + 1;
		const qsmBlockClientID = getBlockRootClientId( currentPageClientID );
		const selectBlockOnInsert = true;
		insertBlock(
			blockToInsert,
			newPageIndex,
			qsmBlockClientID,
			selectBlockOnInsert
		);
	}

	return (
	<>
		<BlockControls>
			<ToolbarGroup>
				<ToolbarButton
					icon='plus-alt2'
					label={ __( 'Add New Question', 'quiz-master-next' ) }
					onClick={ () => insertNewQuestion() }
				/>
				<ToolbarButton
					icon='welcome-add-page'
					label={ __( 'Add New Page', 'quiz-master-next' ) }
					onClick={ () => insertNewPage() }
				/>
			</ToolbarGroup>
		</BlockControls>
	{ isOpenAdvanceQModal && (
		<Modal 
		contentLabel={ __( 'Use QSM Editor for Advanced Question', 'quiz-master-next' ) }
		className='qsm-advance-q-modal'
		isDismissible={ false }
		size='small'
		__experimentalHideHeader={ true }
		>
			<div className='qsm-modal-body' >
				<h3 className='qsm-title'>
					{ warningIcon() }
					<br />
					{ __( 'Use QSM editor for Advanced Question', 'quiz-master-next' ) }
				</h3>
				<p className='qsm-description'>
					{ __( "Currently, the block editor doesn't support advanced question type. We are working on it. Alternatively, you can add advanced questions from your QSM's quiz editor.", "quiz-master-next" ) }
				</p>
				<div className='qsm-modal-btn-wrapper'>
					<Button variant="secondary" onClick={ () => setIsOpenAdvanceQModal( false ) }>
						{ __( 'Cancel', 'quiz-master-next' ) }
					</Button>
					<Button variant="primary" onClick={ () => {} }>
						<ExternalLink 
							href={ qsmBlockData.quiz_settings_url+'&quiz_id='+quizID }
						>
							{ __( 'Add Question from quiz editor', 'quiz-master-next' ) }
						</ExternalLink>
					</Button>
				</div>
			</div>
			
		</Modal>
	) }
	 { isAdvanceQuestionType( type ) ? (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Question settings', 'quiz-master-next' ) } initialOpen={ true }>
					<h2 className="block-editor-block-card__title">{ __( 'ID', 'quiz-master-next' )+': '+questionID }</h2>
					<h3>{ __( 'Advanced Question Type', 'quiz-master-next' ) }</h3>
				</PanelBody>
			</InspectorControls>	
			<div  { ...blockProps } >
			<h4 className={ 'qsm-question-title qsm-error-text' } >{ __( 'Advanced Question Type : ', 'quiz-master-next' ) + title }</h4>
			<p> 
			{ __( 'Edit question in QSM ', 'quiz-master-next' ) }
			<ExternalLink 
				href={ qsmBlockData.quiz_settings_url+'&quiz_id='+quizID }
			>
					{ __( 'editor', 'quiz-master-next' ) }
			</ExternalLink>
			</p>
			</div>
		</>
		):(
		<>
		<InspectorControls>
			<PanelBody title={ __( 'Question settings', 'quiz-master-next' ) } initialOpen={ true }>
			<h2 className="block-editor-block-card__title">{ __( 'ID', 'quiz-master-next' )+': '+questionID }</h2>
			{ /** Question Type **/ }
			<SelectControl
				label={ qsmBlockData.question_type.label }
				value={ type || qsmBlockData.question_type.default }
				onChange={ ( type ) =>
					setQuestionType( type )
				}
				help={ qsmIsEmpty( qsmBlockData.question_type_description[ type ] ) ? '' : qsmBlockData.question_type_description[ type ]+' '+notes }
				__nextHasNoMarginBottom
			>
				{
				! qsmIsEmpty( qsmBlockData.question_type.options ) && qsmBlockData.question_type.options.map( qtypes => 
					(
					<optgroup label={ qtypes.category } key={ "qtypes"+qtypes.category }  >
						{
							qtypes.types.map( qtype => 
								(
								<option value={ qtype.slug } key={ "qtype"+qtype.slug }  >{ qtype.name }</option>
								)
							)
						}
					</optgroup>
					)
					)
				}
			</SelectControl>
			{/**Answer Type */}
			{
				['0','4','1','10','13'].includes( type ) && 
				<SelectControl
					label={ qsmBlockData.answerEditor.label }
					value={ answerEditor || qsmBlockData.answerEditor.default }
					options={ qsmBlockData.answerEditor.options }
					onChange={ ( answerEditor ) =>
						setAttributes( { answerEditor } )
					}
					__nextHasNoMarginBottom
				/>
			}
			<ToggleControl
				label={ __( 'Required', 'quiz-master-next' ) }
				checked={ ! qsmIsEmpty( required ) && '1' == required  }
				onChange={ () => setAttributes( { required : ( ( ! qsmIsEmpty( required ) && '1' == required ) ? 0 : 1 ) } ) }
			/>
			<ToggleControl
				label={ __( 'Show Correct Answer Info', 'quiz-master-next' ) }
				checked={ enableCorrectAnsInfo  }
				onChange={ () => setEnableCorrectAnsInfo( ! enableCorrectAnsInfo ) }
			/>
			</PanelBody>
			{/**File Upload */}
			{ '11' == type && (
				<PanelBody title={ __( 'File Settings', 'quiz-master-next' ) } initialOpen={ false }>
				{/**Upload Limit */}
				<TextControl
					type='number'
					label={ qsmBlockData.file_upload_limit.heading  }
					value={ settings?.file_upload_limit ?? qsmBlockData.file_upload_limit.default }
					onChange={ ( limit ) => setAttributes( { settings:{
						...settings,
						file_upload_limit: limit
					} } ) }
				/>
				{/**Allowed File Type */}
				<label className="qsm-inspector-label">
					{ qsmBlockData.file_upload_type.heading }
				</label>
				{
					Object.keys( qsmBlockData.file_upload_type.options ).map( filetype => (
						<CheckboxControl
							key={ 'filetype-'+filetype }
							label={  fileTypes[filetype] }
							checked={ isCheckedFileType( filetype ) }
							onChange={ () => setFileTypes( filetype ) }
						/>
					) )
				}
				</PanelBody>
			)}
			{/**Categories */}
			<SelectAddCategory 
				isCategorySelected={ isCategorySelected }
				setUnsetCatgory={ setUnsetCatgory }
			/>
			{/**Hint */}
			<PanelBody title={ __( 'Hint', 'quiz-master-next' ) } initialOpen={ false }  >
			<TextControl
				label=''
				value={ hint }
				onChange={ ( hint ) => setAttributes( { hint: escapeAttribute( hint ) } ) }
			/>
			</PanelBody>
			{/**Comment Box */}
			<PanelBody title={ qsmBlockData.commentBox.heading } initialOpen={ false } >
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
		</InspectorControls>
		<div  { ...blockProps } >
			<RichText
				tagName='h4'
				title={ __( 'Question title', 'quiz-master-next' ) }
				aria-label={ __( 'Question title', 'quiz-master-next' ) }
				placeholder={  __( 'Type your question here', 'quiz-master-next' ) }
				value={ escapeAttribute( title ) }
				onChange={ ( title ) => setAttributes( { title: escapeAttribute( title ) } ) }
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
					placeholder={  __( 'Description goes here... (optional)', 'quiz-master-next' ) }
					value={ qsmDecodeHtml( description ) }
					onChange={ ( description ) => setAttributes({ description }) }
					className={ 'qsm-question-description' }
					__unstableEmbedURLOnPaste
					__unstableAllowPrefixTransformations
				/>
				{
					! ['8','11','6','9'].includes( type ) &&
					<InnerBlocks
						allowedBlocks={ ['qsm/quiz-answer-option'] }
						template={ QUESTION_TEMPLATE }
					/>
				}
				{
					enableCorrectAnsInfo && (
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
					)
				}
				{
					isParentOfSelectedBlock && ( 
					<div className='block-editor-block-list__insertion-point-inserter qsm-add-new-ques-wrapper'>
					<Button 
					icon={ plusIcon }
					label={ __( 'Add New Question', 'quiz-master-next' ) }
					tooltipPosition="bottom"
					onClick={ () => insertNewQuestion() }
					variant="secondary"
					className='add-new-question-btn block-editor-inserter__toggle'
					>
					</Button>
					</div>
					)
				}
				
				</>
			}
		</div>
		</>
		)
	}
	</>
	);
}
