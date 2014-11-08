<?php

	namespace helpers\Website;
	
	class Resources
	{
		/**
		*
		*/
		public function __construct( $resources ){ $this->_resource = $resources; }
		/**
		*
		*/
		public function css( )
		{
			return Resources::_mergeBlocks( $this->_resource , 'css' );
		}
		/**
		*
		*/
		public function js( )
		{
			return Resources::_mergeBlocks( $this->_resource , 'js' );
		}
		/**
		*
		*/
		public function raw( $resource , $type )
		{
			return Resources::_build( $resource , $type );
		}
		/**
		*
		*/
		protected $_resource = null;
		/**
		*
		*/
		protected static $_xml = null;
		/**
		*
		*/
		protected static $_resources = array( );
		/**
		*
		*/
		protected static function _mergeBlocks( $blocks , $type )
		{
			$blocks = is_array( $blocks ) ? $blocks : array( $blocks );
			$string = null;
			$a = 0;
			foreach ( $blocks as $block_id )
			{
				$string .= static::_getBlock( $block_id , $type );
			}
			return ltrim( $string );
		}
		/**
		*
		*/
		protected static function _build( $items , $type , $comment = null )
		{
			if ( !static::_initialize( ) ){ return false; }
			$tpl = ( 'css' === $type ) ? 
				'<link rel="stylesheet" href="{path}">' : 
					'<script type="text/javascript" src="{path}"></script>';
			$dep = ( $comment ) ? "\t" . '<!-- ' . $comment . '-->' . "\n" : null;
			$items = ( is_array( $items ) ) ? $items : array( $items );
			foreach ( $items as $item )
			{
				if ( !isset( static::$_resources[ $item ] ) )
				{
					trigger_error( 'Resource id "' . 
						$path . $item . '" is not set!', E_USER_ERROR );
					return false;
				}
				$path = ( !static::$_resources[ $item ][ 'external' ] ) ? 
						Website::host( ) . \App::option( 'app.env' ) : null;
				$item = static::$_resources[ $item ][ 'file' ];
				$dep .=  "\t" . str_replace( '{path}' ,  $path . $item , $tpl ) . "\n";
			}
			return $dep;
		}
		/**
		*
		*/
		protected static function _getBlock( $blockID , $type )
		{
			if ( !static::_initialize( ) ){ return false; }
			$block = static::$_xml->xpath("//block[@id='" . $blockID . "']");
			if ( !$block )
			{
				trigger_error( 'Block id "' . $blockID . 
					'" not found in xml config file!' , E_USER_ERROR );
				return false;
			}
			$children = $block[ 0 ]->{$type};
			if ( !$children->resource )
			{
				trigger_error( 'No "' . $type .
					'" resources found in block "' . $blockID . '"!' , E_USER_ERROR );
				return false;
			}
			$string = null;
			for ( $i = 0; $i < count( $children ); $i++ )
			{ 
				$items = array( );
				for ( $a = 0; $a < count( $children->{$i} ); $a++ )
				{
					$resource_id = (string) $children->{$i}->resource->{$a};
					$items[ ] = $resource_id;
				}
				if ( $comment = $children->{$i}->attributes( ) ){ $comment = $comment->type; }
				$string .= static::_build( $items , $type , $comment );
			}
			return $string;
		}
		/**
		*
		*/
		protected static function _initialize( )
		{
			if ( !static::$_xml )
			{
				static::$_xml = simplexml_load_file( 
					ptc_path( 'app' ) . Website::XML_CONFIG . '/resources.xml' );
				$resources = static::$_xml->xpath("//resources");
				foreach ( $resources[ 0 ]->file as $file )
				{
					$id = (string) $file->attributes( )->id;
					if ( isset( static::$_resources[ $id ] ) )
					{
						trigger_error( 'Resource id "' . $id . '" already set!' , E_USER_ERROR );
						return false;
					}
					static::$_resources[ $id ] = array( 'file' => $file->{0} , 'external' => false );
					if ( $external = (string) $file->attributes( )->external )
					{ 
						static::$_resources[ $id ][ 'external' ] = true;
					}
				}
				return static::$_xml;
			}
			return true;
		}
	}