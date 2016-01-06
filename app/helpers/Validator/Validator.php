<?php

	namespace helpers\Validator;
	
	class Validator
	{
		/**
		*
		*/
		public function getRule( $fieldName , $rule = null )
		{ 
			return $this->getRules( $fieldName , $rule ); 
		}
		/**
		*
		*/
		public function getValue( $fieldName = null )
		{
			return $this->getValues( $fieldName ); 
		}
		/**
		*
		*/
		public function getError( $fieldName = null , $rule = null )
		{
			return $this->getErrors( $fieldName , $rule ); 
		}
		/**
		*
		*/
		public function getErrMsg( $fieldName = null , $rule = null )
		{
			return $this->getErrMsgs( $fieldName , $rule ); 
		}
		/**
		*
		*/
		public function __construct( $inputs , $messages = array( ) )
		{
			$class = get_called_class( );
			$this->_values = $inputs;
			$this->_errrorMessages = ( !empty( $messages ) ) ? 
				$messages + $class::$_errMsgs : $class::$_errMsgs;
		}
		/**
		*
		*/
		public function add( $fieldName , $rules )
		{
			$rules = ( is_callable( $rules ) ) ? call_user_func( $rules , $this ) : $rules;
			$rules  = explode( '||' , $rules );
			$arr = array( );
			foreach ( $rules  as $v )
			{
				$r = explode( ':' , $v );
				$arr[ $r[ 0 ] ] = ( isset( $r[ 1 ] ) ) ? $r[ 1 ] : true;
			}
			$this->_fields[ $fieldName ] = $this->_sortRules( $arr );
			return $this;
		}
		/**
		*
		*/
		public function getValues( $fieldName = null )
		{
			if ( $fieldName ){ return ptc_array_get( $this->_values , $fieldName ); }
			$values = array( );
			foreach ( $this->_fields as $field_name => $v )
			{
				$values[ $field_name ] = $this->_values[ $field_name ];
			}
			return $values;
		}
		/**
		*
		*/
		public function getConfig( $type = null )
		{
			$config = array( 'rules' => $this->_fields , 'messages' => $this->_errrorMessages );
			return ( $type ) ? $config[ $type ] : $config;
		}
		/**
		*
		*/
		public function getRules( $fieldName , $rule = null )
		{
			if ( $rule ){ return $this->_fields[ $fieldName ][ $rule ]; }
			return $this->_fields[ $fieldName ];
		}
		/**
		*
		*/
		public function getErrors( $fieldName = null , $rule = null )
		{
			if ( $fieldName && $rule ){ return $this->_errors[ $fieldName ][ $rule ]; }
			if ( $fieldName ){ return $this->_errors[ $fieldName ]; }
			return $this->_errors;
		}
		/**
		*
		*/
		public function getErrMsgs( $fieldName = null , $rule = null )
		{
			if ( $fieldName && $rule ){ return $this->_errMsgsArray[ $fieldName ][ $rule ]; }
			if ( $fieldName ){ return $this->_errMsgsArray[ $fieldName ]; }
			return $this->_errMsgsArray;
		}
		/**
		*
		*/
		public function isValid( )
		{
			$class = get_called_class( );
			foreach ( $this->_fields as $field_name => $rules )
			{
				if ( !isset( $this->_values[ $field_name ] ) ){ $this->_values[ $field_name ] = null; }
				$value = $this->_values[ $field_name ];
				foreach ( $rules as $k => $v )
				{
					$params = null;
					$callback = null;
					if ( in_array( $k , $class::$_rules[ 1 ] ) ) // option 1
					{
						$params = array( $value , $this->_values );
						$callback = array( get_called_class( ) , $k );
					}
					else if ( in_array( $k , $class::$_rules[ 2 ] ) ) // option 2
					{
						$params = array( $value , $v , $this->_values );
						$callback = array( get_called_class( ) , $k );
					}
					else	if ( array_key_exists( $k , $class::$_rules[ 3 ] ) ) // option 3 custom 
					{
						$callback = $class::$_rules[ 3 ][ $k ];
						$params =  array( $value , $this , $v );
					}
					else if ( 'default' === $k )
					{
						if ( is_null( $value ) )
						{ 
							$this->_values[ $field_name ] = ( '_NULL_' === $v ) ? null : $v;
							$value = $this->_values[ $field_name ]; 							
						}
						continue;
					}
					else if ( 'empty' === $k )
					{
						if ( empty( $value ) )
						{ 
							$this->_values[ $field_name ] = ( '_NULL_' === $v ) ? null : $v; 
							$value = $this->_values[ $field_name ]; 	
						}
						continue;
					}
					else
					{
						trigger_error( 'No callback found for rule "' . $k . '"!' , E_USER_ERROR );
						return false;
					}
					if ( !$is_valid = call_user_func_array( $callback , $params ) )
					{ 
						$this->_setError( $field_name , $k );
					}
				}
			}
			return ( empty( $this->_errors ) ) ? true : false; 
		}
		/**
		*
		*/
		protected $_errrorMessages = null;
		/**
		*
		*/
		protected $_values = null;
		/**
		*
		*/
		protected $_errors = array( );
		/**
		*
		*/
		protected $_fields = array( );
		/**
		*
		*/
		protected $_errMsgsArray = array( );
		/**
		*
		*/
		protected function _setError( $fieldName , $rule )
		{
			$class = get_called_class( );
			@$this->_errors[ $fieldName ][ $rule ] = 1; 
			@$this->_errMsgsArray[ $fieldName ][ $rule ] = $this->_errrorMessages[ $rule ]; 
		}
		/**
		*
		*/				
		protected function _sortRules( $rules )
		{
			if ( array_key_exists( 'default' , $rules ) )
			{
				$rules = array_merge( array( 'default' => $rules[ 'default' ] ) , $rules );
				if ( !array_key_exists( 'empty' , $rules ) ){ $rules[ 'empty' ] = $rules[ 'default' ]; }
			}
			if ( array_key_exists( 'empty' , $rules ) )
			{
				$rules = array_merge( array( 'empty' => $rules[ 'empty' ] ) , $rules );
			}
			return $rules;
		}
		/**
		*
		*/
		public static function make( $inputs , $rules , $messages = null )
		{
			$class = get_called_class( );
			$validator = new $class( $inputs , $messages );
			foreach ( $rules as $fieldName => $rule ){ $validator->add( $fieldName , $rule ); }
			return $validator;
		}
		/**
		*
		*/
		public static function loadConfig( $options )
		{
			if ( @!empty( $options[ 'rules' ] ) && 
				( @!empty( $options[ 'regex' ] ) || @!empty( $options[ 'defaults' ] ) ) )
			{
				foreach ( $options[ 'rules' ] as $name => $rules )
				{
					$replace = array( );
					foreach ( $rules as $k => $v )
					{
						$replace[ $k ] = @str_replace( array( '{regex}' , '{defaults}' ) , 
							array( $options[ 'regex' ][ $k ] , $options[ 'defaults' ][ $k ] ) , $v );
					}
					$options[ 'rules' ][ $name ] = $replace;
				}
			}
			if ( @!empty( $options[ 'custom_methods' ] ) )
			{
				foreach ( $options[ 'custom_methods' ] as $key => $val )
				{
					static::addRule( $key , $val );
				}
			}
			return $options;
		}
		/**
		*
		*/	
		public static function addRule( $ruleName , $callback , $errMsg = null )
		{
			if ( in_array( $ruleName , static::$_rules[ 1 ]  ) || 
				in_array( $ruleName , static::$_rules[ 2 ] ) || 
					array_key_exists( $ruleName , static::$_rules[ 3 ] ) )
			{
				trigger_error( 'Rule name "' . $ruleName . '"already exists!' , E_USER_ERROR );
			}
			static::$_rules[ 3 ][ $ruleName ] = $callback;
			if ( $errMsg ){ static::$_errMsgs[ $ruleName ] = $errMsg; }
		}
		/**
		*
		*/	
		public static function inputs( $type )
		{
			$case = explode( '.' , $type );
			$key = ( isset( $case[ 1 ] ) ) ? str_replace( $case[ 0 ] . '.' , '' , $type ) : null;
			switch ( strtolower( $case[ 0 ] ) )
			{
				case '_get': $inputs = $_GET; break;
				case '_session': $inputs = $_SESSION; break;
				case '_cookies': $inputs = $_COOKIE; break;
				case '_request': $inputs = $_REQUEST; break;
				case '_post': $inputs = $_POST; break;
				case '_put': 
				case '_delete': 
					parse_str( file_get_contents( 'php://input' ) , $inputs ); 
				break;
				case '_raw': $inputs = file_get_contents( 'php://input' ); break;
				default: 
					trigger_error( 'Input type "' . $case[ 0 ] . '" is not supported!' , E_USER_ERROR );
					return false;
			}
			return ( $key ) ? ptc_array_get( $inputs , $key ) : $inputs;
		}
		/**
		*
		*/		
		public static function equalTo( $value , $matchFieldName , $inputs )
		{
			if ( is_null( $value ) ){ return true; }
			return ( $value === @$inputs[ $matchFieldName ] ) ? true : false; 
		}
		/**
		*
		*/	
		public static function required( $value )
		{
			return ( @strlen( $value ) > 0 ) ? true : false; 
		}
		/**
		*
		*/		
		public static function match( $value , $pattern )
		{
			if ( is_null( $value ) ){ return true; }
			return preg_match( $pattern , $value ) ? true : false;
		}
		/**
		*
		*/		
		public static function email( $value )
		{
			if ( is_null( $value ) ){ return true; }
			//$pattern = "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$";
			//return preg_match( '/' . $pattern . '/i' , strtolower( $value ) ) ? true : false;
			return ( filter_var( strtolower( $value ) , FILTER_VALIDATE_EMAIL ) ) ? true : false;
		}
		/**
		*
		*/		
		public static function number( $value )
		{
			if ( is_null( $value ) ){ return true; }
			return ( @is_numeric( $value ) ) ? true : false; 
		}
		/**
		*
		*/		
		public static function range( $value , $range )
		{
			if ( is_null( $value ) ){ return true; }
			$ranges = explode( ',' , $range );
			return ( $value >= $ranges[ 0 ] && $value <= $ranges[ 1 ] ) ? true : false;
		}
		/**
		*
		*/		
		public static function min( $value , $length )
		{
			if ( is_null( $value ) ){ return true; }
			return ( @strlen( $value ) >= $length ) ? true : false; 
		}
		/**
		*
		*/		
		public static function max( $value , $length )
		{
			if ( is_null( $value ) ){ return true; }
			return ( @strlen( $value ) <= $length ) ? true : false; 
		}
		/**
		*
		*/				
		protected static $_rules = array
		(
			1 => array( 'required' , 'email' , 'number' ) ,
			2 => array( 'equalTo' , 'match' , 'range' , 'min' , 'max' ) ,
			3 => array( ) // custom validator methods
		);
		/**
		*
		*/	
		protected static $_errMsgs = array
		(
			'required'	=> 	'This field is required' ,
			'email'	=> 	'This is not a valid email' ,
			'match'	=> 	'Invalid pattern' ,
			'equalTo'	=>	'Please enter the same value' ,
			'number'	=>	'Invalid numeric value' ,
			'max'	=>	'value must not be greater then {0}' ,
			'min'		=>	'value must not be smaller then {0}' ,
			'range'	=>	'value be between {0} and {1}'
		);
	}