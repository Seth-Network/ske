<?php

class Seth_Identity_User extends Seth_Identity implements Identity_User {
	
	
	
	protected $groups = array();

	
	public function groups() {
		return $this->groups;
	}
	
	public function add_group(Identity_Group $group) {
		$this->groups[] = $group;
		return $this;
	}
	
	/**
	 * Returns a SKE link object used to create a link or request to the identity
	 *
	 * @return Link
	 */
	public function link() {
		
	}
}
