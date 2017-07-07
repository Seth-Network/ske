<?php


class Seth_ORM extends Kohana_ORM {
	
	protected $_size = 0;
	
	/**
	 * Returns the size of this tupel in kilo bytes.
	 * 
	 * @return int
	 */
	public function size() {
		if ( $this->_size == 0 ) {
			foreach ( $this->_object as $c => $v ) {
				$this->_size += strlen($v); 
			}
		}
			
		return round($this->_size / 1024, 2);
	}
	
	public function object_name() {
		return $this->_object_name;
	}
	
	public function has_pending() {
		return ( count($this->_db_pending) > 0 );
	}
	
	
	/**
	 * Count the number of records currently queried. Resets this query object (if set) and returns the number.
	 *
	 * @param boolean $reset Reset this query object after counting the number of queried objects
	 * @return integer
	 */
	public function count($reset=true) {
		$addSelect = true;
		$grouped = false;

		$limits = array();
			
		// check if a select is available, remove limit and look for a group by
		foreach ($this->_db_pending as $key => $method) {
			if ($method['name'] == 'select') {
				$addSelect = false;
			} elseif ( $method['name'] == 'group_by' ) {
				$grouped = true;
			} elseif ( $method['name'] == 'limit' ) {
				// Ignore any limits for now
				$limits[] = $method;
				unset($this->_db_pending[$key]);
			}
		}
			
		$this->_build(Database::SELECT);

		if ( $addSelect ) { 
			$this->_db_builder->select(array(DB::expr('COUNT("'. $this->_object_name .'.'. $this->_primary_key.'")'), 'records_found'));
		}
		// build inner sql for fetching requested rows
		$innerSql =  $this->_db_builder->from(array($this->_table_name, $this->_object_name))->compile($this->_db);

		if ( $grouped || !$addSelect) {
			// count result
			$sql = "SELECT COUNT(*) as total FROM ($innerSql) as tmp_row_count";
			$result = DB::query(Database::SELECT, $sql)->as_object()->execute($this->_db)->current()->total;
		} else {
			$result = DB::query(Database::SELECT, $innerSql)->as_object()->execute($this->_db)->current();
			
			if ( $result === false ) {
				$result = 0;
			} else {
				$result = $result->records_found;
			}
		}

		// Add back in limits
		$this->_db_pending = array_merge($this->_db_pending, $limits);

		if ( $reset ) {
			$this->reset();
		}
		

		// Return the total number
		return $result;
	}


}