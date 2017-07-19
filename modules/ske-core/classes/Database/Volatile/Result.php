<?php

class Database_Volatile_Result extends Database_Result {

	/**
	 * Creates a volatile database result object using another result object
	 * as base and imports the result data if it is wished. If $importResult is
	 * set to FALSE, the returned result object will be empty and you may add
	 * new entries with Database_Volatile_Result::add()
	 *
	 * @param Database_Result $result
	 * @param boolean $importResult
	 * @return Database_Volatile_Result
	 */
	public static function convert(Database_Result &$result, $importResult=true) {
		return new Database_Volatile_Result(($importResult ? $result->as_array():array()), $result->_query, $result->_as_object, $result->_object_params);
	}

	/**
	 * Sets the total number of rows and stores the result locally.
	 *
	 * @param   mixed   query result
	 * @param   string  SQL query
	 * @return  void
	 */
	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL) {
		parent::__construct($result, $sql, $as_object, $params);

		$this->_total_rows = count($result);
	}

	/**
	 * This will add a row to the current result set and rewind the result object.
	 *
	 * @param mixed $row
	 */
	public function add($row) {
		$this->_result[] = $row;
		$this->_total_rows++;
		$this->rewind();
	}


	public function __destruct() {

		unset($this->_result);
	}
	/**
	 * Seeks to a position
	 *
	 * @param int $offset
	 * @return boolean
	 */
	public function seek($offset) {
		if ($this->offsetExists($offset)) {
			// Set the current row to the offset
			$this->_current_row = $offset;

			return TRUE;
		} else {
			return FALSE;
		}
	}
	/**
	 * Return the current element
	 *
	 * @return mixed
	 */
	public function current() {

		if ( ! $this->seek($this->_current_row)) {
			return NULL;
		}

		return $this->_result[$this->_current_row];
	}
}