<?php

class Shortcode {

	public static function setup( &$context, AbstractSiteModel $sitemodel  ){
		$context['prop'] = 'Hello world';
	}
}
