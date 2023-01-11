<?php

class URL {
	public $scheme = '';
	public $host = '';
	public $port = '';
	public $path = [ ];
	public $query = '';
	public $fragment = '';
	
	function __construct() {
	}
	
	public static function createFromRequest() {
		$pageURL = 'http';
		// key 'HTTPS' must take precedence over 'REQUEST_SCHEME' because wpengine headers are conflicting 
		if ( isset( $_SERVER ["HTTPS"] ) && $_SERVER ["HTTPS"] == "on" ) {
			$pageURL .= "s";
		}else{
			if( isset( $_SERVER['REQUEST_SCHEME'] ) ){
				$pageURL = $_SERVER['REQUEST_SCHEME'];
			}
		}
		$pageURL .= "://";
		if ( $_SERVER ["SERVER_PORT"] != "80" ) {
			$pageURL .= $_SERVER ["SERVER_NAME"] . ":" . $_SERVER ["SERVER_PORT"] . $_SERVER ["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER ["SERVER_NAME"] . $_SERVER ["REQUEST_URI"];
		}
		return URL::createFromURL( $pageURL );
	}
	
	public function normalize( $toURL, $verifyPathMatch = false ){
		$base = URL::createFromURL( $toURL );
		$basePathCount = count( $base->path );
		if( $basePathCount <= 0 ){
			return;
		}
		if( $basePathCount > count( $this->path ) ){
			throw new Exception( 'Cannot normalize URL: path is too short' );
		}
		
		if( $verifyPathMatch ){
			for( $n = 0; $n < $basePathCount; $n++ ){
				if( $base->path[$n] !== $this->path[$n] ){
					throw new Exception( 'Base path does not match' );
				}
			}
		}
		
		array_splice( $this->path,0,count( $base->path ) );
	}
	
	public static function createFromURL( string $urlStr ) {
		$components = parse_url( $urlStr );
		$url = new URL();
		$url->scheme = $components ['scheme'] ?? '';
		$url->host = $components ['host'] ?? '';
		$url->port = $components ['port'] ?? '';
		$url->path = isset( $components ['path'] ) ? preg_split( '/\\//', $components ['path'], 10, PREG_SPLIT_NO_EMPTY ) : [ ];
		$url->query = $components ['query'] ?? '';
		$url->fragment = $components ['fragment'] ?? '';
		return $url;
	}

	public function getPathString(){
		return implode( $this->path,DIRECTORY_SEPARATOR );
	}
	
	public function toString() {
		$url = $this->scheme !== '' ? $this->scheme . '://' : '';
		$url .= $this->host !== '' ? $this->host . '/' : '';
		$url .= $this->port !== '' ? $this->port . ':' : '';
		$url .= implode( '/', $this->path );
		$url .= $this->query !== '' ? '?' . $this->query : '';
		$url .= $this->fragment !== '' ? '#' . $this->fragment : '';
		
		return $url;
	}
	
}
