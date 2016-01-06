<?php

	namespace helpers\Dumper;
	
	class Dumper
	{
		// this method patches problems with the phpconsole and ptcdebug
		public static function log( $var = null , $tag = null , $category = null )
		{
			if ( \App::option( 'app.test_env' ) && !static::$_debugStarted )
			{
				require_once( ptc_path( 'root' ) . '/system/PhpToolCase/PhpConsole/__autoload.php' );	
				static::$_debugInstance = \PhpConsole\Connector::getInstance( );				
				static::$_debugInstance->setHeadersLimit( 4096 );
				static::$_debugInstance->getDebugDispatcher( )->setDumper( new \PhpConsole\Dumper( 10 ) ); // maximum dump depth
				//static::$_debugInstance->getDebugDispatcher( )->detectTraceAndSource = true;	// trace calls
				//static::$_debugInstance = \PhpConsole\Handler::getInstance( );
				//static::$_debugInstance->setErrorsHandlerLevel( E_ERROR ^ E_WARNING );
				\PhpConsole\Handler::getInstance( )->setHandleErrors( true );
				\PhpConsole\Handler::getInstance( )->setHandleExceptions( true );
				\PhpConsole\Handler::getInstance( )->start( );
				static::$_debugStarted = true;
			}
			\PhpConsole\Handler::getInstance( )->debug( $var , $tag ); 
			//ptc_log( $var , $tag , $category );	// not really working with phpconsole
		}
		
		public static function show( $var ){ echo '<pre>' . print_r( $var , true ) . '</pre>'."\n"; }
		
		protected static $_debugStarted = false; 
		
		protected static $_debugInstance = null;
	}