
function elevateAdminAjax( specificAction, additionalParams, callback ) {
	var data = {
		'action': 'elevate_ajax',
		'elevate_action': specificAction,
		'elevate_nonce': ElevateData.elevate_nonce
	};

	// Add our parameters to the primary AJAX ones
	for ( var key in additionalParams ) {
	    if ( additionalParams.hasOwnProperty( key  )) {
	    	data[ key ] = additionalParams[ key ];
	    }
	}	

	// We can also pass the url value separately from ajaxurl for front end AJAX implementations
	jQuery.post( ElevateData.admin_ajax, data, function( response ) {
		callback( response );
	});
}