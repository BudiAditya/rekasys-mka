<?php
/**
 * Interface IRouterHook
 *
 * This interface act as contract for Router class. This contract able to modify user request. These methods will be called all along Router sequence
 * Please read each method description. It will tell when this method called and their usage.
 *
 * IMPORTANT:
 * 		If you want to stop Router sequence then you MUST return an instance of RouteData with RouteData::PreventNextSequence value to true
 *
 * @see RouteData::PreventNextSequence
 */
interface IRouterHook {
	/**
	 * This callback is called when Router class instantiated for the first time. Since Router class is singleton then this only called once
	 * Called by 'index.php' script.
	 *
	 * USAGE: Logging purpose. No user data at this moment.
	 *
	 * @param Router $router
	 * @return void
	 */
	public function RouterCreated(Router $router);

	/**
	 * This callback called before Router initialize sequence
	 *
	 * USAGE: Logging purpose. No user data at this moment.
	 *
	 * @param Router $router
	 * @return void
	 */
	public function PreInitialize(Router $router);

	/**
	 * This callback called after Client IP address and AJAX request detected.
	 * NOTE: $ipAddress is passed as reference
	 *
	 * USAGE:
	 * 	- Change user IP detection / Request Origin (NOT RECOMMENDED to change these value)
	 * 	- Force re-route without any user URL process. In this step user URL request is not detected yet.
	 *
	 * @param Router $router
	 * @param RouteData $routeData
	 * @return null|RouteData
	 */
	public function PostIpAddressDetected(Router $router, RouteData $routeData);

	/**
	 * This callback called before user URL request being examined for their Controller, Method, Namespace, Parameters
	 * If $routeData already changed / modified occasionally then this method should return an instance of RouteData
	 *
	 * USAGE:
	 * 	- This step is mainly used to re-route user request! Why? Since this method execute before user request being examined and we able to skip Router::ExtractRequest sequence
	 * 	- Re-route based on regular expression from URL can be performed here
	 *
	 * @see Router::ExtractRequest
	 *
	 * @param Router $router
	 * @param RouteData $routeData
	 * @return null|RouteData
	 */
	public function PreExtractRequest(Router $router, RouteData $routeData);

	/**
	 * This callback called after RouteData::RawData is being examined and extracted into Controller, Method, Namespace, Parameters
	 * This is the last chance where you can interfere with user request. You able to change the Controller, Method, Namespace, Parameters here and then Dispatcher will dispatch these request
	 *
	 * USAGE: Re-Route user request. Request extraction is require a lot of CPU cycle... routing in this step should be avoided.
	 * 		  Perform re-route here if your routing is require controller / method to be known first before decide re-route
	 *
	 * @see Router::ExtractRequest
	 * @param Router $router
	 * @param RouteData $routeData
	 * @return void
	 */
	public function PostExtractRequest(Router $router, RouteData $routeData);
}

// EoF: i_router_hook.php