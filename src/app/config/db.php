<?php

	/*
	| ---------------------------------------------------
	| Database Connections Cofiguration File
	| ----------------------------------------------------------
	|
	| This file should hold details for all your database connections
	| Refer to http://phptoolcase.com/ptc-db-guide.html to 
	| understand all available options
	|
	*/

	return array
	(
		/*
		| ------------------------------------------------------------------------------------
		| Default connection , configure user credentials 
		| and add more connections if needed 
		| ------------------------------------------------------------------------------------
		*/
		
		'default'	=>	array
		(
			'driver'				=>	'mysql' , 
			'user'				=>	'' ,
			'pass'				=>	'' ,
			'host'				=>	'localhost' ,
			'db'					=>	'' ,
			'charset'				=>	'utf8' ,
			'query_builder'			=>	true ,
			'query_builder_class'	=>	'QueryBuilder'
		) ,
	);