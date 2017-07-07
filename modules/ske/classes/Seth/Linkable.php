<?php

interface Seth_Linkable {
	
	/**
	 * Returns the class name of the link class used to create links to this
	 * object. The returned class name must extend the Seth_Link class
	 * 
	 * @return String
	 */
	public function get_link_class();
}