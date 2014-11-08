/* 
* Client side function to call the api_calls.php file
*
* @param	string	params.request	required		the request type [ POST  ,PUT , GET , DELETE ]
* @param	string	params.uri		required		the api request uri
* @param	string	successCallBack	required 		the success callback to handle the response
* @param	string	errorCallBack		required 		the error callback to handle the response
*
*/
function apiRequest( params , successCallBack , errorCallBack , timeout )
{

	var ajaxURL = 'https://full_url_of_ajax_script'; // change this to url where ajax script is hosted


	if ( typeof( timeout ) ==='undefined' ){ timeout = 60000; }
	if ( typeof jQuery != 'undefined' )
	{
		if ( typeof( errorCallBack ) === 'undefined' )
		{ 
			errorCallBack = function( xhr, errorType , error )
			{ 
				alert( 'Error while requesting webservice!' ); 
			}
		}
		jQuery.ajax
		( {
			url: ajaxURL ,
			type: 'POST' ,
			data: params ,
			dataType: 'json' ,
			timeout: timeout ,
			headers: { 'X-Requested-With' : 'Ajax'} ,
			success: function( response ){ successCallBack( response ); } ,
			error: function( xhr, errorType , error )
			{ 
				errorCallBack( xhr, errorType , error ); 
			}
		} );
	}
	else if ( typeof Ext != 'undefined' )
	{
		if ( typeof( errorCallBack ) === 'undefined' )
		{ 
			errorCallBack = function( response )
			{ 
				alert( 'Error while requesting webservice!' ); 
			}
		}
		Ext.Ajax.request
		( {
			url: ajaxURL , 
			method: 'post' , 
			params: params ,
			timeout: timeout ,
			success: function( response ){ successCallBack( response ); } ,
			failure: function( response ){ errorCallBack( response ); }
		} );
	}
	else{ alert( 'Sorry we need Jquery or Extjs libraries to make the api calls!' ); }
}

/*
	EXAMPLE USAGE

	options = { request : 'DELETE' , uri: 'guestlist' , email : 'Doe@doe.com' };		
	apiRequest( options , function( response )
	{ 	
		// success
		console.log( response );
	} , function( response )
	{ 
		// error
		console.log( response );
	}, 30000 );
*/