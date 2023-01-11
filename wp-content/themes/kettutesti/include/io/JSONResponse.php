<?php
/*
 * http://buena.fi
*/

class JSONResponse{
	
	public $data;
	public $status;
	public $reason;
	public $nonce;
	
	public function __construct( $status = true, $reason = null, $data = null, $nonce = null ){
		$this->data = $data === null ? new stdClass() : $data;
		$this->status = $status;
		$this->reason = $reason;
		$this->nonce = $nonce;
	} 
	/*
	public function respond($success,$data=null){
		$this->status=$success;
		$this->data=$data;
		print($this->toString());
	}
	*/

	public function toString(){
		return json_encode( $this );
	}
	
	
	
}
