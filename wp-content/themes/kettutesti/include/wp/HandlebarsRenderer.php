<?php

use LightnCandy\LightnCandy;

/**
 * Used for rendering all html output.
 */
class HandlebarsRenderer implements IRenderer
{
	private $partialPath;
	private $partials;

	private $translations;
	private $textDomain;
	private $templatePath;
	private $layoutPath;
	public static $uid = 0;

	/**
	 * Constructor.
	 *
	 * @param string $partialPath Path to partials.
	 * @param string $useTextDomain WPML text domain key.
	 */
	function __construct($templatePath, $layoutPath, $useTextDomain)
	{
		$this->textDomain = $useTextDomain;
		$this->translations = [];
		if (!is_dir($templatePath)) {
			throw new RuntimeException('Cannot find template path ' . getcwd() . '/' . $templatePath);
		}
		$this->templatePath = $templatePath;
		$this->layoutPath = $layoutPath;
	}

	// update for production

	/**
	 * Collects all partials.
	 *
	 * Note : Template partial files must have unique basenames.
	 *
	 * @return void
	 */
	private function preloadPartials()
	{
		if (!empty($this->partials)) {
			return;
		}

		// preload all partials
		$files = getFilesRecursive($this->templatePath, '/\.hbs$/');
		$files = array_merge($files, getFilesRecursive($this->layoutPath, '/\.hbs$/'));
		$this->partials = [];
		$c = count($files);
		for ($n = 0; $n < $c; $n++) {
			$file = $files[$n];
			$this->partials[basename($file)] = file_get_contents($file);
			//l( 'Registered partial ' . basename( $file ) );
		}
	}

	final public function getLayoutPath()
	{
		return $layoutPath;
	}

	/**
	 * Output html from $model and Handlebars $template file
	 *
	 * @param PageModel $model
	 * @param string $templateName
	 * @return void
	 */

