<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request Client for internal execution
 *
 */
class Seth_Request_Client_Internal extends Kohana_Request_Client_Internal {

	public function execute_request(Request $request, Response $response)
	{
		// Create the class prefix
		$prefix = 'Controller_';

		// Directory
		$directory = $request->directory();

		// Controller
		$controller = $request->controller();

		if ($directory)
		{
			// Add the directory name to the class prefix
			$prefix .= str_replace(array('\\', '/'), '_', trim($directory, '/')).'_';
		}

		if (Kohana::$profiling)
		{
			// Set the benchmark name
			$benchmark = '"'.$request->uri().'"';

			if ($request !== Request::$initial AND Request::$current)
			{
				// Add the parent request uri
				$benchmark .= ' « "'.Request::$current->uri().'"';
			}

			// Start benchmarking
			$benchmark = Profiler::start('Requests', $benchmark);
		}

		// Store the currently active request
		$previous = Request::$current;

		// Change the current request to this request
		Request::$current = $request;

		// Is this the initial request
		$initial_request = ($request === Request::$initial);

		try
		{
			if ( ! class_exists($prefix.$controller))
			{
				var_dump($prefix.$controller);
				die();
				throw HTTP_Exception::factory(404,
						'The requested URL :uri was not found on this server.',
						array(':uri' => $request->uri())
						)->request($request);
			}

			// Load the controller using reflection
			$class = new ReflectionClass($prefix.$controller);

			if ($class->isAbstract())
			{
				throw new Kohana_Exception(
						'Cannot create instances of abstract :controller',
						array(':controller' => $prefix.$controller)
						);
			}



			// Create a new instance of the controller
			// Org code:
			#$controller = $class->newInstance($request, $response);
			// New code
			$controller = Kohana::$di->create($class->getName(), array($request, $response));

			// Invoke event that the controller is called
			if ( !Kohana::$event_bus->post(new Execution_Event_Controller($controller))->isCancelled() ) {

				// Run the controller's execute() method
				$response = $class->getMethod('execute')->invoke($controller);

				if ( ! $response instanceof Response)
				{
					// Controller failed to return a Response.
					throw new Kohana_Exception('Controller failed to return a Response');
				}
			}
		}
		catch (HTTP_Exception $e)
		{
			// Store the request context in the Exception
			if ($e->request() === NULL)
			{
				$e->request($request);
			}

			// Get the response via the Exception
			$response = $e->get_response();
		}
		catch (Exception $e)
		{
			// Generate an appropriate Response object
			$response = Kohana_Exception::_handler($e);
		}

		// Restore the previous request
		Request::$current = $previous;

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		// Return the response
		return $response;
	}

	/**
	 * See original documentation in Kohana_Request_Client_Internal->execute_request()
	 * This method is copied to implement event invocation before the main
	 * controller is called with its action!
	 * Further more the controller will be loaded in a managed environment using
	 * the SKE DI Container.
	 * ================================================================================
	 *
	 *
	 * Processes the request, executing the controller action that handles this
	 * request, determined by the [Route].
	 *
	 * 1. Before the controller action is called, the [Controller::before] method
	 * will be called.
	 * 2. Next the controller action will be called.
	 * 3. After the controller action is called, the [Controller::after] method
	 * will be called.
	 *
	 * By default, the output from the controller is captured and returned, and
	 * no headers are sent.
	 *
	 *     $request->execute();
	 *
	 * @param   Request $request
	 * @return  Response
	 * @throws  Kohana_Exception
	 * @uses    [Kohana::$profiling]
	 * @uses    [Profiler]
	 * @deprecated passing $params to controller methods deprecated since version 3.1
	 *             will be removed in 3.2
	 */
	// TODO rewrite method to be used in 3.3.3.1
	public function old_3_2_execute_request(Request $request, Response $response)
	{
		// Create the class prefix
		$prefix = 'controller_';

		// Directory
		$directory = $request->directory();

		// Controller
		$controller = $request->controller();

		if ($directory)
		{
			// Add the directory name to the class prefix
			$prefix .= str_replace(array('\\', '/'), '_', trim($directory, '/')).'_';
		}

		if (Kohana::$profiling)
		{
			// Set the benchmark name
			$benchmark = '"'.$request->uri().'"';

			if ($request !== Request::$initial AND Request::$current)
			{
				// Add the parent request uri
				$benchmark .= ' « "'.Request::$current->uri().'"';
			}

			// Start benchmarking
			$benchmark = Profiler::start('Requests', $benchmark);
		}

		// Store the currently active request
		$previous = Request::$current;

		// Change the current request to this request
		Request::$current = $request;

		// Is this the initial request
		$initial_request = ($request === Request::$initial);


		try
		{
			if ( ! class_exists($prefix.$controller))
			{
				throw new HTTP_Exception_404('The requested URL :uri was not found on this server.'. $prefix.$controller,
						array(':uri' => $request->uri()));
			}

			// Load the controller using reflection
			$class = new ReflectionClass($prefix.$controller);

			if ($class->isAbstract())
			{
				throw new Kohana_Exception('Cannot create instances of abstract :controller',
						array(':controller' => $prefix.$controller));
			}

			// Create a new instance of the controller
			// Org code:
			# $controller = $class->newInstance($request, $request->response() ? $request->response() : $request->create_response());
			// New code
			$controller = Kohana::$di->create($class->getName(), array($request, $request->response() ? $request->response() : $request->create_response()));

			// Invoke event that the main controller is called
			if ( !Kohana::$event_bus->post(new Execution_Event_Controller($controller))->isCancelled() ) {
				$class->getMethod('before')->invoke($controller);

				// Determine the action to use
				$action = $request->action();

				$params = $request->param();

				// If the action doesn't exist, it's a 404
				if ( ! $class->hasMethod('action_'.$action))
				{
					throw new HTTP_Exception_404('The requested URL :uri was not found on this server.',
							array(':uri' => $request->uri()));
				}

				$method = $class->getMethod('action_'.$action);
				$method->invoke($controller);

				// Execute the "after action" method
				$class->getMethod('after')->invoke($controller);
			}
		}
		catch (Exception $e)
		{
			// Restore the previous request
			if ($previous instanceof Request)
			{
				Request::$current = $previous;
			}

			if (isset($benchmark))
			{
				// Delete the benchmark, it is invalid
				Profiler::delete($benchmark);
			}

			// Re-throw the exception
			throw $e;
		}

		// Restore the previous request
		Request::$current = $previous;

		if (isset($benchmark))
		{
			// Stop the benchmark
			Profiler::stop($benchmark);
		}

		// Return the response
		return $request->response();
	}
	} // End Kohana_Request_Client_Internal
