<?php

	namespace helpers\Website;
	
	class Routes
	{
		/**
		*
		*/
		public function __construct( $controllerID )
		{
			$this->_xml = simplexml_load_file( 
				ptc_path( 'app' ) . Website::XML_CONFIG . '/routes.xml' );
			$this->_controllerID = $controllerID;
		}
		/**
		*
		*/
		public function compile( )
		{
			$controller_id = $this->_controllerID;
			$block = $this->_xml->xpath( "//controller[@id='" . $controller_id . "']" );
			if ( !$block )
			{
				trigger_error( 'Controller block "' . $controller_id . 
						'" was not set in routes.xml file!' , E_USER_ERROR );
				return false;
			}
			$route_prefix = ( $prefix = $block[ 0 ]->attributes( )->prefix ) ? $prefix : '';
			if ( $block[ 0 ]->patterns )
			{
				foreach ( $block[ 0 ]->patterns[ 0 ]->pattern as $pattern )
				{
					$param = ( string ) $pattern->attributes( )->param;
					if ( isset( $this->_patterns[ $param ] ) )
					{
						trigger_error( 'Pattern for param ' . 
							$param . ' already exists!' , E_USER_ERROR );
						return false;
					}
					$this->_patterns[ $param ] = ( string ) $pattern;
				}
			}
			foreach ( $block[ 0 ]->route as $route )
			{
				if ( !$route->map )
				{
					trigger_error( 'Please add a map tag to route ' . 
						$route->attributes( )->url . '!' , E_USER_ERROR );
					return false;
				}
				$map = (string ) $route->map[ 0 ];
				$callback = function( ) use( $route , $controller_id , $map )
				{
					\helpers\Website\Website::currentController( $controller_id );
					\helpers\Website\Website::currentRoute( $map );
					\helpers\Website\Website::setLang( $controller_id );
					return \helpers\Website\Website::page( ( string ) $route->page[ 0 ] );
				};
				$url = $route->attributes( )->url;
				if ( $route_prefix )
				{
					$url = ( '/' === $r = substr( $url , -1 ) ) ? $r : $url;
					$route_prefix  = ( '/' !== substr( 
						$route_prefix , -1 ) ) ? $route_prefix . '/' : $route_prefix;
					$url= $route_prefix . $url;
				}
				$params = array( $url , $callback );
				$methods = ( $route->methods ) ? 
					explode( ':' , $route->methods[ 0 ] ) : array( 'get' );
				$a = 0;
				foreach ( $methods as $method )
				{
					$router = call_user_func_array( 
						'\system\PhpToolCase\PtcRouter::' . $method , $params );
					foreach ( $this->_patterns as $k => $v )
					{
						if ( preg_match( '<{' . $k . '}|{' . $k . '?}>' , $route->attributes( )->url ) )
						{
							$router->where( $k , $v );
						}
					}
					$router->map( (string ) $route->map[ 0 ] );
					// THIS PART SHOULD BE ADDED WITH THE METHOD
					if ( $route->protocol ){ $router->protocol( (string ) $route->protocol ); }
					if ( $route->domain ){ $router->domain( (string ) $route->domain ); }
					++$a;
				}
			}
			return $this;
		}
		/**
		*
		*/
		protected $_xml = null;
		/**
		*
		*/
		protected $_controllerID = null;
		/**
		*
		*/
		protected $_patterns = array( );
	}