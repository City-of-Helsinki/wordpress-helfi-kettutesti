<?php


class TemplateFunctions
{

	private $model;
	private $isDev;

	public function __construct($model, $isDev)
	{
		$this->model = $model;
		$this->isDev = $isDev;
	}

	public function disable_user_profile()
	{

		if (is_admin()) {

			$user = wp_get_current_user();

			if (current_user_can('subscriber')) {
				$url = trailingslashit(home_url()) . __('omat-tiedot/', 'kettutesti');
				header("Location: " . $url);
				die();
				//wp_die( 'You are not allowed to edit the user profile.' );
			}
		}
	}

	public function show_admin_bar()
	{
		if (!current_user_can('manage_options')) {
			return false;
		} else {
			return true;
		}
	}

	public function load_dashicons_front_end()
	{
		wp_enqueue_style('dashicons');
	}

	public function gform_ajax_spinner_url($image_src, $form)
	{
		return get_template_directory_uri() . '/img/spinner.svg';
	}

	public function wp_redirect()
	{
		l('--wp_redirect');
	}

	public function serve_404()
	{
		//https://richjenks.com/wordpress-throw-404/
		// 1. Ensure `is_*` functions work
		global $wp_query;
		$wp_query->set_404();

		// 2. Fix HTML title
		add_action('wp_title', function () {
			return '404: Not Found';
		}, 9999);

		// 3. Throw 404
		status_header(404);
		nocache_headers();

		// 4. Show 404 template
		require get_404_template();

		// 5. Stop execution
		die();
	}
}
