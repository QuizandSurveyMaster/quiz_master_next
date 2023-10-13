import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import {
	InspectorControls,
	InnerBlocks,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	PanelBody,
	PanelRow,
	TextControl,
	ToggleControl,
	RangeControl,
	RadioControl,
	SelectControl,
} from '@wordpress/components';
import { qsmIsEmpty } from '../helper';
export default function Edit( props ) {
	//check for QSM initialize data
	if ( 'undefined' === typeof qsmBlockData ) {
		return null;
	}

	const { className, attributes, setAttributes, isSelected, clientId, context } = props;

	const quizID = context['quiz-master-next/quizID'];
	
	const {
		pageID,
		pageKey,
		hidePrevBtn,
	} = attributes;

	const [ qsmPageAttr, setQsmPageAttr ] = useState( qsmBlockData.globalQuizsetting );
	
	const blockProps = useBlockProps();

	return (
	<>
	<InspectorControls>
		<PanelBody title={ __( 'Page settings', 'quiz-master-next' ) } initialOpen={ true }>
			<TextControl
				label={ __( 'Page Name', 'quiz-master-next' ) }
				value={ pageKey }
				onChange={ ( pageKey ) => setAttributes( { pageKey } ) }
			/>
			<ToggleControl
				label={ __( 'Hide Previous Button?', 'quiz-master-next' ) }
				checked={ ! qsmIsEmpty( hidePrevBtn ) && '1' == hidePrevBtn  }
				onChange={ () => setAttributes( { hidePrevBtn : ( ( ! qsmIsEmpty( hidePrevBtn ) && '1' == hidePrevBtn ) ? 0 : 1 ) } ) }
			/>
		</PanelBody>
		
	</InspectorControls>
	<div { ...blockProps }>
		<InnerBlocks
			allowedBlocks={ ['qsm/quiz-question'] }
		/>
	</div>
	</>
	);
}
