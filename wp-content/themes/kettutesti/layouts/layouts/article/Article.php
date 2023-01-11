<?php

class Article {

	public static function setup( &$context, AbstractSiteModel $sitemodel  ){
		$context['prop'] = 'Hello world';
	}
}
