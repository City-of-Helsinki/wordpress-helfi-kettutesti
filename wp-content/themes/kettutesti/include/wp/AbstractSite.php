<?php


interface IRenderer {
	function render( AbstractSiteModel &$model, $templateName );
}


/**
 * Main class for this site.
 */
abstract class AbstractSite {

	protected $model;
	protected $renderer;

	// v--- null textDomain means no lang support!
	public function __construct( AbstractSiteModel $model, IRenderer $renderer, $textDomain = null ) {
		if ( $textDomain ) {
			// Loads wp-content/languages/themes/kg-it_IT.mo.
			load_theme_textdomain( $textDomain, trailingslashit( WP_LANG_DIR ) . 'themes' );
			load_theme_textdomain( $textDomain, trailingslashit( WP_LANG_DIR ) . 'wpml' );
		}

		$this->renderer = $renderer;
		$this->model = $model;

		add_action( 'after_setup_theme', [ $this, 'onThemeSupports' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'onEnqueueScripts' ] );

		$this->onEnqueueStyles();

		add_action( 'admin_enqueue_scripts', [ $this, 'onAdminScripts' ] );

		add_action( 'init', function() {
			// force implementations:
			$this->addTaxonomies();
			$this->addPostTypes();
			$this->addImageSizes();

			// override if needed
			$this->addOptionsPage();
		} );

		if ( is_admin() ) {
			add_action( 'tgmpa_register', [ $this, 'onTGMPA' ] );
		}
	}

	abstract public function onAdminScripts();

	abstract public function onTGMPA();

	abstract public function onThemeSupports();

	abstract public function onEnqueueScripts();

	abstract public function onEnqueueStyles();

	abstract protected function addTaxonomies();

	abstract protected function addPostTypes();

	abstract protected function addImageSizes();

	final public function preparePageContent( string $contentTemplate = 'layouts.hbs' ) {
		$this->model->preparePageContent( $contentTemplate );
	}

	/**
	 * Simple wrapper for rendering current page.
	 *
	 * @param string $templateFile
	 * @return void
	 */
	final public function render( string $templateFile ) {
		$this->renderer->render( $this->model, $templateFile );
	}

	protected function addOptionsPage() {
		if ( function_exists( 'acf_add_options_page' ) ) {

			/*acf_add_options_page( array(
				'page_title' => __( 'Theme General Settings' ),
				'menu_title' => __( 'Theme Settings' ),
				'menu_slug'  => 'theme-general-settings',
				'capability' => 'edit_posts',
				'redirect'   => false,
			) );*/
		}
	}

}
