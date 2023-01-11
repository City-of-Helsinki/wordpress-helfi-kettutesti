<?php
/*
 * buena.fi
*/

class HTTPHeaders{

	// request
	public $method;
	public $location;

	// common
	public $protocol;
	private $headers;	// assoc array

	// response
	public $statusMessage;
	public $code;
	public $body;

	private static $MESSAGES = array(
		// [Informational 1xx]
		100 => 'Continue',
		101 => 'Switching Protocols',

		// [Successful 2xx]
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		// [Redirection 3xx]

		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',

		// [Client Error 4xx]
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',

		// [Server Error 5xx]
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);

	public function __construct( $code = 200, $protocol = null, $statusMessage = null, $method = null ){
		$this->headers = array();
		$this->code = $code;
		$this->method = $method;
		$this->protocol = $protocol;
		$this->statusMessage = $statusMessage;
	}

	public function isValid(){
		// TODO : actually validate?
		return count( $this->headers ) > 0;// && isset($this->method) && isset($this->protocol);
	}

	public function toString( $isResponse ){
		$status = array();

		if( $isResponse ){
			$status[] = $this->getProtocol();
			$status[] = $this->code;
			$status[] = $this->getStatusMessage();
		}else{
			$status[] = $this->method;
			$status[] = $this->location;
			$status[] = $this->getProtocol();
		}

		$result = implode( " ", $status )."\r\n";

		foreach( $this->headers as $key => $value ){
			$result .= $key.": ".$value."\r\n";
		}

		$result .= "\r\n";

		if( $isResponse && isset( $this->body ) ){
			$result .= $this->body;
		}

		return $result;
	}

	private function getProtocol(){
		if( $this->protocol === null ){
			return $_SERVER['SERVER_PROTOCOL'];
		}else{
			return $this->protocol;
		}
	}

	private function getStatusMessage(){
		if( $this->statusMessage === null ){
			return self::$MESSAGES[$this->code];
		}else{
			return $this->statusMessage;
		}
	}

	public function getValue( $key ){
		if( isset( $this->headers[$key] ) ){
			return $this->headers[$key];
		}else{
			return null;
		}
	}

	public function setValue( $key, $value, $override = false ){
		if( strpos( $value, "\r\n" ) === false ){
			if( !$override && isset( $this->headers[$key] ) ){
				throw new Error( 'Header '.$key.' already set' );
			}else{
				$this->headers[$key] = $value;
			}
		}else{
			throw new InvalidArgumentError( "Invalid header value: cannot contain <CRLF>" );
		}
	}

	public function commit(){

		if( headers_sent() ){
			throw new Error( 'Cannot set headers : already sent' );
		}

		// php 5.4.0 =>
		//http_response_code();

		// set status line
		header( $this->getProtocol().' '.$this->code.' '.$this->getStatusMessage(),true,$this->code );

		foreach( $this->headers as $key => $value ){
			header( $key.': '.$value,true );
		}
	}

	public static function createFromString( $header ){
		$headerObj = new HTTPHeaders();
		$retVal = array();
		$fields = explode( "\r\n", preg_replace( '/\x0D\x0A[\x09\x20]+/', ' ', $header ) );

		$match;

		foreach( $fields as $field ){
			if( preg_match( '/([^:]+): (.+)/m', $field, $match ) ) {
				$match[1] = preg_replace_callback(
					'/(?<=^|[\x09\x20\x2D])./',
					function( $matches ){
							return strtoupper( $matches[0] );
					},
					strtolower( trim( $match[1] ) )
				);
					
				if( isset( $retVal[$match[1]] ) ) {
					$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
				} else {
					$retVal[$match[1]] = trim( $match[2] );
				}

				// assume [method] [path] [protocol+version]
			}else if( strlen( $field ) > 0 ){
				$parts = explode( " ",$field );
				// should we throw parse error?
				if( count( $parts ) === 3 ){
					$headerObj->method = $parts[0];
					$headerObj->location = $parts[1];
					$headerObj->protocol = $parts[2];
				}
			}
		}

		$headerObj->headers = $retVal;
		return $headerObj;
	}

}
