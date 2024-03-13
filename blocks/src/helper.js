//Check if undefined, null, empty
export const qsmIsEmpty = ( data ) => ( 'undefined' === typeof data || null === data || '' === data );

//Get Unique array values
export const qsmUniqueArray = ( arr ) => {
	if ( qsmIsEmpty( arr ) || ! Array.isArray( arr ) ) {
		return arr;
	}
	return arr.filter( ( val, index, arr ) => arr.indexOf( val ) === index );
}

//Match array of object values and return array of cooresponding matching keys 
export const qsmMatchingValueKeyArray = ( values, obj ) => {
	if ( qsmIsEmpty( obj ) || ! Array.isArray( values ) ) {
		return values;
	}
	return values.map( ( val ) => Object.keys(obj).find( key => obj[key] == val) );
}

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

// Remove anchor tags from text content.
export const qsmStripTags = ( text ) => {
	let div = document.createElement("div");
	div.innerHTML = qsmDecodeHtml( text );
	return  div.innerText;
}

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

//Generate random number
export const qsmGenerateRandomKey = (length) => {
    const charset = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let key = "";

    // Generate random bytes
    const values = new Uint8Array(length);
    window.crypto.getRandomValues(values);

    for (let i = 0; i < length; i++) {
        // Use the random byte to index into the charset
        key += charset[values[i] % charset.length];
    }

    return key;
}

//generate uiniq id
export const qsmUniqid = (prefix = "", random = false) => {
    const id = qsmGenerateRandomKey(8);
    return `${prefix}${id}${random ? `.${ qsmGenerateRandomKey(7) }`:""}`;
};

//return data if not empty otherwise default value
export const qsmValueOrDefault = ( data, defaultValue = '' ) => qsmIsEmpty( data ) ? defaultValue :data;