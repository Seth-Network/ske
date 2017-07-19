<?php

class Seth_Route extends Kohana_Route {
	/**
	 * Configuration array of disabled routes
	 * @var Array(String)
	 */
	public static $config = null;
	
	/**
	 * Callback function to modify the parameter set in method matches().
	 * 
	 * @var Callable
	 */
	protected $modifier = NULL;
	
	/**
	 * Stores a named route and returns it. The "action" will always be set to
	 * "index" if it is not defined.
	 *
	 *     Route::set('default', '(<controller>(/<action>(/<id>)))')
	 *         ->defaults(array(
	 *             'controller' => 'welcome',
	 *         ));
	 *         
	 * @edit:
	 * This version of Route::set() will add an additional parameter to provide a callback to modify parameters,
	 * some profiling and the implementation of disabled routes via SKE config. THhe modifier will be called after
	 * the default parameters are applied and has to have the interface modifier(array $params):array 
	 *
	 * @param   string   route name
	 * @param   string   URI pattern
	 * @param   array    regex patterns for route keys
	 * @param	callable function to modify parameters
	 * @return  Route
	 */
	public static function set($name, $uri = NULL, $regex = NULL, $modifier= NULL) {
		
		if ( self::$config === null ) {
			self::$config = Kohana::$config->load(SKE::CFG_ROUTES_DISABLED, array())->as_array();
		}
		
		/*
		$routes = Cache::instance()->get(SKE::CACHE_ROUTES, array());
		if ( !isset($routes[$name]) ) {
			// track registered routes only when profiling is enabled
			if (Kohana::$profiling === TRUE) {
				# FIXME remove version check when removing older PHP version from list of supported version
				if (version_compare(phpversion(), '5.4', '<')) {
					$stack = debug_backtrace(0);
				} else {
					$stack = debug_backtrace(0, 1);
				}
				$routes[$name] = $stack[0]['file'] .'#'. $uri;
				Cache::instance()->set(SKE::CACHE_ROUTES, $routes, SKE::CACHE_LIFETIME);
			}
		} 
		// Return route object, but do not add it to local routes: Route can not be used to find a controller (route is disabled)
		else if ( array_search($name, self::$config) !== false ) {
			return new Route($uri, $regex);
		}*/
		$route = Route::$_routes[$name] = new Route($uri, $regex);
		$route->modifier($modifier);
		
		return $route;
		
		
	}
	
	/**
	 * Tests if the route matches a given Request. A successful match will return
	 * all of the routed parameters as an array. A failed match will return
	 * boolean FALSE.
	 *
	 *     // Params: controller = users, action = edit, id = 10
	 *     $params = $route->matches(Request::factory('users/edit/10'));
	 *
	 * This method should almost always be used within an if/else block:
	 *
	 *     if ($params = $route->matches($request))
	 *     {
	 *         // Parse the parameters
	 *     }
	 *
	 * @param   Request $request  Request object to match
	 * @return  array             on success
	 * @return  FALSE             on failure
	 */
	public function matches(Request $request) {
	 	$params = parent::matches($request);
	 	
	 	if ( $params !== false && is_array($params) && $this->modifier !== NULL ) {
	 		$params = call_user_func($this->modifier, $params);
	 	} 
	 	return $params;
	 }
	 
	
	/**
	 * Gets and sets the modifier callable for this route
	 *
	 * @param   Callable $value
	 * @return  Route|Callable
	 */
	public function modifier($value = NULL)
	{
		if ($value === NULL) {
			return $this->modifier;
		}
	
		$this->modifier = $value;
	
		return $this;
	}
	
	/**
	 * Gets and sets the URI pattern for the route
	 *
	 * @param   String  value
	 * @return  Route|String
	 */
	public function pattern($value = NULL)
	{
		if ($value === NULL) {
			return $this->_uri;
		}

		$this->_uri = $value;

		return $this;
	}
}