<?php

class Seth_Content_Spot_Event extends Event {
	
	protected $id = "";
	
	protected $scope = "";
	
	protected $content = null;
	
	protected $default = "";
	
	/**
	 * Sets and gets the event's id
	 *
	 * @param String $value
	 * @return String|Content_Spot_Event
	 */
	public function id($value = null) {
		if ($value === null) {
			return $this->id;
		}
		$this->id = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the event's default value for the content spot
	 *
	 * @param String $value
	 * @return String|Content_Spot_Event
	 */
	public function default($value = null) {
		if ($value === null) {
			return $this->default;
		}
		$this->default = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the event's scope
	 *
	 * @param String $value
	 * @return String|Content_Spot_Event
	 */
	public function scope($value = null) {
		if ($value === null) {
			return $this->scope;
		}
		$this->scope = $value;
		return $this;
	}
	
	/**
	 * Sets and gets the event's content
	 *
	 * @param String $value
	 * @return String|Content_Spot_Event
	 */
	public function content($value = null) {
		if ($value === null) {
			return $this->content;
		}
		$this->content = $value;
		return $this;
	}
}