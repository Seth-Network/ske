<?php

class Seth_Event_Bus {
	
	protected $listeners = array();
	
	/**
	 * If TRUE, the bus will store the listener's call methods in a cache for better performance
	 * TODO implement cache usage and check, if it worth it
	 * @var boolean
	 */
	protected $use_cache = true;
	
	/**
	 * If TRUE, the bus will call listeners which are added for an event's parent class when an event
	 * is posted
	 * 
	 * @var boolean
	 */
	protected $call_generic_listener = true;
	
	/**
	 * Sets and gets if the bus uses a cache for storing listener's methods
	 *
	 * @param   boolean   $value  Cache usage
	 * @return  mixed
	 */
	protected function cache($value=null) {
		if ( $value === null ) {
			return $this->use_cache;
		}
		$this->use_cache = $value;
		return $this;
	}
	
	/**
	 * Sets and gets if the bus will call listeners, which were added for an event's parent class, when the event
	 * is posted on the bus
	 *
	 * @param   boolean   $value
	 * @return  mixed
	 */
	protected function call_generic_listener($value=null) {
		if ( $value === null ) {
			return $this->call_generic_listener;
		}
		$this->call_generic_listener = $value;
		return $this;
	}
	
	/**
	 * Adds a new listener object for a specific event type to the bus. The event may be an object instance or
	 * the class name of the event class or an array of event classes. Whatever, the event class must extend the abstract event class Event.
	 * A listener will be added for all parent classes of the event too. The priority will determine the order in which the listeners
	 * will be called if an event is posted on the bus. The listener's method called can be any method, having only one 
	 * required parameter which is exactly the given $event class.
	 * 
	 * @param Event|String|Array(Event)|Array(String) $event
	 * @param Object $listener
	 * @param String|Event_Priority $priority
	 * @throws Exception
	 * @return void
	 */
	public function add_listener($event, $listener, $priority=NULL) {
		if ( !is_object($listener) ) {
			throw new Exception("Listener must be an object!");
		}
		
		// 
		$events = array();
		if ( is_array($event) ) {
			foreach ( $event as $e ) {
				 $tmp = new ReflectionClass($e);
				 $events[] = $tmp;
				if ( !$tmp->isSubclassOf("Event") ) {
					throw new Exception("Event class '". $tmp->name ."' must extend the abstract event class 'Event'");
				}
			}
		} else {
		 		$tmp = new ReflectionClass($event);
				$events[] = $tmp;
				if ( !$tmp->isSubclassOf("Event") ) {
					throw new Exception("Event class '". $tmp->name ."' must extend the abstract event class 'Event'");
				}
		}
		
		// get priority
		if ( $priority !== null && !($priority instanceof Event_Priority) ) {
			$priority = new Event_Priority($priority);
		} else if ( $priority === NULL ) {
			$priority = new Event_Priority(Event_Priority::Normal);
		}
		
		$listener_c = new ReflectionClass($listener);
		$listener_name = $listener_c->name;
	
		foreach ( $listener_c->getMethods(ReflectionMethod::IS_PUBLIC) as $method ) {
			
			// FIXME: Validate if this "owning" behavior is the correct one
			// Imagine an abstract listener class defining multiple common listener methods and
			// then you create a class extending this "listener class". When the newly created class
			// registers itself as a listener at this bus, all common listener methods wont be
			// used as they are not defined in the newly created class but in the parent class :(
			
			// check if method is owned by listener and there is just one parameter required
			if ( /*$method->getDeclaringClass()->name == $listener_name && */
				$method->getNumberOfRequiredParameters() == 1 ) {
				$params = $method->getParameters();
				$p1 = $params[0];	// get first (and only required) parameter
				foreach ( $events as $event ) {
					// check if required parameter is of type of the event
					if ( $p1->getClass() != null && $p1->getClass()->name == 
							$event->name ) {
						$this->__add_listener($event->name, $listener, $method->name, $priority->__toString());
						break;
					}
				}
			}
		}
	}
	
