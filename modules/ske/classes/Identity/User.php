<?php

/**
 * This is an read-only interface for an user identity. This interface just provides a minimal set
 * of methods to get information about a user. Any specific interface methods, including write
 * access to the user's data depends on the implementation and are NOT part of this interface
 *
 * @author eth4n
 *
 */
interface Identity_User extends Identity {

	// User
	const GUEST = -1;		// Virtual ID
	const ROOT = -2;
	const SYSTEM = -99;
	

	/**
	 * Returns an array of groups this user belongs to
	 * 
	 * @return Array(Identity_Group)
	 */

	public function groups();
	
}
