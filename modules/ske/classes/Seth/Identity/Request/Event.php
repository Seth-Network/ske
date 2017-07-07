<?php

abstract class Seth_Identity_Request_Event extends Request_Event {
	
	/**
	 * Identity found
	 * 
	 * @var Identity
	 */
	protected $identity = null;
	
	/**
	 * Identifier to search for
	 * 
	 * @var String
	 */
	protected $identitfier = "";
	
	
	public function __construct($identifier) {
		$this->identitfier($identifier);
	}
	
	/**
	 * Sets and gets the identifier this request event is looking for
	 *
	 * @param   String   $value  Identifier
	 * @return  String|Identity_Request_Event
	 */
	protected function identitfier($value=null) {
		if ( $value === null ) {
			return $this->identitfier;
		}
		$this->identitfier = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the identity found
	 *
	 * @param   Identity   $value  Identity
	 * @return  Identity|Identity_Request_Event
	 */
	protected function identity($value=null) {
		if ( $value === null ) {
			return $this->identity;
		}
		$this->identity = $value;
		return $this;
	}
}