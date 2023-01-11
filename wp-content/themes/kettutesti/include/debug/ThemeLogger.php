<?php

/*
 * http://buena.fi
 *
 * NOTE-TO-SELF: you are doing custom log writing mainly because
 * there is no way to have line breaks in apache error log
*/


interface ILogWriter{
	function write( $message, int $type, bool $printEOL = true );
}


interface ILogFormatter{
	function format( &$lines, int $type, int $entryIndex );
}


class LogTypes{

	// TODO : use buildin E_XXX instead
	public static $NONE = 0;
	public static $ERROR = 2;
	public static $WARNING = 4;
	public static $INFO = 8;
	public static $FATAL = 16;
	public static $DEBUG = 32;
	public static $ALL = 62;

	public static function getLogTypesHumanReadable(){
		$result = [];
		if( self::$logTypes & self::$ERROR ) $result[] = self::getTypeString( self::$ERROR );
		if( self::$logTypes & self::$WARNING ) $result[] = self::getTypeString( self::$WARNING );
		if( self::$logTypes & self::$INFO ) $result[] = self::getTypeString( self::$INFO );
		if( self::$logTypes & self::$FATAL ) $result[] = self::getTypeString( self::$FATAL );
		if( self::$logTypes & self::$DEBUG ) $result[] = self::getTypeString( self::$DEBUG );
		return $result;
	}

	public static function getTypeString( int $type ){
		switch( $type ){
			case self::$ERROR:
				return "ERROR";
			case self::$WARNING:
				return "WARNING";
			case self::$INFO:
				return "INFO";
			case self::$DEBUG:
				return "DEBUG";
			case self::$FATAL:
				return "FATAL";
			default:
				return "OTHER";
		}
	}

	public static function getHumanReadableType( int $type ){
		return str_pad( self::getTypeString( $type ), 8, " ", STR_PAD_RIGHT );
	}
}



/////////////////////////////////

class PaddingFormatter implements ILogFormatter{

	private $count;
	private $prefixMaxLength;

	public function __construct( $prefixMaxLength = 45 ){
		$this->prefixMaxLength = $prefixMaxLength;
		$this->count = 0;
	}

	private function getNextPadding() {
		$this->count = ( $this->count + 1 ) % $this->prefixMaxLength;
		$pad = str_pad( str_repeat( '|', $this->count ), $this->prefixMaxLength, '-' );
		return $pad;
	}

	public function format( &$lines, int $type, int $entryIndex ){
		if( $entryIndex === 0 ){
			array_unshift( $lines, str_repeat( '#', $this->prefixMaxLength ) );
		}
		for( $n = 0; $n < count( $lines ); $n++ ){
			$lines[$n] = $this->getNextPadding().$lines[$n];
		}
	}
}	


class TimestampFormatter implements ILogFormatter{

	public $timestampFormat = null;

	public function __construct( $timestampFormat = 'H:i:s.v' ){
		$this->timestampFormat = $timestampFormat;
	}

	public function format( &$lines, int $type, int $entryIndex ){
		$prefix = "";
		$date = new DateTime();
		$prefix = $date->format( $this->timestampFormat )." : ";
		for( $n = 0; $n < count( $lines ); $n++ ){
			$lines[$n] = $prefix.$lines[$n];
		}
	}

}


class ColorFormatter implements ILogFormatter{

	private const ANSI_RESET   = "\033[0m";
	private const ANSI_BLACK   = "\033[30m";
	private const ANSI_RED     = "\033[31m";
	private const ANSI_GREEN   = "\033[32m";
	private const ANSI_YELLOW  = "\033[33m";
	private const ANSI_BLUE    = "\033[34m";
	private const ANSI_MAGENTA = "\033[35m";
	private const ANSI_CYAN    = "\033[36m";
	private const ANSI_WHITE   = "\033[37m";
	private const ANSI_BOLDBLACK   = "\033[1m\033[30m";
	private const ANSI_BOLDRED     = "\033[1m\033[31m";
	private const ANSI_BOLDGREEN   = "\033[1m\033[32m";
	private const ANSI_BOLDYELLOW  = "\033[1m\033[33m";
	private const ANSI_BOLDBLUE    = "\033[1m\033[34m";
	private const ANSI_BOLDMAGENTA = "\033[1m\033[35m";
	private const ANSI_BOLDCYAN    = "\033[1m\033[36m";
	private const ANSI_BOLDWHITE   = "\033[1m\033[37m";

	public function __construct(){
	}

