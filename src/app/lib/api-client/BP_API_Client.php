<?php

	/**
	* BPREMIUM API V3 CLIENT LIBRARY
	* @version	1.0.1
	* @requires	php curl extension
	*/

	class BP_API_Client
	{
		public function __construct( array $options )
		{
			if ( !isset( $options[ 'client_id' ] ) )
			{
				trigger_error( 'Parameter cliient_id is not set, cannot continue!' , E_USER_ERROR );
				return false;
			}
			if ( !isset( $options[ 'client_secret' ] ) )
			{
				trigger_error( 'Parameter client_secret is not set, cannot continue!' , E_USER_ERROR );
				return false;
			}
			if ( !isset( $options[ 'api_address' ] ) )
			{
				trigger_error( 'Parameter api_address is not set, cannot continue!' , E_USER_ERROR );
				return false;
			}
			
			if ( '/' === substr( $options[ 'api_address' ] , -1 ) )
			{
				$options[ 'api_address' ] = substr( $options[ 'api_address' ] , 0 , -1 );
			}
			
			$this->_isAjax = ( !isset( $options[ '_is_ajax' ] ) ) ? true : $options[ '_is_ajax' ];
			
			if ( $this->_isAjax && !isset( $options[ 'origin' ] ) )
			{
				trigger_error( 'Parameter _is_ajax cannot be true without origin parameter!' , E_USER_ERROR );
				return false;
			}
			
			$this->_options = $options;
		}
		
		public function fetch( $uri , $request , $data = array( ) )
		{
			if ( @$this->_isAjax( ) ) // accept ajax calls only from same domain
			{
				if ( '/' !== substr( $uri  , -1 ) ){ $uri = $uri . '/'; }
				if ( '/' !== substr( $uri , 0 , 1 ) ){ $uri  = '/' . $uri; }
				$this->_uri = $uri;
				unset( $data[ 'uri' ] );
				unset( $data[ 'request' ] );
				$data[ 'remote_ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
				$this->_data = $data;
				$this->_request = strtoupper( $request );
				$this->_buildSignature( $data );
				return $this->_request( );
			}
			return array( 'result' => array( 'success' => false , 'error' => true , 
										'error_description' => 'unauthorized' , 
										'message' => 'unauthorized' , 'code' => 512 ) );
		}
		
		protected function _isAjax( )	 // accept ajax calls only
		{
			if ( $this->_isAjax )
			{
				if ( empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && 
					strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) != 'xmlhttprequest' )
				{
					return false;
				}
				if ( '*' !== $this->_options[ 'origin' ] && 
					$this->_options[ 'origin' ] !== $_SERVER[ 'HTTP_HOST' ] )
				{
					return false;
				}
				return true;
			}
			return true;
		}
		
		// put request could be improved
		protected function _request( )
		{
			$url = $this->_options[ 'api_address' ] . $this->_uri;
			$this->_data[ 'client_id' ] = $this->_options[ 'client_id' ];
			$this->_data[ 'request_time' ] = $this->_timestamp;
			$this->_data[ 'signature' ] = $this->_signature;
			$fields_string = '';
			$fields_string = http_build_query( $this->_data );
			$ch = curl_init( );
			curl_setopt( $ch , CURLOPT_HEADER , 0 );
			curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 );
			curl_setopt( $ch , CURLOPT_CONNECTTIMEOUT , 30 );
			curl_setopt( $ch , CURLOPT_SSL_VERIFYPEER , true );
			curl_setopt( $ch , CURLOPT_REFERER , @$_SERVER[ 'HTTP_REFERER' ] );
			curl_setopt( $ch , CURLOPT_USERAGENT , 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)' );	
			curl_setopt( $ch , CURLOPT_CUSTOMREQUEST , $this->_request );	
			if ( 'POST' === $this->_request )
			{
				curl_setopt( $ch , CURLOPT_POST , count( $this->_data ) );
				curl_setopt( $ch , CURLOPT_POSTFIELDS , $fields_string );
			}
			else{ $url = $url . '?' . $fields_string; } // get , put and delete
			curl_setopt( $ch , CURLOPT_URL , $url );
			$result = curl_exec( $ch );
			$http_code = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
			$content_type = curl_getinfo( $ch , CURLINFO_CONTENT_TYPE );
			if ( $curl_error = curl_error( $ch ) ) 
			{
				throw new \Exception( $curl_error , \Exception::CURL_ERROR );
			} 
			else{ $json_decode = json_decode( $result , true ); }
			curl_close( $ch );
			$this->_reset( ); // reset values for next request
			return array
			(
				'result'		=>	( null === $json_decode ) ? $result : $json_decode ,
				'code'		=>	$http_code ,
				'_is_ajax'		=>	$this->_isAjax ,
				'content_type'	=>	$content_type
			);
			
		}
		
		protected function _buildSignature( $data )
		{
			$this->_timestamp = time( );
			$this->_signature = sha1( $this->_timestamp . $this->_request . $this->_options[ 'api_address' ] . 
								$this->_uri .$this->_options[ 'client_id' ] . $this->_options[ 'client_secret' ] );
		}
		
		protected function _reset( )
		{
			$this->_request = null;
			$this->_uri = null;
			$this->_data = array( );
		}
		
		protected $_isAjax = true;
		
		protected $_timestamp = null;
		
		protected $_signature = null;
		
		protected $_request = null;
		
		protected $_data;
		
		protected $_uri = null;
		
		protected $_options = array( );
	}
