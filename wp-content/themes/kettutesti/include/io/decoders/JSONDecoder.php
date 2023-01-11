<?php
/*
 * http://buena.fi
*/

class JSONDecoder implements IDecoder{

	protected $content;
	private $assocArray;
	private $opts;

	public function __construct( $assocArray = false, $opts = 0 ){
		$this->assocArray = $assocArray;
		$this->opts = $opts;
	}

	public function decode( $data ){
		if( empty( $data ) ){
			throw new Error( "Data cannot be empty" );
		}
		
		if( $this->opts ){
			$result = json_decode( $data,$this->assocArray,512,$this->opts );
		}else{
			$result = json_decode( $data,$this->assocArray,512 );
		}

		if( function_exists( "json_last_error" ) ){ // legacy support :/
			$error = json_last_error();

			if( $error === JSON_ERROR_STATE_MISMATCH ){
				throw new Error( "Invalid or malformed JSON" );
			}elseif( $error === JSON_ERROR_DEPTH ){
				throw new Error( "Maximum stack depth exceeded" );
			}elseif( $error === JSON_ERROR_CTRL_CHAR ){
				throw new Error( "Control character error, possibly incorrectly encoded" );
			}elseif( $error === JSON_ERROR_SYNTAX ){
				throw new Error( "Syntax error, malformed JSON" );
			}elseif( defined( 'JSON_ERROR_UTF8' ) && $error === JSON_ERROR_UTF8 ){ // php 5.3.3 only!
				throw new Error( "Malformed UTF-8 characters, possibly incorrectly encoded" );
			}
		}

		$this->content = $result;
		return $result;
	}

	public function getContent(){
		return $this->content;
	}


}

?>
