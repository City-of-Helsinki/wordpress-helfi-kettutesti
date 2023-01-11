<?php

use WPML\FP\Undefined;

class SiteModel extends AbstractSiteModel
{
	//private $pageData;

	// needed when including
	private $layoutFiles;        // cached by key

	private $cache;
	private $isDev;

	private $param_course_id = 'course_id';

	function __construct(string $layoutPath)
	{
		parent::__construct($layoutPath);
	}

	protected function setupDefaultData()
	{
		$languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc&link_empty_to=' . home_url());

		if ($languages) {
			foreach ($languages as $language) {
				if ($language['active']) {
					unset($languages[$language['language_code']]);
				}
			}
		}

		$categories = get_terms(array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
		));

		$url = URL::createFromRequest();
		$js_version = filemtime(get_template_directory() . '/js/main.js');
		//$css_version = filemtime( get_template_directory() . '/css/style.css' );

		$this->pageData['menus'] = $this->getMenus();

		$this->pageData['footer'] = [
			"bgfooter" => get_field('bgfooter', 'option'),
			"logofoot" => get_field('logofoot', 'option'),
			"linksfoot" => get_field('linksfoot', 'option'),
			"linknames" => get_field('linknames', 'option')
		];

		// l($this->pageData['menus'])

		$this->pageData['wp'] = [
			'bodyclass'           => '',
			'language_attributes' => get_language_attributes(),
			'charset'             => get_bloginfo('charset'),
			'sitename'            => get_bloginfo('blogname'),
			'description'         => get_bloginfo('description'),
			'theme_url'           => get_template_directory_uri(),
			'home_url'            => home_url(),
			//'css_url'             => get_template_directory_uri() . $css_used,
			//'tailwind_css_url'    => get_template_directory_uri() . '/css/tailwind.css',
			'js_url'              => get_template_directory_uri() . '/js/main.js?v=' . $js_version,
			'swiper_css_url'      => get_template_directory_uri() . '/css/swiper.min.css',
			'swiper_js_url'       => get_template_directory_uri() . '/js/swiper.min.js',
			'languages'           => $languages,
			'current_language'    => defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '',

			'maintenance' => false,

			'categories'           => $categories,
			'user_logged_in'       => is_user_logged_in(),
			'course_endpoint_slug' => 'kurssi',
			'search_action_url'    => trailingslashit(home_url()),

			'cookie_policy_url'   => __('https://www.ncc.fi/ota-yhteytta/tietoa-sivustosta/tietosuojaseloste/', 'kettutesti'),
			'cookie_policy_title' => __('Tietosuojaseloste', 'kettutesti'),

		];

		$this->pageData['header'] = [
			"siteLogo" => get_field('logo', 'option'),
			"navbg" => get_field('bgimage', 'option'),
			'pageurl' => get_home_url()
		];




		$this->pageData['page'] = [
			'title'     => null,
			'permalink' => null,
		];

		$this->pageData['wpml'] = defined('ICL_LANGUAGE_CODE') ? wpml_get_active_languages_filter('') : null;

		$this->pageData['current_url'] = [
			'path'    => $url->path,
			'full'    => $url->toString() . '/',
			'request' => $_SERVER,
		];

