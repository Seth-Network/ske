<?php

/**
 * This is an read-only interface for a group. This interface just provides a minimal set
 * of methods to get information about a group. Any specific interface methods, including write
 * access to the group's data depends on the implementation and are NOT part of this interface
 *
 * @author eth4n
 *
 */
interface Identity_Group extends Identity {
	// Virtual IDs for identities
	// Group
	const ALL = -1;
	const ADMIN = -2;
	const UNREGISTERED = -3;
	const REGISTERED = -4;
	const CREATOR = -5;
	const AUTHOR = -6;
	const OWNER = -8;
	
	/**
	* Returns an array of users which are members of this group
	* 
	* @return Array(Identity_User)
	*/
	public function users();
	

	/**
	 * Returns an array of subgroups
	 * 
	 * @return Array(Identity_Group)
	 */
	public function groups();
	
	/**
	 * Returns the parent group or NULL, if this group does not have a parent group
	 * 
	 * @return Identity_Group
	 */
	public function parent();
}
