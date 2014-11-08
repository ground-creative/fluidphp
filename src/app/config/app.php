<?php

	/*
	| ---------------------------------------------------
	| Application Main Cofiguration File
	| ----------------------------------------------------------
	|
	| This files controls the directories and files to use with the autoloader
	| it also hold the aliases for class names
	|
	*/
	
	return array
	(
		/*
		| ------------------------------------------------------------------------------------
		| Directories with classes for the autoloader
		| ------------------------------------------------------------------------------------
		*/
		'directories'			=>	array
		( 
			ptc_path( 'models' ) ,
			ptc_path( 'controllers' )
		) ,
		/*
		| ------------------------------------------------------------------------------------
		| Namespace directories with classes for the autoloader
		| ------------------------------------------------------------------------------------
		*/
		'namespaces'			=>	array
		( 
		
		) ,
		/*
		| ------------------------------------------------------------------------------------
		| Class files for the autoloader
		| ------------------------------------------------------------------------------------
		*/
		'files'				=>	array
		( 

		) ,
		/*
		| ----------------------------------------------------------------------------------
		| Separators for different class names
		| ----------------------------------------------------------------------------------
		*/
		'separators'			=>	array( ) ,
		/*
		| ----------------------------------------------------------------------------------
		| Class name conventions for diffent class names
		| ----------------------------------------------------------------------------------
		*/
		'conventions'			=>	array( ) ,
		/*
		| ------------------------------------------------------------------------------------
		| Class aliases
		| ------------------------------------------------------------------------------------
		*/
		'aliases'				=>	array
		(
			// SYSTEM
			'App'				=>	'system\Core\App' ,
			'HandyMan'			=>	'system\PhpToolCase\PtcHandyMan' ,
			'Router'				=>	'system\PhpToolCase\PtcRouter' ,
			'Form'				=>	'system\PhpToolCase\PtcForm' ,
			'Auth'				=>	'system\PhpToolCase\PtcAuth' ,
			'View'				=>	'system\PhpToolCase\PtcView' ,
			'DB'					=>	'system\PhpToolCase\PtcDb' ,
			'QueryBuilder'			=>	'system\PhpToolCase\PtcQueryBuilder' ,
			'Model'				=>	'system\PhpToolCase\PtcMapper' ,
			'Debug'				=>	'system\PhpToolCase\PtcDebug' ,
			'Event'				=>	'system\PhpToolCase\PtcEvent'
		) ,
		/*
		|--------------------------------------------------------------------------
		| Application URL
		|--------------------------------------------------------------------------
		*/
		'url' 					=>	'http://v3.bpremium.com' ,
		/*
		|--------------------------------------------------------------------------
		| Application Main Folder Path
		|--------------------------------------------------------------------------
		*/
		'env' 				=>	'' ,
		/*
		|--------------------------------------------------------------------------
		| Application Timezone
		|--------------------------------------------------------------------------
		*/
		'timezone' 			=>	'Europe/Madrid' ,
		/*
		|--------------------------------------------------------------------------
		| Application Locale Configuration
		|--------------------------------------------------------------------------
		*/		
		'locale' 				=>	'en' ,
		/*
		| ------------------------------------------------------------------------------------
		| Check Router Configuration when building routes
		| ------------------------------------------------------------------------------------
		*/
		'check_router_config'	=>	true ,
		/*
		| --------------------------------------------------------------------------------------
		| Test Environment parameter
		| --------------------------------------------------------------------------------------
		*/
		'testing'				=>	false
	);