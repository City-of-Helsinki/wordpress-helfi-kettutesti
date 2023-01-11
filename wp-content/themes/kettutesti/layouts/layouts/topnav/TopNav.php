<?php

class TopNav
{

	public static function setup(&$context, AbstractSiteModel $siteModel)
	{
		//$context['prop'] = 'Hello world';
		$language_args = [
			'skip_missing' => true,
		];
		$context["wp_languages"] = wpml_get_active_languages_filter($language_args);
	}
}
