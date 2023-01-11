<?php


class Site extends AbstractSite {

	//private $model;
	//private $renderer;
	private $layoutFilesGenerator;
	private $templateFunctions;

	function __construct(bool $isDev) {

		$layoutPath = get_template_directory() . '/layouts';
		$templatePath = get_template_directory() . '/templates';

		load_theme_textdomain('kettutesti');

		parent::__construct(
			new SiteModel(
				$layoutPath
			),
			new HandlebarsRenderer(
				$templatePath,
				$layoutPath,
				'kettutesti'
			),
			'kettutesti'
		);

		register_nav_menus(array(

			'page-menu'      => 'Page menu',
			'footer-menu'    => 'Footer menu',
			'footer-contact' => 'Footer contact menu',
			'footer-some'    => 'Footer some menu',

		));

		$this->templateFunctions = new TemplateFunctions($this->model, $isDev);

		if (function_exists('acf_add_options_page')) {

			/*acf_add_options_sub_page( array(
				'page_title'  => 'Pelikirja',
				'menu_title'  => 'Pelikirja',
				'menu_slug'   => 'pelikirja',
				'parent_slug' => 'options-general.php',
				'capability'  => 'edit_posts',
			) );*/

			/*acf_add_options_page(array(
				'page_title' 	=> 'Theme General Settings',
				'menu_title'	=> 'Theme Settings',
				'menu_slug' 	=> 'theme-general-settings',
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Header Settings',
				'menu_title'	=> 'Header',
				'parent_slug'	=> 'theme-general-settings',
			));*/

			/*acf_add_options_sub_page( array(
				'page_title'  => 'Profile page Settings',
				'menu_title'  => 'Profile',
				'menu_slug'   => 'profile-settings',
				'parent_slug' => 'options-general.php',
				'capability'  => 'edit_posts',
			) );*/

			acf_add_options_sub_page(array(
				'page_title'  => 'Theme Footer Settings',
				'menu_title'  => 'Footer',
				'menu_slug'   => 'footer-settings',
				'parent_slug' => 'options-general.php',
				'capability'  => 'edit_posts',
			));

			acf_add_options_sub_page(array(
				'page_title' 	=> 'Theme Header Settings',
				'menu_title'	=> 'Header',
				'menu_slug'   => 'header-settings',
				'parent_slug' => 'options-general.php',
				'capability'  => 'edit_posts',

			));
			/*acf_add_options_sub_page( array(
				'page_title'  => 'Theme General Settings',
				'menu_title'  => 'General',
				'menu_slug'   => 'them-general-settings',
				'parent_slug' => 'options-general.php',
				'capability'  => 'edit_posts',
			) );*/
		}

		add_filter('gform_ajax_spinner_url', [$this->templateFunctions, 'gform_ajax_spinner_url'], 10, 2);

		add_filter('show_admin_bar', [$this->templateFunctions, 'show_admin_bar']);

		add_action('wp_enqueue_scripts', [$this->templateFunctions, 'load_dashicons_front_end']);

		add_action('after_setup_theme', [$this, 'onThemeSupports']);

		add_shortcode('blue', [$this, 'on_shortcode_blue']);

		add_filter('embed_oembed_html', [$this, 'wrap_video_embeds'], 10, 4);
	}

	//@override
	final protected function addImageSizes() {

		add_image_size('postlift', 448, 314, true);
		add_image_size('postlift-x2', 896, 628, true);

		add_image_size('hero', 720, 612, true);
		add_image_size('hero-x2', 1440, 1224, true);

		add_image_size('article', 1408, 720, true);

		add_image_size('text-image', 568, 528, true);
		add_image_size('text-image-x2', 1136, 1056, true);


		add_image_size('articlelift', 328, 296, true);
		add_image_size('articlelift-x2', 656, 592, true);


		//add_image_size( 'wayfinder', 624, 672, true );
		//add_image_size( 'gallery', 392, 392, true );

		//add_image_size( 'product_image', 576, 0, false );
		//add_image_size( 'product_image_2x', 1152, 0, false );
		//add_image_size( 'hero_image', 848, 624, true );
	}

