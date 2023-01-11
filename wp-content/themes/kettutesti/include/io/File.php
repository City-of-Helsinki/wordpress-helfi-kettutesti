<?php
/*
 * http://buena.fi
*/

//Importer::import('bphp/debug/Logger.php');

class File extends SplFileInfo{


// 	const OPEN_MODE_R='r';// 	Open for reading only; place the file pointer at the beginning of the file.
// 	const OPEN_MODE_R_PLUS='r+';// Open for reading and writing; place the file pointer at the beginning of the file.

// 	const OPEN_MODE_W='w';// Open for writing only; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.
// 	const OPEN_MODE_W_PLUS='w+';// Open for reading and writing; place the file pointer at the beginning of the file and truncate the file to zero length. If the file does not exist, attempt to create it.

// 	const OPEN_MODE_A='a';// Open for writing only; place the file pointer at the end of the file. If the file does not exist, attempt to create it.
// 	const OPEN_MODE_A_PLUS='a+';// Open for reading and writing; place the file pointer at the end of the file. If the file does not exist, attempt to create it.

// 	const OPEN_MODE_X='x';// Create and open for writing only; place the file pointer at the beginning of the file. If the file already exists, the fopen() call will fail by returning FALSE and generating an error of level E_WARNING. If the file does not exist, attempt to create it. This is equivalent to specifying O_EXCL|O_CREAT flags for the underlying open(2) system call.
// 	const OPEN_MODE_X_PLUS='x+';// Create and open for reading and writing; otherwise it has the same behavior as 'x'.

// 	const OPEN_MODE_C='c';// Open the file for writing only. If the file does not exist, it is created. If it exists, it is neither truncated (as opposed to 'w'), nor the call to this function fails (as is the case with 'x'). The file pointer is positioned on the beginning of the file. This may be useful if it's desired to get an advisory lock (see flock()) before attempting to modify the file, as using 'w' could truncate the file before the lock was obtained (if truncation is desired, ftruncate() can be used after the lock is requested).
// 	const OPEN_MODE_C_PLUS='c+';// Open the file for reading and writing; otherwise it has the same behavior as 'c'.


	private $decoder;
	private $encoder;

	private $fileData = null;
	private $filePath;

	private $isDecoderDirty;


	public function __construct( $filePath, IDecoder $decoder = null, IEncoder $encoder = null ){
		parent::__construct( $filePath );
		$this->filePath = $filePath;
		$this->decoder = $decoder;
		$this->encoder = $encoder;
		$this->isDecoderDirty = true;
	}

	public function hasData(){
		return $this->fileData !== null;
	}

	public function touch( $createdir = false ){
		if( $createdir ){
			$this->createPathForfile( $this->filePath );
		}
		touch( $this->filePath );
	}

	// let's support local files only...
	public function load(){
		// should the loading be asyncronous..?
//		Logger::log("Loading ".$this->filePath);

		$this->fileData = @file_get_contents( $this->filePath );

		if( $this->fileData === false ){
			throw new Error( "Cannot load ".$this->filePath );
		}

		$this->isDecoderDirty = true;
		return $this;
	}


	public function copy( $newPath, $createdir = false ){
		clearstatcache();
		if( !$this->exists() ){
			throw new Error( 'Cannot copy "'.$this->getFilePath().'" : does not exist on disk' );
		}
		if( $createdir ){
			$this->createPathForfile( $newPath );
		}
		$result = copy( $this->getRealPath(), $newPath );
		if( $result === false ){
			throw new Error( 'Cannot copy '.$this->getRealPath().' to '.$newPath );
		}
	}


	protected function getEncoded( $content ){
		if( $this->encoder !== null ){
			return $this->encoder->encode( $content );
		}else{
			return $content;
		}
	}

	private function createPathForfile( $filePath, $createDirMode = 0775 ){
		$path = dirname( $filePath );
		if( !is_dir( $path ) ){
			Logger::log( "Making path ".$path );
			if( mkdir( $path,$createDirMode,true ) === false ){
				throw new Error( "Cannot create path ".$path );
			}
		}
	}

