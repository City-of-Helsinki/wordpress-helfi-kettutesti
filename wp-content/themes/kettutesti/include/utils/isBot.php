<?php

function isBot() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match( '/bot|facebookexternalhit|crawl|slurp|spider|mediapartners/i', 
			$_SERVER['HTTP_USER_AGENT']
		);
}

