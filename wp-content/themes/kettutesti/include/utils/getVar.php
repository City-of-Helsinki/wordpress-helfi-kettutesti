<?php

function getVar( $name ){
	if( isset( $_REQUEST[$name] ) ){
		return $_REQUEST[$name];
	}else if( isset( $_FILES[$name] ) ){
		return $_FILES[$name];
	}else{
		return null;
	}
}