	final public function render(AbstractSiteModel &$model, $templateName = 'main.hbs', bool $return = false)
	{
		$this->preloadPartials();

		$templatePath = $this->templatePath . '/' . $templateName;
		if (!file_exists($templatePath)) {
			throw new RuntimeException("Template file not found : " . $templatePath);
		}
		$template = file_get_contents($templatePath);
		ThemeLogger::log('Rendering HB template ' . $templatePath, LogTypes::$INFO);

		// need local copy for passing to anonymous function via "use" keyword
		$partials = $this->partials;

		$phpStr = LightnCandy::compile($template, [
			'flags'    => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_INSTANCE | LightnCandy::FLAG_NOESCAPE | LightnCandy::FLAG_RUNTIMEPARTIAL,
			'partials' => $partials,
			'helpers'  => [
				'equals'                  => function ($a, $b, $options) {
					return $a == $b ? $options['fn']() : $options['inverse']();
				},
				'if_gt'                   => function ($a, $b, $options) {
					return $a > $b ? $options['fn']() : $options['inverse']();
				},
				'if_lt'                   => function ($a, $b, $options) {
					return $a < $b ? $options['fn']() : $options['inverse']();
				},
				'if_odd'
				=> function ($index, $options) {
					if (($index % 2) != 0) {
						return $options['inverse']();
					} else {
						return $options['fn']();
					}
				},
				'contains'                => function ($needle, $haystack, $options) {
					if (is_array($haystack)) {
						return in_array($needle, $haystack) ? $options['fn']() : $options['inverse']();
					} else {
						return strpos($haystack, $needle) === false ? $options['inverse']() : $options['fn']();
					}
				},
				'dump'                    => function ($data) {
					ThemeLogger::log($data, LogTypes::$DEBUG);

					return '<pre style="color:#000;background-color:#fff">' . htmlspecialchars(print_r($data, true)) . '</pre>';
				},
				'get_partial_base'        => function ($name) {
					l($name);

					return basename($name, '.hbs');
				},
				'get_partial'             => function ($name) use (&$partials) {
					$fname = basename($name, '.hbs') . '.hbs';
					if (!array_key_exists($fname, $partials)) {
						throw new Error('Missing (not loaded) partial ' . $fname);
					}
					ThemeLogger::log(' > including ' . $fname, LogTypes::$INFO);

					return $fname;
				},
				'get_svg'                 => function ($name) {
					$fname = basename($name, '.svg') . '.svg';

					return $fname;
				},
				'do_shortcode'            => function (...$args) {
					$code = array_shift($args);

					return do_shortcode(vsprintf($code, $args));
				},
				'do_action'               => function ($action) {
					ob_start();
					do_action($action);

					return ob_get_clean();
				},
				'get_the_content'         => function ($action) {
					return get_the_content();
				},
				'get_imageurl_by_post_id' => function ($pid) {
					return get_the_post_thumbnail_url($pid, 'medium');
				},
				'get_gravityform'         => function ($id) {
					return do_shortcode('[gravityform id="' . $id . '" ajax="true"]');
				},
				'wp_content_filter'       => function ($data) {
					return apply_filters('the_content', $data);
				},
				'call'                    => function ($method, ...$args) {
					$cx = &$args[count($args) - 1];

					// get from root context, not _this
					$model = &$cx['data']['root']['model'];

					if ($model == null) {
						ThemeLogger::log('[!] Cannot find model', LogTypes::$ERROR);

						return;
					}

					return call_user_func_array(
						[
							$model,
							$method,
						],
						$args
					);
				},
				'truncate'                => function ($text, $maxLength) {
					if (strlen($text) <= $maxLength) {
						return $text;
					}
					$chunks = explode(PHP_EOL, wordwrap($text, $maxLength, PHP_EOL));

					return htmlspecialchars($chunks[0] . '...');
				},
				'wrap_lines'              => function ($tag, $text) {
					$text = trim($text);
					$lines = explode(PHP_EOL, $text);
					array_walk($lines, function (&$value, $key, &$tag) {
						$value = '<' . $tag . '>' . trim($value) . '</' . $tag . '>';
					}, $tag);
					$result = implode('', $lines);

					return $result;
				},
				'urlencode'               => function ($text) {
					return urlencode($text);
				},
				'join'                    => function ($values, $delimiter) {
					if (is_array($values)) {
						return implode($delimiter, $values);
					} else {
						return $values;
					}
				},
				'next_uid' => function () {
					++HandlebarsRenderer::$uid;
				},
				'uid' => function () {
					return HandlebarsRenderer::$uid;
				},
			],
		]);

		$renderer = LightnCandy::prepare($phpStr);

		$pageData = $model->getPageData();

		$pageData['renderer'] = &$this;

		// need to render first before dumping tr strings...
		$html = $renderer($pageData);

		if ($return) {
			return $html;
		}
		print($html);
	}

	/**
	 * Simple autonomous render().
	 *
	 * Use this if you need to render Handlebars from main template recursively.
	 *
	 * @param [type] $templateName
	 * @param array $data
	 * @return void
	 */
	final public function renderSimple($templateName, $data = [], bool $return = false)
	{
		//$this->preloadPartials();

		$templatePath = get_template_directory() . '/templates/' . $templateName;
		$template = file_get_contents($templatePath);
		ThemeLogger::log('Rendering simple HB template ' . $templatePath, LogTypes::$INFO);

		$phpStr = LightnCandy::compile($template, [
			'flags'   => LightnCandy::FLAG_ERROR_LOG | LightnCandy::FLAG_ERROR_EXCEPTION | LightnCandy::FLAG_HANDLEBARS | LightnCandy::FLAG_INSTANCE | LightnCandy::FLAG_NOESCAPE,
			'helpers' => [
				'dump' => function ($data) {
					ThemeLogger::log($data, LogTypes::$DEBUG);

					return '<pre style="color:#000;background-color:#fff">' . htmlspecialchars(print_r($data, true)) . '</pre>';
				},
			],
		]);

		$data['renderer'] = &$this;
		if (empty($phpStr)) {
			throw new Exception("error");
		}
		$renderer = LightnCandy::prepare($phpStr);
		$html = $renderer($data);

		if ($return) {
			return $html;
		}
		print($html);
	}
}
