<?php

class HeroMediaQuiz
{

	public static function setup(&$context, AbstractSiteModel $siteModel)
	{
		//$context['prop'] = 'Hello world';
		$context["jsonData"] = json_encode($context["quizs"]);
		$context["resultpagelink"] = json_encode($context["resultpageurl"]);
		$context["quiztag"] = json_encode($context["quiztag"]);
	}
}
