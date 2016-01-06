<?php

	namespace helpers\Sermepa;

	class Form
	{	
		public function __construct( $params = array( ) , $isSandBox = false )
		{
			$this->_params = array_merge( $this->_defaultParams , $params );
			$this->_isSandBox = $isSandBox;
			return $this;
		}
		
		public function create( )
		{
			return $this->_setFormParams( );
		}
		
		public function signature( $params )
		{
			$signature = $params[ 'amount' ] . $params[ 'order' ] . 
						$params[ 'merchant_code' ] . $params[ 'currency' ];
			if ( $params[ 'full_signature' ] )
			{
				$signature .= $params[ 'transaction_type' ] . $params[ 'merchant_url' ];
			}
			$signature .= $params[ 'merchant_key' ];
			return strtoupper( sha1( $signature ) );
		}
		
		protected $_params = array( );
		
		protected $_isSandBox = false;
		
		protected $_defaultParams = array
		(
			'amount'				=>	null ,		// required
			'merchant_code'		=>	null ,		// required
			'merchant_key'			=>	'qwertyasdf0123456789' ,
			'request_url'			=>	'https://sis.sermepa.es/sis/realizarPago' ,	
			'sandbox_url'			=>	'https://sis-t.sermepa.es:25443/sis/realizarPago' ,
			'currency'				=>	978 ,
			'terminal'				=>	001 ,
			'transaction_type'		=>	0 ,
			'merchant_url'			=>	null ,
			'url_ok'				=>	null ,
			'url_ko'				=>	null ,
			'consumer_language'	=>	2 ,
			'merchant_data'		=>	null ,
			'product_description'	=>	null ,
			'order'				=>	null , 
			'form_id'				=>	null ,
			'full_signature'			=>	false ,
			'form_tpl'				=>	null
		);
		
		protected function _setFormParams( )
		{
			$params = $this->_params;
			$params[ 'form_url' ] = ( $this->_isSandBox ) ? 
								$params[ 'sandbox_url' ] : $params[ 'request_url' ]; 
			unset( $params[ 'request_url' ] );
			unset( $params[ 'sandbox_url' ] );
			$params[ 'form_tpl' ] = ( $params[ 'form_tpl' ] ) ? 
										$params[ 'form_tpl' ] : __DIR__ . '/form.tpl.html';
			ob_start( );
			require_once( $params[ 'form_tpl' ] );
			$form = ob_get_contents( );
			ob_end_clean( );
			unset( $params[ 'form_tpl' ] );
			$params[ 'order' ] = ( $params[ 'order' ] ) ? $params[ 'order' ] : date( 'ymdHis' ); 
			$params[ 'form_id' ] = ( $params[ 'form_id' ] ) ? 
								$params[ 'form_id' ] : 'tpv-form-' . rand( '100' , '999' );
			$params[ 'signature' ] = $this->signature( $params );
			foreach ( $params as $k => $v )
			{
				$form = str_replace( '{' . $k . '}' , $v , $form );
			}
			return $form;
		}
	}