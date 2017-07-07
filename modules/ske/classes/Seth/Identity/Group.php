<?php

class Seth_Identity_Group extends Seth_Identity implements Identity_Group {
	protected $users = array();

	/**
	 *
	 * Enter description here ...
	 * @return Array(Identity_User)
	 */
	public function users() {
		return $this->users;
	}

	/**
	 * Returns an array of subgroups
	 *
	 * @return Array(Identity_Group)
	 */
	public function groups() {
		# TODO
		return array();
	}
	
	/**
	 * Returns the parent group or NULL, if this group does not have a parent group
	 *
	 * @return Identity_Group
	 */
	public function parent() {
		# TODO
		return null;
	}
	
	/**
	 * Returns a SKE link object used to create a link or request to the identity
	 *
	 * @return Link
	 */
	public function link() {
	
	}
}