	public function format( &$lines, int $type, int $entryIndex ){
		for( $n = 0; $n < count( $lines ); $n++ ){
			$lines[$n] = $lines[$n];

			switch( $type ){
				case LogTypes::$ERROR:
					$lines[$n] = self::ANSI_RED . $lines[$n] . self::ANSI_RESET;
					break;

				case LogTypes::$WARNING:
					$lines[$n] = self::ANSI_YELLOW . $lines[$n] . self::ANSI_RESET;
					break;

				case LogTypes::$INFO:
					$lines[$n] = self::ANSI_BLUE . $lines[$n] . self::ANSI_RESET;
					break;

				case LogTypes::$FATAL:
					$lines[$n] = self::ANSI_BOLDRED . $lines[$n] . self::ANSI_RESET;
					break;

				default:
					break;
			}
		}
	}
}


//////////////////////////////////////

class LogFileWriter implements ILogWriter{

	private $file;

	public function __construct( $fileName, $rotateSizeMB, $rotateFileCount = 1, $trunkate = false ){
		$this->file = new File( $fileName );

		if( $trunkate && $this->file->isFile() ){
			throw new Error( "trunkate not implemented" );
		}

		if( $rotateSizeMB > 0 && $rotateFileCount > 0 && $this->file->isFile() && $this->file->getSize() > $rotateSizeMB * 1024 * 1024 ){
//			print_pre($this->file->getSize().' bytes. Rotating now ('.$rotateSize.' bytes, '.$rotateFileCount.' files total)');

			// do the rotate
			$count = $rotateFileCount - 1;
			while( $count >= 0 ){
				$source = new File( $fileName.'.'.$count );
				if( $source->isFile() ){
					if( $count === $rotateFileCount ){
						// rotate full, remove last
//						print_pre('remove '.$source->getRealPath());
						$source->delete();
					}else{
						// move "as next"
//						print_pre('move '.$source->getRealPath().' to '.$fileName.'.'.($count+1));
						$target = $source->move( $fileName.'.'.($count + 1) );
					}
				}
				$count--;
			}

			// move current as .0
			$this->file = $this->file->move( $fileName.'.0' );
//			print_pre('move (current) '.$this->file->getRealPath());
		}
	}

	public function write( $message, int $type, bool $printEOL = true ){
		$this->file->append( $message.($printEOL ? PHP_EOL : '') );
	}


}




class WPDebugLogWriter extends LogFileWriter{

	public function __construct(){
		parent::__construct( ABSPATH . 'wp-content/debug.log', 1, 3 );
	}


}



class HTMLWriter implements ILogWriter{

	public function write( $message, int $type, bool $printEOL = true ){
		print( '<pre>' . $message . '</pre>');
	}

}









//////////////////////////////////








class ThemeLogger {
	public static $logTypes = 62;

	// ignore identical entries
	public static $trunkate = false;

	private static $lastMessage;
	private static $lastMessageType;
	private static $lastMessageRepeatCount;

	private static $writers = [];
	private static $formatters = [];

	private static $count = 0;

	private static $enableLogging = true;

	private static $startTime;

	final public static function setEnableLogging( bool $value ){
		ini_set('display_errors', $value );
		ini_set('display_startup_errors', $value );
		error_reporting( E_ALL );
		if( !defined( 'WP_DEBUG' ) ){
			define('WP_DEBUG', $value);
		}
		self::$enableLogging = $value;
	}

	final public static function startTimer(){
		self::$startTime = round(microtime(true) * 1000);
		self::log('Timer start @ ' . self::$startTime);
		self::getStackTrace( 5, true );
	}

	final public static function markTime( $tag ){
		$now = round(microtime(true) * 1000);
		self::log('Timer "' . $tag . '" mark @ ' . $now .', delta ' . ( $now - self::$startTime ));
	}
	
	private static function getStackTrace( $limit = 10, bool $dump = false ){
		$frames = debug_backtrace( 0, $limit );
		$result = [];
		$c = count( $frames ) - 1;
		for( $n = 0; $n < $c; $n++ ){
			$frame = $frames[$n];
			if( isset( $frame['file'] ) && !$dump ){
				$result[] = ($c - $n + 1) . ': ' . basename( $frame['file'] ).':'.$frame['line'] . '::' .  $frame['function'] . '()';
			}else{
				$result[] = ($c - $n + 1) . ': ' . print_r( $frame,true );
			}
		}
		return $result;
	}

	public static function logWarnings(){
		set_error_handler( function( int $errno, string $errstr ){
			printf( '><strong>%s</strong>',$errstr );
			ThemeLogger::log( $errstr, LogTypes::$WARNING, 5 );
		} );
	}

