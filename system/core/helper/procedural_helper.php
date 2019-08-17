<?php
/**
 * This helper is NOT OOP !
 * Created using procedural style to help common function (for easy use please auto load it in user.config.php)
 */
$_helper = new AppHelper();		// Make sure that this helper only creating one AppHelper instance... (Performance purpose)

/**
 * Used to generate absolute URL or framework compatible link
 *
 * @param $url		relative path to the site
 * @return string	absolute path to the site
 */
function site_url($url) {
	global $_helper;
	return $_helper->site_url($url);
}

/**
 * Used to redirect response to another page
 *
 * @param string $url			 => relative path of new response
 * @param bool $endResponse		 => determine whenever
 * @param bool $useLocation		 => use header location technique for redirecting
 * @param int $httpResponseCode	 => specify HTTP Response Code for redirecting
 * @return void
 */
function redirect_url($url, $endResponse = true, $useLocation = true, $httpResponseCode = 302) {
	Dispatcher::Redirect(site_url($url), $endResponse, $useLocation, $httpResponseCode);
}

function redirect($controller, $method = "index", array $params = array(), array $namedParams = array(), $preferSlash = PREFER_SLASH, $endResponse = true, $useLocation = true, $httpResponseCode = 302) {
	global $_helper;
	$url = $_helper->url($controller, $method, $params, $namedParams, $preferSlash);

	Dispatcher::Redirect($url, $endResponse, $useLocation, $httpResponseCode);
}

/**
 * Generate absolute path for resource type link.
 *
 * @param string $path	=> resource relative path based on installation folder
 * @return string		=> absolute path of resource so it'll never clash with url_rewrite module
 */
function base_url($path = "") {
	global $_helper;
	return $_helper->path($path);
}

// EOF: ./system/core/helper/procedural_helper.php
