<?php

function getFilesRecursive( $path, $includePattern = null, $includeDirectories = false) {
	if ( ! is_dir( $path ) ) {
		throw new Exception( $path . " is not a directory" );
	}
	
	$files = array ();
	
	foreach ( new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path ) ) as $splFile ) {
		
		if ( $includeDirectories || $splFile->isFile() ) {
			if ( $includePattern !== null ) {
				$match = preg_match( $includePattern, $splFile->getBaseName() );
				if ( $match === false )
					throw new Exception( "invalid regexp " . $includePattern );
				if ( $match ) {
					array_push( $files, $splFile->getRealPath() );
				}
			} else {
				array_push( $files, $splFile->getRealPath() );
			}
		}
	}
	return $files;
}


