<?php
/*
 * buena.fi
 */

class Session{

	private $salt = null;
	private $siteURL;
	
	// never use in production!
	public static $DISABLE_SESSION_VALIDATION = false;


	// do NOT regerate on every request: there are very, very, likely race conditions! especially when using ajax
	public function __construct( $salt, $regenerateId = false, $resetTimeout = false, $siteURL = null ){
		$this->siteURL = $siteURL;

		if( $siteURL !== null ){
			// generate id to identify multiple sessions on the same server, but in different paths
			session_name( md5( $siteURL ) );
		}

		if( session_start() === false ){
			throw new Exception( 'Cannot get session' );
		}

		$this->salt = $salt;

		if( $this->isValid() ){
			if( $regenerateId ){
				session_regenerate_id( true );
			}
			if( $resetTimeout === true ){
				$this->resetTimeout();
			}
		}
	}

	public function shouldTimeout(){
		if( isset( $_SESSION["created"] ) ){
			$sessionTTL = time() - $_SESSION["created"];
			if( $_SESSION["timeout"] > 0 ){
				return $sessionTTL > $_SESSION["timeout"];
			}else{
				return false;
			}
		}else{
			return true;
		}
	}

	public function create( $timeout = null ){
		$this->clear();
		Logger::log( 'Session : created' );
		// no need for check success: session might not be initialized yet!
		if( session_regenerate_id( true ) === false ){
			Logger::log( 'Cannot regenerate session id' );
		}
		$_SESSION["fingerprint"] = $this->getFingerprint();

		if( $timeout !== null ){
			$_SESSION["timeout"] = intval( $timeout );
		}else{
			$_SESSION["timeout"] = 0;
		}
		//$_SESSION['paths']=$_SERVER['HTTP_USER_AGENT'].$this->salt.$_SERVER['REMOTE_ADDR'].getBaseURL();
		$this->resetTimeout();
	}

	public function isActive(){
		return isset( $_SESSION["fingerprint"] ) && (session_id() !== '');
	}

	public function resetTimeout(){
		$_SESSION["created"] = time();
	}

	public function isValid(){
		if( Session::$DISABLE_SESSION_VALIDATION ){
			return true;
		}
		return $this->isActive() && $_SESSION["fingerprint"] === $this->getFingerprint() && !$this->shouldTimeout();
	}

	protected function getFingerprint(){
		// hashed because session data is written to disk...
		// FIXME : change to something stronger...just in case
		return sha1( $_SERVER['HTTP_USER_AGENT'].$this->salt.$_SERVER['REMOTE_ADDR'].$this->siteURL );
	}

	public function getValue( $key ){
		return isset( $_SESSION[$key] ) ? $_SESSION[$key] : null;
	}

	public function setValue( $key, $value ){
		return $_SESSION[$key] = $value;
	}

	public function getSessionHash(){
		return md5( $this->getFingerprint().session_id() );
	}

	public function clear(){
		//Logger::log("Session : destroyed");
		session_unset();
		if( $this->isActive() ){
			return session_destroy();
		}
	}

	public function dump(){
		$result = PHP_EOL;
		$result .= 'session fingerprint   : '.(isset( $_SESSION["fingerprint"] ) ? $_SESSION["fingerprint"] : '[ not set ]').PHP_EOL;
		$result .= 'client fingerprint    : '.$this->getFingerprint().PHP_EOL;
		$result .= 'session id            : '.session_id().PHP_EOL;
		$result .= 'should timeout        : '.($this->shouldTimeout() ? 'yes' : 'no').PHP_EOL;
		if( isset( $_SESSION ) ){
//			$result.='current stored paths  : '.$_SERVER['HTTP_USER_AGENT'].$this->salt.$_SERVER['REMOTE_ADDR'].getBaseURL().PHP_EOL;
//			$result.='session stored paths  : '.$_SESSION['paths'].PHP_EOL;
			if( isset( $_SESSION["timeout"] ) ){
				$result .= 'timeout               : '.$_SESSION["timeout"].PHP_EOL;
				$result .= 'created               : '.$_SESSION["created"].PHP_EOL;
				$result .= '....delta             : '.(time() - $_SESSION["created"]).PHP_EOL;
			}
		}else{
			$result .= 'no $_SESSION variable set!'.PHP_EOL;
		}

		$result .= PHP_EOL.'Session var dump:'.PHP_EOL;
		$result .= print_r( $_SESSION,true );

		return $result;
	}

}