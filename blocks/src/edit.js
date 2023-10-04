import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	InspectorControls,
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import { store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	PanelBody,
	Button,
	PanelRow,
	TextControl,
	ToggleControl,
	RangeControl,
	RadioControl,
	SelectControl,
	Placeholder,
	Icon,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import './editor.scss';
import { qsmIsEmpty } from './helper';
export default function Edit( props ) {
	//check for QSM initialize data
	if ( 'undefined' === typeof qsmBlockData ) {
		return null;
	}

	const { className, attributes, setAttributes, isSelected, clientId } = props;
	/*
	"	quiz_name": {
			"type": "string",
			"default": ""
		},
		"quiz_featured_image": {
			"type": "string",
			"default": ""
		},
		"form_type": {
			"type": "string",
			"default": ""
		},
		"system": {
			"type": "string",
			"default": ""
		},
		"timer_limit": {
			"type": "string",
			"default": ""
		},
		"pagination": {
			"type": "string",
			"default": ""
		},
		"enable_pagination_quiz": {
			"type": "number",
			"default": 0
		},
		"progress_bar": {
			"type": "number",
			"default": 0
		},
		"require_log_in": {
			"type": "number",
			"default": 0
		},
		"disable_first_page": {
			"type": "number",
			"default": 0
		},
		"comment_section": {
			"type": "number",
			"default": 1
		}
	*/
	const {
		quizID 
	} = attributes;

	const [ quizAttr, setQuizAttr ] = useState( qsmBlockData.globalQuizsetting );
	const [ quizList, setQuizList ] = useState( qsmBlockData.QSMQuizList );
	const [ createQuiz, setCreateQuiz ] = useState( false );
	const [ saveQuiz, setSaveQuiz ] = useState( false );
	const [ showAdvanceOption, setShowAdvanceOption ] = useState( false );
	const [ quizTemplate, setQuizTemplate ] = useState( [] );
	const quizOptions = qsmBlockData.quizOptions;
	/**Initialize block from server */
	useEffect( () => {
		let shouldSetQSMAttr = true;
		if ( shouldSetQSMAttr ) {

			if ( ! qsmIsEmpty( quizID ) && 0 < quizID && ( qsmIsEmpty( quizAttr ) || qsmIsEmpty( quizAttr?.quizID ) || quizID != quizAttr.quiz_id ) ) {
				apiFetch( {
					path: '/quiz-survey-master/v1/quiz/structure',
					method: 'POST',
					data: { quizID: quizID },
				} ).then( ( res ) => {
					console.log( res );
					if ( 'success' == res.status ) {
						let result = res.result;
						setQuizAttr( {
							...quizAttr,
							...result
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
																isCorrect:answer[2]
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
								//console.log("page",page);
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
						// QSM_QUIZ = [
						// 	[

						// 	]
						// ];
					} else {
						console.log( "error "+ res.msg );
					}
				} );
				
			}
		}
		
		//cleanup
		return () => {
			shouldSetQSMAttr = false;
		};
		
	}, [ quizID ] );

	
	const feedbackIcon = () => (
	<Icon 
			icon={
				() => (
					<svg
						width="24"
						height="24"
						viewBox="0 0 24 24"
						fill="none"
						xmlns="http://www.w3.org/2000/svg"
						color="#ffffff"
						>
						<rect
							x="2.75"
							y="3.75"
							width="18.5"
							height="16.5"
							stroke="#0EA489"
							strokeWidth="1.5"
						/>
						<rect x="6" y="7" width="12" height="1" fill="#0EA489" />
						<rect x="6" y="11" width="12" height="1" fill="#0EA489" />
						<rect x="6" y="15" width="12" height="1" fill="#0EA489" />
					</svg>
				)
			}
	/>
	);
	const quizPlaceholder = ( ) => {
		return (
			<Placeholder
				icon={ feedbackIcon }
				label={ __( 'Quiz And Survey Master' ) }
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
							setAttributes( { quizID } )
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
						{ showAdvanceOption && (<>
							{/**Form Type */}
							<SelectControl
								label={ quizOptions?.form_type?.label }
								value={ quizAttr?.form_type }
								options={ quizOptions?.form_type?.options }
								onChange={ ( val ) => setQuizAttributes( val, 'form_type') }
								__nextHasNoMarginBottom
							/>
							{/**Grading Type */}
							<SelectControl
								label={ quizOptions?.system?.label }
								value={ quizAttr?.system }
								options={ quizOptions?.system?.options }
								onChange={ ( val ) => setQuizAttributes( val, 'system') }
								help={ quizOptions?.system?.help }
								__nextHasNoMarginBottom
							/>
							{
								[ 
									'timer_limit', 
									'pagination',
								].map( ( item ) => (
									<TextControl
										type='number'
										label={ quizOptions?.[item]?.label }
										help={ quizOptions?.[item]?.help }
										value={ qsmIsEmpty( quizAttr[item] ) ? 0 : quizAttr[item] }
										onChange={ ( val ) => setQuizAttributes( val, item) }
									/>
								) )
							}
							{
								[ 
									'enable_contact_form', 
									'enable_pagination_quiz', 
									'show_question_featured_image_in_result',
									'progress_bar',
									'require_log_in',
									'disable_first_page',
									'comment_section'
								].map( ( item ) => (
								<ToggleControl
									label={ quizOptions?.[item]?.label }
									help={ quizOptions?.[item]?.help }
									checked={ ! qsmIsEmpty( quizAttr[item] ) && '1' == quizAttr[item]  }
									onChange={ () => setQuizAttributes( ( ( ! qsmIsEmpty( quizAttr[item] ) && '1' == quizAttr[item] ) ? 0 : 1 ), item ) }
								/>
								) )

							}
						</>)
						}
						<Button 
							variant="primary"
							disabled={ saveQuiz || qsmIsEmpty( quizAttr.quiz_name ) }
							onClick={ () => createNewQuiz()	}
							>
							{ __( 'Create Quiz', 'quiz-master-next' ) }
						</Button>
					</VStack>
	                }
					</>
				}
			</Placeholder>
		);
	};

	const setQuizAttributes = ( value , attr_name ) => {
		let newAttr = quizAttr;
		newAttr[ attr_name ] = value;
		setQuizAttr( { ...newAttr } );
	}

	const createNewQuiz = () => {

	}

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
		<TextControl
			label={ __( 'Quiz Name *', 'quiz-master-next' ) }
			help={ __( 'Enter a name for this Quiz', 'quiz-master-next' ) }
			value={ quizAttr?.quiz_name || '' }
			onChange={ ( val ) => setQuizAttributes( val, 'quiz_name') }
		/>
		</PanelBody>
	</InspectorControls>
	{ ( qsmIsEmpty( quizID ) || '0' == quizID ) ? 
    quizPlaceholder() 
	:
	<div { ...innerBlocksProps } />
	}
	
	</>
	);
}
