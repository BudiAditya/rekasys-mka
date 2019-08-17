<?php
/**
 * Class SimpleRouterHook
 *
 * This class will re-routing all user request to REROUTE_NEW_NAMESPACE, REROUTE_NEW_CONTROLLER, REROUTE_NEW_METHOD
 * Therefore these three keys must be defined before
 */
class SimpleRouterHook implements IRouterHook {

	/**
	 * Nothing to do
	 *
	 * @param Router $router
	 * @return void
	 */
	public function RouterCreated(Router $router) {	}

	/**
	 * Nothing to do
	 *
	 * @param Router $router
	 * @return void
	 */
	public function PreInitialize(Router $router) {	}

	/**
	 * Nothing to do..
	 *
	 * @param Router $router
	 * @param RouteData $routeData
	 * @return null|RouteData
	 */
	public function PostIpAddressDetected(Router $router, RouteData $routeData) { }

	/**
	 * SimpleRouteHook will re-route all request to REROUTE_NEW_XXX
	 *
	 * @see Router::ExtractRequest
	 * @param Router $router
	 * @param RouteData $routeData
	 * @return null|RouteData
	 */
	public function PreExtractRequest(Router $router, RouteData $routeData) {
		$routeData = new RouteData($routeData);
		$routeData->Namespace = REROUTE_NEW_NAMESPACE;
		$routeData->ControllerName = REROUTE_NEW_CONTROLLER;
		$routeData->MethodName = REROUTE_NEW_METHOD;
		$routeData->PreventNextSequence = true;

		return $routeData;
	}

	/**
	 * Routing done at PreExtractRequest...
	 *
	 * @see Router::ExtractRequest
	 * @param Router $router
	 * @param RouteData $routeData
	 * @return void
	 */
	public function PostExtractRequest(Router $router, RouteData $routeData) {	}
}

// EoF: simple_router_hook.php