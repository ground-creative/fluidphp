<?php

	namespace helpers\UserAgent;
	
	Class UserAgent
	{
		public static function isBot( )
		{
			return ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) && 
							preg_match( '/bot|crawl|slurp|spider/i' , 
								$_SERVER[ 'HTTP_USER_AGENT' ] ) ) ? true : false;
		}
		
		public static function getLang( $full = false , $httpAccept = null , $defaultLang = null ) 
		{
			$httpAccept = ( !$httpAccept ) ? @$_SERVER[ 'HTTP_ACCEPT_LANGUAGE' ] : $httpAccept;
			if ( @strlen( $httpAccept ) > 1 )  
			{
				$x = explode( ',' , $httpAccept );
				foreach ( $x as $val ) 
				{
					if ( preg_match( '/(.*);q=([0-1]{0,1}\.\d{0,4})/i' , $val , $matches ) )
					{
						$lang[ $matches[ 1 ] ] = ( float ) $matches[ 2 ];
					}
					else{ $lang[ $val ] = 1.0; }
					$qval = 0.0;
					foreach ( $lang as $key => $value ) 
					{
						if ( $value > $qval ) 
						{
							$qval = ( float ) $value;
							$defaultLang = $key;
						}
					}
				}
				$defaultLang = ( !$defaultLang ) ? 'en-us' : $defaultLang;
				return ( $full ) ? strtolower( $defaultLang ) :
						strtolower( substr( $defaultLang , 0 , 2 ) );
			}
			return null;
		}
		
		public static function getDetails( )
		{
			return ( isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) ) ? $_SERVER[ 'HTTP_USER_AGENT' ] : null;
		}
	}