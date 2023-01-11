<?php

/**
 * Main data storage for rendering Handlebars templates from.
 */


abstract class AbstractSiteModel {
	protected $pageData;

	private $isPrepareCalled;
	// needed when including
	private $layoutFiles;        // cached by key
	private $cache;

	/**
	 * Constructor.
	 *
	 * Setup all WP defaults that are available at this time. Note: some
	 * data elements are only available later.
	 *
	 * @param string $layoutPath
	 */
	public function __construct( string $layoutPath ) {
		$this->isPrepareCalled = false;
		$this->cache = new TransientCache();

		// "pre-find" all layout php files
		//$layoutPHPFiles = getFilesRecursive( $layoutPath, '/\.php$/' );
		require_once $layoutPath . '/layouts.php';

		$generatedLayouts = new GeneratedLayouts();
		$this->layoutFiles = $generatedLayouts->layoutFiles;

		if ( !is_admin() ) {
			$this->setupModel();
			$this->setupDefaultData();
		}
	}

	private function setupModel() {

		$url = URL::createFromRequest();

		$js_version = @filemtime( get_template_directory() . '/js/main.js' );
		$css_version = @filemtime( get_template_directory() . '/css/style.css' );

		$this->pageData = [
			'menus'       => '',
			'wp'          => [
				'bodyclass'           => '',
				'language_attributes' => get_language_attributes(),
				'charset'             => get_bloginfo( 'charset' ),
				'sitename'            => get_bloginfo( 'blogname' ),
				'description'         => get_bloginfo( 'description' ),
				'theme_url'           => get_template_directory_uri(),
				'home_url'            => home_url(),
				'blog_url'            => get_permalink( get_option( 'page_for_posts' ) ),
				'css_url'             => get_template_directory_uri() . '/css/style.css?v=' . $css_version,
				'js_url'              => get_template_directory_uri() . '/js/main.js?v=' . $js_version,
				'current_language'    => defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : '',
			],
			'page'        => [
				'title'     => null,
				'permalink' => null,
			],
			'wpml'        => defined( 'ICL_LANGUAGE_CODE' ) ? wpml_get_active_languages_filter( '' ) : null,
			'layouts'     => [],
			'posts'       => [],
			'slug'        => [
				'name' => null,
				'id'   => null,
			],
			'current_url' => [
				'path' => $url->path,
				'full' => $url->toString() . '/',
			],
			'model'       => &$this,
		];
	}

	abstract protected function setupDefaultData();

	// private function populateACF( &$posts ) {
	// 	foreach ( $posts as &$post ) {
	// 		$post = (object)array_merge( (array)$post, (array)get_fields( $post->ID ) );
	// 	}
	// }

	private function populateACFLayouts( $usePage = null ) {
		// supress ACF "Warning: Invalid argument supplied for foreach()" :/
		$this->pageData['layouts'] = @get_fields( $usePage );
	}

	private function populateImageAbsUrls( array &$attachmentMeta ) {
		// using probably acf image, which already has urls as absolute
		/*if ( isset( $attachmentMeta['url'] ) ) {
			return;
		}*/

		// assume same path as original
		$root = wp_upload_dir()['baseurl'] . '/' . dirname( $attachmentMeta['file'] ) . '/';

		$attachmentMeta['url'] = wp_upload_dir()['baseurl'] . '/' . $attachmentMeta['file'];

		foreach ( $attachmentMeta['sizes'] as $size => &$image ) {
			$image['url'] = $root . $image['file'];
		}
	}

	public function getResponsiveImage( $id = '' ) {
		if ( $id === '' ) {
			return null;
		}

		// $attachment = $this->cache->getCached( TransientCache::TRANSIENT_ATTACHMENT_SUFFIX . $id );

		// if ( !empty( $attachment ) ) {
		// 	return $attachment;
		// }

		$meta = get_post_meta( $id );
		$meta_post = get_post( $id );

		// $attachment = wp_get_attachment_metadata( $id );
		try {
			if ( isset( $meta['_wp_attachment_metadata'] ) && is_array( $meta['_wp_attachment_metadata'] ) ) {
				$attachment = unserialize( $meta['_wp_attachment_metadata'][0] );
			}
		} catch ( Error $e ) {
			ThemeLogger::log( '[!] Cannot unserialize() attachment meta for #' . $id, LogTypes::$ERROR );
		}

		if ( empty( $attachment ) ) {
			return null;
		}

		$attachment['id'] = $id;

		// why these not populated by WP? :(

		if ( isset( $meta['_wp_attachment_image_alt'] ) ) {
			$attachment['image_meta']['alt'] = implode( ',', $meta['_wp_attachment_image_alt'] );
		}

		$attachment['image_meta']['caption'] = $meta_post->post_excerpt;
		$attachment['image_meta']['description'] = $meta_post->post_content;
		$attachment['image_meta']['title'] = $meta_post->post_title;

		$srcset = @wp_get_attachment_image_srcset( $id );
		if ( $srcset === false ) {
			ThemeLogger::log( '[!] Cannot get srcset for ' . print_r( $attachment, true ), LogTypes::$ERROR );
			$this->populateImageAbsUrls( $attachment );

			return $attachment;
		} else {
			$attachment['srcset'] = $srcset;
		}

		// need to manually populate paths (see https://core.trac.wordpress.org/ticket/32117)
		$this->populateImageAbsUrls( $attachment );

		$this->cache->setCached( TransientCache::TRANSIENT_ATTACHMENT_SUFFIX . $id, $attachment );

		return $attachment;
	}

