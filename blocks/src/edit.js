import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { decodeEntities } from '@wordpress/html-entities';
import {
	InspectorControls,
	InnerBlocks,
	useBlockProps,
	store as blockEditorStore,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import {
	PanelBody,
	Button,
	TextControl,
	ToggleControl,
	SelectControl,
	Placeholder,
	ExternalLink,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import './editor.scss';
import { qsmIsEmpty, qsmFormData, qsmUniqid, qsmValueOrDefault, qsmDecodeHtml } from './helper';
import InputComponent from './component/InputComponent';
import { qsmBlockIcon } from './component/icon';
export default function Edit( props ) {
	//check for QSM initialize data
	if ( 'undefined' === typeof qsmBlockData ) {
		return null;
	}

	const { className, attributes, setAttributes, isSelected, clientId, context } = props;
	
	const page_post_id = context['postId'];
	const { createNotice } = useDispatch( noticesStore );
	//quiz attribute
	const globalQuizsetting = qsmBlockData.globalQuizsetting;
	const {
		quizID,
		postID,
		quizAttr = globalQuizsetting
	} = attributes;


	//quiz list
	const [ quizList, setQuizList ] = useState( qsmBlockData.QSMQuizList );
	//quiz list
	const [ quizMessage, setQuizMessage ] = useState( {
		error: false,
		msg: ''
	} );
	//whether creating a new quiz
	const [ createQuiz, setCreateQuiz ] = useState( false );
	//whether saving quiz
	const [ saveQuiz, setSaveQuiz ] = useState( false );
	//whether to show advance option
	const [ showAdvanceOption, setShowAdvanceOption ] = useState( false );
	//Quiz template on set Quiz ID
	const [ quizTemplate, setQuizTemplate ] = useState( [] );
	//Quiz Options to create attributes label, description and layout
	const quizOptions = qsmBlockData.quizOptions;

	//check if page is saving
	const isSavingPage = useSelect( ( select ) => {
		const { isAutosavingPost, isSavingPost } = select( editorStore );
		return isSavingPost() && ! isAutosavingPost();
	}, [] );

	const editorSelectors = useSelect( ( select ) => {
		return select( 'core/editor' );
	}, [] );
	
	const { getBlock } = useSelect( blockEditorStore );

	/**Initialize block from server */
	useEffect( () => {
		let shouldSetQSMAttr = true;
		if ( shouldSetQSMAttr ) {
			//add upgrade modal
			if ( '0' == qsmBlockData.is_pro_activated ) {
				setTimeout(() => {
					addUpgradePopupHtml();
				}, 100);
			}
			//initialize QSM block
			if ( ! qsmIsEmpty( quizID ) && 0 < quizID ) {
				//Check if quiz exists
				let hasQuiz = false;
				quizList.forEach( quizElement => {
					if ( quizID == quizElement.value ) {
						hasQuiz = true;
						return true;
					}
				});
				if ( hasQuiz ) {
					initializeQuizAttributes( quizID );
				} else {
					setAttributes({
						quizID : undefined
					});
					setQuizMessage( {
						error: true,
						msg: __( 'Quiz not found. Please select an existing quiz or create a new one.', 'quiz-master-next' )
					} );
				}
				
			}
		}
		
		//cleanup
		return () => {
			shouldSetQSMAttr = false;
		};
		
	}, [ ] );

	/**Add modal advanced-question-type */
	const addUpgradePopupHtml = () => {
		let modalEl = document.getElementById('modal-advanced-question-type');
		if ( qsmIsEmpty( modalEl ) ) {
			apiFetch( {
				path: '/quiz-survey-master/v1/quiz/advance-ques-type-upgrade-popup',
				method: 'POST',
			} ).then( ( res ) => {
				let bodyEl = document.getElementById('wpbody-content');
				if ( ! qsmIsEmpty( bodyEl ) && 'success' == res.status ) { 
					bodyEl.insertAdjacentHTML('afterbegin', res.result );
				}
			} ).catch(
				( error ) => {
					console.log( 'error',error );
				}
			);
		}
	}

	/**Initialize quiz attributes: first time render only */
	const initializeQuizAttributes = ( quiz_id ) => {
		if ( ! qsmIsEmpty( quiz_id ) && 0 < quiz_id  ) {
			apiFetch( {
				path: '/quiz-survey-master/v1/quiz/structure',
				method: 'POST',
				data: { quizID: quiz_id },
			} ).then( ( res ) => {
				
				if ( 'success' == res.status ) {
					setQuizMessage( {
						error: false,
						msg: ''
					} );
					let result = res.result;
					setAttributes( { 
						quizID: parseInt( quiz_id ),
						postID: result.post_id,
						quizAttr: { ...quizAttr, ...result }
					} );
					if ( ! qsmIsEmpty( result.qpages ) ) {
						let quizTemp = [];
						result.qpages.forEach( page  => {
							let questions = [];
							if ( ! qsmIsEmpty( page.question_arr ) ) {
								page.question_arr.forEach( question => {
									if ( ! qsmIsEmpty( question ) ) {
										let answers = [];
										//answers options blocks
										if ( ! qsmIsEmpty( question.answers ) && 0 < question.answers.length ) {
											
											question.answers.forEach( ( answer, aIndex ) => {
												answers.push(
													[
														'qsm/quiz-answer-option',
														{
															optionID:aIndex,
															content:answer[0],
															points:answer[1],
															isCorrect:answer[2],
															caption: qsmValueOrDefault( answer[3] )
														}
													]
												);
											});
										}
										//question blocks
										questions.push(
											[
												'qsm/quiz-question',
												{
													questionID: question.question_id,
													type: question.question_type_new,
													answerEditor: question.settings.answerEditor,
													title: question.settings.question_title,
													description: question.question_name,
													required: question.settings.required,
													hint:question.hints,
													answers: question.answers,
													correctAnswerInfo:question.question_answer_info,
													category:question.category,
													multicategories:question.multicategories,
													commentBox: question.comments,
													matchAnswer: question.settings.matchAnswer,
													featureImageID: question.settings.featureImageID,
													featureImageSrc: question.settings.featureImageSrc,
													settings: question.settings
												},
												answers
											]
										);
									}
								});
							}
							
							quizTemp.push(
								[
									'qsm/quiz-page',
									{
										pageID:page.id,
										pageKey: page.pagekey,
										hidePrevBtn: page.hide_prevbtn,
										quizID: page.quizID
									},
									questions
								]
							)
						});
						setQuizTemplate( quizTemp );
					}
					
				} else {
					console.log( "error "+ res.msg );
				}
			} ).catch(
				( error ) => {
					console.log( 'error',error );
				}
			);
			
		}
	}

	/**
	 * 
	 * @returns Placeholder for quiz in case quiz ID is not set
	 */
	const quizPlaceholder = ( ) => {
		return (
			<Placeholder
				className='qsm-placeholder-wrapper'
				icon={ qsmBlockIcon }
				label={ __( 'Quiz And Survey Master', 'quiz-master-next' ) }
				instructions={ __( 'Easily and quickly add quizzes and surveys inside the block editor.', 'quiz-master-next' ) }
			>
				{
					<>
					{ ( ! qsmIsEmpty( quizList ) && 0 < quizList.length )&&  
					<div className='qsm-placeholder-select-create-quiz'>
					<SelectControl
						label={ __( '', 'quiz-master-next' ) }
						value={ quizID }
						options={ quizList }
						onChange={ ( quizID ) =>
							initializeQuizAttributes( quizID )
						}
						disabled={ createQuiz }
						__nextHasNoMarginBottom
					/>
					<span>{ __( 'OR', 'quiz-master-next' ) }</span>
					<Button 
					variant="link"
					onClick={ () => setCreateQuiz( ! createQuiz )	}
					>
					{ __( 'Add New', 'quiz-master-next' ) }
					</Button>
					</div>
					}
					{ ( qsmIsEmpty( quizList ) || createQuiz ) &&
					<VStack 
					spacing='3'
					className='qsm-placeholder-quiz-create-form'
					>
						<TextControl
							label={ __( 'Quiz Name *', 'quiz-master-next' ) }
							help={ __( 'Enter a name for this Quiz', 'quiz-master-next' ) }
							value={ quizAttr?.quiz_name || '' }
							onChange={ ( val ) => setQuizAttributes( val, 'quiz_name') }
						/>
						<Button 
							variant="link"
							onClick={ () => setShowAdvanceOption( ! showAdvanceOption )	}
							>
							{ __( 'Advance options', 'quiz-master-next' ) }
						</Button>
						<div className='qsm-advance-settings'>
						{ showAdvanceOption && quizOptions.map( qSetting => (
							<InputComponent
								key={ 'qsm-settings'+qSetting.id }
								data={ qSetting }
								quizAttr={ quizAttr }
								setAttributes={ setAttributes }
								onChangeFunc={ setQuizAttributes }
							/>
						))
						}
						</div>
						<Button 
							variant="primary"
							disabled={ saveQuiz || qsmIsEmpty( quizAttr.quiz_name ) }
							onClick={ () => createNewQuiz()	}
							>
							{ __( 'Create Quiz', 'quiz-master-next' ) }
						</Button>
					</VStack>
	                }
					{
						quizMessage.error && (
							<p className='qsm-error-text'>{ quizMessage.msg }</p>
						)
					}
					</>
				}
			</Placeholder>
		);
	};

	/**
	 * Set attribute value
	 * @param { any } value attribute value to set
	 * @param { string } attr_name attribute name
	 */
	const setQuizAttributes = ( value , attr_name ) => {
		let newAttr = quizAttr;
		newAttr[ attr_name ] = value;
		setAttributes( { quizAttr: { ...newAttr } } );
	}

	/**
	 * Prepare quiz data e.g. quiz details, questions, answers etc to save 
	 * @returns quiz data
	 */
	const getQuizDataToSave = ( ) => {	
		let blocks = getBlock( clientId ); 
		if ( qsmIsEmpty( blocks ) ) {
			return false;
		}
		
		blocks = blocks.innerBlocks;
		let quizDataToSave = {
			quiz_id: quizAttr.quiz_id,
			post_id: quizAttr.post_id,
			quiz:{},
			pages:[],
			qpages:[],
			questions:[]
		};
		let pageSNo = 0;
		//loop through inner blocks
		blocks.forEach( (block) => {
			if ( 'qsm/quiz-page' === block.name ) {
				let pageID = block.attributes.pageID;
				let questions = [];
				if ( ! qsmIsEmpty( block.innerBlocks ) && 0 <  block.innerBlocks.length ) { 
					let questionBlocks = block.innerBlocks;
					//Question Blocks
					questionBlocks.forEach( ( questionBlock ) => {
						if ( 'qsm/quiz-question' !== questionBlock.name ) {
							return true;
						}
						
						let questionAttr = questionBlock.attributes;
						let answerEditor = qsmValueOrDefault( questionAttr?.answerEditor, 'text' );
						let answers = [];
						//Answer option blocks
						if ( ! qsmIsEmpty( questionBlock.innerBlocks ) && 0 <  questionBlock.innerBlocks.length ) { 
							let answerOptionBlocks = questionBlock.innerBlocks;
							answerOptionBlocks.forEach( ( answerOptionBlock ) => {
								if ( 'qsm/quiz-answer-option' !== answerOptionBlock.name ) {
									return true;
								}
								let answerAttr = answerOptionBlock.attributes;
								let answerContent = qsmValueOrDefault( answerAttr?.content );
								//if rich text
								if ( ! qsmIsEmpty( questionAttr?.answerEditor ) && 'rich' === questionAttr.answerEditor ) {
									answerContent = qsmDecodeHtml( decodeEntities( answerContent ) );
								}
								let ans = [
									answerContent,
									qsmValueOrDefault( answerAttr?.points ),
									qsmValueOrDefault( answerAttr?.isCorrect ),
								];
								//answer options are image type
								if ( 'image' === answerEditor && ! qsmIsEmpty( answerAttr?.caption ) ) {
									ans.push( answerAttr?.caption );
								}
								answers.push( ans );
							});
						}
						
						//questions Data
						questions.push( questionAttr.questionID );
						//update question only if changes occured
						if ( questionAttr.isChanged ) {
							quizDataToSave.questions.push({
								"id": questionAttr.questionID,
								"quizID": quizAttr.quiz_id,
								"postID": quizAttr.post_id,
								"answerEditor": answerEditor,
								"type": qsmValueOrDefault( questionAttr?.type , '0' ),
								"name": qsmDecodeHtml( qsmValueOrDefault( questionAttr?.description ) ),
								"question_title": qsmValueOrDefault( questionAttr?.title ),
								"answerInfo": qsmDecodeHtml( qsmValueOrDefault( questionAttr?.correctAnswerInfo ) ),
								"comments": qsmValueOrDefault( questionAttr?.commentBox, '1' ),
								"hint": qsmValueOrDefault( questionAttr?.hint ),
								"category": qsmValueOrDefault( questionAttr?.category ),
								"multicategories": qsmValueOrDefault( questionAttr?.multicategories, [] ),
								"required": qsmValueOrDefault( questionAttr?.required, 0 ),
								"answers": answers,
								"featureImageID":qsmValueOrDefault( questionAttr?.featureImageID ),
								"featureImageSrc":qsmValueOrDefault( questionAttr?.featureImageSrc ),
								"page": pageSNo,
								"other_settings": {
									...qsmValueOrDefault( questionAttr?.settings, {} ),
									"required": qsmValueOrDefault( questionAttr?.required, 0 ),
									"question_title": qsmValueOrDefault( questionAttr?.title ),
									"answerEditor": answerEditor
								}
							});
						}
						
					});
				}

				// pages[0][]: 2512
				// 	qpages[0][id]: 2
				// 	qpages[0][quizID]: 76
				// 	qpages[0][pagekey]: Ipj90nNT
				// 	qpages[0][hide_prevbtn]: 0
				// 	qpages[0][questions][]: 2512
				// 	post_id: 111
				//page data
				quizDataToSave.pages.push( questions );
				
				quizDataToSave.qpages.push( {
					'id': pageID,
					'quizID': quizAttr.quiz_id,
					'pagekey': qsmIsEmpty( block.attributes.pageKey ) ? qsmUniqid() :block.attributes.pageKey,
					'hide_prevbtn':block.attributes.hidePrevBtn,
					'questions': questions
				} );
				pageSNo++;
			}
		});

		//Quiz details
		quizDataToSave.quiz =  {   
			'quiz_name': quizAttr.quiz_name,
			'quiz_id': quizAttr.quiz_id,
			'post_id': quizAttr.post_id,
		};
		if ( showAdvanceOption ) {
			[
			'form_type', 
			'system', 
			'timer_limit', 
			'pagination',
			'enable_contact_form', 
			'enable_pagination_quiz', 
			'show_question_featured_image_in_result',
			'progress_bar',
			'require_log_in',
			'disable_first_page',
			'comment_section'
			].forEach( ( item ) => { 
				if ( 'undefined' !== typeof quizAttr[ item ] && null !== quizAttr[ item ] ) {
					quizDataToSave.quiz[ item ] = quizAttr[ item ];
				}
			});
		}
		return quizDataToSave;
	}

	//saving Quiz on save page
	useEffect( () => {
		if ( isSavingPage ) {
			let quizData =  getQuizDataToSave();
			//save quiz status
			setSaveQuiz( true );

			//post status
			let post_status = 'publish';
			if ( ! qsmIsEmpty( editorSelectors ) ) {
				post_status = editorSelectors.getEditedPostAttribute( 'status' );
			} 
			if ( qsmIsEmpty( post_status ) ) {
				post_status = 'publish';
			}
			
			quizData = qsmFormData({
				'save_entire_quiz': '1',
				'quizData': JSON.stringify( quizData ),
				'qsm_block_quiz_nonce' : qsmBlockData.nonce,
				'page_post_id' : qsmIsEmpty( page_post_id ) ? 0: page_post_id ,
				'post_status' : post_status,
				"nonce": qsmBlockData.saveNonce,//save pages nonce
			});

			//AJAX call
			apiFetch( {
				path: '/quiz-survey-master/v1/quiz/save_quiz',
				method: 'POST',
				body: quizData
			} ).then( ( res ) => {
				//create notice
				createNotice( res.status, res.msg, {
					isDismissible: true,
					type: 'snackbar',
				} );
			} ).catch(
				( error ) => {
					console.log( 'error',error );
					createNotice( 'error', error.message, {
						isDismissible: true,
						type: 'snackbar',
					} );
				}
			);
		}
	}, [ isSavingPage ] );

	/**
	 * Create new quiz and set quiz ID
	 * 
	 */
	const createNewQuiz = () => {
		if ( qsmIsEmpty( quizAttr.quiz_name ) ) {
			console.log("empty quiz_name");
			return;
		}
		//save quiz status
		setSaveQuiz( true );
		
		let quizData = qsmFormData({
			'quiz_name': quizAttr.quiz_name,
			'qsm_new_quiz_nonce': qsmBlockData.qsm_new_quiz_nonce
		});
		
		['form_type', 
		'system', 
		'timer_limit', 
		'pagination',
		'enable_contact_form', 
		'enable_pagination_quiz', 
		'show_question_featured_image_in_result',
		'progress_bar',
		'require_log_in',
		'disable_first_page',
		'comment_section'
		].forEach( ( item ) => ( 'undefined' === typeof quizAttr[ item ] || null === quizAttr[ item ] ) ? '' : quizData.append( item, quizAttr[ item ] ) );

		//AJAX call
		apiFetch( {
			path: '/quiz-survey-master/v1/quiz/create_quiz',
			method: 'POST',
			body: quizData
		} ).then( ( res ) => {
			//save quiz status
			setSaveQuiz( false );
			if ( 'success' == res.status ) {
				//create a question
				let newQuestion = qsmFormData( {
					"id": null,
					"quizID": res.quizID,
					"answerEditor": "text",
					"type": "0",
					"name": "",
					"question_title": "",
					"answerInfo": "",
					"comments": "1",
					"hint": "",
					"category": "",
					"required": 0,
					"answers": [],
					"page": 0
				} );
				//AJAX call
				apiFetch( {
					path: '/quiz-survey-master/v1/questions',
					method: 'POST',
					body: newQuestion
				} ).then( ( response ) => {
					
					if ( 'success' == response.status ) {
						let question_id = response.id;

						/**Page attributes required format */
						// pages[0][]: 2512
						// 	qpages[0][id]: 2
						// 	qpages[0][quizID]: 76
						// 	qpages[0][pagekey]: Ipj90nNT
						// 	qpages[0][hide_prevbtn]: 0
						// 	qpages[0][questions][]: 2512
						// 	post_id: 111
						
						let newPage = qsmFormData( {
							"action": qsmBlockData.save_pages_action,
							"quiz_id": res.quizID,
							"nonce": qsmBlockData.saveNonce,
							"post_id": res.quizPostID,
						} );
						newPage.append( 'pages[0][]', question_id  );
						newPage.append( 'qpages[0][id]', 1  );
						newPage.append( 'qpages[0][quizID]', res.quizID );
						newPage.append( 'qpages[0][pagekey]', qsmUniqid()  );
						newPage.append( 'qpages[0][hide_prevbtn]', 0  );
						newPage.append( 'qpages[0][questions][]', question_id  );


						//create a page
						apiFetch( {
							url: qsmBlockData.ajax_url,
							method: 'POST',
							body: newPage
						} ).then( ( pageResponse ) => {
							
							if ( 'success' == pageResponse.status ) {
								//set new quiz
								initializeQuizAttributes( res.quizID );
							}
						});

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

			//create notice
			createNotice( res.status, res.msg, {
				isDismissible: true,
				type: 'snackbar',
			} );
		} ).catch(
			( error ) => {
				console.log( 'error',error );
				createNotice( 'error', error.message, {
					isDismissible: true,
					type: 'snackbar',
				} );
			}
		);
	  
	}

	/**
	 * Inner Blocks
	 */
	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		template: quizTemplate,
		allowedBlocks : [
			'qsm/quiz-page'
		]
	} );
	

	return (
	<>
	<InspectorControls>
		<PanelBody title={ __( 'Quiz settings', 'quiz-master-next' ) } initialOpen={ true }>
		<label className="qsm-inspector-label">
			{ __( 'Status', 'quiz-master-next' )+':' }
			<span className="qsm-inspector-label-value" >
				{ quizAttr.post_status }
			</span>
		</label>
		
		<TextControl
			label={ __( 'Quiz Name *', 'quiz-master-next' ) }
			help={ __( 'Enter a name for this Quiz', 'quiz-master-next' ) }
			value={ quizAttr?.quiz_name || '' }
			onChange={ ( val ) => setQuizAttributes( val, 'quiz_name') }
			className='qsm-no-mb'
		/>
		{
			( ! qsmIsEmpty( quizID ) || '0' != quizID ) && 
			<p>
				<ExternalLink 
				href={ qsmBlockData.quiz_settings_url+'&quiz_id='+quizID+'&tab=options' }
				>
					{ __( 'Advance Quiz Settings', 'quiz-master-next' ) }
				</ExternalLink>
			</p>
		}
		</PanelBody>
	</InspectorControls>
	{ ( qsmIsEmpty( quizID ) || '0' == quizID ) ? 
    <div { ...blockProps }> { quizPlaceholder() } </div>
	:
	<div { ...innerBlocksProps } />
	}
	
	</>
	);
}
