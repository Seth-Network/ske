<?php defined('SYSPATH') or die('No direct script access.');

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
		return array();
	}
	
	/**
	 * Returns the parent group or NULL, if this group does not have a parent group
	 *
	 * @return Identity_Group
	 */
	public function parent() {
		return ( $this->id() != Identity_Group::ALL ) ? Identity_Provider::factory()->get_group(Identity_Group::ALL):null;
	}

}
