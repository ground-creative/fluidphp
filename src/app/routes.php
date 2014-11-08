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
		Router::get( '/' , function( )
		{
			return "the index page";
		} );
		
	} )->prefix( App::option( 'app.env' ) );

	Router::notFound( 404 , function( ) // not found urls
	{
		return "sorry no route was found";
	} );