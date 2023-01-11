<?php

// Importer::import('bphp/debug/Logger.php');
// Importer::import('bphp/utils/url/getVar.php',false);
// Importer::import("bphp/io/decoders/JSONDecoder.php");
// Importer::import('bphp/io/JSONPResponse.php');
// Importer::import('bphp/io/JSONResponse.php');
// Importer::import('bphp/net/HTTPHeaders.php');

/*
 * 		100% JSON API for XHR calls.
 *
 * 		Important : Use API.js (not AjaxAPI.js) client side!
 *
 */


class APIResponder{
	
	private $argName;
	private $args;
	
	public function __construct( $argName = 'args' ){
		$this->checkCors();
		$this->argName = $argName;
	}
	
	private function checkCors(){
		
		// Allow from any origin
		if ( isset( $_SERVER['HTTP_ORIGIN'] ) ) {
			// Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
			// you want to allow, and if so:
			header( "Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}" );
			header( 'Access-Control-Allow-Credentials: true' );
			header( 'Access-Control-Max-Age: 86400' );    // cache for 1 day
		}
		
		// Access-Control headers are received during OPTIONS requests
		if ( $_SERVER['REQUEST_METHOD'] == 'OPTIONS' ) {
			
			if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ) )
				header( "Access-Control-Allow-Methods: GET, POST, OPTIONS" );
				
				if ( isset( $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ) )
					header( "Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}" );
					
					// TODO : should not exit so bluntly
					die();
		}
	}
	
	
	public function parseRequest( $jsonStr = null ){
		if( $jsonStr === null ){
			$jsonStr = getVar( $this->argName );
		}
			
		if( $jsonStr === null ){
			//throw new Exception('Missing required parameter '.$argName);
			$this->respond( false,'Missing variable '.$this->argName );
			return false;
		}

		// FIXME : something is escaping our data...?
		$jsonStr = stripcslashes( html_entity_decode( $jsonStr ) );
		
		$decoder = new JSONDecoder();
		
		try{
			$this->args = $decoder->decode( $jsonStr );
		}catch( Error $e ){
			ThemeLogger::log( 'Input json:',ThemeLogger::$INFO );
			ThemeLogger::dump( $jsonStr );
			$this->respond( false,'Malformed JSON' );
		}
		return true;
	}
	
	protected function requireArgument( $name ){
		$arg = getVar( $name );
		if( $arg == null ){
			$this->respond( false,'Missing argument "'.$name.'"' );
		}
		return $arg;
	}
	
	protected function requireAPIArgument( $name ){
		if( !isset( $this->args->{$name} ) ){
			$this->respond( false,'Missing API argument "'.$name.'"' );
		}
		return $this->args->{$name};
	}
	
	protected function getAPIArgument( $name ){
		if( !isset( $this->args->{$name} ) ){
			return null;
		}
		return $this->args->{$name};
	}
	/*
	 protected function requireArgument($name){
	 if(!isset($this->args->{$name})){
	 Logger::dump($this->args);
	 $this->respond(false,'Missing variable '.$name);
	 die();
	 }
	 return $this->args->{$name};
	 }
	 */
	protected function respond( $success, $reason = null, $data = null ){
		
		// create new nonce on successfull api call
		if( $success ){
			$nonce = mt_rand();
			$_SESSION['apiNonce'] = $nonce;
			ThemeLogger::log( "Nonce set ".$nonce );
		}else{
			$nonce = null;
		}
		
		ThemeLogger::log( 'Sending API response ('.($success ? 'ok) ' : 'error) ').$reason );
		$callback = getVar( 'callback' );
		
		$headers = new HTTPHeaders( 200 );
		$headers->setValue( 'Content-type', 'application/json; charset=utf-8' );
		$headers->commit();
		
		if( $callback !== null ){
			$response = new JSONPResponse( $callback,$success,$reason,$data,$nonce );
			//Logger::dump($response->toString());
			print($response->toString());
		}else{
			$response = new JSONResponse( $success,$reason,$data,$nonce );
			print($response->toString());
		}
		die();
	}
	
}
