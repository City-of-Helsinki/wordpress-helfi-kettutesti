<?php

class GeneratedLayouts {
    public $layoutFiles;

    function __construct() {
        $root = get_template_directory(); 
        $this->layoutFiles = [
			'article' => [
				'className'   => 'Article',
				'requirePath' => $root . '/layouts/layouts/article/Article.php',
			],
			'topnav' => [
				'className'   => 'TopNav',
				'requirePath' => $root . '/layouts/layouts/topnav/TopNav.php',
			],
			'linkcard' => [
				'className'   => 'linkcard',
				'requirePath' => $root . '/layouts/layouts/linkcard/linkcard.php',
			],
			'heromedia' => [
				'className'   => 'HeroMedia',
				'requirePath' => $root . '/layouts/layouts/heromedia/HeroMedia.php',
			],
			'heroresultdisplay' => [
				'className'   => 'HeroResultDisplay',
				'requirePath' => $root . '/layouts/layouts/heroresultdisplay/HeroResultDisplay.php',
			],
			'foxgallary' => [
				'className'   => 'foxgallary',
				'requirePath' => $root . '/layouts/layouts/foxgallary/foxgallary.php',
			],
			'bookdisplay' => [
				'className'   => 'BookDisplay',
				'requirePath' => $root . '/layouts/layouts/bookdisplay/BookDisplay.php',
			],
			'imagedisplay' => [
				'className'   => 'ImageDisplay',
				'requirePath' => $root . '/layouts/layouts/imagedisplay/ImageDisplay.php',
			],
			'heromediaquiz' => [
				'className'   => 'HeroMediaQuiz',
				'requirePath' => $root . '/layouts/layouts/heromediaquiz/HeroMediaQuiz.php',
			],
			'hero' => [
				'className'   => 'Hero',
				'requirePath' => $root . '/layouts/layouts/hero/Hero.php',
			],
			'icon_linklist' => [
				'className'   => 'IconLinklist',
				'requirePath' => $root . '/layouts/layouts/icon_linklist/IconLinklist.php',
			],
			'linklist' => [
				'className'   => 'Linklist',
				'requirePath' => $root . '/layouts/layouts/linklist/Linklist.php',
			],
			'map' => [
				'className'   => 'Map',
				'requirePath' => $root . '/layouts/layouts/map/Map.php',
			],
			'some_navigation' => [
				'className'   => 'Somenav',
				'requirePath' => $root . '/layouts/layouts/some_navigation/Somenav.php',
			],
			'timeline' => [
				'className'   => 'Timeline',
				'requirePath' => $root . '/layouts/layouts/timeline/Timeline.php',
			],
			'timeline_textual' => [
				'className'   => 'TimelineTextual',
				'requirePath' => $root . '/layouts/layouts/timeline_textual/TimelineTextual.php',
			],

        ];
    }
}