		$this->pageData['general_strings'] = [
			'author_title'        => __('Kirjoittaja', 'kettutesti'),
			'related_posts_title' => __('Nämäkin sisällöt voivat kiinnostaa sinua', 'kettutesti'),
		];
	}

	private function overridePost($title)
	{
		global $post;
		$post = get_page_by_title($title);
	}

	private function populateTerms(&$posts, $taxonomy)
	{
		foreach ($posts as &$post) {
			//			$post->categories = wp_get_post_categories( $post->ID );
			$post->terms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'all']);
		}
	}

	private function populateAttachments(&$posts)
	{
		foreach ($posts as &$post) {
			$post->attachment = $this->getResponsiveImage(get_post_thumbnail_id($post->ID));
		}
	}

	private function populateACF(&$posts)
	{
		foreach ($posts as &$post) {
			$post = (object)array_merge((array)$post, (array)get_fields($post->ID));
		}
	}

	/*private function populateACFLayouts( $usePage = null ) {
		// supress ACF "Warning: Invalid argument supplied for foreach()" :/
		l( 'Page: ' . $usePage );
		$this->pageData['layouts'] = @get_fields( $usePage );
	}*/

	private function populateMetaDescription(array &$posts)
	{
		foreach ($posts as &$post) {
			$post->meta_description = get_post_meta($post->ID, '_yoast_wpseo_metadesc', true);
		}
	}

	private function populatePermalinks(&$posts)
	{
		foreach ($posts as &$post) {
			$post->url = get_permalink($post);
		}
	}

	private function populateAuthorMeta(&$posts)
	{
		foreach ($posts as &$post) {
			$post->author_meta = get_user_by('ID', $post->post_author);
			$author = get_field('author', $post->ID);
			if ($author) {
				$post->author_data = $author;
				$post->author_data->image = $this->getResponsiveImage(get_post_thumbnail_id($post->ID));
				$post->author_data->url = get_permalink($author);
				$post->author_data->fields = get_fields($author->ID);
			}
		}
	}

	private function populateNiceDates(&$posts)
	{
		foreach ($posts as &$post) {

			if ($post->post_type != 'podcast') {
				$post->nice_date = mysql2date('d.m.Y', $post->post_date);
			}
		}
	}

	private function populateMainCategories(&$posts)
	{
		foreach ($posts as &$post) {
			$item_terms = get_the_terms($post, 'category');
			if ($item_terms && is_array($item_terms)) {

				$post->category = $item_terms[0];
				$post->category->name = ucfirst($post->category->name);
			}
		}
	}

	private function populateCourseExcerpts(&$posts)
	{
		foreach ($posts as &$post) {
			if ($post->post_type == 'koulutus') {
				$intro_text = get_field('intro_text', $post);

				$max_length = 40;

				if (strlen($intro_text) > $max_length) {
					$offset = ($max_length - 3) - strlen($intro_text);
					$intro_text = substr($intro_text, 0, strrpos($intro_text, ' ', $offset)) . '...';
				}

				$post->course_excerpt = $intro_text;
			}
		}
	}

	private function populateImageAbsUrls(array &$attachmentMeta)
	{
		// using probably acf image, which already has urls as absolute
		/*if ( isset( $attachmentMeta['url'] ) ) {
			return;
		}*/

		// assume same path as original
		$root = wp_upload_dir()['baseurl'] . '/' . dirname($attachmentMeta['file']) . '/';

		$attachmentMeta['url'] = wp_upload_dir()['baseurl'] . '/' . $attachmentMeta['file'];

		foreach ($attachmentMeta['sizes'] as $size => &$image) {
			$image['url'] = $root . $image['file'];
		}
	}

	public function populateLiftData(&$posts)
	{

		$this->populateAuthorMeta($posts);
		$this->populateNiceDates($posts);
		$this->populatePermalinks($posts);
		$this->populateMainCategories($posts);
		$this->populateAttachments($posts);

		$this->populateCourseExcerpts($posts);
	}

	/*public function getResponsiveImage( $id = '' ) {
		if ( $id === '' || $id === null || $id === 0 ) {
			return null;
		}

		$attachment = $this->cache->getCached( TransientCache::TRANSIENT_ATTACHMENT_SUFFIX . $id );

		if ( !empty( $attachment ) ) {
			return $attachment;
		}

		l( 'attachment ID: ' . $id );

		$attachment = wp_get_attachment_metadata( $id );
		if ( $attachment === false ) {
			return null;
		}
		$attachment['id'] = $id;

		$srcset = @wp_get_attachment_image_srcset( $id );
		if ( $srcset === false ) {
			l( '[!] Cannot get srcset for ' . print_r( $attachment, true ) );
		} else {
			$attachment['srcset'] = $srcset;
		}

		//l( $attachment );

		// need to manually populate paths (see https://core.trac.wordpress.org/ticket/32117)
		$this->populateImageAbsUrls( $attachment );

		$this->cache->setCached( TransientCache::TRANSIENT_ATTACHMENT_SUFFIX . $id, $attachment );

		return $attachment;
	}*/

	/**
	 * Prepares data for rendering.
	 *
	 * This should called once, before render, by user!
	 *
	 * @return void
	 */

	//@override
	public function preparePageContent(string $template)
	{

		$this->pageData['menus'] = $this->getMenus();
		$this->populateBreadcrumb();

		parent::preparePageContent($template);
		$this->pageData['wp']['bodyclass'] = implode(" ", get_body_class());

		$this->pageData['page']['permalink'] = get_permalink();
		$this->pageData['page']['title'] = get_the_title();

		global $posts;
		$this->pageData['posts'] = $posts;

		//$this->populateACFLayouts();

		$this->populateLiftData($this->pageData['posts']);

		foreach ($this->pageData['posts'] as &$item) {
			if (!$item) {
				continue;
			}
			//$item->nice_date = mysql2date( 'd.m.Y', $item->post_date );
			//$item->url = get_permalink( $item );
			//$item_terms = get_the_terms( $item, 'category' );
			//$item->category = $item_terms[0];
		}

		switch ($template) {
			case 'archive.hbs':

				$pagination = get_the_posts_pagination([
					'screen_reader_text' => __('Sivutus', 'kettutesti'),
				]);

				// If we're not outputting the previous page link, prepend a placeholder with `visibility: hidden` to take its place.
				if (strpos($pagination, 'prev page-numbers') === false) {
					$pagination = str_replace('<div class="nav-links">', '<div class="nav-links"><span class="prev page-numbers placeholder" aria-hidden="true">' . __('Edellinen', 'kettutesti') . '</span>', $pagination);
				}

				// If we're not outputting the next page link, append a placeholder with `visibility: hidden` to take its place.
				if (strpos($pagination, 'next page-numbers') === false) {
					$pagination = str_replace('</div>', '<span class="next page-numbers placeholder" aria-hidden="true">' . __('Seuraava', 'kettutesti') . '</span></div>', $pagination);
				}
				$this->pageData['archive']['pagination'] = $pagination;

				if (is_home()) {

					$page_for_posts_id = get_option('page_for_posts');
					$this->pageData['single_post'] = get_post($page_for_posts_id);
					$this->pageData['single_post']->intro_text = get_field('intro_text', $page_for_posts_id);
					$thumb_id = get_post_thumbnail_id($this->pageData['single_post']->ID);
					if ($thumb_id) {
						$this->pageData['single_post']->image = $this->getResponsiveImage($thumb_id);
					}

					$this->pageData['single_post']->fields = get_fields($page_for_posts_id);

					$tags = get_field('featured_tags', $page_for_posts_id);

					if ($tags && is_array($tags)) {

						$featured_tags = [];
						foreach ($tags as $tag_id) {
							$term = get_term($tag_id);
							$term->link = get_category_link($term);
							$featured_tags[] = $term;
						}
						$this->pageData['single_post']->featured_tags = $featured_tags;
					}
				} else if (is_post_type_archive()) {
					$pt = get_queried_object();

					$this->pageData['archive']['title'] = $pt->label;
				} else if (is_tag() || is_category() || is_tax()) {

					$tag = get_queried_object();

					if ($term_img) {
						$this->pageData['single_post']->image = $this->getResponsiveImage($term_img['ID']);
					} else {

						$page_for_posts_id = get_option('page_for_posts');
						$thumb_id = get_post_thumbnail_id($page_for_posts_id);

						if ($thumb_id) {
							$this->pageData['single_post']->image = $this->getResponsiveImage($thumb_id);
						}
					}

					$this->pageData['single_post']->fields = [
						'title' => ucfirst($tag->name),
						'text'  => $tag->description,
					];
				} else if (is_search()) {

					$this->pageData['archive']['title'] = __('Hakusi: ', 'kettutesti') . get_search_query();
				}

				break;

			case 'tmpl-resultpage.hbs':
				if (isset($posts[0])) {

					$this->pageData['single_post'] = $posts[0];
					$this->pageData['single_post']->post_content_wpautop = apply_filters('the_content', $this->pageData['single_post']->post_content);
					//$this->pageData['single_post']->post_content_wpautop = wpautop( do_shortcode( $this->pageData['single_post']->post_content ) );
					$this->pageData['pub_date'] = get_the_date('j.n.Y', $this->pageData['single_post']->ID);

					$thumb_id = get_post_thumbnail_id($this->pageData['single_post']->ID);

					if ($thumb_id) {
						$this->pageData['single_post']->image = $this->getResponsiveImage($thumb_id);
					}

					$this->pageData['single_post']->shares_heading = __('Jaa somessa', 'kettutesti');
					$this->pageData['single_post']->share_url = get_permalink($this->pageData['single_post']->ID);
					$this->pageData['single_post']->share_text_facebook = __('Jaa artikkeli Facebookissa', 'kettutesti');
					$this->pageData['single_post']->share_text_twitter = __('Jaa artikkeli Twitterissä', 'kettutesti');
					$this->pageData['single_post']->share_text_linkedin = __('Jaa artikkeli Linkedinissä', 'kettutesti');

					if (!is_page()) {
						$this->pageData['single_post']->excerpt = get_the_excerpt();
						$terms = get_the_terms($posts[0], 'category');

						if ($terms && is_array($terms)) {
							$this->pageData['single_post']->category = $terms[0];
							$this->pageData['single_post']->category->name = ucfirst($this->pageData['single_post']->category->name);
							$this->pageData['single_post']->categories = $terms;
						}

						$tags = get_the_terms($posts[0], 'post_tag');

						if ($tags && is_array($tags)) {
							foreach ($tags as &$tag) {
								$tag->link = get_category_link($tag);
							}

							$this->pageData['single_post']->tags = $tags;
						}

						$author = get_field('author', $this->pageData['single_post']->ID);

						if ($author) {
							$this->pageData['single_post']->author_data = $author;
							$this->pageData['single_post']->author_data->image = $this->getResponsiveImage(get_post_thumbnail_id($author->ID));
							$this->pageData['single_post']->author_data->fields = get_fields($author->ID);
							$this->pageData['single_post']->author_data->url = get_permalink($author);
						}

						$args = [
							'category'    => $this->pageData['single_post']->category->term_id,
							'exclude'     => $this->pageData['single_post']->ID,
							'post_status' => 'publish',
							'numberposts' => 2,
						];

						$this->pageData['single_post']->related_posts = get_posts($args);

						$this->populateLiftData($this->pageData['single_post']->related_posts);
					} else {
						$this->pageData['single_post']->is_page = true;
						$language_args = [
							'skip_missing' => true,
						];
						$this->pageData["wp_languages"] = wpml_get_active_languages_filter($language_args);
						$this->pageData['resultinformation'] = get_field("resultlayouts");
						$resultinformation = $this->pageData['resultinformation'];

						if (!isset($_GET['kettu'])) {
							return;
						};

						$param = $_GET['kettu'];
						$pageurl = esc_url(get_permalink() . "?kettu=" . $param);

						// social media links
						$this->pageData["pageurl"] = $pageurl;
						$this->pageData["facebookurl"] = 'https://www.facebook.com/sharer/sharer.php?u=' . $pageurl;
						$this->pageData["twitterurl"] = 'https://twitter.com/intent/tweet?text=' . "kettutesti" . '&amp;url=' . $pageurl . '&amp;via=wpvkp';


						switch ($param) {
							case 'kokki':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][0];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][0]["url"];
								break;
							case 'retki':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][1];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][2]["url"];
								break;
							case 'taitava':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][2];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][1]["url"];
								break;
							case 'keksija':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][3];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][6]["url"];
								break;
							case 'taitelija':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][4];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][3]["url"];
								break;
							case 'tarina':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][5];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][5]["url"];
								break;
							case 'viisas':
								$this->pageData["kettu"] = $resultinformation["resultinfo"][6];
								$this->pageData["kettu-image"] = $resultinformation["resultimages"][4]["url"];
								break;

							default:
								break;
						}
					}

					if (false && (comments_open() || get_comments_number())) {

						$comments = new stdClass();
						$comment_count = get_comments_number();

						if ($comment_count == 1) {
							$comments->title = __('1 comment', 'kettutesti');
						} else {
							$comments->title = sprintf(
								/* translators: %s: comment count number. */
								esc_html(_nx('%s comment', '%s comments', $comment_count, 'Comments title', 'kettutesti')),
								esc_html(number_format_i18n($comment_count))
							);
						}

						ob_start();
						comments_template();
						$this->pageData['single_post']->comment_template = ob_get_contents();
						ob_end_clean();
					}
				}

				break;

			case 'single.hbs':

				if (isset($posts[0])) {

					$this->pageData['single_post'] = $posts[0];
					$this->pageData['single_post']->post_content_wpautop = apply_filters('the_content', $this->pageData['single_post']->post_content);
					//$this->pageData['single_post']->post_content_wpautop = wpautop( do_shortcode( $this->pageData['single_post']->post_content ) );

					$this->pageData['pub_date'] = get_the_date('j.n.Y', $this->pageData['single_post']->ID);

					$thumb_id = get_post_thumbnail_id($this->pageData['single_post']->ID);

					if ($thumb_id) {
						$this->pageData['single_post']->image = $this->getResponsiveImage($thumb_id);
					}

					$this->pageData['single_post']->shares_heading = __('Jaa somessa', 'kettutesti');
					$this->pageData['single_post']->share_url = get_permalink($this->pageData['single_post']->ID);
					$this->pageData['single_post']->share_text_facebook = __('Jaa artikkeli Facebookissa', 'kettutesti');
					$this->pageData['single_post']->share_text_twitter = __('Jaa artikkeli Twitterissä', 'kettutesti');
					$this->pageData['single_post']->share_text_linkedin = __('Jaa artikkeli Linkedinissä', 'kettutesti');

					if (!is_page()) {
						$this->pageData['single_post']->excerpt = get_the_excerpt();
						$terms = get_the_terms($posts[0], 'category');

						if ($terms && is_array($terms)) {
							$this->pageData['single_post']->category = $terms[0];
							$this->pageData['single_post']->category->name = ucfirst($this->pageData['single_post']->category->name);
							$this->pageData['single_post']->categories = $terms;
						}

						$tags = get_the_terms($posts[0], 'post_tag');

						if ($tags && is_array($tags)) {
							foreach ($tags as &$tag) {
								$tag->link = get_category_link($tag);
							}

							$this->pageData['single_post']->tags = $tags;
						}

						$author = get_field('author', $this->pageData['single_post']->ID);

						if ($author) {
							$this->pageData['single_post']->author_data = $author;
							$this->pageData['single_post']->author_data->image = $this->getResponsiveImage(get_post_thumbnail_id($author->ID));
							$this->pageData['single_post']->author_data->fields = get_fields($author->ID);
							$this->pageData['single_post']->author_data->url = get_permalink($author);
						}

						$args = [
							'category'    => $this->pageData['single_post']->category->term_id,
							'exclude'     => $this->pageData['single_post']->ID,
							'post_status' => 'publish',
							'numberposts' => 2,
						];

						$this->pageData['single_post']->related_posts = get_posts($args);

						$this->populateLiftData($this->pageData['single_post']->related_posts);
					} else {
						$this->pageData['single_post']->is_page = true;
					}

					if (false && (comments_open() || get_comments_number())) {

						$comments = new stdClass();
						$comment_count = get_comments_number();

						if ($comment_count == 1) {
							$comments->title = __('1 comment', 'kettutesti');
						} else {
							$comments->title = sprintf(
								/* translators: %s: comment count number. */
								esc_html(_nx('%s comment', '%s comments', $comment_count, 'Comments title', 'kettutesti')),
								esc_html(number_format_i18n($comment_count))
							);
						}

						ob_start();
						comments_template();
						$this->pageData['single_post']->comment_template = ob_get_contents();
						ob_end_clean();
					}
				}

				break;

			case 'error404.hbs':
				$this->pageData['error404']['title'] = __('Voi ei! Hakemaasi sivua ei löytynyt.', 'kettutesti');
				$this->pageData['error404']['text'] = __('Euismod leo viverra turpis arcu felis. Arcu, viverra dictum tristique consequat viverra nibh vitae viverra. Tortor viverra elementum aliquet viverra ultrices sit nunc. Condimentum sit pretium odio tristique ante feugiat. Urna eu nam a in.', 'kettutesti');
				$this->pageData['error404']['button-text'] = __('Etusivulle', 'kettutesti');

				$this->pageData['search']['search-placeholder'] = __('Hae sivustolta', 'kettutesti');
				$this->pageData['search']['search-button-text'] = __('Hae', 'kettutesti');

				break;

			case 'search.hbs':
				//$this->populateSearchMeta( $this->pageData['posts'] );
				global $wp_query;

				if ($wp_query->found_posts) {
					$archive_subtitle = sprintf(
						/* translators: %s: Number of search results. */
						_n(
							'Löysimme <span class="hilite">%s</span> tulosta haullesi <span class="hilite">“' . get_search_query() . '”</span>',
							'Löysimme <span class="hilite">%s</span> tulosta haullesi <span class="hilite">“' . get_search_query() . '”</span>',
							$wp_query->found_posts,
							'kettutesti'
						),
						number_format_i18n($wp_query->found_posts)
					);
				} else {
					$archive_subtitle = __('Emme löytäneet tuloksia haullesi. Voit kokeilla sitä uudelleen yllä olevan hakukentän avulla.', 'kettutesti');
				}

				global $posts;
				foreach ($posts as $wp_post) {
					$wp_post->post_title = get_the_title($wp_post->ID);
				}

				$this->pageData['search']['title'] = $archive_subtitle;
				$this->pageData['search']['search_term'] = get_search_query();

				$this->pageData['search']['search-placeholder'] = __('Hae sivustolta', 'kettutesti');
				$this->pageData['search']['search-button-text'] = __('Hae', 'kettutesti');

				$pagination = get_the_posts_pagination([
					'screen_reader_text' => __('Haun sivutus', 'kettutesti'),
				]);

				//$this->pageData['search']['paging'] = $pagination;

				// If we're not outputting the previous page link, prepend a placeholder with `visibility: hidden` to take its place.
				if (strpos($pagination, 'prev page-numbers') === false) {
					$pagination = str_replace('<div class="nav-links">', '<div class="nav-links"><span class="prev page-numbers placeholder" aria-hidden="true">' . __('Edellinen', 'kettutesti') . '</span>', $pagination);
				}

				// If we're not outputting the next page link, append a placeholder with `visibility: hidden` to take its place.
				if (strpos($pagination, 'next page-numbers') === false) {
					$pagination = str_replace('</div>', '<span class="next page-numbers placeholder" aria-hidden="true">' . __('Seuraava', 'kettutesti') . '</span></div>', $pagination);
				}
				$this->pageData['search']['pagination'] = $pagination;

				break;
		}
	}

	public function addDataToModel(string $data, string $key)
	{

		$this->pageData['wp'][$key] = $data;
	}

	private function populateTermLinks(&$terms)
	{
		if (is_array($terms)) {

			foreach ($terms as $term) {
				$term->link = get_term_link($term);
			}
		}
	}

	private function prepareTaxonomy($useTemplate)
	{

		$this->populateACFLayouts('term_' . get_queried_object()->term_id);
	}

	// cannot be populated at constructor
	private function populateSlug($cx = null)
	{
		$slug = [
			'name' => null,
			'id'   => null,
		];

		global $post;
		if (!$post) {
			l('[!] No post in this page');

			return;
		}

		if ($cx) {
			$cx['_this']['slug'] = $slug;

			return;
		}

		$postTypeObj = get_queried_object();
		if (!empty($postTypeObj->rewrite['slug'])) {
			$slug['name'] = $postTypeObj->rewrite['slug'];
			$this->pageData['slug'] = $slug;

			return;
		}

		$slug['name'] = $post->post_name;
		$this->pageData['slug'] = $slug;

		l('[!] No post type?');
	}

	private function populateBreadcrumb()
	{
		$this->pageData['breadcrumb'] = [];

		$this->addToBreadcrumb(__('Etusivu', 'kettutesti'), home_url());

		global $post;

		if (is_single($post)) {

			/*$post_type = get_post_type( $post );
			$archive_link = get_post_type_archive_link( $post_type );

			if ( $archive_link && $post_type !== 'post' ) {
				$post_type_object = get_post_type_object( $post_type );

				$this->addToBreadcrumb( $post_type_object->label, $archive_link );
			}*/

			$this->addToBreadcrumb(get_the_title($post), get_permalink($post));
		} else if (is_page() && !is_front_page()) {

			$this->addToBreadcrumb(get_the_title($post), get_permalink($post));
		}
	}

	private function addToBreadcrumb($title, $url)
	{
		$this->pageData['breadcrumb'][] = [
			'title' => $title,
			'url'   => $url,
		];
	}
}
