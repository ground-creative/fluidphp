<?php

	/**
	* Application Interface
	* @package	ZeroConfig MVC
	* @author	Carlo Pietrobattista
	*/
	
	namespace system\Core;
	
	use system\PhpToolCase\PtcHandyMan as HandyMan;
	use system\PhpToolCase\PtcDebug as Debug;
	use system\PhpToolCase\PtcRouter as Router;
	use system\PhpToolCase\PtcDb as DB;

	class App
	{
		/**
		*
		*/
		public function __construct( )
		{
			$class = get_called_class( );
			if ( $class::$_startEvent )
			{ 
				ptc_fire( 'app.start' , array( &$class::$_config , &$this ) ); 
			}
			$this->_appConfig = $class::$_config;
			ptc_log( $this->_appConfig , 'Started the application' , 'App Config' );
		}
		/**
		* Application start event
		* @param	mixed	$callback		a valid callback
		*/
		public static function start( $callback )
		{ 
			static::$_startEvent = true; 
			return ptc_listen( 'app.start' , $callback );
		}
		/**
		* Application stop event
		* @param	mixed	$callback		a valid callback
		*/
		public static function stop( $callback )
		{ 
			static::$_stopEvent = true; 
			return ptc_listen( 'app.stop' , $callback );
		}
		/**
		* Alias of @ref App::options( )
		*/
		public static function option( $key = null , $option = null )
		{ 
			return static::options( $key , $option );
		}
		/**
		*
		*/
		public static function options( $key = null , $options = null )
		{
			if ( is_null( $options ) )
			{
				if ( !$key ){ return static::$_config; }
				return ptc_array_get( static::$_config , $key );
			}
			else if ( ptc_array_get( static::$_config , $key ) )
			{
				trigger_error( 'Option name ' . $key . ' already exists!' , E_USER_ERROR );
				return false;
			}
			return ptc_array_set( static::$_config , $key , $options );
		}
		/**
		*
		*/
		public static function storage( $key = null , $value = null )
		{
			if ( is_null( $value ) )
			{
				if ( !$key ){ return static::$_storage; }
				return ptc_array_get( static::$_storage , $key );
			}
			else if ( ptc_array_get( static::$_storage , $key ) )
			{
				trigger_error( 'Storage name ' . $key . ' already exists!' , E_USER_ERROR );
				return false;
			}
			return ptc_array_set( static::$_storage , $key , $value );
		}
		/**
		*
		*/
		public function run( $print = true )
		{
			$check_config = ptc_array_get( $this->_appConfig , 'app.check_router_config' );
			$response = Router::run( $check_config ); 
			$class = get_called_class( );
			return $class::_shutdown( $response , $print , $this );
		}
		/**
		*
		*/
		public static function configure( )
		{
			/* debugging */
			static::option( 'debug' , require_once( ptc_path( 'root' ) . '/app/config/debug.php' ) );
			if ( ptc_array_get( static::$_config , 'debug.replace_error_handler' ) )
			{
				$die_on_error = ( ptc_array_get( static::$_config , 'debug.die_on_error' ) ) ? true : false;
				Debug::setErrorHandler( $die_on_error );
				ptc_array_set( static::$_config , 'debug.replace_error_handler' , false );
			}
			if ( ptc_array_get( static::$_config , 'debug.start' ) ){ Debug::load( static::$_config[ 'debug' ] ); }
			/* paths */
			static::option( 'paths' , require_once( ptc_path( 'root' ) . '/app/config/paths.php' ) );
			ptc_add_path( ptc_array_get( static::$_config , 'paths' ) );
			/* application */
			static::option( 'app' , require_once( ptc_path( 'root' ) . '/app/config/app.php' ) );
			HandyMan::addAlias( ptc_array_get( static::$_config , 'app.aliases' ) );
			if ( $locale = ptc_array_get( static::$_config , 'app.locale' ) ){ setlocale( LC_ALL , $locale ); } 
			if ( $timezone = ptc_array_get( static::$_config , 'app.timezone' ) )
			{ 
				date_default_timezone_set( $timezone ); 
			}
			ptc_add_file( ptc_array_get( static::$_config , 'app.files' ) );
			ptc_add_dir( ptc_array_get( static::$_config , 'app.directories' ) );
			ptc_add_dir( ptc_array_get( static::$_config , 'app.namespaces' ) );
			HandyMan::addSeparators( ptc_array_get( static::$_config , 'app.separators' ) );
			HandyMan::addConventions( ptc_array_get( static::$_config , 'app.conventions' ) );
			/* database */
			static::option( 'db' , require_once( ptc_path( 'root' ) . '/app/config/db.php' ) );
			if ( $db = ptc_array_get( static::$_config , 'db' ) )
			{
				foreach ( $db as $k => $v ){ if ( ptc_array_get( $v , 'user' ) ){ DB::add( $v , $k ); } }
			}
			/* auth */
			static::option( 'auth' , require_once( ptc_path( 'root' ) . '/app/config/auth.php' ) );
			/* custom config files */
			$files = array( '..' , '.' , 'app.php' , 'db.php' , 'debug.php' , 'paths.php' , 'auth.php' );
			$scanned_directory = array_diff( scandir( ptc_path( 'root' ) . '/app/config' ) , $files );
			if ( !empty( $scanned_directory ) )
			{
				foreach ( $scanned_directory as $file )
				{
					$option_name = str_replace( '.php' , '' , $file );
					$options =  require_once( ptc_path( 'root' ) . '/app/config/' . $file );
					if ( ptc_array_get( $options , 'autoload' ) )
					{
						$options = call_user_func( ptc_array_get( $options , 'autoload' ) , $options );
					}
					static::option( $option_name , $options );
				}
			}
		}
		/**
		*
		*/
		protected $_appConfig = array( );
		/**
		*
		*/
		protected static $_startEvent = false;
		/**
		*
		*/
		protected static $_stopEvent = false;
		/**
		*
		*/
		protected static $_config = array( );
		/**
		*
		*/
		protected static $_storage = array( );
		/**
		*
		*/
		protected static function _shutdown( $response , $print , $obj )
		{
			if ( static::$_stopEvent )
			{ 
				ptc_fire( 'app.stop' , array( $obj , &$response ) ); 
			}
			if ( !$print ){ return $response; }  
			echo $response;
		}
	}