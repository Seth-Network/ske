<?php defined('SYSPATH') or die('No direct script access.');

class Seth_Identity_User extends Seth_Identity implements Identity_User {
	protected $groups = array();

	
	public function groups() {
		return $this->groups;
	}
	
	public function add_group(Identity_Group $group) {
		$this->groups[] = $group;
		return $this;
	}
}
