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
		Router::get( '/', function( )
		{
			echo "Hello";
		});


	} )->prefix( \App::option( 'app.env' ) );
