<?php
	
	namespace helpers\EmailManager;
	
	class EmailManager
	{
		const BASE_PATH =  '/views/email-templates';
	
		public function __construct( $folder , $translator = null )
		{
			$this->_basePath = realpath( ptc_path( 'app' ) . static::BASE_PATH . $folder );
			if ( !$this->_basePath )
			{
				trigger_error( 'EmailManager helper could not find folder ' . 
					ptc_path( 'root' ) . static::BASE_PATH . $folder . 
							' for email templates!' , E_USER_ERROR );
			}
			if ( $translator && !$this->_xml( $translator ) ){ return false; }
		}
		
		public function data( $data )
		{
			$this->_data = $data;
			return $this;
		}
		
		public function compile( $template )
		{
			if ( $this->_translator ){ $this->_data[ '_lang' ] = $this->_translator; }
			$tpl = $this->_basePath . '/views/' . $template;
			$this->_template = \system\PhpToolCase\PtcView::make( $tpl , $this->_data )->compile( );
			return $this;
		}
		
		public function getTemplate( ){ return $this->_template; }
		
		public function getSubject( ){ return $this->_compileSubject( ); }
		
		public function subject( $template , $compileSubject = false )
		{
			$this->_compileSubject = $compileSubject;
			if ( false !== strpos( $template , 'xml:' ) )
			{
				$this->_subject = $this->_translator->val( str_replace( 'xml:' , '' , $template ) );
				return $this;
			}
			$this->_subject = $template;
			return $this;
		}
		
		public function from( $address )
		{
			$this->_from = $address;
			return $this;
		}
		
		public function send( $address , $type = 'html' )
		{
			$this->_compileSubject( );
			$headers = array( );
			$headers .= 'MIME-Version: 1.0' . "\r\n";
			
			if ( 'html' === $type ){ $headers .= "Content-type: text/html\r\n"; }
			if ( $this->_from )
			{
				$headers .= 'From: ' . $this->_from . "\r\n";
				$headers .= 'Reply-To: ' . $this->_from . "\r\n";
			}
			$headers .= 'X-Mailer: PHP/' . phpversion( );
			mail( $address , $this->_subject , $this->_template , $headers );
			$this->reset( );
		}
		
		public function reset( )
		{
			$this->_data = array( );
			$this->_template = null;
			$this->_subject = null;
			$this->_from = null;
			$this->_compileSubject = false;
		}
		
		protected function _xml( $file )
		{
			if ( !class_exists( '\helpers\Translator\Translator' ) )
			{
				trigger_error( 'Cannot use xml language files, ' .
					'helper class must be present!' , E_USER_ERROR );
				return false;
			}
			$path = $this->_basePath . '/languages/';
			if ( is_array( $file ) )
			{
				$this->_translator = new \helpers\Translator\Translator( $path . $file[ 0 ] , $path . $file[ 1 ] );
			}
			else{ $this->_translator = new \helpers\Translator\Translator( $path . $file ); }
			if ( !$this->_translator ){ return false; }
		}
		
		protected function _compileSubject( )
		{
			if ( $this->_compileSubject && !empty( $this->_data ) )
			{
				foreach ( $this->_data as $key => $val )
				{
					if ( '_lang' === $key ){ continue; }
					$this->_subject = str_replace( '{' . $key . '}' , $val , $this->_subject );
				}
			}
			return $this->_subject;
		}
		
		protected $_compileSubject = false;
		
		protected $_from = null;
		
		protected $_data = array( );
		
		protected $_folder = null;
		
		protected $_basePath = null;
		
		protected $_template = null;
		
		protected $_translator = null;
		
		protected $_subject = null;
	}