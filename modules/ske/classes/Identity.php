<?php

/**
 * This is an read-only interface for an identity which can be used for
 * users, groups etc. This interface just provides a minimal set
 * of methods to work with an identity
 * 
 * @author eth4n
 *
 */
interface Identity {
	
	/**
	 * Returns the unique identification number of this identity
	 * 
	 * @return int
	 */
	public function id();
	
	/**
	 * Returns a SKE link object used to create a link or request to the identity
	 * 
	 * @return Link
	 */
	public function link();

}