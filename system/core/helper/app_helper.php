<?php
class AppHelper {
	private $templates = array(
		'a' => '<a href="%s"%s>%s</a>',
		'path' => '%s/%s',
		'url' => '%s/%s/%s%s',
		'site_url' => '%s/%s'
	);
	
	/**
	 * Create a <a> HTML element
	 * 
	 * @param string $content
	 * @param string $url
	 * @param array $attributes
	 * @return string of anchor (<a></a>) element
	 */
	public function a($content, $url = "#", array $attributes = array()) {
		$template = $this->templates["a"];
		$attr = "";
		foreach ($attributes as $key => $value) {
			$attr .= sprintf(' %s="%s"', $key, $value);
		}
		return sprintf($template, $url, $attr, $content);
	}
	
	/**
	 * Used to determine base / root directory of the web apps.
	 * If your web hosting doesn't support url_rewrite please use the prependBaseName for URL generation
	 * 
	 * @param bool $prependBaseName
	 * @return base URL of any resources
	 */
	private function BaseUrl($prependBaseName = true) {
		// Base URL generation for absolute location instead of relative
		if (strlen(FOLDER) > 0)
			$temp = "/" . FOLDER;
		else
			$temp = "";
		// Remove slash from the end
		if (strrpos($temp, "/") === strlen($temp) - 1) 
			$temp = substr($temp, 0, strlen($temp) - 1);
		if ($prependBaseName && defined("BASE_NAME")) {
			$temp .= "/" . BASE_NAME;
		}
		return $temp;
	}
	
	/**
	 * Used to generate base path for a resource file (CSS, JavaScript, etc)
	 * Don't use this for URL generation
	 * 
	 * @param string $path
	 * @return string
	 */
	public function path ($path) {
		$template = $this->templates["path"];
		return sprintf($template, $this->BaseUrl(false), $path);
	}

	/**
	 * Used to generate url of specific resource
	 *
	 * @param string $fqn
	 * @param string $method
	 * @param array $params
	 * @param array $namedParams
	 * @param bool $preferSlash
	 * @return string
	 */
	public function url($fqn, $method = "index", array $params = array(), array $namedParams = array(), $preferSlash = PREFER_SLASH) {
		// Change from $controller to $fqn (Fully Qualified Name) to suppot namespace
		// To create url with namespace please append '.' before controller class
		// Ex: Report.Sales
		if ($preferSlash) {
			$fqn = str_replace(".", "/", $fqn);
		}
		
		$template = $this->templates["url"];
		// $param used as container for appending parameter to the url
		$param = "";
		
		foreach ($params as $value) {
			$param .= "/$value";
		}
		
		foreach ($namedParams as $key => $value) {
			$param .= sprintf("/%s:%s", $key, $value);
		}
		
		return sprintf($template, $this->BaseUrl(), $fqn, $method, $param);
	}

	/**
	 * Check and generate absolute URL path from given URL
	 * Given url must be a relative path based on framework installation folder.
	 *
	 * @param $url
	 * @return string
	 */
	public function site_url($url) {
		// Some checking...
		$skipList = array("/", "http://", "https://", "ftp://", "mailto:");
		foreach ($skipList as $pattern) {
			if (strpos($url, $pattern) === 0) {
				return $url;		// OK return the given URL because absolute URL pattern detected !
			}
		}

		/**
		 * We will assume $url will be in correct format of relative url
		 * Format: controller[/method[/param[/named_param]]]
		 *
		 * URL generation for method, parameter(s) and named parameter(s) are omitted (assumed already in $url)
		 */
		return sprintf($this->templates["site_url"], $this->BaseUrl(), $url);
	}
}

// EOF: ./system/core/helper/app_helper.php
