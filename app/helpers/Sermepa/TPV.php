<?php

	namespace helpers\Sermepa;

	class TPV
	{	
		public static function form( $params = array( ) , $isSandBox = false )
		{
			$form = new Form( $params , $isSandBox );
			return $form->create( );
		}
		
		public static function authorize( $amount , $merchantKey , $inputs )
		{
			$transaction = new Transaction( );
			static::$_lastError = null;
			if ( !$authorize = $transaction->authorize( $amount , $merchantKey , $inputs ) )
			{
				static::$_lastError = $transaction->getError( );
			}
			return $authorize;
		}
		
		public static function getLastError( )
		{
			return static::$_lastError;
		}
		
		protected static $_lastError = null;
		
		protected static $_transactionInstance = null;
	}