	/**
	 * Prepares template for rendering.
	 *
	 * Sets default data elements in this context (current, global $post)
	 * @param string $template
	 * @return void
	 */
	public function preparePageContent( string $template ) {
		ThemeLogger::log( 'Using template: ' . $template );

		if ( $this->isPrepareCalled ) {
			throw new RuntimeException( 'model->prepare() called more than once' );
		}
		$this->isPrepareCalled = true;

		$this->pageData['wp']['bodyclass'] = implode( " ", get_body_class() );
		$this->pageData['template_file'] = $template;
		$this->pageData['page']['permalink'] = get_permalink();
		$this->pageData['page']['title'] = get_the_title();

		$this->populateACFLayouts();

		global $posts;
		$this->pageData['posts'] = $posts;

		foreach ( $this->pageData['posts'] as &$item ) {
			if ( !$item ) {
				continue;
			}
			$item->nice_date = get_the_date( '', $item );//mysql2date( 'd.m.Y', $item->post_date );
			$item->url = get_permalink( $item );
		}
	}

	/**
	 * Getter for all data.
	 *
	 * Mainly used by renderer to retrieve data. Should not be called by user.
	 *
	 * @return object
	 */
	final public function getPageData() {
		if ( !$this->isPrepareCalled ) {
			throw new RuntimeException( 'preparePageContent() must be called before getting data from model.' );
		}

		return $this->pageData;
	}

	public function insertAsLayout( $localCx, $phpPath, $cx ) {
		$className = basename( $phpPath, '.php' );
		$phpPath = get_stylesheet_directory() . '/' . $phpPath;
		$this->runLayout( $localCx, $phpPath, $className );
	}

	public function setupLayoutData( $cx ) {
		$me = &$cx['_this'];
		$layoutKey = $me['acf_fc_layout'];

		if ( !isset( $this->layoutFiles[$layoutKey] ) ) {
			$msg = 'Cannot find files for layout "' . $layoutKey . '"' . PHP_EOL;
			$msg .= 'Registered layout are : ' . implode( ',', $this->layoutFiles );
			ThemeLogger::log( $msg, LogTypes::$FATAL );
		}

		$phpPath = $this->layoutFiles[$layoutKey]['requirePath'];
		$className = $this->layoutFiles[$layoutKey]['className'];

		$this->runLayout( $me, $phpPath, $className );
	}

	private function runLayout( &$cx, $phpPath, $className ) {
		require_once $phpPath;

		if ( !is_callable( $className . '::setup' ) ) {
			ThemeLogger::log( $className . '::setup() not found or is not callable. Layout php class must implement static setup(&$handlebarsContext)', LogTypes::$FATAL );
		}

		$className::setup( $cx, $this );
	}

	protected function getMenus() {
		//$menus = $this->cache->getCached( TransientCache::TRANSIENT_MENUS );

		if ( !empty( $menus ) ) {

			return $menus;
		} else {

			//get menus by locations
			$theme_locations = get_nav_menu_locations();
			$menus = [];

			foreach ( $theme_locations as $key => $theme_location ) {
				$menu_obj = get_term( $theme_locations[$key], 'nav_menu' );

				if ( !is_null( $menu_obj ) ) {

					$menus[$key] = WPMenuBuilder::buildTree( $menu_obj->slug );

					if ( is_array( $menus[$key] ) ) {
						foreach ( $menus[$key] as $menu_item ) {
							$nav_item_icon = get_field( 'icon', $menu_item ); //url to svg

							if ( $nav_item_icon ) {
								$menu_item->icon = $nav_item_icon;
							}

							$nav_item_icon_white = get_field( 'icon_white', $menu_item );

							if ( $nav_item_icon_white ) {
								$menu_item->icon_white = $nav_item_icon_white;
							}
						}
					}
				}
			}

			//$this->cache->setCached( TransientCache::TRANSIENT_MENUS, $menus );

			return $menus;
		}
	}

}
