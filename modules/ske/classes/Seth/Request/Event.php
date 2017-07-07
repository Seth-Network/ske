<?php

abstract class Seth_Request_Event extends Event {
	
	const SUCCESS = "success";
	const FAILED = "failed";
	
	protected $response = self::FAILED;
	
	/**
	 * Sets and gets the event's response
	 * Also, if this event's result is set to SUCCESS, this event will be cancelled
	 *
	 * @param   String   $value  Response
	 * @return  String|Request_Event
	 */
	protected function response($value=null) {
		if ( $value === null ) {
			return $this->response;
		}
		$this->response = $value;
		
		if ( $value == self::SUCCESS && $this->cancellable() ) {
			$this->cancelled(true);
		}
		
		return $this;
	}
	
}