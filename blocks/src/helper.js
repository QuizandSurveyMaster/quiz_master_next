//Check if undefined, null, empty
export const qsmIsEmpty = ( data ) => ( 'undefined' === typeof data || null === data || '' === data );

export const qsmSanitizeName = ( name ) => {
	if ( qsmIsEmpty( name ) ) {
		name = '';
	} else {
		name = name.toLowerCase().replace( / /g, '_' );
		name = name.replace(/\W/g, '');
	}
	
	return name;
}

// Remove anchor tags from button text content.
export const qsmStripTags = ( text ) => text.replace( /<\/?a[^>]*>/g, '' );