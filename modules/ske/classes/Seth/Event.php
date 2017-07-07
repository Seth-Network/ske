<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @author eth4n
 * @event
 */
abstract class Seth_Event {
	
	/**
	 * Flag to set if this event is cancelled
	 * @var boolean
	 */
	protected $cancelled = false;
	
	/**
	 * Flag if this event can be cancelled
	 * @var boolean
	 */
	protected $cancellable = true;
	
	/**
	 * Creates a new event object
	 */
	public function __construct() {
	}
	
	/**
	 * Sets and gets if the event can be cancelled
	 *
	 * @param boolean $value
	 * @return mixed
	 */
	protected function cancellable($value = null) {
		if ($value === null) {
			return $this->cancellable;
		}
		$this->cancellable = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the cancelled status for the event. This method will throw an exception if this event can't be canceleld
	 *
	 * @see Event::cancellable()
	 * @param boolean $value
	 * @return mixed
	 * @throws Exception
	 */
	public function cancelled($value = null) {
		if ($value === null) {
			return $this->cancelled;
		}
		if (!$this->cancellable()) {
			throw new Exception("You can not cancel this event: It is not cancellable");
		}
		$this->cancelled = $value;
		return $this;
	}
	
	/**
	 * Returns TRUE if the event was cancelled by one or more listeners
	 *
	 * @return boolean
	 */
	public function isCancelled() {
		return $this->cancelled;
	}
	
	/**
	 * Sets cancelled status of the event. Returns the former cancelled state of the eventThis method will throw an exception if this event can't be canceleld
	 *
	 * @see Event::cancellable()
	 * @param boolean $cancel        
	 * @return boolean
	 * @throws Exception
	 */
	public function setCancelled($cancel = true) {
		$tmp = $this->cancelled;
		$this->cancelled($cancel);
		
		return $tmp;
	}
}
