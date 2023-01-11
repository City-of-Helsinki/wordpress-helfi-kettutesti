<?php

if (!defined('IS_DEV')) {
	define('IS_DEV', false);
}

require_once 'include/vendor/lightncandy/loader.php';
require_once 'include/io/File.php';
require_once 'include/debug/ThemeLogger.php';

ThemeLogger::setEnableLogging(true);
//set_exception_handler( 'ThemeLogger::logException' );
ThemeLogger::addFormatter(new TimestampFormatter());
ThemeLogger::addFormatter(new ColorFormatter());
ThemeLogger::addFormatter(new PaddingFormatter());
ThemeLogger::addWriter(new LogFileWriter(__DIR__ . '/../../debug.log', 1, 3)); // use wp default log file.

if (is_admin()) {
	require_once 'include/vendor/class-tgm-plugin-activation.php';
}

function startsWith($haystack, $needle)
{
	$length = strlen($needle);

	return (substr($haystack, 0, $length) === $needle);
}


function endsWith($haystack, $needle)
{
	$length = strlen($needle);
	if ($length == 0) {
		return true;
	}

	return (substr($haystack, -$length) === $needle);
}


require_once 'include/wp/SimpleAdminNotice.php';
require_once 'include/wp/TransientCache.php';
require_once 'include/wp/AbstractSite.php';
require_once 'include/wp/AbstractSiteModel.php';
require_once 'include/wp/WPMenuBuilder.php';

require_once 'include/wp/HandlebarsRenderer.php';

require_once 'include/utils/getVar.php';
require_once 'include/utils/getFilesRecursive.php';

require_once 'include/io/decoders/IDecoder.php';
require_once 'include/io/decoders/JSONDecoder.php';
require_once 'include/io/encoders/IEncoder.php';
require_once 'include/io/encoders/JSONEncoder.php';

require_once 'include/io/JSONResponse.php';
require_once 'include/net/HTTPHeaders.php';
require_once 'include/net/URL.php';
require_once 'include/net/Session.php';

require_once 'include/TemplateFunctions.php';

if (!function_exists('l')) {
	function l($msg)
	{
		ThemeLogger::log($msg);
	}
}

add_theme_support('post-thumbnails');
add_theme_support('html5');
add_theme_support('menus');

require_once 'include/SiteModel.php';
require_once 'include/Site.php';




global $site;
$site = new Site(IS_DEV);
