/**
 * Image Component: Upload, Use media, external image url
 */
import { getBlobByURL, isBlobURL, revokeBlobURL } from '@wordpress/blob';
import {
	Button,
	Spinner,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import {
	BlockControls,
	BlockIcon,
	MediaReplaceFlow,
	MediaPlaceholder,
	store as blockEditorStore,
} from '@wordpress/block-editor';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { image as icon } from '@wordpress/icons';
import { store as noticesStore } from '@wordpress/notices';
import { isURL } from '@wordpress/url';
import { qsmIsEmpty } from '../helper';

export const pickRelevantMediaFiles = ( image, size ) => {
	const imageProps = Object.fromEntries(
		Object.entries( image ?? {} ).filter( ( [ key ] ) =>
			[ 'alt', 'id', 'link', 'caption' ].includes( key )
		)
	);

	imageProps.url =
		image?.sizes?.[ size ]?.url ||
		image?.media_details?.sizes?.[ size ]?.source_url ||
		image.url;
	return imageProps;
};

/**
 * Is the URL a temporary blob URL? A blob URL is one that is used temporarily
 * while the image is being uploaded and will not have an id yet allocated.
 *
 * @param {number=} id  The id of the image.
 * @param {string=} url The url of the image.
 *
 * @return {boolean} Is the URL a Blob URL
 */
const isTemporaryImage = ( id, url ) => ! id && isBlobURL( url );

/**
 * Is the url for the image hosted externally. An externally hosted image has no
 * id and is not a blob url.
 *
 * @param {number=} id  The id of the image.
 * @param {string=} url The url of the image.
 *
 * @return {boolean} Is the url an externally hosted url?
 */
export const isExternalImage = ( id, url ) => url && ! id && ! isBlobURL( url );


export function ImageType( {
	url = '',
	caption = '',
	alt = '',
	setURLCaption,
} ) {
	
	const ALLOWED_MEDIA_TYPES = [ 'image' ];
	const [ id, setId ] = useState( null );
	const [ temporaryURL, setTemporaryURL ] = useState();

	const ref = useRef();
	const { imageDefaultSize, mediaUpload } = useSelect( ( select ) => {
		const { getSettings } = select( blockEditorStore );
		const settings = getSettings();
		return {
			imageDefaultSize: settings.imageDefaultSize,
			mediaUpload: settings.mediaUpload,
		};
	}, [] );

	const { createErrorNotice } = useDispatch( noticesStore );
	function onUploadError( message ) {
		createErrorNotice( message, { type: 'snackbar' } );
		setURLCaption( undefined, undefined );
		setTemporaryURL( undefined );
	}

	function onSelectImage( media ) {
		if ( ! media || ! media.url ) {
			setURLCaption( undefined, undefined );
			return;
		}

		if ( isBlobURL( media.url ) ) {
			setTemporaryURL( media.url );
			return;
		}

		setTemporaryURL();

		let mediaAttributes = pickRelevantMediaFiles( media, imageDefaultSize );
		setId( mediaAttributes.id  );
		setURLCaption( mediaAttributes.url, mediaAttributes.caption );
	}

	function onSelectURL( newURL ) {
		if ( newURL !== url ) {
			setURLCaption( newURL, caption );
		}
	}

	let isTemp = isTemporaryImage( id, url );

	// Upload a temporary image on mount.
	useEffect( () => {
		if ( ! isTemp ) {
			return;
		}

		const file = getBlobByURL( url );

		if ( file ) {
			mediaUpload( {
				filesList: [ file ],
				onFileChange: ( [ img ] ) => {
					onSelectImage( img );
				},
				allowedTypes: ALLOWED_MEDIA_TYPES,
				onError: ( message ) => {
					isTemp = false;
					onUploadError( message );
				},
			} );
		}
	}, [] );

	// If an image is temporary, revoke the Blob url when it is uploaded (and is
	// no longer temporary).
	useEffect( () => {
		if ( isTemp ) {
			setTemporaryURL( url );
			return;
		}
		revokeBlobURL( temporaryURL );
	}, [ isTemp, url ] );

	const isExternal = isExternalImage( id, url );
	const src = isExternal ? url : undefined;
	const mediaPreview = !! url && (
		<img
			alt={ __( 'Edit image' ) }
			title={ __( 'Edit image' ) }
			className={ 'edit-image-preview' }
			src={ url }
		/>
	);

	let img = (
		<>
			<img
				src={ temporaryURL || url }
				alt=""
				className="qsm-answer-option-image"
				style={ {
					width:'200',
					height:'auto',
				} }
			/>
			{ temporaryURL && <Spinner /> }
		</>
	);

	return (
		<figure >
			{ qsmIsEmpty( url ) ? (
				<MediaPlaceholder
					icon={ <BlockIcon icon={ icon } /> }
					onSelect={ onSelectImage }
					onSelectURL={ onSelectURL }
					onError={ onUploadError }
					accept="image/*"
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					value={ { id, src } }
					mediaPreview={ mediaPreview }
					disableMediaButtons={ temporaryURL || url }
				/>
			) : (
			<>
			<BlockControls group="other">
				<MediaReplaceFlow
					mediaId={ id }
					mediaURL={ url }
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					accept="image/*"
					onSelect={ onSelectImage }
					onSelectURL={ onSelectURL }
					onError={ onUploadError }
				/>
			</BlockControls>
			<div >{ img }</div>
			</>
			) }
		</figure>
	);
}

export default ImageType;
