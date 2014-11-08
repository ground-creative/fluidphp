<?php

	/**
	* PHP TOOLCASE OBJECT RELATIONAL MAPPING CLASS
	* PHP version 5.3
	* @category 	Library
	* @version	0.9.3b
	* @author   	Irony <carlo@salapc.com>
	* @license  	http://www.gnu.org/copyleft/gpl.html GNU General Public License
	* @link     	http://phptoolcase.com
	*/
	
	namespace system\PhpToolCase;

	class PtcMapper
	{
		/**
		* Retrives the query builder from the connection manager and table column names 
		*/
		public function __construct( ){ static::_initialize( ); }
		/**
		* Resets previously set values
		*/
		public function reset( ){ $this->_fields = array( ); }
		/**
		* Removes values from fields property
		* @param	string		$key		the table column name
		*/
		public function remove( $key ){ unset( $this->_fields[ $key ] ); }
		/**
		* Returns values as an associative array. See @ref convert_to_array
		*/
		public function toArray( ){ return $this->_fields; }
		/**
		* Returns values as a json array. See @ref convert_to_json
		*/
		public function toJson( ){ return json_encode( $this->_fields ); }
		/**
		* Sets values based on associative array. See @ref adding_record
		* @param	array		$array	an associative array with values
		* @return	the new created record
		*/
		public static function create( $array ) 
		{ 
			$class = get_called_class( );
			$record = new $class( );
			foreach ( $array as $k => $v ){ $record->$k = $v; }
			return $record;
		}
		/**
		* Deletes record in table based on id. See @ref delete_record
		* @return	the number of affected rows
		*/
		public function delete( )
		{
			$storage = static::$_storage[ get_called_class( ) ];
			static::_fireEvent( 'deleting' , array( 
					&$this->_fields[ static::$_uniqueKey ] , &$this ) );
			$result = static::_getQB( )->table( $storage[ 'table' ] )
							 ->delete( $this->_fields[ static::$_uniqueKey ] )
							 ->run( );	 
			static::_fireEvent( 'deleted' , 
				array( &$this->_fields[ static::$_uniqueKey ] , &$this , &$result ) );
			//$this->reset( );	// reset fields
			return $result;
		}
		/**
		* Inserts a new record in table. See @ref adding_record and @ref update_record
		* @return	the numbr of affected rows
		*/
		public function save( )
		{
			$storage = static::$_storage[ get_called_class( ) ];
			if ( empty( $this->_fields ) )
			{
				trigger_error( 'Nothing to save in table' . 
					static::$storage[ 'table' ] . '!' , E_USER_WARNING );
				return false;
			}
			static::_mapFields( );
			$values = $this->_fields;
			static::_fireEvent( 'saving' , array( &$this ) );
			if ( array_key_exists( static::$_uniqueKey , $this->_fields ) ) // update record
			{
				static::_fireEvent( 'updating' , array( &$this ) );
				unset( $values[ static::$_uniqueKey ] );
				$result = static::_getQB( )->table( $storage[ 'table' ] )
							->update( $values , $this->_fields[ static::$_uniqueKey ] )
							->run( );
				static::_fireEvent( 'updated' , array( &$this , $result ) );
			}
			else // insert new row
			{
				static::_fireEvent( 'inserting' , array( &$this ) );
				$result = static::_getQB( )->table( $storage[ 'table' ] )
								->insert( $this->_fields )->run( ); 
				static::_fireEvent( 'inserted' , array( &$this , $result ) );
			}
			static::_fireEvent( 'saved' , array( &$this , $result ) );
			//$this->reset( );	// reset fields
			return $result;
		}
		/**
		* Retrieves single record from the table based on id. See @ref retrieve_record_by_id
		* @param	int	$id	the record id
		* @return	a new instance of this class.
		*/
		public static function find( $id )
		{
			$class = static::_initialize( );
			static::_getQB( )->setFetchMode( \PDO::FETCH_CLASS | 
										\PDO::FETCH_PROPS_LATE , $class );
			return $result = static::_getQB( )->table( static::$_storage[ $class ][ 'table' ] )
								->where( static::$_uniqueKey , '=' , $id )
								->row( );
			return static::_guard( $result );
		}
		/**
		* Gets all records. See @ref retrieve_records
		* @return	an array with multiple instances of this class as rows
		*/
		public static function all( )
		{
			$class = static::_initialize( );
			static::_getQB( )->setFetchMode( \PDO::FETCH_CLASS | 
									\PDO::FETCH_PROPS_LATE , $class );
			$result = static::_getQB( )->table( static::$_storage[ $class ][ 'table' ] )
								->run( );
			return static::_guard( $result );
		}
		/**
		* Retrieves column names from table
		@return	an associative array with column names as keys 
		*/
		public static function getColumns( )
		{
			$class = static::_initialize( );
			if ( array_key_exists( 'columns' , static::$_storage[ $class ] ) )
			{ 
				return static::$_storage[ $class ][ 'columns' ]; 
			}
			$cols = array( );
			static::_getQB( )->setFetchMode( \PDO::FETCH_ASSOC );
			$columns = static::_getQB( )->run( 'SHOW COLUMNS FROM ' . 
				static::_getQB( )->sanitize( static::$_storage[ $class ][ 'table' ] ) );
			foreach ( $columns as  $name ){ $cols[ $name[ 'Field' ] ] = $name[ 'Field' ]; }
			return static::$_storage[ $class ][ 'columns' ] = $cols;
		}
		/**
		* Retrieves the table name used by the class
		@return	a string with the table name
		*/
		public static function getTable( )
		{
			$class = static::_initialize( );
			return static::$_storage[ $class ][ 'table' ]; 
		}
		/**
		* Adds observers to the class to use event listeners with the queries. See @ref using_observers
		* @param	string		$class	the name of the class that will be used as observer
		*/
		public static function observe( $class = null )
		{
			$events_class = static::_namespace( static::$_eventClass , 'PtcEvent' );
			if ( !class_exists( $events_class ) )
			{
				trigger_error( $events_class . ' NOT FOUND!' , E_USER_ERROR );
				return false;
			}
			$class = ( $class ) ? $class : get_called_class( );
			$methods = get_class_methods( $class );
			foreach ( static::$_events as $event )
			{
				if ( in_array( $event , $methods ) )
				{
					$cls = strtolower( $class );
					$events_class::listen( $cls . '.' . $event , $class . '::' . $event );
					static::$_observers[ get_called_class( ) ][ $cls . '.' . $event ] = $event;
				}
			}
		}
		/**
		* Retrieves last inserted id 
		*/
                public static function lastId( )
                { 
                        static::_initialize( );
                        return static::_getQB( )->lastId( ); 
                }
		/**
		* Database table property 
		*/
		protected static $_table = null;
		/**
		* Unique row identifier column name
		*/
		protected static $_uniqueKey = 'id';		
		/**
		* Maps column names to fields, if "as" is used. See @ref mapping_fields
		*/
		protected static $_map = array( );
		/**
		* Guards certain table columns that should not be used with the object
		*/
		protected static $_guard = array( );
		/**
		* Event class name property. See @ref specifyEventClass
		*/
		protected static $_eventClass = 'PtcEvent';
		/**
		* Connection Manager class name property. See @ref specifyConnectionManagerClass
		*/
		protected static $_connectionManager = 'PtcDb';
		/**
		* Connection name to be used property. See @ref change_connection
		*/
		protected static $_connectionName ='default';
		/**
		* Possible observer events array. See @ref using_observers
		*/
		protected static $_events = array
		(	
			'inserting' , 'inserted' , 'updating' , 'updated' , 
			'deleting' , 'deleted' , 'saving' , 'saved'
		);
		/**
		* Property that holds the observer classes
		*/
		protected static $_observers = array( );
		/**
		* Column and table names property
		*/
		protected static $_storage = array( );
		/**
		* Array of created values property 
		*/
		protected $_fields = array( );
		/**
		* Sets values
		* @param	string		$key		the column name
		* @param	mixed		$value	the value
		*/
		public function __set( $key , $value )
		{
			if ( !static::_checkColumn( $key ) ){ return false; }
			if ( in_array( $key , static::$_guard ) ){ return; }
			return $this->_fields[ $key ] = $value;
		}
		/**
		* Retrieves values 
		* @param	string		$key		the column name
		*/
		public function __get( $key )
		{
			if ( !static::_checkColumn( $key ) ){ return false; }
			return $this->_fields[ $key ];
		}
		/**
		* Calls shortcut methods for getting / setting single values, or the QueryBuilder methods directly.
		* See @ref  update_single_value , @ref  retrieve_single_value and @ref using_query_builder
		* @param	string		$method		the method name
		* @param	array		$args		an array with arguments for the method		
		* @return	the result of the query
		*/
		public static function __callStatic( $method , $args )
		{
			$class = static::_initialize( ); 
			if ( 0 === strpos( $method , 'get_' ) )
			{
				$col = substr( $method , 4 );
				if ( !static::_checkColumn( $col ) ){ return false; }
				$column = ( !array_key_exists( 1 , $args ) ) ? static::$_uniqueKey : $args[ 0 ];
				$value = ( !array_key_exists( 1 , $args ) ) ? $args[ 0 ] : $args[ 1 ];
				return static::_getQB( )->table( static::$_storage[ $class ][ 'table' ] )
							     ->where( $column , '=' , $value )
							     ->row( $col );
			}
			else if ( 0 === strpos( $method , 'set_' ) )
			{
				$column = substr( $method , 4 );
				if ( !static::_checkColumn( $column ) ){ return false; }
				static::_fireEvent( array( 'updating' , 'saving' ) , array( &$column , &$args ) );			     
				$result = static::_getQB( )->table( static::$_storage[ $class ][ 'table' ] )
						->where( static::$_uniqueKey , '=' , $args[ 1 ] )
						->update( array( $column => $args[ 0 ] ) )
						->run( );
				static::_fireEvent( array( 'updated' , 'saved' ) , array( &$column , &$args , &$result ) );
				return $result;
			}
			$qb = static::_getQB( )->table( static::$_storage[ $class ][ 'table' ] );
			$qb->setFetchMode( \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE , $class );
			return call_user_func_array( array( $qb , $method ), $args );
		}
		/**
		* Checks if a given column name exists in table
		* @param	string		$column	the value to check
		* @return	true if column exists, false otherwise
		*/
		protected static function _checkColumn( $column )
		{
			$storage = static::$_storage[ get_called_class( ) ];
			if ( !array_key_exists( $column , $storage[ 'columns' ] ) && 
							!in_array( $column , static::$_map ) )
			{
				trigger_error( 'Column ' . $column . ' does not exists in table  ' . 
								$storage[ 'table' ] . '!' , E_USER_ERROR );
				return false;
			}
			return true;
		}
		/**
		* Fires events if methods are present in observers classes. See @ref using_observers
		* @param	string		$event	the event name stored in the $_observers property
		* @param	array		$data		in array with the data to pass to the listeners
		*/
		protected static function _fireEvent( $event , $data )
		{
			$event = ( is_array( $event ) ) ? $event : array( $event );
			$event_class = static::_namespace( static::$_eventClass, 'PtcEvent' );
			if ( array_key_exists( $class = get_called_class( ) , static::$_observers ) )
			{
				foreach ( static::$_observers[ $class ] as $k => $v )
				{
					foreach ( $event as $ev )
					{
						if ( $v === $ev ){ $event_class::fire( $k , $data ); }
					}
				}
			}
		}
		/**
		* Retrieve the query builder object if present
		*/		
		protected static function _getQB( )
		{
			$manager = static::_namespace( static::$_connectionManager , 'PtcDb' );
			return call_user_func( $manager . '::getQB' , static::$_connectionName );	
		}
		/**
		* Initializes the class, adding columns and table name to the PtcMapper::$_storage property. 
		* See @ref using_boot
		* @return	the name of called class as string
		*/		
		protected static function _initialize( )
		{
			$db = static::_getQB( );
			if ( !array_key_exists( $class = get_called_class( ) , static::$_storage ) )
			{
				static::$_storage[ $class ] = array( );
				if ( static::$_table ){ static::$_storage[ $class ][ 'table' ] = static::$_table; }
				else
				{
					static::$_storage[ $class ][ 'table' ] = strpos( $class , '\\' ) ? 
						@strtolower( end( explode( '\\' , $class ) ) ) : strtolower( $class );
				}
				$db->run( 'SHOW TABLES LIKE ?' , array( static::$_storage[ $class ][ 'table' ] ) );
				if ( !$db->countRows( ) )
				{ 
					trigger_error( 'Table ' . static::$_storage[ $class ][ 'table' ] . 
								' does not exists, quitting now!' , E_USER_ERROR );
					return false;
				}
				static::$_storage[ $class ][ 'columns' ] = static::getColumns( );
				if ( method_exists( $class , 'boot' ) ){ static::boot( ); }
			}
			return $class;
		}
		/**
		* Replaces column names with values in the PtcMapper::$_map property. 
		* See @ref mapping_fields
		*/
		protected function _mapFields( )
		{
			if ( !empty( static::$_map ) )
			{
				foreach ( static::$_map as $k => $v )
				{
					if ( array_key_exists( $v , $this->_fields ) )
					{
						$this->_fields[ $k ] =  $this->_fields[ $v ];
						unset( $this->_fields[ $v ] );
					}
				}
			}
		}
		/**
		* Guards protected fields defined with the PtcMapper::$_guard property
		*/
		protected static function _guard( $data )
		{
			if ( static::$_guard )
			{
				if ( is_object( $data ) )
				{
					foreach ( static::$_guard as $guard ){ $data->remove( $guard ); }
				}
				else if ( is_array( $data ) )
				{
					foreach ( static::$_guard as $guard )
					{
						foreach ( $data as $obj )
						{
							if ( is_object( $obj ) ){ $obj->remove( $guard );  }
						}
					}
				}
			}
			return $data;
		}
		/**
		* Adds namespace to the library components
		*/	
		protected static function _namespace( $className , $string = 'PtcDb' )
		{
			return ( $string === $className ) ? 
				__NAMESPACE__ . '\\' . $className : $className;
		}
	}