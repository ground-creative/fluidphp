<?php

	namespace helpers\Model;

	class Base
	{		
		public function __construct( $data = null , $lang = null )
		{
			$this->_fields = is_object( $data ) ? $data : ( object ) $data;
			$this->_lang = $lang;
		}
		
		public function __get( $name )
		{
			if ( !property_exists( $this->_fields , $name ) )
			{
				trigger_error( get_called_class( ) . ' warning: ' . 
					'Could not find field with name "' . $name . '"!' , E_USER_WARNING );
				return false;
			}
			return $this->_fields->{$name};
		}
		
		/*public function __set( $name )
		{
			if ( !property_exists( $this , $name ) )
			{
				throw new \Exception( get_called_class( ) . ' error: ' . 
						'Could not find field with name "' . $name . '"!' );
			}
			return $this->_fields->{$name};
		}*/
		
		public function __call( $method , $args = array( ) )
		{
			if ( 0 === strpos( $method , 'get_' ) )
			{
				$field = substr( $method , 4 );
				return $this->{$field};
			}
			else if ( 0 === strpos( $method , 'set_' ) )
			{
				$field = substr( $method , 4 );
				return $this->{$field} = $args[ 0 ];
			}
			else if ( 0 === strpos( $method , 'has_' ) )
			{
				$field = substr( $method , 4 );
				return ( property_exists( $this>_fields , $name ) ) ? true : false;
			}
			else if ( 0 === strpos( $method , 'is_set_' ) ) // same as "has_"
			{
				$field = substr( $method , 7 );
				return ( property_exists( $this>_fields , $name ) ) ? true : false;
			}
			else if ( 0 === strpos( $method , 'is_empty_' ) )
			{
				$field = substr( $method , 9 );
				$value = $this->{$field};
				switch( $value )
				{
					case is_string( $value ): 
						return ( 0 == strlen( $value ) ) ? true : false;
					break;
					case is_array( $value ):
						return ( 0 == sizeof( $value ) ) ? true : false;
					break;
					case is_object( $value ):
						return false;
					break;
					case is_null( $value ):
						return true;
					break;
					case is_bool( $value ):
						return false;
					break;
					default: return false;
				}
				
			}
			return call_user_func_array( array( $this , $method ), $args );
		}
		
		public static function __callStatic( $method , $args = array( ) )
		{
			$class = get_called_class( );
			//$obj = new $class( $args[ 0 ] );
			return call_user_func_array( array( $class , $method ), $args );
		}
		
		public function toArray( ){ return $this->_fields; }
		
		public function toJson( ){ return json_encode( $this->_fields ); }
	}