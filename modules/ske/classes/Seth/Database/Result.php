<?php defined('SYSPATH') or die('No direct script access.');

abstract class Seth_Database_Result extends Kohana_Database_Result {
	
	/**
	 * Gets the result's query string
	 * 
	 * @return String
	 */
	public function query() {
		return $this->_query;
	}
}