	/**
	 * Post an $event on the bus. If any listener cancels the event, this method immediatly returns. Returns
	 * the event.
	 * 
	 * @param Event $eventl
	 * @return Event
	 */
	public function post(Event $event) {
		$event_class = with(new ReflectionClass($event))->name;
		
		$listeners = array();
		
		$obj = $obj_o = new ReflectionClass($event);
		
		// check, if listeners for $event's parent classes should be called too?
		$b = $this->call_generic_listener();
		
		// fetch all listeners for the $event class AND its parent classes (if this bus is set to, @see call_inheritence_tree())
		do {
			if ( isset($this->listeners[$obj->name])) {
				$listeners = array_merge_recursive($listeners, $this->listeners[$obj->name]);
			} 
		} while ( $b && ($obj = $obj->getParentClass()) != null);
		
		// profiling event calls
		if (Kohana::$profiling === TRUE) {
			// add listener to statistics (for administrativ purpose)
			if ( self::$event_cache === null ) {
				self::$event_cache = Cache::instance()->get(SKE::CACHE_EVENTS, array());
			}
			$count = 0;
			if ( isset(self::$event_cache[$event_class]) ) {
				$postedBy = self::$event_cache[$event_class][1];
			}
			// get called Class
			if (version_compare(phpversion(), '5.4', '<')) {
				$stack = debug_backtrace(false);
			} else {
				$stack = debug_backtrace(false, 2);
			}
			
			$postedBy[( isset($stack[1]['class']) ? $stack[1]['class']:"direct")] = ( isset($stack[1]['file']) ) ? $stack[1]['file']:"n/a";
			
			self::$event_cache[$event_class] = array(count($listeners), $postedBy, $obj_o->getFileName());
			Cache::instance()->set(SKE::CACHE_EVENTS, self::$event_cache, SKE::CACHE_LIFETIME);
			
			$benchmark_events = Profiler::start('SKE/Events', get_class($event));
		}
		
		// call all listeners
		foreach ( $listeners as $prio_listeners ) {
			foreach ( $prio_listeners as $l ) {
				$listener_object = $l[0];
				$listener_method = $l[1];
				
				// call listener
				$listener_object->$listener_method($event);
				
				if ( $event->isCancelled() ) {
					break 2;
				}
			}
		}
		
		if (isset($benchmark_events)) {
			Profiler::stop($benchmark_events);
		}
		
		return $event;
	}
	
	/**
	 * Internal function to add a new listener object with a specific listener method for
	 * an event to this bus. The priority will determine the order in which the listeners
	 * will be called if an event is posted on the bus
	 * 
	 * @param String $event_class
	 * @param Object $listener
	 * @param String $method
	 * @param String $priority
	 */
	private function __add_listener($event_class, $listener, $method, $priority) {		
		if ( !isset($this->listeners[$event_class])) {
			$this->listeners[$event_class] = array();
		}
		
		if ( !isset($this->listeners[$event_class][$priority])) {
			$this->listeners[$event_class][$priority] = array();
		}
		
		$this->listeners[$event_class][$priority][] = array($listener, $method);
		
		// add listener to statistics (for administrativ purpose)
		if (Kohana::$profiling === TRUE) {
			if ( self::$listener_cache === null ) {
				self::$listener_cache = Cache::instance()->get(SKE::CACHE_EVENT_LISTENERS, array());
			}
			$c = new ReflectionClass($listener);
			self::$listener_cache[get_class($listener)] = array($event_class, $method, $priority, $c->getFileName());
			Cache::instance()->set(SKE::CACHE_EVENT_LISTENERS, self::$listener_cache, SKE::CACHE_LIFETIME);
		}
	}
	
	/**
	 * Cache array for event listener
	 * @var Array(String=>Array(String, String, int))
	 */
	protected static $listener_cache = null;
	protected static $event_cache = null;
}