	public function move( $target, $createDir = false, $createDirMode = 0775 ){
		if( $createDir ){
			$this->createPathForfile( $target,$createDirMode );
		}

//		Logger::log("Moving ".$this->getRealPath().' to '.$target,Logger::$INFO);

		if( rename( $this->getRealPath(),$target ) === false ){
			throw new Error( 'Cannot move '.$this->getFilePath().' to '.$target );
		}

		$this->filePath = $target;

		return new File( $target,$this->decoder,$this->encoder );
	}

	
	public function append( $content ){
		$success = file_put_contents(
			$this->filePath,
			$this->getEncoded( $content ),
			FILE_APPEND
		);
		
		if( $success === false ){
			throw new Error( "Cannot save ".$this->filePath );
		}
		return true;
	}
	
	public function save( $content, $createDir = false, $createDirMode = 0775 ){

		if( $createDir ){
			$this->createPathForfile( $this->getFilePath(),$createDirMode );
		}

//		Logger::log("Saving ".$this->filePath,Logger::$INFO);

		$encoded = $this->getEncoded( $content );

		$success = file_put_contents(
			$this->filePath,
			$encoded,
			LOCK_EX
		);

		if( $success === false ){
			throw new Error( "Cannot save ".$this->filePath );
		}

		$this->setFileData( $encoded );

	}

	public function delete(){
//		Logger::log('Deleting "'.$this->getRealPath().'"',Logger::$INFO);
		unlink( $this->getRealPath() );
	}

	public function rename( $newPath ){
		rename( $this->getRealPath(),$newPath );
	}

	// TODO : implement using splinfo instead
	public function getSize(){
		return filesize( $this->getFilePath() );
	}

	public function exists(){
		return $this->isFile();
	}

	// TODO : implement using splinfo instead
	public function getDir(){
		if( $this->exists() ){
			return dirname( $this->getFilePath() )."/";
		}else{
			throw new Error( "File ".$this->getFilePath()." not found" );
		}
	}

	private function setFileData( $data ){
		$this->isDecoderDirty = true;
		$this->fileData = $data;
	}

	public function getContent( $decode = true ){
		if( $this->decoder !== null && $decode ){
			if( $this->isDecoderDirty ){
				$this->isDecoderDirty = false;
				return $this->decoder->decode( $this->fileData );
			}else{
				return $this->decoder->getContent();
			}
		}else{
			return $this->fileData;
		}
	}

	public function getFilePath(){
		return $this->filePath;
	}

	public static function createFromUpload( $paramName ){
		if( !isset( $_FILES[$paramName] ) ){
			throw new Error( $paramName." not set" );
		}

		$params = $_FILES[$paramName];

		switch( $params["error"] ){

			case UPLOAD_ERR_INI_SIZE :
				throw new Error( "Upload file size exceeded" );
				break;

			case UPLOAD_ERR_FORM_SIZE :
				throw new Error( "HTML upload file size exceeded" );
				break;

			case UPLOAD_ERR_PARTIAL :
				throw new Error( "File not fully received" );
				break;

			case UPLOAD_ERR_NO_TMP_DIR :
				throw new Error( "Missing temporary directory" );
				break;

			case UPLOAD_ERR_CANT_WRITE :
				throw new Error( "Cannot write to disk" );
				break;

			case UPLOAD_ERR_EXTENSION :
				throw new Error( "Extension stopped file upload" );
				break;

			case UPLOAD_ERR_NO_FILE:
				// no file, do nothing
				break;

			default:
				// TODO : deal with errors some other way...
				// TODO : check image type better: mimetype cannot be trusted

		}
		$file = new File( $params['tmp_name'] );
		if( !$file->exists() ){
			throw new Error( "Temporary file missing" );
		}
		return $file;
	}

	public function discard(){
		$this->fileData = null;
		$this->decoder = null;
		$this->encoder = null;
	}

}
