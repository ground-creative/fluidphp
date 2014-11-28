<?php

	namespace helpers\Website;

	class Website
	{
		/**
		*
		*/
		const XML_CONFIG = '/xml/config';
		/**
		*
		*/
		const XML_TRANSLATIONS = '/xml/lang';
		/**
		*
		*/
		const VIEWS_PATH = '/';
		/**
		*
		*/
		const LANG_PARAM = '_lang';
		/**
		*
		*/
		const RESOURCES_PARAM = '_resources';
		/**
		*
		*/
		const DATA_PARAM = '_data';
		/**
		*
		*/
		const CURRENT_ROUTE_PARAM = '_currentRoute';
		/**
		*
		*/		
		public static function autoload( $controllers )
		{
			$controllers = ( is_array( $controllers ) ) ? $controllers : array( $controllers );
			\system\PhpToolCase\PtcRouter::group( 'website.autoload' , function( ) use ( $controllers )
			{
				foreach ( $controllers as $controller )
				{
					static::controller( $controller );
				}
			} )->prefix( \App::env( ) );
		}
		/**
		*
		*/
		public static function page( $page )
		{	
			ptc_log( $page , 
				'Website page is been compiled!' , static::$_debugCategory . ' Action' );
			$html = new Page( $page );
			return $html->compile( $page );
		}
		/**
		*
		*/
		public static function controller( $id )
		{
			$msg = 'Adding controller "' . $id . '" routes with website router helper!';
			ptc_log( $msg , '' , static::$_debugCategory . ' Config' );
			$routes = new Routes( $id );
			$routes->compile( );
		}
		/**
		*
		*/
		public static function setLang( $controllerID )
		{
			static::_getLanguages( );
			if ( $lang = \App::storage( 'website.languages.' . $controllerID ) )
			{
				\App::storage( 'website.current_lang' , $lang );
				$fallback_key = static::$_fallbackLang;
				$fallback_lang = (  $fallback_key && $fallback_key != $controllerID ) ? 
							\App::storage( 'website.languages.' . $fallback_key ) : null;
				\App::storage( 'website.fallback_lang' , $fallback_lang );
			}
			return false;
		}
		/**
		*
		*/
		public static function getLang( $param = 'suffix' )
		{
			return \App::storage( 'website.current_lang.' . $param );
		}
		/**
		*
		*/
		public static function getPath( $full = false )
		{
			return ( $full ) ? static::host( ) . \App::option( 'app.env' ) : static::host( );
		}
		/**
		*
		*/
		public static function host( )
		{
			if ( !static::$_urlPath )
			{
				static::$_urlPath = \system\PhpToolCase\PtcRouter::getProtocol( ) . '://' . $_SERVER[ 'HTTP_HOST' ];
			}
			return static::$_urlPath;
		}
		/**
		*
		*/
		public static function getRoute( $name , $relative = false )
		{
			$name = str_replace( '{current_lang}' , static::getLang( 'suffix' ) , $name );
			$path = ( $relative ) ? null : static::host( );
			return $path . \system\PhpToolCase\PtcRouter::getRoute( $name );
		}
		/**
		*
		*/
		public static function currentController( $controllerID = null )
		{
			if ( $controllerID ){ static::$_currentController = $controllerID; }
			return static::$_currentController;
		}
		/**
		*
		*/
		public static function currentRoute( $name = null )
		{
			if ( $name ){ static::$_currentRoute = $name; }
			return static::$_currentRoute;
		}
		/**
		*
		*/
		protected static $_languages = null;
		/**
		*
		*/
		protected static $_currentController = null;
		/**
		*
		*/
		protected static $_currentRoute = null;
		/**
		*
		*/		
		protected static $_debugCategory = 'Website Helper';
		/**
		*
		*/		
		protected static $_urlPath = null;
		/**
		*
		*/		
		protected static $_fallbackLang = null;
		/**
		*
		*/
		protected static function _getLanguages( )
		{
			if ( !static::$_languages )
			{
				$xml = simplexml_load_file( ptc_path( 'app' ) . static::XML_CONFIG . '/languages.xml' );
				if ( $languages = $xml->xpath( "//lang" ) )
				{
					foreach ( $languages as $language )
					{
						$array = array
						( 
							'prefix'		=>	( string ) $language->prefix ,
							'suffix'		=>	( string ) $language->suffix ,
							'controller'	=>	( string ) $language->attributes( )->controller
						);
						if ( $language->xml )
						{
							$array[ 'xml' ] = ptc_path( 'app' ) . 
								static::XML_TRANSLATIONS . '/' . ( string ) $language->xml; 
						}
						if ( $language->js ){ $array[ 'js' ] = ( string ) $language->js; }
						if ( $language->attributes( )->main )
						{
							static::$_fallbackLang = ( string ) $language->attributes( )->controller;
						}
						\App::storage( 'website.languages.' . $language->attributes( )->controller , $array );
					}
				}
			}
			static::$_languages = true;
		}
	}