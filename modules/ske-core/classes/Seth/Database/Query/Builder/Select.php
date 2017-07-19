<?php

/**
 * This is a fixed child class from kohanas selection builder
 * 
 * #	Tag			Date		Author	Description
 * --------------------------------------------------------------------------------------------------------------------------------
 * 1	#0000001	22/12/2014	eth4n	Fixed a bug when using UNION and ORDER BY together.
 * 
 * @author eth4n
 *
 */
class Seth_Database_Query_Builder_Select extends Kohana_Database_Query_Builder_Select {
	
	/**
	 * Compile the SQL query and return it.
	 *
	 * @param   mixed  $db  Database instance or name of instance
	 * @return  string
	 */
	public function compile($db = NULL) {
		#0000001 Reset all ORDER BYs but the last one
		if ( !empty($this->_order_by) && !empty($this->_union)) {
	    	$this->_order_by = array();
	    	for($i=0;($i+1)<count($this->_union);$i++) {
	    		
	    		$this->_union[$i]['select']->_order_by = array();
	    	}
	    }
		return parent::compile($db);
	}
}