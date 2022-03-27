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
		 Router::get( "/" , function( )
		 {
			echo "here 123";
		 } );
		 
		 Router::get( "test" , function( )
		 {
			echo "heere eeee 444";
		 } );
		 
	} )->prefix( \App::option( 'app.env' ) );
