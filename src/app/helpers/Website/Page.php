<?php

	namespace helpers\Website;
	
	class Page
	{
		/**
		*
		*/
		public function __construct( $page ){ $this->_page = $page; }
		/**
		*
		*/
		public function render( ){ echo $this->compile( $this->_page ); }
		/**
		*
		*/
		public function compile( )
		{
			return $this->_parseXML( $this->_page )->compile( );
		}
		/**
		*
		*/
		protected function _parseXML( )
		{
			$this->_xml = simplexml_load_file( ptc_path( 'app' ) . Website::XML_CONFIG . '/pages.xml' );
			$xml_block = $this->_xml->xpath( "//page[@id='" . $this->_page . "']" );
			if ( !$xml_block )
			{
				trigger_error( 'Page block "' . $this->_page . 
					'" was not set in pages.xml file!' , E_USER_ERROR );
				return false;
			}
			$data = array
			( 
				Website::RESOURCES_PARAM => null , 
				Website::LANG_PARAM => null , 
				Website::DATA_PARAM => null , 
				Website::CURRENT_ROUTE_PARAM => Website::currentRoute( )
			);
			$data[ Website::CURRENT_ROUTE_PARAM . 'Raw' ] = 
				( Website::currentRoute( ) && Website::getLang( 'suffix' ) ) ? 
				str_replace( '_' . Website::getLang( 'suffix' ) , '' ,  Website::currentRoute( ) ) : null;
			$views = $xml_block[ 0 ]->views->view;
			if ( $resources = $xml_block[ 0 ]->resources )
			{
				$resources_blocks = array( );
				foreach ( $resources[ 0 ] as $block )
				{
					$resources_blocks[ ] = ( string ) $block;
				}
				$data[ Website::RESOURCES_PARAM ] = new Resources( $resources_blocks );
			}
			$data[ Website::LANG_PARAM ] = $this->_getTranslator( );
			$view = \system\PhpToolCase\PtcView::make( 
				ptc_path( 'views' ) . Website::VIEWS_PATH . $views[ 0 ] , $data );
			if ( count( $views ) > 1 )
			{
				unset( $views[ 0 ] );
				foreach ( $views as $file )
				{
					$child = ( string ) $file->attributes( )->child;
					$view = $view->nest( $child , 
						ptc_path( 'views' ) . Website::VIEWS_PATH . $file , $data );
				}
			}
			return $view;
		}
		/**
		*
		*/		
		protected function _getTranslator( )
		{
			if ( $xml = \App::storage( 'website.current_lang.xml' ) )
			{
				$fallback = \App::storage( 'website.fallback_lang.xml' );
				$translator = new \helpers\Translator\Translator( $xml , $fallback );
				return $translator;
			}
			return null;
		}
		/**
		*
		*/		
		protected $_xml = null;
		/**
		*
		*/		
		protected $_page = null;
		/**
		*
		*/		
		protected $_translator = null;
	}