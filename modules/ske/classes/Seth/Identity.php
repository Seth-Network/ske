<?php

abstract class Seth_Identity implements Identity {

	protected $id = 0;
	protected $name = "";
	
	public function __construct($id, $name="") {
		$this->id = $id;
		$this->set_name($name);
	}
	public function id() {
		return $this->id;
	}
	public function set_id($id) {
		$this->id = $id;
	}
	
	public function set_name($name) {
		$this->name = $name;
	}
	
	public function name() {
		return $this->name;
	}

}