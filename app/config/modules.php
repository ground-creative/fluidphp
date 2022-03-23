<?php

	/*
	| ---------------------------------------------------
	| Modules Cofiguration File
	| ----------------------------------------------------------
	|
	| This files controls the modules configuration.
	| You can add your modules under the main domain or use a multi domain system
	|
	*/

	return
	[
		/* Switch between develop and prod config */
		'test_env'	=>	true ,
		'env'		=>	'' ,
		/* Development Config */
		'develop'	=>
		[
			'domains'	=> [ ] ,
			'locations'	=> [ ] ,	// only works with main domain
			'cli'	=> [ ]
		] ,
		/* Production Config */
		'prod'	=>
		[
			'domains'	=> [ ] ,
			'locations'	=> [ ] ,	// only works with main domain
			'cli'	=> [ ]
		]
	];