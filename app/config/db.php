<?php

	/*
	| ---------------------------------------------------
	| Database Connections Cofiguration File
	| ----------------------------------------------------------
	|
	| This file should hold details for all your database connections
	| Refer to http://phptoolcase.com/ptc-db-guide.html for all available options
	|
	*/

	return
	[
		'develop'	=>
		[
			'default'   =>  
			[
				'driver'				=>	'mysql' , 
				'user'				=>	'' ,
				'pass'				=>	'' ,
				'host'				=>	'localhost' ,
				'db'					=>	'' ,
				'charset'				=>	'utf8' ,
				'query_builder'			=>	true ,
				'query_builder_class'		=>	'QueryBuilder'
			]
		] ,
		'prod'	=>
		[
			'default'   =>
			[
				'driver'				=>	'mysql' , 
				'user'				=>	'' ,
				'pass'				=>	'' ,
				'host'				=>	'localhost' ,
				'db'					=>	'' ,
				'charset'				=>	'utf8' ,
				'query_builder'			=>	true ,
				'query_builder_class'		=>	'QueryBuilder'
			]
		]
	];