	//@override
	final public function onThemeSupports() {
		add_filter('use_block_editor_for_post', '__return_false', 10);
		add_theme_support('automatic-feed-links');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);
		add_theme_support(
			'post-formats'
		);
		add_theme_support('menus');
	}

	//@override
	final public function onTGMPA() {

		l('onTGMPA()');
		/*
		* Array of plugin arrays. Required keys are name and slug.
		* If the source is NOT from the .org repo, then source is also required.
		*/
		$plugins = [
			// include a plugin from the WordPress Plugin Repository.
			[
				'name'     => 'Yoast_SEO',
				'slug'     => 'wordpress-seo',
				'required' => false,
			],
			/*[
				'name'               => 'WPML Multilingual CMS', // The plugin name.
				'slug'               => 'sitepress-multilingual-cms', // The plugin slug (typically the folder name).
				'source'             => get_template_directory() . '/install-files/sitepress-multilingual-cms.4.2.9.zip',
				'required'           => false, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'is_callable'        => 'icl_get_languages', // If set, this callable will be be checked for availability to determine if a plugin is active.
			],*/
			[
				'name'               => 'Activity Log', // The plugin name.
				'slug'               => 'aryo-activity-log', // The plugin slug (typically the folder name).
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				//'is_callable'        => 'loco_debugging', // If set, this callable will be be checked for availability to determine if a plugin is active.
			],
			[
				'name'               => 'Loco Translate', // The plugin name.
				'slug'               => 'loco-translate', // The plugin slug (typically the folder name).
				'required'           => false, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'is_callable'        => 'loco_debugging', // If set, this callable will be be checked for availability to determine if a plugin is active.
			],
			[
				'name'               => 'Advanced Custom Fields PRO', // The plugin name.
				'slug'               => 'advanced-custom-fields-pro', // The plugin slug (typically the folder name).
				'source'             => get_template_directory() . '/install-files/advanced-custom-fields-pro.zip',
				'required'           => true, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'is_callable'        => 'get_fields', // If set, this callable will be be checked for availability to determine if a plugin is active.
			],
			[
				'name'               => 'Buena Default', // The plugin name.
				'slug'               => 'buena-default', // The plugin slug (typically the folder name).
				'source'             => 'https://github.com/BuenaCreative/buena-default.git',
				'required'           => false, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				'is_callable'        => 'buena_default_admin_page',

			],
			[
				'name'               => 'Relevanssi',
				'slug'               => 'relevanssi',
				'required'           => true,
				'force_activation'   => false,
				'force_deactivation' => false,
			],
			[
				'name'             => 'Gravity Forms',
				'slug'             => 'gravityforms',
				'source'           => get_template_directory() . '/install-files/gravityforms_2.4.21.7.zip',
				'required'         => true,
				'force_activation' => false,
			],
			[
				'name'               => 'Query monitor', // The plugin name.
				'slug'               => 'query-monitor', // The plugin slug (typically the folder name).
				'required'           => false, // If false, the plugin is only 'recommended' instead of required.
				'force_activation'   => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
				'force_deactivation' => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
				//'is_callable'        => 'loco_debugging', // If set, this callable will be be checked for availability to determine if a plugin is active.
			],

		];

		$config = [
			'id'           => 'tgmpa',                 // Unique ID for hashing notices for multiple instances of TGMPA.
			'menu'         => 'tgmpa-install-plugins', // Menu slug.
			'parent_slug'  => 'themes.php',            // Parent menu slug.
			'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,                    // Show admin notices or not.
			'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => true,                   // Automatically activate plugins after installation or not.
			'message'      => '',                      // Message to output right before the plugins table.
		];

		tgmpa($plugins, $config);
	}

	//@override
	final public function onAdminScripts() {
		wp_enqueue_style('admin-style', get_template_directory_uri() . '/css/admin-style.css');
	}

	//@override
	final public function onEnqueueScripts() {
		wp_enqueue_script("jquery");
		wp_enqueue_script("cookie-hub", get_template_directory_uri() . '/src/js/cookieHub.js', false);
		wp_enqueue_script("cookie-placeholder", get_template_directory_uri() . '/src/js/youtubePlaceholder.js', false);
	}

	//@override
	final public function onEnqueueStyles() {
		if (is_admin()) {
			wp_enqueue_style('admin-style', get_template_directory_uri() . '/css/admin-style.css');
		} else {
			$css_version = filemtime(get_template_directory() . '/css/tailwind.css');
			$css_used = '/css/tailwind.css?v=' . $css_version;


			wp_enqueue_style('tailwind-css', get_template_directory_uri() . $css_used);
		}
	}
	// 

	//@override
	final protected function addPostTypes() {
	}

	//@override
	final protected function addTaxonomies() {
	}

	public function on_shortcode_blue($atts, $content = null) {

		$content = $this->custom_filter_shortcode_text($content);

		return '<div class="blue">' . $content . '</div>';
	}

	private function custom_filter_shortcode_text($text = "") {
		//kudos: https://www.blindemanwebsites.com/today-i-learned/2019/shortcode-autop/

		// Replace all the poorly formatted P tags that WP adds by default.
		$tags = array("<p>", "</p>");
		$text = str_replace($tags, "\n", $text);

		// Remove any BR tags
		$tags = array("<br>", "<br/>", "<br />");
		$text = str_replace($tags, "", $text);

		// Add back in the P and BR tags again, remove empty ones
		return apply_filters("the_content", $text);
	}

	public function wrap_video_embeds($html, $url, $attr, $post_id) {
		return '<div class="video">' . $html . '</div>';
	}
}
