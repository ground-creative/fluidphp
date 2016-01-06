<?php

	/**
	* Request Validator Config FIle 
	*/
	return array
	(
		/* autoload method */
		'autoload'		=>	'\helpers\Validator\Validator::loadConfig' ,
		/* Response error codes */
		'error_messages'		=>	array
		(
			'required'				=>	501 ,
			'email'				=>	102 ,
			'equalTo'				=>	101 ,
			'match'				=>	300 ,
			'min'					=>	802 ,
			'max'				=>	803 ,
			'number'				=>	502 ,
			'duplicate'			=>	100 ,
			'password'			=>	801 ,
			'login_token'			=>	804
		) ,
		/* Values regular expressions */
		'regex'			=>	array
		(
			'lang'				=>	'~es_ES|en_GB~' ,
			'birthdate'			=>	'~^[0-9]{4}/(0[1-9]|1[0-2])/(0[1-9]|[1-2][0-9]|3[0-1])$~' ,
			'newsletter'			=>	'~yes|no~' ,
			'agreement'			=>	'~on~' ,
			'gender'				=>	'~male|female~'
		) ,
		/* Defaults if value is not set */
		'defaults'			=>	array
		(
			'lang'				=>	'_NULL_' ,
			'birthdate'			=>	'_NULL_' ,
			'newsletter'			=>	'no' ,
			'country'				=>	'_NULL_' ,
			'city'					=>	'_NULL_' ,
			'address'				=>	'_NULL_' ,
			'zip'					=>	'_NULL_' ,
			'register_facebook_id'	=>	'_NULL_' ,
			'quantity'				=>	'1' ,
			'tel'					=>	'_NULL_'
		) ,
		/* Defaults if value is empty */
		'empty'			=>	array
		(
		
		) ,
		/* Custom validation methods */
		'custom_methods'	=>	array
		(
			//'duplicate'			=>	'\interfaces\Request::check_duplicate' ,
			'password'			=>	'\interfaces\Request::check_password' ,
			//'login_token'			=>	'\interfaces\Request::check_login_token'
		) ,
		/* Rules */	
		'rules'			=>	array
		(
			/* Register new login user */
			'user_register'			=>	array
			(
				'firstname' 			=>	'required' ,
				'lastname' 			=>	'required' ,
				'email_1' 				=>	'required||email||duplicate:email' ,	
				'email_2'				=>	'equalTo:email_1' ,							
				'password_1'  			=>	'required||min:5||max:20' ,
				'password_2'			=>	'equalTo:password_1' ,
				'agreement'			=>	'required||match:{regex}' ,
				'newsletter'			=>	'match:{regex}||default:{defaults}' ,
				'lang'				=>	'match:{regex}||default:{defaults}' ,
				'gender'				=>	'match:{regex}' ,
				'country'				=> 	'default:{defaults}' ,
				'city'					=> 	'default:{defaults}' ,
				'address'				=> 	'default:{defaults}' ,
				'zip'					=> 	'default:{defaults}' ,
				'register_facebook_id'	=> 	'number||default:{defaults}' ,
				'birthdate'			=>	'match:{regex}||default:{defaults}'
			)  ,
			'user_login'			=>	array
			(
				'email' 				=>	'required||email' ,
				'password'  			=>	'required||password' ,
			)
		)
	);