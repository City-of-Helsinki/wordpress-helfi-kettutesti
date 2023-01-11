<?php

class HeroResultDisplay
{

	public static function setup(&$context, AbstractSiteModel $siteModel)
	{
		//$context['prop'] = 'Hello world';
		$context["jsonImages"] = json_encode($context["resultimages"]);
		$context["jsonInfo"] = json_encode($context["resultinfo"]);
	}
}
