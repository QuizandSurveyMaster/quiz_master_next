/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import {
	DropZone,
	Button,
	Spinner,
	ResponsiveWrapper,
	withNotices,
	withFilters,
	__experimentalHStack as HStack,
} from '@wordpress/components';
import { isBlobURL } from '@wordpress/blob';
import { useState, useRef, useEffect } from '@wordpress/element';
import { store as noticesStore } from '@wordpress/notices';
import { useDispatch, useSelect, select, withDispatch, withSelect } from '@wordpress/data';
import {
	MediaUpload,
	MediaUploadCheck,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { store as coreStore } from '@wordpress/core-data';
import { qsmIsEmpty } from '../helper';

const ALLOWED_MEDIA_TYPES = [ 'image' ];

// Used when labels from post type were not yet loaded or when they are not present.
const DEFAULT_FEATURE_IMAGE_LABEL = __( 'Featured image' );
const DEFAULT_SET_FEATURE_IMAGE_LABEL = __( 'Set featured image' );

const instructions = (
	<p>
		{ __(
			'To edit the featured image, you need permission to upload media.'
		) }
	</p>
);

const FeaturedImage = ( {
	featureImageID,
	onUpdateImage,
	onRemoveImage
} ) => {
	const { createNotice } = useDispatch( noticesStore );
	const toggleRef = useRef();
	const [ isLoading, setIsLoading ] = useState( false );
	const [ media, setMedia ] = useState( undefined );
	const { mediaFeature, mediaUpload } = useSelect( ( select ) => {
		const { getMedia } = select( coreStore );
			return {
				mediaFeature: qsmIsEmpty( media ) && ! qsmIsEmpty( featureImageID ) && getMedia( featureImageID ),
				mediaUpload: select( blockEditorStore ).getSettings().mediaUpload 
			};
	}, [] );

	/**Set media data */
	useEffect( () => {
		let shouldSetQSMAttr = true;
		if ( shouldSetQSMAttr ) {
			if ( ! qsmIsEmpty( mediaFeature ) && 'object' === typeof mediaFeature ) {
				setMedia({
					id: featureImageID,
					width: mediaFeature.media_details.width, 
					height: mediaFeature.media_details.height, 
					url: mediaFeature.source_url,
					alt_text: mediaFeature.alt_text,
					slug: mediaFeature.slug
				});
			}
		}
		
		//cleanup
		return () => {
			shouldSetQSMAttr = false;
		};
		
	}, [ mediaFeature ] );

	function onDropFiles( filesList ) {
		mediaUpload( {
			allowedTypes: [ 'image' ],
			filesList,
			onFileChange( [ image ] ) {
				if ( isBlobURL( image?.url ) ) {
					setIsLoading( true );
					return;
				}
				onUpdateImage( image );
				setIsLoading( false );
			},
			onError( message ) {
				createNotice( 'error', message, {
					isDismissible: true,
					type: 'snackbar',
				} );
			},
		} );
	}

	return (
		<div className="editor-post-featured-image">
			{ media && (
				<div
					id={ `editor-post-featured-image-${ featureImageID }-describedby` }
					className="hidden"
				>
					{ media.alt_text &&
						sprintf(
							// Translators: %s: The selected image alt text.
							__( 'Current image: %s' ),
							media.alt_text
						) }
					{ ! media.alt_text &&
						sprintf(
							// Translators: %s: The selected image filename.
							__(
								'The current image has no alternative text. The file name is: %s'
							),
							media.slug
						) }
				</div>
			) }
			<MediaUploadCheck fallback={ instructions }>
				<MediaUpload
					title={
						DEFAULT_FEATURE_IMAGE_LABEL
					}
					onSelect={ ( media ) => { 
						setMedia( media );
						onUpdateImage( media );
					} }
					unstableFeaturedImageFlow
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					modalClass="editor-post-featured-image__media-modal"
					render={ ( { open } ) => (
						<div className="editor-post-featured-image__container">
							<Button
								ref={ toggleRef }
								className={
									! featureImageID
										? 'editor-post-featured-image__toggle'
										: 'editor-post-featured-image__preview'
								}
								onClick={ open }
								aria-label={
									! featureImageID
										? null
										: __( 'Edit or replace the image' )
								}
								aria-describedby={
									! featureImageID
										? null
										: `editor-post-featured-image-${ featureImageID }-describedby`
								}
							>
								{ !! featureImageID && media && (
									<ResponsiveWrapper
										naturalWidth={ media.width }
										naturalHeight={ media.height }
										isInline
									>
										<img
											src={ media.url }
											alt={ media.alt_text }
										/>
									</ResponsiveWrapper>
								) }
								{ isLoading && <Spinner /> }
								{ ! featureImageID &&
									! isLoading &&
									(	DEFAULT_SET_FEATURE_IMAGE_LABEL ) }
							</Button>
							{ !! featureImageID && (
								<HStack className="editor-post-featured-image__actions">
									<Button
										className="editor-post-featured-image__action"
										onClick={ open }
										// Prefer that screen readers use the .editor-post-featured-image__preview button.
										aria-hidden="true"
									>
										{ __( 'Replace' ) }
									</Button>
									<Button
										className="editor-post-featured-image__action"
										onClick={ () => {
											onRemoveImage();
											toggleRef.current.focus();
										} }
									>
										{ __( 'Remove' ) }
									</Button>
								</HStack>
							) }
							<DropZone onFilesDrop={ onDropFiles } />
						</div>
					) }
					value={ featureImageID }
				/>
			</MediaUploadCheck>
		</div>
	);
}

export default FeaturedImage;