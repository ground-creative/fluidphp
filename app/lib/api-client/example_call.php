<?
	/**
	* BPREMIUM API V3 EXAMPLE CALL SCRIPT
	* MAKE SURE YOU FILL THE CLEINT CREDENTIAL SECTION WITH DAT SUPPLIED BY BPREMIUM
	* THE RESPONSE CAN BE CHANGED HOW YOU LIKE TO FIT YOUR NEEDS
	*/
	
	/* API CLIENT CREDENTIALS CONFIGURATION */
	$api_options = array
	(
		'api_address'	=>	'https://api.premiumguest.com/v3' ,			// the address of the api
		'client_id'		=>	'client_id' ,							// client_id supplied from bpremium
		'client_secret'	=>	'client_secret' ,						// client password supplied from bpremium
		'origin'		=>	'domain.com' 							// main domain where script is hosted
	);
	/* END CLIENT CREDENTIALS CONFIGURATION */
	
	/* API CALL NO NEED TO MANIPULATE THIS CODE */
	require_once( 'BP_API_Client.php' );
	$test = new BP_API_Client( $api_options );
	$result = $test->fetch( $_POST[ 'uri' ] , $_POST[ 'request' ] , $_POST );
	
	/* USE RESULT WITH JSON EXAMPLE */
	echo json_encode( $result );	// you can return the response how you like