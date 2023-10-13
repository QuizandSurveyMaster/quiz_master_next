//Check if undefined, null, empty
export const qsmIsEmpty = ( data ) => ( 'undefined' === typeof data || null === data || '' === data );

//Decode htmlspecialchars
export const qsmDecodeHtml = ( html ) => {
	var txt = document.createElement("textarea");
	txt.innerHTML = html;
	return txt.value;
}

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

//prepare form data
export const qsmFormData = ( obj = false ) => {
	let newData = new FormData();
	//add to check if api call from editor
	newData.append('qsm_block_api_call', '1');
	if ( false !== obj ) {
		for ( let k in obj ) {
			if ( obj.hasOwnProperty( k ) ) {
			   newData.append( k, obj[k] );
			}
		}
	}
	return newData;
}

//add objecyt to form data
export const qsmAddObjToFormData = ( formKey, valueObj, data = new FormData() ) => {
	if ( qsmIsEmpty( formKey ) || qsmIsEmpty( valueObj ) || 'object' !== typeof valueObj ) {
		return data;
	}

	for (let key in valueObj) {
		if ( valueObj.hasOwnProperty(key) ) {
			let value = valueObj[key];
			if ( 'object' === value ) {
				qsmAddObjToFormData( formKey+'['+key+']', value,  data );
			} else {
				data.append( formKey+'['+key+']', valueObj[key] );
			}
			
		}
	}

	return data;
}

//generate uiniq id
export const qsmUniqid = (prefix = "", random = false) => {
    const sec = Date.now() * 1000 + Math.random() * 1000;
    const id = sec.toString(16).replace(/\./g, "").padEnd(8, "0");
    return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}`:""}`;
};

//return data if not empty otherwise default value
export const qsmValueOrDefault = ( data, defaultValue = '' ) => qsmIsEmpty( data ) ? defaultValue :data;