	public static function inspect( $obj, $level = 8 ){
		self::log( '************  INSPECT START ************', $level );
		self::log( "Content:", $level );
		$type = gettype( $obj );
		if( $type === 'boolean' ){
			self::log( $obj ? 'true' : 'false', $level );
		}else{
			self::log( $obj, $level );
		}
		$class;
		self::log( '    Type  : ' . $type, $level );
		try{
			$class = @get_class( $obj );
		}catch( Exception $e ){
			$class = "N/A";
		}
		self::log( '    Class : ' . ($class ? $class : 'N/A') );
		self::log( '************  INSPECT END ************', $level );
		
	}

	public static function log( $msg, $type = 8, $stackFrames = 0 ) {
		if ( !ThemeLogger::$enableLogging && $type != LogTypes::$FATAL ) {
			return;
		}
		
		if( !($type & ThemeLogger::$logTypes) ){
			return;
		}
		
		ThemeLogger::logWrite( $msg, $type, $stackFrames );
		if( $type === LogTypes::$FATAL ){
			throw new Error( $msg,true );
		}
	}

	private static function logWrite( $message, $type = 8, $stackFrames = 0 ){
		if( ThemeLogger::$trunkate ){

			if( ThemeLogger::$lastMessage == $message ){
				ThemeLogger::$lastMessageRepeatCount++;
				return;
			}else{

				if( ThemeLogger::$lastMessageRepeatCount > 1 ){
					ThemeLogger::write( "(Last message repeated " + ThemeLogger::$lastMessageRepeatCount + " times)",ThemeLogger::$lastMessageType );
					ThemeLogger::$lastMessage = null;
					ThemeLogger::$lastMessageRepeatCount = 0;
				}
				ThemeLogger::write( $message, $type, $stackFrames );
			}

			ThemeLogger::$lastMessage = $message;
			ThemeLogger::$lastMessageType = $type;


		}else{
			ThemeLogger::write( $message, $type, $stackFrames  );
		}
	}

	private static function write( $message, $type, $stackFrames = 0 ){
		$lines = explode( "\n", print_r( $message,true ) );
		

		if( $stackFrames > 0 ){
			$lines = array_merge( $lines, ThemeLogger::getStackTrace( $stackFrames, false ) );
		}
		
		$c = count( $lines );
		for( $n = 0; $n < $c; $n++ ){
			$lines[$n] = LogTypes::getHumanReadableType( $type ).' : '.$lines[$n];
		}

		$c = count( ThemeLogger::$formatters );
		for( $n = 0; $n < $c; $n++ ){
			ThemeLogger::$formatters[$n]->format( $lines, $type, self::$count );
		}
		
		$message = implode( "\n", $lines );

		$c = count( ThemeLogger::$writers );
		if( $c > 0 ){
			for( $n = 0; $n < $c; $n++ ){
				ThemeLogger::$writers[$n]->write( $message,$type );
			}
		}else{
			// no writers attached!
			error_log( '[No writers] '.(string)$message );
		}
		self::$count++;
	}

	public static function addWriter( ILogWriter $writer ){
		ThemeLogger::$writers[] = $writer;
	}

	public static function addFormatter( ILogFormatter $formatter ){
		ThemeLogger::$formatters[] = $formatter;
	}

	public static function throwException( $errno, $errstr, $errfile, $errline, $errcontext ){
		throw new ErrorException( $errstr, $errno, 0, $errfile, $errline );
	}

	public static function logException( $exception ){
		ThemeLogger::log( ThemeLogger::getTrace( $exception ).PHP_EOL, LogTypes::$FATAL );
		return false;
	}

	public static function getTrace( $exception ){
		$stack = $exception->getTrace();

		$c = count( $stack );
		$out = '';


		$out .= basename( $exception->getFile() ).' ('.$exception->getLine().') : '.$exception->getMessage().PHP_EOL;

		for( $n = 0; $n < $c; $n++ ) {

			$out .= ($n).') ';
			$frame = $stack[$n];

			if( isset( $frame['file'] ) ){
				$out .= basename( $frame['file'] ).' ';
			}else{
				$out .= '? ';
			}

			if( isset( $frame['line'] ) ){
				$out .= '('.$frame['line'].') : ';
			}else{
				$out .= ' ? : ';
			}

			if( isset( $frame['class'] ) ){
				if( strlen( $frame['class'] ) > 0 ){
					$out .= $frame['class'];
					$out .= $frame['type'];
				}else{
					$out .= " ? ";
				}
			}

			if( isset( $frame['function'] ) ){
				if( strlen( $frame['function'] ) > 0 ){
					$out .= $frame['function'];
				}else{
					$out .= " ? ";
				}
			}

			$out .= '()'.PHP_EOL;
		}

		return $out;
	}

}
