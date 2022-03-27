<?php

	/*
	|--------------------------------------------------------------------------
	| Application Routes
	|--------------------------------------------------------------------------
	|
	| Use this file to register all of the routes for your application.
	|
	*/
	
	Router::group( 'main' , function( )
	{
		 
	} )->prefix( \App::option( 'app.env' ) );
